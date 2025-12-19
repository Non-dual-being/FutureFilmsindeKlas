<?php
use GeoFort\Enums\FlashTarget\DashboardFlashTarget;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./../styles/global.css">
    <link rel="stylesheet" href="./../styles/flash.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script defer>window.flashTargetIds = <?= json_encode(DashboardFlashTarget::getValues()); ?></script>
    <script defer>window.flashPrefix = <?= json_encode(DashboardFlashTarget::FLASHMESSAGE_PREFIX); ?></script>
    <title>Future Dashboard</title>
</head>
<body>
    <header>Future GeoFort Dashboard</header>
    <main>
        <?php $result = flash($flashHandler, DashboardFlashTarget::Result)?>
        <?php if ($result->hasMessage) :?>
        <div 
            class="<?= htmlspecialchars(DashboardFlashTarget::getWrapperClass())?>"
        >
            <div 
                id="<?= htmlspecialchars(DashboardFlashTarget::Result->varName())?>" 
                class="future-flash-message flash-hide<?php echo htmlspecialchars($result->type);?>"
                data-message-type=<?= htmlspecialchars($result->type)?>
            >
                <?= htmlspecialchars($result->message); ?>
            </div>
        </div>
        <?php endif;?>
    </main>
    <footer></footer>
</body>
</html>
