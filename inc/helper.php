<?php
namespace WP_PLUGIN;

/**
 * Class Helper Used in Custom Helper Method For This Plugin
 */
class Helper {
  
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
