<?php

namespace Shasoft\Filesystem;

class Link
{
    // Создать ссылку
    public static function create(string $from, string $to): bool
    {
        // Создать директорию (если её нет)
        $rcMkDirTo = Filesystem::mkdir(dirname($to));
        // Удалить текущий элемент
        Filesystem::remove($to);
        //s_dd('file_exists($to)', file_exists($to));
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
        $ret = @symlink($from, $to);
        if (!$ret) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $fromOrigin = Link::originAll($from);
                if (is_dir($fromOrigin)) {
                    $cmd = '/j ';
                } else {
                    $cmd = '';
                }
                $commandLine = 'mklink ' . $cmd . '"' . Path::normalize($to) . '" "' . Path::normalize($from) . '"';
                $rc = exec($commandLine);
            }
        }
        if (!file_exists($to)) {
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

    // Получить источник
    public static function originAll(string $filepath): string
    {
        $ret = self::origin($filepath);
        if ($ret != $filepath) {
            return self::origin($ret);
        }
        return $ret;
    }

    // Создать ссылку
    public static function createDeprecated(string $from, string $to): bool
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
        $ret = @symlink($from, $to);
        if (!$ret) {
            throw new \Exception(var_export([
                'from' => Path::normalize($from),
                'to' => Path::normalize($to)
            ], true));
        }
        return $ret;
    }
}
