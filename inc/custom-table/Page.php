<?php

namespace CustomTable;

class Page
{


    public static function page_slug(): string
    {
        return '';
    }

    public static function screen_id(): string
    {
        return 'toplevel_page_' . static::page_slug();
    }

    public static function is_page(): bool
    {
        global $pagenow;
        return ($pagenow == "admin.php" and isset($_GET['page']) and $_GET['page'] == static::page_slug());
    }

    public static function is_screen($name = ''): bool
    {
        return (static::is_page() and !empty($_GET['screen']) and $_GET['screen'] == $name);
    }

    public static function screen(): string
    {
        return (!empty($_GET['screen']) ? trim($_GET['screen']) : '');
    }

    public static function url($args = []): string
    {
        return add_query_arg(array_replace(['page' => static::page_slug()], $args), admin_url('admin.php'));
    }

    public static function reset_entry_form(): void
    {
        if (isset($_POST['ct'])) {
            $_POST['ct'] = '';
        }
    }

}
