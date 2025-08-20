<?php

class FlashMessage
{

    public static string $key = 'flash-message';

    public static int $timeout = 4; // Second

    public static string $handler = 'cookie'; // 'meta' or 'cookie'

    public static function get($user_id = null)
    {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }

        $get = [];
        if (self::$handler == "cookie") {

            $cookie = ($_COOKIE[self::$key] ?? "[]");
            if (is_string($cookie) && is_array(json_decode(stripslashes_deep($cookie), true))) {
                $get = json_decode(stripslashes_deep($cookie), true);
            }
        } else {
            $get = get_user_meta($user_id, self::$key, true);
        }
        if (empty($get)) {
            return $get;
        }

        if (isset($get['type']) and isset($get['data']) and isset($get['expire']) and (int)$get['expire'] >= time()) {
            // First Clean
            self::clean($user_id);

            // Return
            return $get;
        }

        return [];
    }

    public static function set($data = '', $type = 'success', $user_id = null): bool
    {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }

        $args = [
            'type' => $type,
            'data' => $data,
            'expire' => current_time('timestamp') + self::$timeout
        ];

        if (self::$handler == "cookie") {
            setcookie(self::$key, json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), (time() + self::$timeout), COOKIEPATH, COOKIE_DOMAIN);
        } else {
            update_user_meta($user_id, self::$key, $args);
        }

        // Return
        return true;
    }

    public static function clean($user_id = null): bool
    {
        if (is_null($user_id)) {
            $user_id = get_current_user_id();
        }

        if (self::$handler == "cookie") {
            if (isset($_COOKIE[self::$key])) {
                unset($_COOKIE[self::$key]);
                @setcookie(self::$key, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
            }
        } else {
            update_user_meta($user_id, self::$key, []);
        }

        return true;
    }
}
