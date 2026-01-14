<?php
// views/error_view.php
// Variabelen $errorCode en $errorMessage worden door de router (error.php) doorgegeven.
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Systeemfout - Future Films</title>
    
    <!-- Link naar je bestaande, globale CSS voor een consistente stijl -->
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/error/index.css'))?>">
</head>
<body class="videoPlayer"> <!-- Gebruik je geanimeerde achtergrond! -->
    <div class="video-artboard"> <!-- Gebruik je artboard als container -->
        <div class="error-container">
            <h1 class="error-code"><?= htmlspecialchars($errorCode) ?></h1>
            <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
            <? if ($errorCode !== 404): ?>
            <p class="error-contact">
                Neem contact op met de beheerder als dit probleem aanhoudt.
            </p>
            <? else: ?>
                <?php include(VIEW_PATH . '/partials/error/404.php') ?>
            <? endif; ?>
        </div>
    </div>
</body>
</html>