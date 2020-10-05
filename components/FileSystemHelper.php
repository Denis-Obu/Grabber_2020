<?php
namespace app\components;


class FileSystemHelper
{
    /**
     * удалить директорию со всеми вложенными файлами
     * @param $dir
     * @return bool
     */
    static function delDir($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir($dir . '/' . $file)) ? self::delDir($dir . '/' . $file) : unlink($dir . '/' . $file);
        }
        return rmdir($dir);
    }
}