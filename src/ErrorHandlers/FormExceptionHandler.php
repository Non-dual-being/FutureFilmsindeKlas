<?php
namespace GeoFort\ErrorHandlers;

use GeoFort\Enums\FlashTarget\FlashTargetContract;
use GeoFort\Enums\FlashTarget\GeneralTarget;

class FormExceptionHandler extends GeneralException {
    public string $target; /**calender || calender_Submit || general */
    public string $msgType; /**error || success */
    private static int $defaultCode = 0; /**kan hier niet code gebruik, is gereserveeerd door de parent */

    /**
     * protected zorgt ervoor dat je de construct niet kan aanroepen buiten de klasse
     * En dus dat alles via de static moethode moet lopen
     */

    protected function __construct(string $target, string $message, string $msgType = 'error'){
        $this->target = $target;
        $this->msgType = $msgType;


        // Belangrijk: geef message & code door aan de ouder (RuntimeException)

        parent::__construct($message, self::$defaultCode);
    }

    public static function FormExceptionThrower(
        string $msg,
        string $msgType = 'error',
        FlashTargetContract $target = GeneralTarget::general,
        bool $throw = true
    ): void {
        if ($throw) throw new self($target->getValue(), $msg, $msgType);
    }

}


?>