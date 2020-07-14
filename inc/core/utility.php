<?php

namespace WP_PLUGIN\core;
class Utility
{

    /*------------------------------------------------------------
     - WP Email Api
     ------------------------------------------------------------/

    /**
     * Send Email
     *
     * @param $to
     * @param $subject
     * @param $content
     * @return bool
     */
    public static function send_mail($to, $subject, $content)
    {
        //Email Template
        $email_template = wp_normalize_path(\WP_APPLE_APPS::$plugin_path . '/templates/email.php');
        if (trim(\WP_APPLE_APPS::$Template_Engine) != "") {
            $template = wp_normalize_path(path_join(get_template_directory(), '/wp-apple-apps/email.php'));
            if (file_exists($template)) {
                $email_template = $template;
            }
        }

        //Get option Send Mail
        $opt = get_option('wp_apple_apps_email_opt');

        //Set To Admin
        if ($to == "admin") {
            $to = get_bloginfo('admin_email');
        }

        //Email from
        $from_name  = $opt['from_name'];
        $from_email = $opt['from_email'];

        //Template Arg
        $template_arg = array(
            'title'       => $subject,
            'logo'        => $opt['email_logo'],
            'content'     => $content,
            'site_url'    => home_url(),
            'site_title'  => get_bloginfo('name'),
            'footer_text' => $opt['email_footer'],
            'is_rtl'      => (is_rtl() ? true : false)
        );

        //Send Email
        try {
            WP_MAIL::init()->from('' . $from_name . ' <' . $from_email . '>')->to($to)->subject($subject)->template($email_template, $template_arg)->send();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /*------------------------------------------------------------
     - WP POST Api
    ------------------------------------------------------------/

    /**
     * Get List Post From Post Type
     *
     * @param $post_type
     * @return array
     */
    public static function wp_query($arg = array(), $title = true)
    {
        // Create Empty List
        $list = array();

        // Prepare Params
        $default = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => '-1',
            'order'          => 'ASC',
            'fields'         => 'ids',
            'cache_results'  => false,
            'no_found_rows' => true, //@see https://10up.github.io/Engineering-Best-Practices/php/#performance
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        );
        $args = wp_parse_args($arg, $default);

        // Get Data
        $query = new \WP_Query($args);

        // Get SQL
        //echo $query->request;
        //exit;

        // Added To List
        foreach ($query->posts as $ID) {
            if ($title) {
                $list[$ID] = get_the_title($ID);
            } else {
                $list[] = $ID;
            }
        }

        return $list;
    }

    /**
     * Check Post Exist By ID in wordpress
     *
     * @param $ID
     * @param bool $post_type
     * @return int
     */
    public static function post_exist($ID, $post_type = false)
    {
        global $wpdb;

        $query = "SELECT count(*) FROM `$wpdb->posts` WHERE `ID` = $ID";
        if (!empty($post_type)) {
            $query .= " AND `post_type` = '$post_type'";
        }

        return ((int) $wpdb->get_var($query) > 0 ? true : false);
    }

    // Create function for filter WordPress WP_query
    public static function get_post_count_filter($sql)
    {
        return 'COUNT(*) over()';
    }

    /**
     * Post Count With WP Query
     *
     * @param array $arg
     * @return mixed
     */
    public static function postCount($arg = array())
    {

        // Add filter for WP_Query Post fields
        add_filter('posts_fields', array(__CLASS__, 'get_post_count_filter'), 10, 1);

        // Create new request get list posts
        $default = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'cache_results'  => false,
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        );
        $args = wp_parse_args($arg, $default);

        // Send Query
        $Query = new \WP_Query($args);

        // Remove filter
        remove_filter('posts_fields', array(__CLASS__, 'get_post_count_filter'));

        // Show SQL generated from WP_Query
        //echo $Query->request;

        // Get Post Count
        if (!isset($Query->posts[0])) {
            return 0;
        }

        return $Query->posts[0];
    }

    /**
     * Get Number Of Post Comment
     *
     * @param array $arg
     * @return mixed
     */
    public static function getNumberPostComment($arg = array())
    {
        $default = array(
            'parent' => 0, // Count Only Parent 0
            'status'         => 'approve',
            'type'           => 'comment',
            'post_id'        => 0,
            'number'         => false,
            'order'          => 'DESC',
            'orderby'        => 'comment_ID',
            'hierarchical' => false, //@see https://wordpress.stackexchange.com/questions/265014/wp-comment-query-with-5-top-level-comments-per-page
            'count'          => true,
            'update_comment_meta_cache' => false,
            'update_comment_post_cache' => false,
        );
        $args = wp_parse_args($arg, $default);
        $comments_count_query = new \WP_Comment_Query;
        $all = $comments_count_query->query($args);

        return $all;
    }

    /**
     * is_edit_page 
     * function to check if the current page is a post edit page
     * 
     * @author Ohad Raz <admin@bainternet.info>
     * 
     * @param  string  $new_edit new|edit
     * @return boolean
     * @example global $typenow; (is_edit_page('new') and $typenow =="POST_TYPE")
     */
    public static function is_edit_page($new_edit = null)
    {
        global $pagenow;
        //make sure we are on the backend
        if (!is_admin()) return false;

        if ($new_edit == "edit")
            return in_array($pagenow, array('post.php',));
        elseif ($new_edit == "new") //check for new post page
            return in_array($pagenow, array('post-new.php'));
        else //check for either new or edit
            return in_array($pagenow, array('post.php', 'post-new.php'));
    }

    /*------------------------------------------------------------
     - WP Admin Ui
    ------------------------------------------------------------/

    /**
     * Show Admin Wordpress Ui Notice
     *
     * @param $text
     * @param string $model
     * @param bool $close_button
     * @param bool $echo
     * @param string $style_extra
     * @return string
     */
    public static function admin_notice($text, $model = "info", $close_button = true, $echo = true, $style_extra = 'padding:12px;')
    {
        $text = '
        <div class="notice notice-' . $model . '' . ($close_button === true ? " is-dismissible" : "") . '">
           <div style="' . $style_extra . ' inline">' . $text . '</div>
        </div>
        ';
        if ($echo) {
            echo $text;
        } else {
            return $text;
        }
    }

    /*------------------------------------------------------------
     - WP Request
    ------------------------------------------------------------/

    /**
     * Show Json and Exit
     *
     * @since    1.0.0
     * @param $array
     */
    public static function json_exit($array)
    {
        wp_send_json($array);
        exit;
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     * @return bool
     */
    public static function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }
}
