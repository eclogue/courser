<?php

namespace Barge\Set;

class Config
{

    private static $_config = [];

    /*
     * used like Config::get('redis.host') => ['redis' => ['host' => 'local']]
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     * */
    public static function get($key, $default = '')
    {
        if (strpos($key, '.')) {
            $indexes = explode('.', $key);
            $temp = '';
            foreach ($indexes as $key => $index) {
                if ($key == 0) {
                    if (!isset(self::$_config[$index])) {
                        return $default;
                    } else {
                        $temp = self::$_config[$index];
                    }
                } else {
                    if (!isset($temp[$index]))
                        return $default;
                    else
                        $temp = $temp[$index];
                }

            }

            return $temp;
        } else {
            if (isset(self::$_config[$key]))
                return self::$_config[$key];

            return $default;

        }
    }

    public static function set($key, $val = '')
    {
        if (is_string($key)) {
            if (strpos($key, '.')) {
                $indexes = explode('.', $key);
                $temp = '';
                foreach ($indexes as $key => $index) {
                    $temp .= '[' . $index . ']';
                }

                self::$_config{$temp} = $val;

            } else {
                self::$_config[$key] = $val;

            }
        } elseif (is_array($key) && $val === '') {
            self::$_config = array_merge(self::$_config, $key);
        } else {
            return false;
        }

        return true;

    }

    public static function all()
    {
        return self::$_config;
    }
}