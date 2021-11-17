<?php

namespace ahhp\jCalendar;

class jCalendar {
    private static $persianDayNames = [
        ["ش", "ی", "د", "س", "چ", "پ", "ج"],
        ["شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنجشنبه", "جمعه"]
    ];
    private static $persianMonthNames = [
        ["فروردین", "اردیبهشت", "خرداد", "تير", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دى", "بهمن", "اسفند"],
        ["فروردین", "اردیبهشت", "خرداد", "تير", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دى", "بهمن", "اسفند"]
    ];
    private static $persianDayTimes = ["pm" => "ب.ظ", "am" => "ق.ظ", "PM" => "بعد از ظهر", "AM" => "قبل از ظهر"];
    private static $persianOrdinalSuffix = "ام";
    private static $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    /**
     * PHP::getdate() in Jalali result
     *
     * @param $timestamp int|null  Unix Timestamp
     * @param bool $translate bool  Whether translate the number or not
     * @return array Date data on standard getdate result array
     */
    public static function getdate(int $timestamp = null, bool $translate = true): array {
        $timestamp = $timestamp ?? time();
        $getDate = [$timestamp];
        list($getDate['mday'], $getDate['wday'], $getDate['mon'], $getDate['year'], $getDate['yday'], $getDate['weekday'], $getDate['month'])
            = explode('|', self::date('j|w|n|Y|z|l|F', $timestamp, $translate));
        return $getDate;
    }

    /**
     * PHP::mktime() by Jalali inputs
     *
     * @param $hour int|null  Standard hour
     * @param $minute int|null  Standard minute
     * @param $second int|null  Standard second
     * @param $month int|null  Standard Jalali month
     * @param $day int|null  Standard Jalali day number
     * @param $year int|null  Standard Jalali year
     * @return int Unix Timestamp (GREGORIAN)
     */
    public static function mktime(int $hour = null, int $minute = null, int $second = null, int $month = null, int $day = null, int $year = null): int {
        $fmt = ($hour ?? 'H') . '|' . ($minute ?? 'i') . '|' . ($second ?? 's') . '|' . ($month ?? 'n') . '|' . ($day ?? 'j') . '|' . ($year ?? 'Y');
        list($hour, $minute, $second, $month, $day, $year) = explode('|', self::date($fmt, null, false));
        list($year, $month, $day) = self::jalali_to_gregorian($year, $month, $day);
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    /**
     * PHP::date() by Jalali result
     *
     * @param $format string  Standard PHP::date() format
     * @param $stamp int|null  Unix Timestamp
     * @param bool $translate bool  Whether translate the number or not
     * @return string Converted input
     */
    public static function date(string $fmt, int $stamp = null, bool $translate = true): string {
        $format = [];
        $gmt = $gmt ?? date("Z");
        $stamp = ($stamp ?? time()) + $gmt;
        $matches = [];
        preg_match_all("(a|A|B|D|e|g|G|h|H|i|I|l|o|T|s|u|U|Z)", $fmt, $matches);
        $formatArr = array_merge($matches[0], ['Y', 'n', 'j']);
        $fullFormat = explode("|", date(join("|", $formatArr), $stamp));
        for ($i = 0, $count = count($formatArr); $i < $count; $i += 1) {
            $format[$formatArr[$i]] = $fullFormat[$i];
        }

        list($format['Y'], $format['n'], $format['j']) = self::gregorian_to_jalali($format['Y'], $format['n'], $format['j']);

        $result = '';
        for ($i = 0; $i < strlen($fmt); $i += 1) {
            $char = $fmt[$i];
            $tch = $char;
            switch ($char) {
                // Day
                case 'd':
                    $tch = str_pad($format['j'], 2, '0', STR_PAD_LEFT);
                    break;
                case 'D':
                    $tch = self::$persianDayNames[0][array_search(strtolower($format['D']), ["sat", "sun", "mon", "tue", "wed", "thu", "fri"])];
                    break;
                case 'l':
                    $tch = self::$persianDayNames[1][array_search(strtolower($format['l']), ["saturday", "sunday", "monday", "tuesday", "wednesday", "thursday", "friday"])];
                    break;
                case 'N':
                case 'w':
                    $tch = array_search(strtolower(date('l', $stamp)), ["saturday", "sunday", "monday", "tuesday", "wednesday", "thursday", "friday"]);
                    if ($char == 'N') {
                        $tch += 1;
                    }
                    break;
                case 'S':
                    $tch = self::$persianOrdinalSuffix;
                    break;
                case 'z':
                    $tch = ($format['n'] > 6 ? 186 + (($format['n'] - 6 - 1) * 30) : ($format['n'] - 1) * 31) + $format['j'];
                    break;

                // Week
                case 'W':
                    $tch = ceil(self::date('z', $stamp, false) / 7);
                    break;

                // Month
                case 'F':
                    $tch = self::$persianMonthNames[0][$format['n'] - 1];
                    break;
                case 'm':
                    $tch = str_pad($format['n'], 2, '0', STR_PAD_LEFT);
                    break;
                case 'M':
                    $tch = self::$persianMonthNames[1][$format['n'] - 1];
                    break;
                case 't':
                    $tch = $format['n'] < 7 ? 31 : ($format['n'] < 12 || self::date('L', $stamp, false) == 1 ? 30 : 29);
                    break;

                // Year
                case 'L':
                    $tch = date("L", (time() - 31536000)) == 1 ? 1 : 0; // previous Gregorian year
                    break;
                case 'o':
                    $tch = self::gregorian_to_jalali($format['o'], $format['n'], $format['j'])[0];
                    break;
                case 'y':
                    $tch = str_pad($format['Y'] % 100, 2, '0', STR_PAD_LEFT);
                    break;

                // Time
                case 'a':
                    $tch = self::$persianDayTimes[$format['a']];
                    break;
                case 'A':
                    $tch = self::$persianDayTimes[$format['A']];
                    break;
                case 'v':
                    $tch = (int)self::date('u', $stamp, false) / 1000;
                    break;

                // Timezone
                case 'O':
                case 'p':
                case 'P':
                    $z = self::date('Z', $stamp, false);
                    $positive_z = abs((int)($z) / 3600);
                    $z_hour = str_pad((int)$positive_z, 2, '0', STR_PAD_LEFT);
                    $z_minute = str_pad(($positive_z - ($positive_z > 1 ? $z_hour : 0)) * 60, 2, '0', STR_PAD_LEFT);
                    $z_sign = $z >= 0 ? "+" : "-";
                    $tch = $char == 'O' ? $z_sign . $z_hour . $z_minute : ($char == 'p' && $z_hour == '00' && $z_minute == '00' ? 'Z' : $z_sign . $z_hour . ':' . $z_minute);
                    break;

                // Fill Date.Time
                case 'c':
                    $tch = self::date('Y-m-dTH:i:sP', $stamp, $translate);
                    break;
                case 'r':
                    $tch = self::date('l j F Y h:i:s O', $stamp, $translate);
                    break;

                case 'B':
                case 'e':
                case 'g':
                case 'G':
                case 'h':
                case 'H':
                case 'i':
                case 'I':
                case 'j':
                case 'n':
                case 'T':
                case 's':
                case 'u':
                case 'U':
                case 'Y':
                case 'Z':
                    $tch = $format[$char];
                    break;
            }

            $result .= $translate ? self::translate($tch) : $tch;
        }
        return $result;
    }

    private static function translate($str) {
        return str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], self::$persianNumbers, $str);
    }

    /**
     * Conversion methods
     * Thanks to Roozbeh Pournader and Mohammad Toosi for their Date Conversion program
     */
    public static function gregorian_to_jalali(int $g_y, int $g_m, int $g_d): array {
        $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        $gy = $g_y - 1600;
        $gm = $g_m - 1;
        $gd = $g_d - 1;
        $g_day_no = 365 * $gy + floor(($gy + 3) / 4) - floor(($gy + 99) / 100) + floor(($gy + 399) / 400);
        for ($i = 0; $i < $gm; ++$i) $g_day_no += $g_days_in_month[$i];
        if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0))) $g_day_no++; /* leap and after Feb */
        $g_day_no += $gd;
        $j_day_no = $g_day_no - 79;
        $j_np = floor($j_day_no / 12053); /* 12053 = 365*33 + 32/4 */
        $j_day_no = $j_day_no % 12053;
        $jy = 979 + 33 * $j_np + 4 * floor($j_day_no / 1461); /* 1461 = 365*4 + 4/4 */
        $j_day_no %= 1461;
        if ($j_day_no >= 366) {
            $jy += floor(($j_day_no - 1) / 365);
            $j_day_no = ($j_day_no - 1) % 365;
        }
        for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i) $j_day_no -= $j_days_in_month[$i];
        $jm = $i + 1;
        $jd = $j_day_no + 1;
        return [$jy, $jm, $jd];
    }

    public static function jalali_to_gregorian(int $j_y, int $j_m, int $j_d): array {
        $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        $jy = $j_y - 979;
        $jm = $j_m - 1;
        $jd = $j_d - 1;
        $j_day_no = 365 * $jy + floor($jy / 33) * 8 + floor(($jy % 33 + 3) / 4);
        for ($i = 0; $i < $jm; ++$i) $j_day_no += $j_days_in_month[$i];
        $j_day_no += $jd;
        $g_day_no = $j_day_no + 79;
        $gy = 1600 + 400 * floor($g_day_no / 146097); /* 146097 = 365*400 + 400/4 - 400/100 + 400/400 */
        $g_day_no = $g_day_no % 146097;
        $leap = true;
        if ($g_day_no >= 36525) { /* 36525 = 365*100 + 100/4 */
            $g_day_no--;
            $gy += 100 * floor($g_day_no / 36524); /* 36524 = 365*100 + 100/4 - 100/100 */
            $g_day_no = $g_day_no % 36524;
            if ($g_day_no >= 365) $g_day_no++; else $leap = false;
        }
        $gy += 4 * floor($g_day_no / 1461); /* 1461 = 365*4 + 4/4 */
        $g_day_no %= 1461;
        if ($g_day_no >= 366) {
            $leap = false;
            $g_day_no--;
            $gy += floor($g_day_no / 365);
            $g_day_no = $g_day_no % 365;
        }
        for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++) $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);
        $gm = $i + 1;
        $gd = $g_day_no + 1;
        return [$gy, $gm, $gd];
    }
}