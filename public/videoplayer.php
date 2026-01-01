<?php
declare(strict_types=1);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/shared/layout-vars.css'))?>" >
    <link rel="stylesheet" href="./styles/global.css">
    <link rel="stylesheet" href="./styles/video-player.css">

    <script defer scr="scripts/shared/cssGlobals.js" type="module"></script>
    <script defer src="scripts/public/videoscript.js" type="module"></script>
    <script defer src="<?= htmlspecialchars(asset('scripts/shared/resize.js'))?>" type="module"></script>
    
    <title>VideoPlayer</title>
</head>
<body class="videoPlayer">
    <header></header>
    <div class="video-artboard">

        <main class="video-player">
            <video id="videoPlayer" width="1080" controls playsinline></video>
        </main>
        <div class="allButtonsContainer">
            <aside class="future-controls">
                <h2 class="future-controls__title">FUTURE CONTROLS</h2>
                <button id="playButton" class="customButton">Speel</button>
                <button id="pauseButton" class="customButton">Pauze</button>
                <button id="fullscreenButton" class="customButton">Volledig Scherm</button>
            </aside>

            <div class="action-buttons">
                <button id="Terugknop" onclick="window.location.href = './index.php';">Nieuwe Selectie</button>
                <button id="shuffleknop" onclick="speelVideos(true);">Shuffle en Speel Opnieuw</button>
            </div>
        </div>
    </div>
    <footer></footer>
</body>
</html>
