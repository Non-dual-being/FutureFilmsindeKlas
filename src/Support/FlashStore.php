<?php 
declare(strict_types=1);
namespace GeoFort\Support;

final class FlashStore 
{
    private const KEY = '__FLASH';

    public static function add(string $key, string $message, string $type): void 
    {
        $_SESSION[self::KEY ] ??= [];
        $_SESSION[self::KEY][$key] = [
            'message' => $message,
            'type' => $type,
        ];
    }

    public static function get(string $key): ?array
    {
        if (!isset($_SESSION[self::KEY][$key])) return null;

        $payload = $_SESSION[self::KEY][$key];
        unset($_SESSION[self::KEY][$key]); // consume
        return $payload;
    }

    public static function peek(string $key): ?array 
    {
        return $_SESSION[self::KEY][$key] ?? null;
    }

    public static function clearAll(): void {
        unset($_SESSION[self::KEY]);
    }

}
?>