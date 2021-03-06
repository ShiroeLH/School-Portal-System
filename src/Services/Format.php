<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Gibbon\Services;

use DateTime;
use Gibbon\Session;

/**
 * Format values based on locale and system settings.
 *
 * @version v16
 * @since   v16
 */
class Format
{
    use FormatResolver;

    protected static $settings = [
        'dateFormatPHP'     => 'd/m/Y',
        'dateTimeFormatPHP' => 'd/m/Y H:i',
        'timeFormatPHP'     => 'H:i',
    ];

    /**
     * Sets the internal formatting options from an array.
     *
     * @param array $settings
     */
    public static function setup(array $settings)
    {
        static::$settings = array_replace(static::$settings, $settings);
    }

    /**
     * Sets the formatting options from session i18n and database settings.
     *
     * @param Session $session
     */
    public static function setupFromSession(Session $session)
    {
        $settings = $session->get('i18n');
        
        $settings['absolutePath'] = $session->get('absolutePath');
        $settings['absoluteURL'] = $session->get('absoluteURL');
        $settings['gibbonThemeName'] = $session->get('gibbonThemeName');
        $settings['currency'] = $session->get('currency');
        $settings['currencySymbol'] = !empty(substr($settings['currency'], 4)) ? substr($settings['currency'], 4) : '';
        $settings['currencyName'] = substr($settings['currency'], 0, 3);
        $settings['nameFormatStaffInformal'] = $session->get('nameFormatStaffInformal');
        $settings['nameFormatStaffInformalReversed'] = $session->get('nameFormatStaffInformalReversed');
        $settings['nameFormatStaffFormal'] = $session->get('nameFormatStaffFormal');
        $settings['nameFormatStaffFormalReversed'] = $session->get('nameFormatStaffFormalReversed');
        
        static::setup($settings);
    }

    /**
     * Formats a YYYY-MM-DD date with the language-specific format. Optionally provide a format string to use instead.
     *
     * @param string $dateString
     * @param string $format
     * @return string
     */
    public static function date($dateString, $format = false)
    {
        $date = DateTime::createFromFormat('Y-m-d', substr($dateString, 0, 10));
        return $date ? $date->format($format ? $format : static::$settings['dateFormatPHP']) : $dateString;
    }

    /**
     * Converts a date in the language-specific format to YYYY-MM-DD.
     *
     * @param string $dateString
     * @return string
     */
    public static function dateConvert($dateString)
    {
        $date = DateTime::createFromFormat(static::$settings['dateFormatPHP'], $dateString);
        return $date ? $date->format('Y-m-d') : $dateString;
    }

    /**
     * Formats a YYYY-MM-DD H:I:S MySQL timestamp as a readable string. Optionally provide a format string to use.
     *
     * @param string $dateString
     * @param string $format
     * @return string
     */
    public static function dateTime($dateString, $format = false)
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
        return $date ? $date->format($format ? $format : static::$settings['dateTimeFormatPHP']) : $dateString;
    }
    
    /**
     * Formats a YYYY-MM-DD date as a readable string with month names.
     *
     * @param string $dateString
     * @return string
     */
    public static function dateReadable($dateString)
    {
        return static::date($dateString, 'F j, Y');
    }

    /**
     * Formats two YYYY-MM-DD dates with the language-specific format. Optionally provide a format string to use instead.
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @return string
     */
    public static function dateRange($dateFrom, $dateTo, $format = false)
    {
        return static::date($dateFrom, $format) . ' - ' . static::date($dateTo, $format);
    }

    /**
     * Formats two YYYY-MM-DD dates as a readable string, collapsing same months and same years.
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @return string
     */
    public static function dateRangeReadable($dateFrom, $dateTo)
    {
        $output = '';
        if (empty($dateFrom) || empty($dateTo)) return $output;

        $startDate = ($dateFrom instanceof DateTime)? $dateFrom : new DateTime($dateFrom);
        $endDate = ($dateTo instanceof DateTime)? $dateTo : new DateTime($dateTo);

        if ($startDate->format('Y-m') == $endDate->format('Y-m')) {
            $output = $startDate->format('M??Y');
        } else if ($startDate->format('Y') == $endDate->format('Y')) {
            $output = $startDate->format('M').' - '.$endDate->format('M??Y');
        } else {
            $output = $startDate->format('M??Y').' - '.$endDate->format('M??Y');
        }

        return $output;
    }  

    /**
     * Formats a Unix timestamp as the language-specific format. Optionally provide a format string to use instead.
     *
     * @param string|int $timestamp
     * @param string $format
     * @return string
     */
    public static function dateFromTimestamp($timestamp, $format = false)
    {
        $date = DateTime::createFromFormat('U', $timestamp);
        return $date ? $date->format($format ? $format : static::$settings['dateFormatPHP']) : $timestamp;
    }

    /**
     * Converts a YYYY-MM-DD date to a Unix timestamp.
     *
     * @return string
     */
    public static function timestamp($dateString)
    {
        if (strlen($dateString) == 10) $dateString .= ' 00:00:00';
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
        return $date ? $date->getTimestamp() : 0;
    }

    /**
     * Formats a time from a given MySQL time or timestamp value.
     * 
     * @param string $timeString
     * @param string|bool $format
     * @return string
     */
    public static function time($timeString, $format = false)
    {
        $convertFormat = strlen($timeString) == 8? 'H:i:s' : 'Y-m-d H:i:s';
        $date = DateTime::createFromFormat($convertFormat, $timeString);
        return $date ? $date->format($format ? $format : static::$settings['timeFormatPHP']) : $timeString;
    }

    /**
     * Formats a range of times from two given MySQL time or timestamp values.
     * 
     * @param string $timeFrom
     * @param string $timeTo
     * @param string|bool $format
     * @return string
     */
    public static function timeRange($timeFrom, $timeTo, $format = false)
    {
        return static::time($timeFrom, $format) . ' - ' . static::time($timeTo, $format);
    }

    /**
     * Formats a number to an optional decimal points.
     *
     * @param int|string $value
     * @param int $decimals
     * @return string
     */
    public static function number($value, $decimals = 0)
    {
        return number_format($value, $decimals);
    }

    /**
     * Formats a currency with a symbol and two decimals, optionally displaying the currency name in brackets.
     *
     * @param string|int $value
     * @param bool $includeName
     * @return string
     */
    public static function currency($value, $includeName = false)
    {
        return static::$settings['currencySymbol'] . number_format($value, 2) . ( $includeName ? ' ('.static::$settings['currencyName'].')' : '');
    }

    /**
     * Formats a Y/N value as Yes or No in the current language.
     *
     * @param string $value
     * @param bool   $translate
     * @return string
     */
    public static function yesNo($value, $translate = true)
    {
        $value = ($value == 'Y' || $value == 'Yes') ? 'Yes' : 'No';
        
        return $translate ? __($value) : $value;
    }

    /**
     * Formats a link from a url. Automatically adds target _blank to external links.
     * 
     * @param string $url
     * @param string $text
     * @param string $title
     * @return string
     */
    public static function link($url, $text = '', $title = '')
    {
        if (empty($url)) return $text;
        if (!$text) $text = $url;

        if (stripos($url, static::$settings['absoluteURL']) === false) {
            return '<a href="'.$url.'" title="'.$title.'" target="_blank">'.$text.'</a>';
        } else {
            return '<a href="'.$url.'" title="'.$title.'">'.$text.'</a>';
        }
    }

    /**
     * Formats a YYYY-MM-DD date as a relative age with years and months.
     *
     * @param string $dateString
     * @param bool $short
     * @return string
     */
    public static function age($dateString, $short = false)
    {
        $date = DateTime::createFromFormat('Y-m-d', $dateString);
        if (!$date) return __('Unknown');

        $date = $date->diff(new DateTime());
        
        return $short 
            ? $date->y . __('y') .', '. $date->m . __('m')
            : $date->y .' '. __('years') .', '. $date->m .' '. __('months');
    }

    /**
     * Formats phone numbers, optionally including countrt code and types.
     * Adds spaces to 7-10 digit numbers based on the most common global formats.
     *
     * @param string|int $number
     * @param bool $countryCode
     * @param bool $type
     * @return string
     */
    public static function phone($number, $countryCode = false, $type = false)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        switch (strlen($number)) {
            case 7:     $number = preg_replace('/([0-9]{3})([0-9]{4})/', '$1 $2', $number); break;
            case 8:     $number = preg_replace('/([0-9]{4})([0-9]{4})/', '$1 $2', $number); break;
            case 9:     $number = preg_replace('/([0-9]{3})([0-9]{2})([0-9]{2})([0-9]{2})/', '$1 - $2 $3 $4', $number); break;
            case 10:    $number = preg_replace('/([0-9]{3})([0-9]{2})([0-9]{2})([0-9]{3})/', '$1 - $2 $3 $4', $number); break;
        }

        return ($type? $type.': ' : '') . ($countryCode? '+'.$countryCode.' ' : '') . $number;
    }

    /**
     * Formats an address including optional district and country.
     *
     * @param string $address
     * @param string $addressDistrict
     * @param string $addressCountry
     * @return string
     */
    public static function address($address, $addressDistrict, $addressCountry)
    {
        if (empty($address)) return '';

        return $address . ($addressDistrict? ', '.$addressDistrict : '') . ($addressCountry? ', '.$addressCountry : '');
    }

    /**
     * Formats a name based on the provided Role Category. Optionally reverses the name (surname first) or uses an informal format (no title).
     *
     * @param string $title
     * @param string $preferredName
     * @param string $surname
     * @param string $roleCategory
     * @param bool $reverse
     * @param bool $informal
     * @return string
     */
    public static function name($title, $preferredName, $surname, $roleCategory = 'Staff', $reverse = false, $informal = false)
    {
        $output = '';

        if (empty($preferredName) && empty($surname)) return '';

        if ($roleCategory == 'Staff' or $roleCategory == 'Other') {
            $setting = 'nameFormatStaff' . ($informal? 'Informal' : 'Formal') . ($reverse? 'Reversed' : '');
            $format = isset(static::$settings[$setting])? static::$settings[$setting] : '[title] [preferredName:1]. [surname]';

            $output = preg_replace_callback('/\[+([^\]]*)\]+/u',
                function ($matches) use ($title, $preferredName, $surname) {
                    list($token, $length) = array_pad(explode(':', $matches[1], 2), 2, false);
                    return isset($$token)
                        ? (!empty($length)? mb_substr($$token, 0, intval($length)) : $$token)
                        : '';
                },
            $format);

        } elseif ($roleCategory == 'Parent') {
            $format = (!$informal? '%1$s ' : '') . ($reverse? '%3$s, %2$s' : '%2$s %3$s');
            $output = sprintf($format, $title, $preferredName, $surname);
        } elseif ($roleCategory == 'Student') {
            $format = $reverse ? '%2$s, %1$s' : '%1$s %2$s';
            $output = sprintf($format, $preferredName, $surname);
        }

        return trim($output, ' ');
    }

    /**
     * Formats a list of names from an array containing standard title, preferredName & surname fields.
     * 
     * @param array $list
     * @param string $roleCategory
     * @param bool $reverse
     * @param bool $informal
     * @return string
     */
    public static function nameList($list, $roleCategory = 'Staff', $reverse = false, $informal = false)
    {
        $output = '';
        foreach ($list as $person) {
            $output .= static::name($person['title'], $person['preferredName'], $person['surname'], $roleCategory, $reverse, $informal);
            $output .= '<br/>';
        }

        return $output;
    }

    /**
     * Returns an HTML <img> based on the supplied photo path, using a placeholder image if none exists. Size may be either 75 or 240 at this time.
     *
     * @param string $path
     * @param int $size
     * @return string
     */
    public static function userPhoto($path, $size = 75)
    {   
        $sizeStyle = $size == 240 ? "width: 240px; height: 320px" : "width: 75px; height: 100px";

        if (empty($path) or file_exists(static::$settings['absolutePath'].'/'.$path) == false) {
            $path = '/themes/'.static::$settings['gibbonThemeName'].'/img/anonymous_'.$size.'.jpg';
        }

        return sprintf('<img class="user" style="%1$s" src="%2$s"><br/>', $sizeStyle, static::$settings['absoluteURL'].'/'.$path);
    }

    public static function userStatusInfo($person = [])
    {
        if (!empty($person['status']) && $person['status'] != 'Full') return __($person['status']);
        if (!empty($person['roleCategory']) && $person['roleCategory'] == 'Student') {
            if (!(empty($person['dateStart']) || $person['dateStart'] <= date('Y-m-d'))) return __('Before Start Date');
            if (!(empty($person['dateEnd']) || $person['dateEnd'] >= date('Y-m-d'))) return __('After End Date');
            if (empty($person['yearGroup'])) return __('Not Enroled');
        } else {
            if (!empty($person['staffType'])) return $person['staffType'];
        }
        return '';
    }

    /**
     * Returns the course and class name concatenated with a . (dot). The separator could become a setting at some point?
     *
     * @param string $courseName
     * @param string $className
     * @return string
     */
    public static function courseClassName($courseName, $className)
    {
        return $courseName .'.'. $className;
    }
}
