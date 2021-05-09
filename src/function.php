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
        ! is_dir($dirname) &&
        throw new Exception("Failed to scan files, $dirname is not a valid directory.");

        $files = [];
        foreach (scandir($dirname) as $filename) {
            if ('.' === $filename || '..' === $filename)
                continue;

            $fullName = $dirname . '/' . $filename;

            if (is_file($fullName)) $files[] = $fullName;

            if (false === $recursive && is_dir($fullName))
                array_push($files, ...get_dir_files($fullName, $recursive));
        }

        return $files;
    }
}

if (function_exists('get_class_from_file') === false) {
    /**
     * Parse class full name from file
     * @param string $filename File full name.
     * @return string
     * @throws Exception
     */
    function get_class_from_file(string $filename): string
    {
        (! is_file($filename) || 'php' !== pathinfo($filename, PATHINFO_EXTENSION)) &&
        throw new Exception("Failed to parse class, $filename is not a valid php script file.");

        $namespace = $class = '';
        $getNamespace = $getClass = false;

        $contents = file_get_contents($filename);
        foreach (token_get_all($contents) as $token) {
            // found flag, and will be getting namespace or class name
            if (true === $getNamespace) {
                if (is_array($token) && $token[0] == T_NAME_QUALIFIED) {
                    $namespace .= $token[1];
                } else if (is_string($token) && ';' === $token) {
                    $getNamespace = false;
                }
            } elseif (true === $getClass) {
                if (is_array($token) && $token[0] == T_STRING) {
                    $class = $token[1];
                    break;
                }
            }

            // check namespace or class name flag
            if (is_array($token) && T_NAMESPACE == $token[0]) {
                $getNamespace = true;
            } elseif (is_array($token) && T_CLASS == $token[0]) {
                $getClass = true;
            }
        }

        return empty($class) ? '' : (empty($namespace) ? $class : ($namespace . '\\' . $class));
    }
}
