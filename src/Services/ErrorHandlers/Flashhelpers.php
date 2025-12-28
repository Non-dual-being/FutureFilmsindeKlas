<?php
use GeoFort\Enums\FlashTarget\FlashTargetContract;
use GeoFort\ErrorHandlers\FlashMessageHandler;

function flashException(FlashMessageHandler $handler, FlashTargetContract $target): object
{
    $data = $handler->get($target);

    return (object) [
        'message'       => $data['message'],
        'type'          => $data['type'],
        'hasMessage'    => !empty($data['message']),
    ];

    /**
     * object verschilt van array in type veiligheid
     * het leest duidelijker $flash->com_load_type
     * Type-hints
     * 
     */
}

?>