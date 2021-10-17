<?php

namespace ahhp\jCalendar;

class jCalendar {
    private $format = [];
    private $jformat = [];
    public $farsiDigits;

    public function __construct($farsiDigits = true) {
        $this->farsiDigits = $farsiDigits;
    }

    /**
     * PHP::getdate() in Jalali result
     *
     * @param $timestamp int|null  Unix Timestamp
     * @return array Date data on standard getdate result array
     */
    public function getdate(int $timestamp = null) {
        $timestamp = $timestamp ?? time();
        $this->jdate('', $timestamp);
        $getDate = getdate($timestamp);
        $getDate['mday'] = $this->jformat['j'];
        $getDate['wday'] = $this->jformat['w'];
        $getDate['mon'] = $this->jformat['n'];
        $getDate['year'] = $this->jformat['Y'];
        $getDate['yday'] = $this->jformat['z'];
        $getDate['weekday'] = $this->jformat['l'];
        $getDate['month'] = $this->jformat['F'];
        foreach ($getDate as $key => $value) {
            $getDate[$key] = $this->_translateDigits($value);
        }
        $getDate[0] = $timestamp;
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
    public function mktime(int $hour = null, int $minute = null, int $second = null, int $month = null, int $day = null, int $year = null) {
        $this->jdate('Y');
        $year = $year === null ? $this->jformat['Y'] : $year;
        $month = $month === null ? $this->jformat['n'] : $month;
        $day = $day === null ? $this->jformat['j'] : $day;
        $hour = $hour === null ? $this->jformat['h'] : $hour;
        $minute = $minute === null ? $this->jformat['i'] : $minute;
        $second = $second === null ? $this->jformat['s'] : $second;
        list($year, $month, $day) = $this->jalali_to_gregorian($year, $month, $day);
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    /**
     * PHP::date() by Jalali result
     *
     * @param $format string  Standard PHP::date() format
     * @param $stamp int|null  Unix Timestamp
     * @param $GMT int|null  Difference to server time in seconds
     * @return string Converted input
     */
    public function date(string $format, int $stamp = null, int $GMT = null) {
        $GMT = $GMT ?? date("Z");
        $stamp = ($stamp ?? time()) + $GMT;
        $formatArr = [
            'd', 'D', 'j', 'l', 'N', 'S', 'w', 'z', 'W', 'F', 'm', 'M', 'n', 't', 'L', 'o', 'Y', 'y',
            'a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'e', 'I', 'O', 'P', 'T', 'Z', 'c', 'r', 'U'
        ];
        $_weekdays = ["saturday", "sunday", "monday", "tuesday", "wednesday", "thursday", "friday"];

        $fullFormat = explode("|", date(join("|", $formatArr), $stamp));
        for ($i = 0, $count = count($formatArr); $i < $count; $i += 1) {
            $this->format[$formatArr[$i]] = $fullFormat[$i];
        }
        list($this->jformat['Y'], $this->jformat['m'], $this->jformat['d']) = $this->gregorian_to_jalali($this->format['Y'], $this->format['m'], $this->format['d']);
        $this->jformat['a'] = ($this->format['a'] == "pm") ? "ب.ظ" : "ق.ظ";
        $this->jformat['A'] = ($this->format['A'] == "PM") ? "بعد از ظهر" : "قبل از ظهر";
        $this->jformat['B'] = $this->format['B'];
        $this->jformat['D'] = ["ش", "ی", "د", "س", "چ", "پ", "ج"][array_search(strtolower($this->format['D']), ["sat", "sun", "mon", "tue", "wed", "thu", "fri"])];
        $this->jformat['F'] = $this->_getMonthName($this->jformat['m']);
        $this->jformat['h'] = $this->format['h'];
        $this->jformat['H'] = $this->format['H'];
        $this->jformat['g'] = $this->format['g'];
        $this->jformat['G'] = $this->format['G'];
        $this->jformat['i'] = $this->format['i'];
        $this->jformat['j'] = $this->jformat['d'];
        $this->jformat['d'] = ($this->jformat['d'] < 10) ? "0" . $this->jformat['d'] : $this->jformat['d'];
        $this->jformat['l'] = ["شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنجشنبه", "جمعه"][array_search(strtolower($this->format['l']), $_weekdays)];
        $ka = date("L", (time() - 31536000)); // previous Gregorian year
        $this->jformat['L'] = ($ka == 1) ? 1 : 0;
        $this->jformat['n'] = $this->jformat['m'];
        $this->jformat['m'] = ($this->jformat['m'] < 10) ? "0" . $this->jformat['m'] : $this->jformat['m'];
        $this->jformat['M'] = $this->jformat['F'];
        $this->jformat['N'] = 1 + array_search(strtolower($this->format['l']), $_weekdays);
        $this->jformat['o'] = $this->jformat['Y'];
        $this->jformat['w'] = $this->jformat['N'] - 1;
        $this->jformat['t'] = ($this->jformat['m'] <= 6) ? 31 : 30;
        $this->jformat['t'] = ($this->jformat['m'] == 12) ? ($this->jformat['L'] == 1 ? 30 : 29) : $this->jformat['t'];
        $this->jformat['s'] = $this->format['s'];
        $this->jformat['S'] = "ام";
        $this->jformat['e'] = $this->format['e'];
        $this->jformat['I'] = $this->format['I'];
        $this->jformat['u'] = $this->format['u'];
        $this->jformat['U'] = $this->format['U'];
        $this->jformat['y'] = $this->jformat['Y'] % 100;
        $this->jformat['Z'] = $this->format['Z'];
        $this->jformat['z'] = ($this->jformat['n'] > 6 ? 186 + (($this->jformat['n'] - 6 - 1) * 30) : ($this->jformat['n'] - 1) * 31) + $this->jformat['j'];
        $this->jformat['W'] = ceil($this->jformat['z'] / 7);
        $positive_z = abs(($this->jformat['Z']) / 3600);
        $z_hour = str_pad((int)$positive_z, 2, '0', STR_PAD_LEFT);
        $z_minute = str_pad(($positive_z - ($positive_z > 1 ? $z_hour : 0)) * 60, 2, '0', STR_PAD_LEFT);
        $this->jformat['P'] = ($this->jformat['Z'] >= 0 ? "+" : "-") . "$z_hour:$z_minute";
        $this->jformat['O'] = ($this->jformat['Z'] >= 0 ? "+" : "-") . $z_hour . $z_minute;
        $this->jformat['c'] = $this->jformat['Y'] . "-" . $this->jformat['m'] . "-" . $this->jformat['d'] . "-" . $this->jformat['H'] . " " . $this->jformat['i'] . ":" . $this->jformat['s'] . $this->jformat['P'];
        $this->jformat['r'] = $this->jformat['l'] . " " . $this->jformat['j'] . " " . $this->jformat['F'] . " " . $this->jformat['Y'] . " " . $this->jformat['h'] . ":" . $this->jformat['i'] . ":" . $this->jformat['s'] . " " . $this->jformat['O'];
        $this->jformat['T'] = $this->format['T'];

        foreach ($formatArr as $key) {
            $format = str_replace($key, $this->jformat[$key], $format);
        }
        return $this->_translateDigits($format);
    }

    public function jdate($format, $stamp = null, $GMT = null) {
        return $this->date($format, $stamp, $GMT);
    }

    /**
     * Built-in methods
     */
    private function _getMonthName($index) {
        return [null, "فروردین", "اردیبهشت", "خرداد", "تير", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دى", "بهمن", "اسفند"][$index];
    }

    private function _translateDigits($str) {
        return !$this->farsiDigits ? $str
            : str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], $str);
    }

    /**
     * Conversion methods
     * Thanks to Roozbeh Pournader and Mohammad Toosi for their Date Conversion program
     */
    public function gregorian_to_jalali($g_y, $g_m, $g_d) {
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

    public function jalali_to_gregorian($j_y, $j_m, $j_d) {
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