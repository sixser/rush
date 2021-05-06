<?php

if (function_exists('get_dir_files') === false) {
    /**
     * Fetch all files in the directory
     * @param string $dirname Directory name.
     * @param bool $recursive Fetch files in all child directory when true, or only current directory.
     * @return array
     * @throws Exception
     */
    function get_dir_files(string $dirname, bool $recursive = false): array
    {
        if (is_dir($dirname) === false) {
            throw new Exception("{$dirname} is not a valid directory");
        }

        $files = [];
        foreach (scandir($dirname) as $filename) {
            if ($filename === '.' || $filename === '..') {
                continue;
            }

            $fullName = $dirname . '/' . $filename;

            if (is_file($fullName) === true) {
                $files[] = $fullName;
            }

            if ($recursive === true && is_dir($fullName)) {
                array_push($files, ...get_dir_files($fullName, $recursive));
            }
        }

        return $files;
    }
}

if (function_exists('get_class_from_file') === false) {
    /**
     * Parse class full name from file
     * @param string $filename File full name.
     * @return string
     */
    function get_class_from_file(string $filename): string
    {
        $namespace = $class = '';
        $getNamespace = $getClass = false;

        $contents = file_get_contents($filename);
        foreach (token_get_all($contents) as $token) {
            // found flag, and will be getting namespace or classname
            if ($getNamespace === true) {
                if (is_array($token) && $token[0] == T_NAME_QUALIFIED) {
                    $namespace .= $token[1];
                } else if (is_string($token) && $token === ';') {
                    $getNamespace = false;
                }
            } elseif ($getClass === true) {
                if (is_array($token) && $token[0] == T_STRING) {
                    $class = $token[1];
                    break;
                }
            }

            // check namespace or classname flag
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $getNamespace = true;
            } elseif (is_array($token) && $token[0] == T_CLASS) {
                $getClass = true;
            }
        }

        if (empty($class) === true) {
            return '';
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }
}
