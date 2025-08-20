<?php

namespace WP_PLUGIN;

class Option
{

    public static string $name = 'wp_plugin_opt';

    public static function get()
    {
        return get_option(self::$name, []);
    }
    
}
