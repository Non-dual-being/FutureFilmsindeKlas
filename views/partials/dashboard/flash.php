<?php
declare(strict_types=1);
$flashMsgObj = flash($handler, $target);
if (!$flashMsgObj->hasMessage) return;

/**
 * bij een require van dit bestand in een ander bestand is de return correct
 * je verlaat dat gwn de uitvoering van dit bestand
 */

?>
<div 
    class="<?= htmlspecialchars($target::getWrapperClass())?>"
>
    <div 
        id="<?= htmlspecialchars($target->varName())?>" 
        class="future-flash-message flash-Hide"
        data-message-type=<?= htmlspecialchars($flashMsgObj->type)?>
    >
        <?= htmlspecialchars($flashMsgObj->message); ?>
    </div>
</div>


