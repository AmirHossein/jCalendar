<?php

use ahhp\jCalendar\jCalendar;

class jCalendarTest extends \PHPUnit\Framework\TestCase {
    public function testDate() {
        $t = strtotime('2021-09-23 05:00:00 +00:00');
        $this->assertEquals('1400-07-01 05:00:00', jCalendar::date('Y-m-d H:i:s', $t, false));
        $this->assertEquals('پنجشنبه 1 مهر 1400 05:00:00 +0000', jCalendar::date('r', $t, false));
    }

    public function testGetdate() {
        $t = strtotime('2021-09-23 05:00:00 +00:00');
        $expected = [
            0 => 1632373200,
            'mday' => 1,
            'wday' => 5,
            'mon' => 7,
            'year' => 1400,
            'yday' => 187,
            'weekday' => "پنجشنبه",
            'month' => "مهر"
        ];
        $this->assertEquals($expected, jCalendar::getdate($t, false));
    }

    public function testMakedate() {
        $t = strtotime('2021-09-23 05:00:00 +00:00');
        list($year, $month, $day, $hour, $minute, $second) = explode('|', jCalendar::date('Y|n|j|H|i|s', $t, false));
        $t2 = jCalendar::mktime($hour, $minute, $second, $month, $day, $year);
        $this->assertEquals($t2, $t);
    }
}