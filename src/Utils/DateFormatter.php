<?php 
declare(strict_types=1);
namespace GeoFort\Utils;
use DateTime;
use Exception;

class DateFormatter
{
    public static function parseToLongDutchDate(string $sqlDate, bool $withTime = true): ?string 
    {
            /**
     * Converteer een SQL-datum/datetime naar een lange Nederlandse datum.
     * Voorbeelden:
     *  - "2025-03-01"        -> "zaterdag 1 maart 2025"
     *  - "2025-03-01 14:35"  -> "zaterdag 1 maart 2025 om 14:35"
     *
     * @param string $sqlDate   Datum of datetime in formaat "Y-m-d" of "Y-m-d H:i[:s]"
     * @param bool   $withTime  Toon de tijd erbij als die aanwezig is
     * @return string|null      Geformatteerde Nederlandse datum, of null bij ongeldige input
     */
        if (trim($sqlDate) === '') return null;

        $dt = self::createDateTimeFromSql($sqlDate);

                // Nederlandse namen
        $days = [
            'Sunday' => 'zondag',
            'Monday' => 'maandag',
            'Tuesday' => 'dinsdag',
            'Wednesday' => 'woensdag',
            'Thursday' => 'donderdag',
            'Friday' => 'vrijdag',
            'Saturday' => 'zaterdag',
        ];

        $months = [
            1 => 'januari',
            2 => 'februari',
            3 => 'maart',
            4 => 'april',
            5 => 'mei',
            6 => 'juni',
            7 => 'juli',
            8 => 'augustus',
            9 => 'september',
            10 => 'oktober',
            11 => 'november',
            12 => 'december',
        ];
        
        //convet day
        $dayEn = $dt->format('l');
        $dayNl = $days[$dayEn] ?? strtolower($dayEn);
        $dayNumber = (int) $dt->format('j');
        

        //convert month
        $monthNumber = $dt->format('n');
        $monthNl = $months[$monthNumber] ?? $dt->format('F');

        //year
        $year = $dt->format('Y');

        $base = "{$dayNl} {$dayNumber} {$monthNl} {$year}";
        
        $hadTimeInput = self::inputHasTime($sqlDate);

        if ($withTime && $hadTimeInput){
            $time = $dt->format('H:i');
            return "{$base} om {$time}";
        }

        return $base;

        
    }

    private static function createDateTimeFromSql(string $sqlDate): ?DateTime 
    {
        $sqlDate = trim($sqlDate);

        $formats = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'];

        foreach($formats as $fmt){
            $dt = DateTime::createFromFormat($fmt, $sqlDate);
            if ($dt instanceof DateTime){
                $errors = DateTime::getLastErrors();
                if (empty($errors['warning_count']) && empty($errors['error_count'])){
                    return $dt;
                }
            }
        }

        try {
            $dt = new DateTime($sqlDate);
        } catch (Exception){
            return null;
        }

    }

    private static function inputHasTime(string $sqlDate): bool 
    {
        return (bool)preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}(:\d{2})?$/', trim($sqlDate));
    }


}

?>