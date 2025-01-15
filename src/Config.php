<?php

namespace xpanse\Sdk;

require_once(__DIR__ . '/tools/Region.php');

/**
 * @copyright xpanse
 */
class Config
{
    public static string $BaseUrl;
    public static string $SecretKey;
    public static int $TimeoutMilliseconds;
    public static string $Environment;
    public static bool $EnableDebug;

    private static array $EnvConfigToUrlMapping = [
        'none-local' => 'https://localhost:5001',
        'none-development' => 'https://develop-api.xpanse.one',
        'none-sandbox' => 'https://sandbox-api.xpanse.one',
        'none-production' => 'https://api.xpanse.one',

        'au-development' => 'https://develop-api-au.xpanse.one',
        'us-development' => 'https://develop-api-us.xpanse.one',
        'jp-development' => 'https://develop-api-jp.xpanse.one',
        'au-sandbox' => 'https://sandbox-api-au.xpanse.one',
        'us-sandbox' => 'https://sandbox-api-us.xpanse.one',
        'au-production' => 'https://api-au.xpanse.one',
        'us-production' => 'https://api-us.xpanse.one',
        'eu-production' => 'https://api-eu.xpanse.one'
    ];

    public static function initialise($SecretKey, $Environment, $TimeoutMilliseconds = 60000, $EnableDebug = false)
    {
        self::$Environment = strtolower($Environment);

        $RawRegionPart = self::extractRegionFromKey($SecretKey);
        $Region = $RawRegionPart !== null
            ? Region::fromLabel(strtolower($RawRegionPart))
            : 'none';

        $Region = $Region !== null
            ? $Region
            : 'none';

        self::$BaseUrl = self::getBaseUri($Region, self::$Environment);

        self::$SecretKey = $SecretKey;
        self::$TimeoutMilliseconds = $TimeoutMilliseconds;
        self::$EnableDebug = $EnableDebug;
    }

    private static function extractRegionFromKey($key): ?string
    {
        if (empty($key)) {
            return null;
        }

        $parts = explode('-', $key);

        if (count($parts) < 2) {
            return null;
        }

        return strtolower($parts[1]);
    }

    private static function getBaseUri($Region, $Environment): string
    {
        $baseUri = self::getBaseUriWithFallback($Region, $Environment);
        return empty($baseUri) ? 'https://sandbox-api.xpanse.one' : $baseUri;
    }

    private static function getBaseUriWithFallback($Region, $Environment): ?string
    {
        $baseUri = '';
        if (array_key_exists($Region . '-' . $Environment, self::$EnvConfigToUrlMapping)) {
            $baseUri = self::$EnvConfigToUrlMapping[$Region . '-' . $Environment];
        }

        if (empty($baseUri)) {
            if (array_key_exists('none-' . $Environment, self::$EnvConfigToUrlMapping)) {
                return self::$EnvConfigToUrlMapping['none' . '-' . $Environment];
            }
        }

        return $baseUri;
    }
}
