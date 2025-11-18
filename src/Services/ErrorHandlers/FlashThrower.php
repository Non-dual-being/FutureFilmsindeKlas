<?php
declare(strict_types=1);
namespace GeoFort\Services\ErrorHandlers;

use GeoFort\Enums\FlashTarget\FlashTargetContract;
use GeoFort\ErrorHandlers\FormExceptionHandler;
use InvalidArgumentException; // Gebruik de standaard PHP exception


final class FlashThrower 
{
    /**
     * @param class-string<FlashTargetContract> $targetEnumClass De FQCN van de Enum.
     */

    public function __construct(private string $targetEnumClass)
    {
        /**
         * De Class string is onderdeel van reflection methode in php
         * Het stelt php mogelijk de blauwdruk van de klasse te bekijken
         */

        /**
         * PHP 8.0 heeft feature "Constructor Property Promotion"
         * Is het een verkorte manier om een private string te koppelen aan een instantie van de klasse
         * 
         */
        if (!is_subclass_of($this->targetEnumClass, FlashTargetContract::class))
            throw new InvalidArgumentException("$targetEnumClass verwijst naar een klasse met ontbrekend of ongeldig contract");  
    }

    /**
     * Gooit een FormExceptionHandler voor een specifieke target.
     *
     * @param string $caseName De naam van de Enum-case (bijv. 'Calender' of 'General').
     * @param string $msg De boodschap voor de gebruiker.
     * @param string $msgType Het type (error|success).
     */

    public function throw(string $msg, string $msgType, FlashTargetContract $enumCase, bool $throw = true): void
    {
        FormExceptionHandler::FormExceptionThrower(
          
            msg: $msg,
            msgType: $msgType,
            target: $enumCase,
            throw: $throw,
        );

    }
}
?>