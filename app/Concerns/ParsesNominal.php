<?php

namespace App\Concerns;

trait ParsesNominal
{
    /**
     * Parse an Indonesian/US formatted currency string or number into float.
     */
    protected function parseNominal(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (empty($value)) {
            return 0.0;
        }

        $str = trim((string) $value);

        // Handle mixed thousand/decimal separators (e.g. 100.000,50 or 100,000.50)
        if (str_contains($str, '.') && str_contains($str, ',')) {
            $lastDot = strrpos($str, '.');
            $lastComma = strrpos($str, ',');
            if ($lastComma > $lastDot) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif (str_contains($str, ',')) {
            $str = str_replace(',', '.', $str);
        }

        return (float) preg_replace('/[^0-9.]/', '', $str);
    }
}
