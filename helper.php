<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);

/**
 * Helper Functions
 * 
 * @author Shubham Chaudhary
 */

/** 
 * Basic Functions
 * */
if (!function_exists('getIpAddress')) {
    /**
     * Get User IP address
     */
    function getIpAddress(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}
if (!function_exists('pluck')) {
    /**
     * Pluck some columns from array
     * @throws \Exception
     */
    function pluck(array $data, array $columns): array
    {
        if (empty($data) || !is_array($data)) throw new Exception("Enter valid data.");
        if (empty($columns) || !is_array($columns)) throw new Exception("Enter valid columns.");

        $keys = array_fill_keys(array_values($columns), '');
        $out = array();

        if (!empty($data[0]) && is_array($data[0])) {
            for ($i = 0, $len = count($data); $i < $len; $i++) {
                $out[$i] = array_filter($data[$i], function ($key) use ($columns) {
                    return in_array($key, $columns) || empty($columns);
                }, ARRAY_FILTER_USE_KEY);

                $out[$i] = array_merge($keys, $out[$i]);
            }
        } else {
            foreach ($data as $key => $value) {
                if (in_array($key, $columns) || empty($columns)) {
                    $out[$key] = $value;
                }
            }
        }

        return $out;
    }
}

/** 
 * CSV Functions
 * */
if (!function_exists('parseCSV')) {
    /**
     * Parse csv file
     */
    function parseCSV(string $filepath, bool $has_header = true): array
    {
        $return_arr = $data_arr = $headers = array();

        if (($file = fopen("{$filepath}", "r")) !== false) {
            while (($row = fgetcsv($file, 4096, ",")) !== false) {
                if (empty($row)) continue;

                $data_arr[] = $row;
            }

            fclose($file);
        }

        if ($has_header) {
            $headers = $data_arr[0];
            unset($data_arr[0]);

            foreach ($data_arr as $k => $v) {
                if (count($headers) !== count($v)) continue;

                $arr = array();
                for ($j = 0; $j < count($headers); $j++) {
                    $arr[$headers[$j]] = $v[$j];
                }

                $return_arr[$k + 1] = $arr;
            }
        }

        return [
            $return_arr,
            $headers
        ];
    }
}
if (!function_exists('arrayToCsv')) {
    /**
     * Array Data to csv file download
     * @throws \Exception
     */
    function arrayToCsv(array $array, string $download = ''): string|false
    {
        if ($download !== '') {
            header("Content-Type: application/csv");
            header("Content-Disposition: attachement; filename=\"{$download}\"");
        }

        ob_start();

        $file = fopen('php://output', 'w');

        if (!$file) throw new Exception('Can\'t open php://output');

        foreach ($array as $k => $line) {
            if (!fputcsv($file, $line)) throw new Exception('Can\'t write line ' . ($k + 1) . ": " . $line);
        }

        if (!fclose($file)) throw new Exception('Can\'t close php://output');

        $content = ob_get_contents();

        ob_end_clean();

        if ($download === '') return $content;
        else echo $content;
    }
}
if (!function_exists('validateColumnsExist')) {
    /**
     * Validate if array column exists
     * @throws \Exception
     */
    function validateColumnsExist(array $headers, array $columns): array
    {
        if (!is_array($headers)) throw new Exception("Provide valid headers array");
        if (!is_array($columns)) throw new Exception("Provide valid columns array");

        $errors = array();

        foreach ($columns as $column) {
            if (in_array($column, $headers)) continue;

            $errors[$column] .= "'{$column}' column is missing";
        }

        return $errors;
    }
}

/**
 * Input Functions
 */
if (!function_exists('arrayValue')) {
    /**
     * Get value of an array property
     */
    function arrayValue(&$input, $default = '')
    {
        return isset($input) ? $input : $default;
    }
}
if (!function_exists('sanitizeInput')) {
    /**
     * Filter and sanitize input
     */
    function sanitizeInput(string|array $input): string|array
    {
        if (is_array($input)) {
            foreach ($input as $k => $v) $input[$k] = sanitizeInput($v);
        } else {
            $input = htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
        }

        return $input;
    }
}
if (!function_exists('encodeInput')) {
    /**
     * Encode String
     */
    function encodeInput(string $string): string
    {
        if (empty($string)) return;

        return trim(base64_encode(gzdeflate($string)));
    }
}
if (!function_exists('decodeInput')) {
    /**
     * Decode String
     */
    function decodeInput(string $string): string
    {
        if (empty($string)) return;

        return trim(gzinflate(base64_decode($string)));
    }
}
if (!function_exists('generateCode')) {
    /**
     * Generate Code
     */
    function generateCode(
        string $string,
        string $prefix = '',
        int $length = 3,
        string $pad_string = '0',
        int $pad_type = STR_PAD_LEFT
    ): string {
        return $prefix . str_pad($string, $length, $pad_string, $pad_type);
    }
}

/**
 * Date Functions
 */
if (!function_exists('isValidDate')) {
    /**
     * Validate input datetime
     */
    function isValidDate(string|int $datetime): bool
    {
        return strtotime($datetime) !== false ? true : false;
    }
}
if (!function_exists('formatDate')) {
    /**
     * Format date
     * @throws \Exception
     */
    function formatDate(string|int $datetime, string $format = 'j M, Y \a\t h:i A'): string
    {
        if (!isValidDate($datetime)) throw new Exception("Enter valid datetime");

        $date_exp = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";

        if (preg_match($date_exp, $datetime) || $format === 'date') {
            $format = 'j M, Y';
        }

        return date($format, strtotime($datetime));
    }
}
if (!function_exists('spellSeconds')) {
    /**
     * Convert number of seconds into hours, minutes and seconds
     * and return an array containing those values
     * @throws \Exception
     */
    function spellSeconds(int $seconds, int $parts = 4, string $join = ', '): string
    {
        if (!is_int($seconds)) throw new Exception('Enter valid seconds');
        if (!in_array($parts, [1, 2, 3, 4])) throw new Exception('Enter valid parts value [1-4]');

        $time_parts = array();

        $sec_in_min     = 60;
        $sec_in_hour    = 60 * $sec_in_min;
        $sec_in_day     = 24 * $sec_in_hour;
        $hour_seconds   = $seconds % $sec_in_day;
        $min_seconds    = $hour_seconds % $sec_in_hour;

        // Format and return
        $sections = [
            'day'       => (int) floor($seconds / $sec_in_day),
            'hour'      => (int) floor($hour_seconds / $sec_in_hour),
            'minute'    => (int) floor($min_seconds / $sec_in_min),
            'second'    => (int) ceil($min_seconds % $sec_in_min),
        ];

        foreach ($sections as $name => $value) {
            if ($value > 0 && $parts !== 0) {
                --$parts;
                $time_parts[] = "{$value} {$name}" . ($value === 1 ? '' : 's');
            }
        }

        return implode($join, $time_parts) . ' ago';
    }
}

/**
 * Amount Functions
 */
if (!function_exists('formatAmount')) {
    /**
     * Format a number with grouped thousands
     * @throws \Exception
     */
    function formatAmount(
        string|float $amount,
        int $decimals = 4,
        string $decimal_separator = '.',
        string $thousands_separator = ''
    ): string {
        if ($amount === null) return 0;

        if (!is_numeric($amount)) throw new Exception('Enter valid amount');

        return number_format($amount, $decimals, $decimal_separator, $thousands_separator);
    }
}
if (!function_exists('displayAmount')) {
    /**
     * Format a number with grouped thousands
     * @throws \Exception
     */
    function displayAmount(
        string|float $amount,
        int $decimals = 2,
        string $decimal_separator = '.',
        string $thousands_separator = ','
    ): string {
        if (!is_numeric($amount)) throw new Exception('Enter valid amount');

        return number_format($amount, $decimals, $decimal_separator, $thousands_separator);
    }
}
if (!function_exists('SpellAmount')) {
    /**
     * Convert amount to word format
     * @throws \Exception
     */
    function SpellAmount(string|float $amount): string
    {
        if (!is_numeric($amount)) throw new Exception('Enter valid amount');

        $ones_arr = [1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'];
        $tens_arr = [1 => 'Ten', 2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty', 6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'];
        $hundreds_arr = ['Hundred', 'Thousand', 'Million', 'Billion', 'Trillion', 'Quadrillion'];

        $num_arr = explode('.', number_format($amount, 2, '.', ','));
        $decnum = $num_arr[1];

        $whole_arr = array_reverse(explode(',', $num_arr[0]));
        krsort($whole_arr);

        $string = '';

        foreach ($whole_arr as $key => $i) {
            if ($i < 20) {
                $string .= $ones_arr[$i];
            } elseif ($i < 100) {
                $string .= $tens_arr[substr($i, 0, 1)];
                $string .= ' ' . $ones_arr[substr($i, 1, 1)];
            } else {
                $string .= $ones_arr[substr($i, 0, 1)] . ' ' . $hundreds_arr[0];
                $string .= ' ' . $tens_arr[substr($i, 1, 1)];
                $string .= ' ' . $ones_arr[substr($i, 2, 1)];
            }

            if ($key > 0) $string .= ' ' . $hundreds_arr[$key] . ' ';
        }

        if ($decnum > 0) {
            $string .= ' and ';
            if ($decnum < 20) {
                $string .= $ones_arr[$decnum];
            } elseif ($decnum < 100) {
                $string .= $tens_arr[substr($decnum, 0, 1)];
                $string .= ' ' . $ones_arr[substr($decnum, 1, 1)];
            }
        }

        return $string;
    }
}
