<?php

namespace App\Support;

class SpreadsheetValue
{
    /**
     * Neutralize spreadsheet formula prefixes (=, +, -, @, tab, CR) on user-supplied text.
     */
    public static function escape(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'" . $value;
        }

        return $value;
    }
}
