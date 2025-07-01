<?php

namespace Shasoft\Filesystem;

class Filesystem
{
    // Пропустить ссылки
    public const SKIP_LINKS = 32768;
    // Перебрать все элементы директории
    public static function items(string $filepath, \Closure $cb): void
    {
        // Получить список элементов
        if (file_exists($filepath)) {
            if (is_dir($filepath)) {
                $items = scandir($filepath);
                foreach ($items as $name) {
                    if ($name != '.' && $name != '..') {
                        // Полное имя элемента
                        $filepathItem = $filepath . '/' . $name;
                        // Создать FileInfo
                        $info = new \SplFileInfo($filepathItem);
                        // Вызвать функцию обратного вызова
                        if ($cb($info) !== false) {
                            // Если это директория
                            if ($info->isDir()) {
                                // то вызвать перебор элементов в ней
                                self::items($filepathItem, $cb);
                            }
                        }
                    }
                }
            } else {
                // Вызвать функцию обратного вызова
                $cb(new \SplFileInfo($filepath));
            }
        }
    }

    // Это ссылка?
    public static function hasLink(string $filepath): bool
    {
        $link = readlink($filepath);
        if ($link === false || Path::normalize($link) !== Path::normalize($filepath)) {
            return true;
        }
        return false;
    }

    // Удалить директорию (рекурсивно)
    public static function rmdir(string $filepath): bool
    {
        //s_dd($path, file_exists($path));
        $ret = true;
        $filepath = Path::normalize($filepath);
        // Директория существует?
        if (file_exists($filepath)) {
            if (self::hasLink($filepath)) {
                if (is_dir($filepath)) {
                    $ret &= \rmdir($filepath);
                } else {
                    $ret &= unlink($filepath);
                }
            } else {
                if (is_dir($filepath)) {
                    // Список элементов
                    $filepaths = new \FilesystemIterator($filepath);
                    // Удалить все элементы директории
                    foreach ($filepaths as $_filepath) {
                        if (is_dir($_filepath)) {
                            $ret &= self::rmdir($_filepath);
                        } else {
                            if (!@unlink($_filepath)) {
                                $ret &= \rmdir($_filepath);
                            }
                        }
                    }
                    $ret &= \rmdir($filepath);
                } else {
                    $ret &= unlink($filepath);
                }
            }
        }
        return $ret;
    }
    // Создать директорию
    public static function mkdir(string $filepath, int $permissions = 0777): bool
    {
        // Проверим существование директории
        $ret = file_exists($filepath);
        // Если директория не существует
        if (!$ret) {
            $ret = mkdir($filepath, $permissions, true);
        }
        return $ret;
    }
    // Копировать директорию
    public static function copyFolder(string $from, string $to, bool $replace = true, int $permissions = 0777): bool
    {
        $ret = true;
        //
        $from = Path::normalize($from);
        $to = Path::normalize($to);
        // Исходная директория существует?
        if (!is_dir($from)) {
            $ret = false;
        } else {
            // Создать папку назначения
            self::mkdir($to, $permissions);

            $dir = opendir($from);
            while (($filename = readdir($dir)) !== false) {
                if ($filename != "." && $filename != "..") {
                    $filepathFrom = $from . DIRECTORY_SEPARATOR . $filename;
                    $filepathTo = $to . DIRECTORY_SEPARATOR . $filename;
                    if (is_dir($filepathFrom)) {
                        self::copyFolder($filepathFrom, $filepathTo, $replace, $permissions);
                    } else {
                        // Если файл существует и его нужно заменять
                        if (file_exists($filepathTo) && $replace) {
                            // то удалить файл
                            unlink($filepathTo);
                        }
                        // Если файла не существует
                        if (!file_exists($filepathTo)) {
                            // то копировать
                            $ret &= copy($filepathFrom, $filepathTo);
                        } else {
                            $ret = false;
                        }
                    }
                }
            }
            closedir($dir);
        }
        return $ret;
    }
    // Удалить
    public static function remove(string $filepath): bool
    {
        if (file_exists($filepath)) {
            $origin = Link::originAll($filepath);
            if (is_dir($origin)) {
                $rc = @rmdir($filepath);
            } else {
                $rc = @unlink($filepath);
            }
            return !file_exists($filepath);
        }
        return true;
    }
}
