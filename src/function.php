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
