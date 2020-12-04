<?php


namespace Hiiicomtw\HiiiSSOClient\Helper;


class DataHelper
{
    /*============[array]============*/
    /**
     * @param string $key
     * @param array  $arr
     * @param null   $default
     *
     * @return null
     */
    public static function keyOrDefault($key, $arr, $default = null)
    {
        return (array_key_exists($key, $arr) && (trim($arr[$key]) !== '')) ? $arr[$key] : $default;
    }

    /**
     * @param string $key
     * @param array  $arr
     * @param null   $default
     *
     * @return mixed
     */
    public static function keyExistsOrDefault($key, $arr, $default = null)
    {
        return (array_key_exists($key, $arr)) ? $arr[$key] : $default;
    }

    public static function keyValueIsEmpty($key, $arr)
    {
        $var = self::keyOrDefault($key, $arr);

        return (is_null($var) || ($var === ''));
    }

}