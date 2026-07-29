<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * HasJalaliDates Trait
 * 
 * Provides automatic conversion between Jalali (Persian/Shamsi) and Gregorian dates.
 * All dates are stored in the database as Gregorian (ISO 8601 format).
 * When retrieving dates, they are automatically converted to Jalali for display.
 * When setting dates from user input (Jalali), they are automatically converted to Gregorian for storage.
 * 
 * Usage:
 *   - In your model: use HasJalaliDates;
 *   - Define jalaliDates array: protected $jalaliDates = ['created_at', 'published_at'];
 *   - All dates in that array will auto-convert
 */
trait HasJalaliDates
{
    /**
     * Array of date attributes that should be converted to/from Jalali.
     * Models using this trait may define their own list.
     *
     * @var array|null
     */
    protected $jalaliDates;

    /**
     * Jalali month names (fa)
     */
    protected static array $jalaliMonths = [
        1 => 'فروردین',
        2 => 'اردیبهشت',
        3 => 'خرداد',
        4 => 'تیر',
        5 => 'مرداد',
        6 => 'شهریور',
        7 => 'مهر',
        8 => 'آبان',
        9 => 'آذر',
        10 => 'دی',
        11 => 'بهمن',
        12 => 'اسفند',
    ];

    /**
     * Convert Gregorian date to Jalali (Shamsi) date
     * 
     * @param Carbon|string|null $date
     * @return string|null Jalali date in format YYYY-MM-DD
     */
    public static function gregorianToJalali($date): ?string
    {
        if (!$date) {
            return null;
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        if (!$date instanceof Carbon) {
            return null;
        }

        return self::convertToJalali(
            $date->year,
            $date->month,
            $date->day
        );
    }

    /**
     * Convert Jalali date to Gregorian date
     * 
     * @param string $jalaliDate Jalali date in format YYYY-MM-DD
     * @return Carbon|null
     */
    public static function jalaliToGregorian(string $jalaliDate): ?Carbon
    {
        try {
            $parts = explode('-', $jalaliDate);
            if (count($parts) !== 3) {
                return null;
            }

            [$jy, $jm, $jd] = array_map('intval', $parts);

            if ($jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
                return null;
            }

            $gregorian = self::convertToGregorian($jy, $jm, $jd);
            if (!$gregorian) {
                return null;
            }

            return Carbon::createFromFormat('Y-m-d', $gregorian);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert Jalali date components to Gregorian date string (YYYY-MM-DD)
     * 
     * Algorithm: Standard Jalali to Gregorian conversion
     */
    private static function convertToGregorian(int $jy, int $jm, int $jd): ?string
    {
        // Calculate total days from Jalali epoch
        $jy += 1595;
        $days = 365 * $jy + (int)(($jy) / 33) * 8 + (int)((($jy) % 33 + 3) / 4);
        $days += 29 * ($jm - 1);

        if ($jm > 6) {
            $days += ($jm - 7);
        }

        $days += $jd;

        // Convert to Gregorian
        $gy = 400 * (int)($days / 146097);
        $days %= 146097;

        $flag = true;
        if ($days >= 36525) {
            $days--;
            $gy += 100 * (int)($days / 36524);
            $days %= 36524;

            if ($days >= 365) {
                $days++;
            }
            $flag = false;
        }

        $gy += 4 * (int)($days / 1461);
        $days %= 1461;

        if ($flag) {
            if ($days >= 366) {
                $days--;
                $gy += (int)($days / 365);
                $days = ($days % 365);
            }
        } else {
            $gy += (int)($days / 365);
            $days %= 365;
        }

        $isLeapYear = (($gy % 4 === 0) && ($gy % 100 !== 0)) || ($gy % 400 === 0);
        $monthDays = [31, ($isLeapYear ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        $month = 1;
        foreach ($monthDays as $day) {
            if ($days < $day) {
                break;
            }
            $days -= $day;
            $month++;
        }

        return sprintf('%04d-%02d-%02d', $gy, $month, $days + 1);
    }

    /**
     * Convert Gregorian date components to Jalali date string (YYYY-MM-DD)
     * 
     * Algorithm: Standard Gregorian to Jalali conversion
     */
    private static function convertToJalali(int $gy, int $gm, int $gd): ?string
    {
        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $jy = 0;
            $gy -= 621;
        }

        if ($gm > 2) {
            $gy2 = $gy + 1;
        } else {
            $gy2 = $gy;
        }

        $days = (365 * $gy) + ((int)($gy2 / 4)) - ((int)($gy2 / 100)) + ((int)($gy2 / 400)) - 80 + $gd + [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334][$gm - 1];

        $jy += 1595 + (int)($days / 365);
        $days %= 365;

        $isLeapYear = ((($jy + 1) % 4 === 0) && ((($jy + 1) % 100 !== 0) || (($jy + 1) % 400 === 0)));

        if ($days < 186) {
            $jm = 1 + (int)($days / 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + (int)(($days - 186) / 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return sprintf('%04d-%02d-%02d', $jy, $jm, $jd);
    }

    /**
     * Format Jalali date with month name
     * Example: 1403-01-15 becomes "15 فروردین 1403"
     * 
     * @param string $jalaliDate in format YYYY-MM-DD
     * @param string $format 'd m Y' or 'd M Y' (M = month name)
     * @return string
     */
    public static function formatJalaliDate(string $jalaliDate, string $format = 'd M Y'): string
    {
        $parts = explode('-', $jalaliDate);
        if (count($parts) !== 3) {
            return $jalaliDate;
        }

        [$year, $month, $day] = $parts;
        $month = (int)$month;

        $monthName = self::$jalaliMonths[$month] ?? $month;

        return str_replace(
            ['d', 'm', 'M', 'Y'],
            [sprintf('%02d', $day), sprintf('%02d', $month), $monthName, $year],
            $format
        );
    }

    /**
     * Get Jalali date formatted for display
     * 
     * @param string $attribute
     * @return string|null
     */
    public function getJalaliDate(string $attribute): ?string
    {
        $value = $this->getAttribute($attribute);

        if (!$value) {
            return null;
        }

        if (!$value instanceof Carbon) {
            $value = Carbon::parse($value);
        }

        return self::gregorianToJalali($value);
    }

    /**
     * Get Jalali date formatted with month name for display
     * 
     * @param string $attribute
     * @param string $format default: 'd M Y' (e.g., "15 فروردین 1403")
     * @return string|null
     */
    public function getJalaliDateFormatted(string $attribute, string $format = 'd M Y'): ?string
    {
        $jalaliDate = $this->getJalaliDate($attribute);

        if (!$jalaliDate) {
            return null;
        }

        return self::formatJalaliDate($jalaliDate, $format);
    }

    /**
     * Set a date from Jalali input
     * Automatically converts to Gregorian for storage
     * 
     * @param string $attribute
     * @param string $jalaliDate in format YYYY-MM-DD
     * @return void
     */
    public function setJalaliDate(string $attribute, string $jalaliDate): void
    {
        $gregorian = self::jalaliToGregorian($jalaliDate);

        if ($gregorian) {
            $this->setAttribute($attribute, $gregorian);
        }
    }

    /**
     * Override getAttribute to auto-convert Jalali dates
     * 
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        // Check if this attribute is in the jalaliDates array
        if (!in_array($key, $this->jalaliDates ?? [])) {
            return $value;
        }

        // If it's a Carbon instance, convert to Jalali
        if ($value instanceof Carbon) {
            return self::gregorianToJalali($value);
        }

        // If it's a date string, convert to Jalali
        if (is_string($value) && !empty($value)) {
            try {
                $carbon = Carbon::parse($value);
                return self::gregorianToJalali($carbon);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Override setAttribute to auto-convert from Jalali to Gregorian
     * 
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // Check if this attribute is in the jalaliDates array
        if (in_array($key, $this->jalaliDates ?? []) && is_string($value)) {
            // Try to convert from Jalali to Gregorian
            $gregorian = self::jalaliToGregorian($value);

            if ($gregorian) {
                $value = $gregorian;
            }
        }

        return parent::setAttribute($key, $value);
    }
}
