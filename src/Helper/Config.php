<?php
/**
 * feature will support more type of source data
 */
namespace Courser\Helper;

class Config
{
    private static $_config = [];

    public static $default = 'default.php';

    /*
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
                    if (!isset($temp[$index])) {
                        return $default;
                    } else {
                        $temp = $temp[$index];
                    }
                }
            }
            return $temp;
        } else {
            if (isset(self::$_config[$key]))
                return self::$_config[$key];

            return $default;

        }
    }

    /**
     * @param $key
     * @param mixed $val
     * @return bool
     */
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
            self::$_config = array_merge_recursive(self::$_config, $key);
        } else {
            return false;
        }

        return true;

    }

    public static function all()
    {
        return self::$_config;
    }

    /**
     * load config from path by env variable
     *
     * @param $path
     * @return void
     */
    public static function load($path)
    {
        $path = rtrim($path, '/');
        $file = $path . '/' . static::$default;
        $config = [];
        if (file_exists($file)) {
            $data = include $file;
            if (is_array($data) && !empty($data)) {
                $config = $data;
            }
        }
        $env = getenv('env');
        $env = $env ?? 'development';
        $file = $path . '/' . $env . '.php';
        if (file_exists($file)) {
            $data = include $file;
            if (is_array($data) && !empty($data)) {
                $config = array_merge($config, $data);
            }
        }
        static::set($config);
    }
}