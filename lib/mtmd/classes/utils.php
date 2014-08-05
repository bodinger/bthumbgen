<?php

class mtmdUtils {
    public static $excludeFiles = array(
        '.',
        '..',
        '.DS_Store'
    );

    /**
     * Dumps variable.
     *
     * @param mixed $var
     *
     * @return void
     */
    public static function dump($var)
    {
        var_dump($var);
    }


    public static function output($string)
    {
        echo "<!--".$string."-->\n";
    }


    /**
     * Create a directory.
     *
     * @param string  $path        Destination path
     * @param boolean $recursive   Recursive yes/no
     * @param integer $permissions Permission mask
     *
     * @return boolean
     */
    public static function mkDir($path, $recursive = false, $permissions = 0777)
    {
        if ($recursive === false && !file_exists($path)) {
            mtmdUtils::output($path);
            mkdir($path);
        }
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $path = '';
        foreach ($parts as $folder) {
            $path .= $folder.DIRECTORY_SEPARATOR;
            if (!is_dir($path)) {
                mkdir($path);
            }
        }

    }


    /**
     * Returns a list of folder contents recursively.
     *
     * @param string $dir       Source directory path.
     * @param bool   $recursive Use recursion yes/no
     *
     * @return array
     */
    public static function listDir($dir, $recursive = true)
    {
        $retArr = array();
        if (!file_exists($dir)) {
            return $retArr;
        }

        $dirHandle = opendir($dir);
        while (false !== ($entry = readdir($dirHandle))) {
            if (in_array($entry, self::$excludeFiles)) {
                continue;
            }
            $fullPath = $dir.DIRECTORY_SEPARATOR.$entry;
            if (is_dir($fullPath) && $recursive === true) {
                $tmpArr = self::listDir($fullPath, true);
                foreach ($tmpArr as $path) {
                    array_push($retArr, $path);
                }
                continue;
            }
            array_push($retArr, $fullPath);
        }

        closedir($dirHandle);

        sort($retArr);

        return $retArr;
    }


    /**
     * Format bytes.
     *
     * @param int $bytes
     * @param int $precision
     *
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2) {
        $base     = log($bytes) / log(1024);
        $suffixes = array('', ' kB', ' MB', ' GB', ' TB');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];

    }


}
