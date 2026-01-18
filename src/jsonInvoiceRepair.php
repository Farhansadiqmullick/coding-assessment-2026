<?php

final class JsonInvoiceRepair
{
    public static function repairIfNeeded(string $json, ?string $logFile = null): string
    {
        // Fast path — valid JSON, do nothing
        json_decode($json);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        $original = $json;
        $fixed = self::fixUnclosedItemsArray($json, $didFix);

        // Validate again after fix
        json_decode($fixed);
        if (json_last_error() === JSON_ERROR_NONE && $didFix) {
            self::log($logFile, 'Fixed missing closing ] for items array');
            return $fixed;
        }

        // Fail-safe: return original if still broken
        return $original;
    }

    /**
     * Fix:
     *   "items": [ ... }
     *   "discount":
     *
     * → insert:
     *   ],
     */
    private static function fixUnclosedItemsArray(string $json, &$didFix = false): string
    {
        $didFix = false;

        $pattern = '/("items"\s*:\s*\[[\s\S]*?\})\s*"discount"\s*:/m';

        $fixed = preg_replace_callback($pattern, function ($matches) use (&$didFix) {
            $didFix = true;
            return $matches[1] . "\n    ],\n    \"discount\":";
        }, $json, 1);

        return $fixed;
    }

    private static function log(?string $file, string $message): void
    {
        if (!$file) {
            return;
        }

        file_put_contents(
            $file,
            '[' . date('c') . '] ' . $message . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}