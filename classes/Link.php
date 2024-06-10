<?php

namespace Shasoft\Filesystem;

class Link
{
    // Создать ссылку
    public static function create(string $from, string $to): bool
    {
        // Создать директорию (если её нет)
        $rcMkDirTo = Filesystem::mkdir(dirname($to));
        // Если ссылка существует
        if (file_exists($to) || is_link($to)) {
            // удалить её
            if (is_dir($to)) {
                $rc = @rmdir($to);
                if (!$rc) {
                    if (is_link($to)) {
                        $rc = @unlink($to);
                    }
                }
            } else {
                $rc = @unlink($to);
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
                'from' => $from,
                'file_exists' => file_exists($from),
                'rcMkDirTo' => $rcMkDirTo,
                'to' => $to
            ], true));
        }
        $ret = symlink($from, $to);
        if (!$ret) {
            throw new \Exception(var_export([
                'from' => $from,
                'to' => $to
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
