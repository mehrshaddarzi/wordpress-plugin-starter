<?php

namespace CustomTable;

class Base
{

    public static function slug(): string
    {
        return 'table-name';
    }

    public static function title(): string
    {
        return '';
    }

    public static function primary_key(): string
    {
        return 'ID';
    }

    public static function get_json_fields(): array
    {
        return ['meta'];
    }

    public static function updated_at(): array
    {
        return [];
    }

    public static function default(): array
    {
        return array(
            'user_id' => get_current_user_id(),
            'status' => 0,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'meta' => []
        );
    }

    public static function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . static::slug();
    }

    public static function insert($arg = [], $fire_after_hook = true): array
    {
        global $wpdb;

        $defaults = static::default();
        $args = wp_parse_args($arg, $defaults);

        $args = apply_filters('ct_insert_data_' . static::table(), $args);

        foreach (static::get_json_fields() as $key) {
            $args[$key] = json_encode($args[$key], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $wpdb->insert(
            static::table(),
            $args
        );

        if ($wpdb->last_error !== '') :
            return ['status' => false, 'message' => $wpdb->last_error];
        endif;

        if ($fire_after_hook) {
            do_action('ct_insert_' . static::table(), $wpdb->insert_id, $args);
        }

        return [
            'status' => true,
            'id' => $wpdb->insert_id
        ];
    }

    public static function get($id)
    {
        global $wpdb;

        // Pre Get data
        $pre = apply_filters('pre_get_ct_' . static::slug(), null, $id);
        if (!is_null($pre)) {
            return $pre;
        }

        // Check From Cache
        $get_cache = wp_cache_get($id, 'ct-' . static::slug());
        if (false === $get_cache) {

            // Get From MySQL
            $sql = $wpdb->prepare("SELECT * FROM `" . static::table() . "` WHERE `" . static::primary_key() . "` = %d", $id);

            // Set Debug
            if (WP_DEBUG === true) {
                error_log($sql);
            }

            // Get Row
            $row = $wpdb->get_row($sql, ARRAY_A);
            if (is_null($row)) {
                return false;
            }

            // Prepare Item
            $prepare = static::prepare($row);

            // Set Cache
            wp_cache_set($id, $prepare, 'ct-' . static::slug());

            // Return
            return $prepare;
        }

        // Return From Cache
        return $get_cache;
    }

    public static function get_field($id, $fields = [])
    {
        $list = self::list([
            'order' => 'ASC',
            'offset' => '0',
            'number' => '1',
            'query' => [
                [
                    'key' => static::primary_key(),
                    'compare' => '=',
                    'value' => $id
                ]
            ],
            'fields' => $fields
        ]);
        if (empty($list)) {
            return [];
        }

        return $list[0];
    }

    public static function update($id, $arg = [], $fire_after_hook = true): array
    {
        global $wpdb;

        $arg = apply_filters('pre_ct_update_data_' . static::table(), $arg, $id);

        $changed = [];
        $item = static::get($id);
        foreach ($arg as $key => $value) {

            $isChanged = apply_filters('ct_is_changed_value_' . static::table(), ($value != $item[$key]), $key, $item, $value, $id);
            if ($isChanged) {
                $changed[$key] = $value;
            }
        }

        if (!empty($changed)) {

            foreach (static::get_json_fields() as $key) {
                if (isset($changed[$key])) {
                    $changed[$key] = json_encode($changed[$key], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            $updated_at = static::updated_at();
            if (!empty($updated_at) and isset($updated_at['name']) and !array_key_exists($updated_at['name'], $changed)) {
                $changed[$updated_at['name']] = $updated_at['value'];
            }

            $wpdb->update(
                static::table(),
                apply_filters('ct_update_data_' . static::table(), $changed, $item, $id),
                array(static::primary_key() => $id)
            );

            if ($wpdb->last_error !== '') :
                return ['status' => false, 'message' => $wpdb->last_error];
            endif;

            if ($fire_after_hook) {
                do_action('ct_update_' . static::table(), $id, $arg, $changed);
            }

            wp_cache_delete($id, 'ct-' . static::slug());
        }

        return ['status' => true, 'id' => $id];
    }

    public static function delete($value = '', $column = null): array
    {
        global $wpdb;

        if (is_null($column)) {
            $column = static::primary_key();
        }

        $pre = apply_filters('ct_pre_delete_' . static::table(), null, $value, $column);
        if (!is_null($pre)) {
            return $pre;
        }

        do_action('ct_before_delete_' . static::table(), $value, $column);

        $wpdb->delete(
            static::table(),
            array(
                $column => $value,
            )
        );

        do_action('ct_deleted_' . static::table(), $value, $column);

        return [
            'status' => true,
            'rows_affected' => $wpdb->rows_affected
        ];
    }

    public static function prepare($item)
    {
        // Json Field
        foreach ($item as $key => $value) {
            if (in_array($key, static::get_json_fields())) {
                if (empty($value)) {
                    $item[$key] = [];
                } else {
                    $item[$key] = json_decode($value, true, 512, JSON_UNESCAPED_UNICODE);
                }
            }
        }

        return apply_filters('ct_prepare_' . static::table(), $item);
    }

    public static function list($arg = []): array|object
    {
        global $wpdb;

        $default = [
            'orderby' => static::primary_key(),
            'order' => 'DESC',
            'offset' => '',
            'number' => '',
            'query' => [],
            'fields' => 'all',
            'where' => '',
            // Return With Prepare Items
            'prepare' => false
        ];
        $args = wp_parse_args($arg, $default);

        // Fields For SELECT
        if (empty($args['fields']) || $args['fields'] == "all" || $args['fields'] == "*") {
            $args['curd'] = '*';
        } elseif ($args['fields'] == 'count') {
            $args['curd'] = 'COUNT(*)';
        } else {
            if (is_array($args['fields'])) {
                $args['curd'] = implode(', ', $args['fields']);
            } else {
                $args['curd'] = $args['fields'];
            }
        }

        // Table
        $sql = "SELECT {$args['curd']} FROM " . static::table();

        // Query
        if (!empty($args['query'])) {

            $where = [];
            foreach ($args['query'] as $clause) {
                $whereClause = static::get_sql_for_clause($clause);
                if (!empty($whereClause['where'])) {
                    $where[] = $whereClause['where'][0];
                }
            }

            if (!empty($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
        }

        // Sql Custom Where
        if (!empty($args['where'])) {
            $sql .= $args['where'];
        }

        // Order
        if (!empty($args['orderby']) and !empty($args['order'])) {
            $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        }

        // Number and Offset
        if (!empty($args['number'])) {
            if (!empty($args['offset'])) {
                $sql .= " LIMIT {$args['offset']},{$args['number']}";
            } else {
                $sql .= " LIMIT {$args['number']}";
            }
        }

        // Get SQL
        $sql = apply_filters('ct_sql_' . static::table(), $sql, $args);

        // Check Cache
        $key = md5($sql);
        $cache = wp_cache_get($key, 'ct-query-' . static::slug());
        if ($cache) {
            return $cache;
        }

        // Set Debug
        if (WP_DEBUG === true) {
            error_log($sql);
        }

        // Get results
        $results = $wpdb->get_results($sql, ARRAY_A);

        // Set Cache
        wp_cache_set($key, $results, 'ct-query-' . static::slug());

        // Check Empty
        if (empty($results)) {
            return [];
        }

        // If Prepare Items
        if ($args['prepare'] === true) {
            return array_map(function ($item) {
                return static::prepare($item);
            }, $results);
        }

        return $results;
    }

    public static function get_sql_for_clause($clause): array
    {
        global $wpdb;

        // @see https://github.com/berlindb/core/blob/b46689ff90b778da0456d7e2c5ccf0764bbce9d6/compare.php
        $sql_chunks = array(
            'where' => array(),
            'join' => array(),
        );

        if (isset($clause['compare'])) {
            $clause['compare'] = strtoupper($clause['compare']);
        } else {
            $clause['compare'] = isset($clause['value']) && is_array($clause['value']) ? 'IN' : '=';
        }

        if (!in_array(
            $clause['compare'], array(
            '=',
            '!=',
            '>',
            '>=',
            '<',
            '<=',
            'LIKE',
            'NOT LIKE',
            'IN',
            'NOT IN',
            'BETWEEN',
            'NOT BETWEEN',
            'EXISTS',
            'NOT EXISTS',
            'REGEXP',
            'NOT REGEXP',
            'RLIKE',
        ), true
        )) {
            $clause['compare'] = '=';
        }

        $compare = $clause['compare'];
        $key = $clause['key'];

        // Column name and value.
        if (array_key_exists('key', $clause) && array_key_exists('value', $clause)) {

            $column = sanitize_key($clause['key']);
            $value = $clause['value'];

            if (in_array($compare, array('IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'), true)) {
                if (!is_array($value)) {
                    $value = preg_split('/[,\s]+/', $value);
                }
            } else {
                $value = trim($value);
            }

            switch ($compare) {
                case 'IN':
                case 'NOT IN':
                    $compare_string = '(' . substr(str_repeat(',%s', count($value)), 1) . ')';
                    $where = $wpdb->prepare($compare_string, $value);
                    break;

                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $value = array_slice($value, 0, 2);
                    $where = $wpdb->prepare('%s AND %s', $value);
                    break;

                case 'LIKE':
                case 'NOT LIKE':
                    $value = '%' . $wpdb->esc_like($value) . '%';
                    $where = $wpdb->prepare('%s', $value);
                    break;

                // EXISTS with a value is interpreted as '='.
                case 'EXISTS':
                    $compare = '=';
                    $where = $wpdb->prepare('%s', $value);
                    break;

                // 'value' is ignored for NOT EXISTS.
                case 'NOT EXISTS':
                    $where = '';
                    break;

                default:
                    $where = $wpdb->prepare('%s', $value);
                    break;

            }

            if ($where) {
                $sql_chunks['where'][] = "{$column} {$compare} {$where}";
            }
        }

        return $sql_chunks;
    }

}
