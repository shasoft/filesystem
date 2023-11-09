<?php

namespace Shasoft\Filesystem;

// Манипуляция с именами файловой структуры
class Path
{
    // Получить расширение
    public static function ext(string $filepath, bool $lowerCase = true): string
    {
        $ret = pathinfo($filepath, PATHINFO_EXTENSION);
        if ($lowerCase) {
            $ret = strtolower($ret);
        }
        return $ret;
    }
    // Получить относительный путь от файла $from до файла $to
    public static function relative(string $from, string  $to): string
    {
        $from = self::normalize($from);
        $to   = self::normalize($to);
        $from = explode('/', $from);
        $to   = explode('/', $to);
        while (!empty($from) && !empty($to) && $from[0] == $to[0]) {
            array_shift($from);
            array_shift($to);
        }
        $ret = './' . str_repeat("../", count($from) - 1) . implode('/', $to);
        return $ret;
    }
    // Заменить расширение
    public static function replaceExt(string $filepath, string $ext): string
    {
        $info = pathinfo($filepath);
        return (empty($info['dirname']) ? $info['filename'] : $info['dirname'] . '/' . $info['filename']) . (empty($ext) ? '' : ('.' . $ext));
    }
    // Нормализовать путь. Убрать '..' и '.'
    public static function normalize(string $path, bool $as_posix = true): string
    {
        $path  = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $ret   = array();
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }

            if ('..' == $part) {
                array_pop($ret);
            } else {
                $ret[] = $part;
            }
        }
        $ret = implode(DIRECTORY_SEPARATOR, $ret);
        // Нужно нормализовать в формат POSIX
        if ($as_posix) {
            // Преобразовать СЛЭШИ
            $ret = str_replace(DIRECTORY_SEPARATOR, '/', $ret);
            // Может это запуск в Windows?
            if (PHP_OS_FAMILY == 'Windows') {
                if (substr($ret, 1, 1) == ':') {
                    // В начале указан диск. перевести его в верхний регистр
                    $ret = ucfirst($ret);
                }
            }
        }
        if (!empty($ret)) {
            if (in_array(substr($ret, -1, 1), ['\\', '/'])) {
                // Отрезать / в конце
                $ret = substr($ret, 0, -1);
            }
        }
        return $ret;
    }
    // Вставить в имя файла суффикс
    public static function appendSuffix(string $filepath, string $suffix): string
    {
        // Расширение
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        if (!empty($ext)) {
            $ext = '.' . $ext;
        }
        // Имя файла
        $filename = basename($filepath, $ext);
        // Путь
        $path = dirname($filepath);
        if (!empty($path)) {
            $path .= '/';
        }
        // Результат
        return $path . $filename . $suffix . $ext;
    }
}
