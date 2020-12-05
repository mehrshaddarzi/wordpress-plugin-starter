<?php
namespace WP_PLUGIN;


class Helper
{

    public function __construct()
    {
        
    }

    public static function readJson($path)
    {
        if (!file_exists($path)) {
            return array('status' => false, 'message' => 'file not found');
        }
        $string = file_get_contents($path);
        $array = json_decode($string, true);
        if ($array === null) {
            return array('status' => false, 'message' => 'problem parse json file');
        }

        return $array;
    }

}

new Helper();
