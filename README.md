# jCalendar
Persian Calendar functions

Implementing PHP [date](https://www.php.net/manual/en/function.date.php), 
[getdate](https://www.php.net/manual/en/function.getdate.php), 
and [mktime](https://www.php.net/manual/en/function.mktime.php)
functions for Perisan calendar

[![Latest Stable Version](https://poser.pugx.org/ahhp/jcalendar/v)](//packagist.org/packages/ahhp/jcalendar) [![Total Downloads](https://poser.pugx.org/ahhp/jcalendar/downloads)](//packagist.org/packages/ahhp/jcalendar) [![Latest Unstable Version](https://poser.pugx.org/ahhp/jcalendar/v/unstable)](//packagist.org/packages/ahhp/jcalendar) [![License](https://poser.pugx.org/ahhp/jcalendar/license)](//packagist.org/packages/ahhp/jcalendar)

Install:
```
composer require ahhp/jcalendar
```
Usage:
```php
$t = strtotime('2021-09-23 05:00:00 +00:00'); // UNIX timestamp

ahhp\jCalendar\jCalendar::date('Y/m/d H:i:s', $t); // ۱۴۰۰/۰۷/۰۱ ۰۵:۰۰:۰۰

ahhp\jCalendar\jCalendar::date('Y-m-d H:i:s', $t, false); // 1400-07-01 05:00:00

ahhp\jCalendar\jCalendar::getdate($t, false);
/* See php getdate function
[
    [0] => 1632373200
    [mday] => 1
    [wday] => 5
    [mon] => 7
    [year] => 1400
    [yday] => 187
    [weekday] => پنجشنبه
    [month] => مهر
]
*/

$timestamp = ahhp\jCalendar\jCalendar::mktime(5, 0, 0, 7, 1, 1400); // 1400-07-1 05:00:00
date('r', $timestamp); // Thu, 23 Sep 2021 05:00:00 +0000

// Converters
ahhp\jCalendar\jCalendar::gregorian_to_jalali(2021, 9, 23); // [1400, 7, 1]
ahhp\jCalendar\jCalendar::jalali_to_gregorian(1400, 7, 1); // [2021, 9, 23]
```
