# jCalendar
Persian Calendar functions

[![Latest Stable Version](https://poser.pugx.org/ahhp/jCalendar)](//packagist.org/packages/ahhp/jCalendar) [![Total Downloads](https://poser.pugx.org/ahhp/jCalendar/downloads)](//packagist.org/packages/ahhp/jCalendar) [![Latest Unstable Version](https://poser.pugx.org/ahhp/jCalendar/v/unstable)](//packagist.org/packages/ahhp/jCalendar) [![License](https://poser.pugx.org/ahhp/jCalendar/license)](//packagist.org/packages/ahhp/jCalendar)

Usage:
```php
$c = new ahhp\jCalendar\jCalendar;
echo $c->date('j F'); // ۶ آبان

$date = $c->getdate();
print_r($date, true);

$timestamp = $c->mktime(19, 45, 30, 7, 23, 1366);
echo date('j F', $timestamp); // 15 October
echo $c->date('j F', $timestamp); // ۲۳ مهر

$c->farsiDigits = false; // ۲۳ مهر
echo $c->date('j F', $timestamp); // 23 
```