<?php

namespace Shasoft\Filesystem;

use Shasoft\Data\Str;

// Работа с файлами
class File
{
    // Сохранить файл, создав директорию, если она не существует
    public static function save(string $filepath, mixed $data, ?string $format = null, ?int $options = null): int|false
    {
        // Создать директорию
        Filesystem::mkdir(dirname($filepath));
        // Определить формат данных
        if (is_null($format)) {
            $format = Path::ext($filepath);
        }
        // Конвертировать данные в строку
        $str = Str::to($data, $format);
        // Сохранить данные в файл
        return file_put_contents($filepath, $str);
    }
    // Читать файл
    public static function load(string $filepath, mixed $defaultData = null, ?string $format = null): mixed
    {
        if (file_exists($filepath)) {
            // Читать данные из файла
            $ret = file_get_contents($filepath);
            // Определить формат данных
            if (is_null($format)) {
                $format = Path::ext($filepath);
            }
            try {
                // Конвертировать данные в строку
                $ret = Str::from($ret, $format);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage() . ' in file ' . $filepath);
            }
        } else {
            if (is_callable($defaultData)) {
                $ret = \call_user_func($defaultData);
                //if( !config('app.debug') ) {
                self::save($filepath, $ret, $format);
                //}
            } else {
                $ret = $defaultData;
            }
        }
        return $ret;
    }
    // Создать имя временного файла
    static $s_tempId = 0;
    public static function temp(string $ext = ''): string
    {
        self::$s_tempId++;
        $id = md5(microtime()) . self::$s_tempId;
        if (!empty($ext)) {
            $ext = '.' . $ext;
        }
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(File::class) . DIRECTORY_SEPARATOR . self::$s_tempId . $id . $ext;
    }
    // Это файл PHP
    public static function hasPhp(\SplFileInfo|string $spi): bool
    {
        // Если имя файла задано строкой
        if (is_string($spi)) {
            // то преобразовать его в объект
            $spi = new \SplFileInfo($spi);
        }
        return ($spi->isFile() && $spi->getExtension() == 'php');
    }
}
