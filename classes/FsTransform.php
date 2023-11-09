<?php

namespace Shasoft\Filesystem;

// Трансформация имен файловой системы
class FsTransform
{
    // Установить функцию трансформации имени файла
    protected static ?\Closure $fn = null;
    public static function set(\Closure $fn): void
    {
        self::$fn = $fn;
    }
    // Получить трансформированное имя файла
    public static function get(string $filepath): string
    {
        return is_null(self::$fn) ? $filepath : call_user_func(self::$fn, $filepath);
    }
}
