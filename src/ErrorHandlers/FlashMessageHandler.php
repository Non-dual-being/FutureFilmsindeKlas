<?php
declare(strict_types=1);
namespace GeoFort\ErrorHandlers;

use InvalidArgumentException;
use GeoFort\Enums\FlashTarget\FlashTargetContract;
use GeoFort\ErrorHandlers\FormExceptionHandler;

final class FlashMessageHandler {
    /**
         * Data-structuur:
         * [
         *   'message' => ['general' => string, '<target>' => string, ...],
         *   'type'    => 'error'|'success'
         * ]
     */
    private array $messages = [];

    /** 
     * @var class-string<FlashTargetContract> 
     * */

    private string $targetEnumClass;
    private const ALLOWED_FLASH_TYPES = ['error', 'success'];

    /**
     * @param class=string<FlashTargetContract> $TargetEnumClass De FQCN van de Enum
     */


    public function __construct(string $targetEnumClass) 
    {
        if (!is_subclass_of($targetEnumClass, FlashTargetContract::class))
                throw new InvalidArgumentException("De klasse {$targetEnumClass} moet de interface " . FlashTargetContract::class . " implementeren");

        $this->targetEnumClass = $targetEnumClass;
        $this->resetAllMessages();
    }

    public function handleException(FormExceptionHandler $e): void 
    {
        $target = $e->target;

        if (array_key_exists($target, $this->messages)){
            $this->messages[$target] = [
                'message' => $e->getMessage(),
                'type' => $this->sanitizeType($e->msgType)
            ];
        } else {
            $this->messages['general'] = [
                'message' => $e->getMessage ?? '',
                'type' => $this->sanitizeType($e->msgType)
            ];
        }
    }

    public function get(FlashTargetContract $target): array {
        return $this->messages[$target->getValue()] ?? 
        ['message' => '', 'type' => 'error'];
        
    }


    private function resetAllMessages(): void 
    {
        $this->messages = [];
        foreach ($this->targetEnumClass::cases() as $case){
            $this->messages[$case->getValue()] = ['message' => '', 'type' => ''];
        }
    }

    private function sanitizeType(string $type): string {
       return in_array($type, self::ALLOWED_FLASH_TYPES, true)
            ? $type
            : self::ALLOWED_FLASH_TYPES[0];
    } 
}
?>