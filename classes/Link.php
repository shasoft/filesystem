<?php

namespace Shasoft\Filesystem;

class Link
{
    // Создать ссылку
    public static function create(string $from, string $to): bool
    {
        // Создать директорию (если её нет)
        $rcMkDirTo = Filesystem::mkdir(dirname($to));
        // Если это ссылка?
        if (is_link($to)) {
            // И она существует?
            if (file_exists($to)) {
                if (is_dir($to)) {
                    @rmdir($to);
                } else {
                    @unlink($to);
                }
            } else {
                @rmdir($to);
            }
        }
        if (file_exists($to)) {
            // удалить её
            if (is_dir($to)) {
                $rc = @rmdir($to);
                if (!$rc) {
                    if (is_link($to)) {
                        $rc = @unlink($to);
                    }
                }
            } else {
                $rc = unlink($to);
                if (!$rc) {
                    if (is_link($to)) {
                        $rc = @rmdir($to);
                    }
                }
            }
        }
        // Создать ссылку
        if (!file_exists($from)) {
            throw new \Exception(var_export([
                'source' => __CLASS__ . ':' . __LINE__,
                'from' => Path::normalize($from),
                'file_exists' => file_exists($from),
                'rcMkDirTo' => $rcMkDirTo,
                'to' => Path::normalize($to)
            ], true));
        }
        $ret = symlink($from, $to);
        if (!$ret) {
            throw new \Exception(var_export([
                'from' => Path::normalize($from),
                'to' => Path::normalize($to)
            ], true));
        }
        return $ret;
    }
    // Получить источник
    public static function origin(string $filepath): string
    {
        $origin = readlink($filepath);
        return Path::normalize($origin == false ? $filepath : $origin);
    }
}
