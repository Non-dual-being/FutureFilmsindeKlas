<?php
declare(strict_types=1);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/shared/layout-vars.css'))?>" >
    <link rel="stylesheet" href="./styles/global.css">
    <link rel="stylesheet" href="./styles/selection-page.css">
    
    <script defer src="./scripts/public/selectionscript.js" type="module"></script>
    <script defer src="<?= htmlentities(asset('scripts/shared/resize.js'))?>" type="module"></script>
    
    <title>Jukebox Future Films</title>
    <script>
        document.documentElement.setAttribute('future-theme', 'light')
    </script>
</head>

<body class="futurefilms">
    <div class="app-container">
        <div id="loadingScreen" class="loading-screen">
            <div class="loader"></div>
        </div>
        <header>
            <div class="header-track">
                <div 
                    class="header-slot header-slot--left"
                    aria-hidden="true"
                >
                </div>
                <h1 class="header-title">FutureFilms VideoQuiz</h1>

                <div class="header-slot header-slot--right">
                    <!-- NIEUWE DOWNLOAD LINK (vervangt je oude knop) -->
                    <a
                        href="./assets/spelbord_future_films.pdf"
                        class="header-download-link"
                        download="Spelbord-FutureFilms.pdf"
                        aria-label="Download het spelbord als PDF"
                    >
                        <!-- Standaard, herkenbaar “download” icoon -->
                        <svg
                            class="icon"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                            focusable="false"
                        >
                            <path
                                d="M5 20h14v2H5v-2zm7-16a1 1 0 0 1 1 1v7.586l2.293-2.293a1 1 0 1 1 1.414 1.414l-4.0 4.0a1 1 0 0 1-1.414 0l-4.0-4.0a1 1 0 1 1 1.414-1.414L11 12.586V5a1 1 0 0 1 1-1z"
                                fill="currentColor"
                            />
                        </svg>
                        <span class="visually-hidden">Download spelbord</span>
                    </a>
                </div>
            </div>
        </header>
        <main class="selection-main">
            <div class="intro-text">
                <h2>
                    <span class="main-text">Selecteer en start de Quiz</span>
                    <span class="subtitle">Klik op de checkboxes om minimaal twee categorieën te selecteren</span>
                </h2>
            </div>
            <form id="themesForm" class="themes-form">

            </form>
        </main>

        <div class="downloadContainer">
            <button
                id="Downloadknop"
                class="download"
            >Download spelbord</button>
        </div>
        <div class="start-container">
            <div class="start-knop-content"
                data-info-disabled-tip="Selecteer twee categorieën om de Jukebox te starten">
                <img
                    class="startknopimg"
                    src="./assets/Future_Films_website_knop_2.png"
                    alt="startquiz"
                >
                <button
                    id="StartQuizknop"
                    class="StartQuizknop"
                    disabled
                >Start Quiz</button>
            </div>
        </div>
        <footer>
            <div class="icons">
                <div class="icon"><img src="./assets/icons/biodiversiteit.png" alt="Icon 1"></div>
                <div class="icon"><img src="./assets/icons/energietransititie.png" alt="Icon 2"></div>
                <div class="icon"><img src="./assets/icons/grondstoffen.png" alt="Icon 3"></div>
                <div class="icon"><img src="./assets/icons/iot.png" alt="Icon 4"></div>
                <div class="icon"><img src="./assets/icons/klimaatverandering.png" alt="Icon 5"></div>
                <div class="icon"><img src="./assets/icons/mobiliteit.png" alt="Icon 6"></div>
                <div class="icon"><img src="./assets/icons/voedselinnovatie.png" alt="Icon 7"></div>
                <div class="icon"><img src="./assets/icons/watermanagement.png" alt="Icon 8"></div>
            </div>
        </footer>
    </div>
</body>

</html>