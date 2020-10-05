<?php

namespace app\components;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ArchiveHelper
{
    /**
     * создать Zip - архив из папки
     *
     * @param $folder
     * @param $zipFile
     *
     * @return bool
     */
    public function dirToZip($folder, $zipFile, $excludes = [])
    {
        if (!file_exists($folder)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($zipFile, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($folder));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {

                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                    continue;
                }

                $file = realpath($file);

                foreach ($excludes as $exclude) {
                    if (
                        ($exclude['type'] == 'dir' && ($file === $exclude['value'] || strpos($file, $exclude['value'] . '/') !== false))
                        || ($exclude['type'] == 'file' && $file === $exclude['value'])
                    ) {
                        continue 2;
                    }
                }

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } elseif (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        if (!$zip->close()) {
            return false;
        }

        return true;
    }


}