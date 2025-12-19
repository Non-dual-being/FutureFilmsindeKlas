<?php
    use GeoFort\Enums\FlashTarget\LoginFlashTarget;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Future Login Panel</title>
    <link rel="stylesheet" href="./../styles/global.css">
    <link rel="stylesheet" href="./../styles/flash.css">
    <link rel="stylesheet" href="./../styles/video-player.css">
    <link rel="stylesheet" href="./../styles/inlog-page.css">
    <script src="./../scripts/shared/cssGlobals.js" defer type="module"></script>
    <script src="./../scripts/auth/loginpage.js" defer type="module"></script>
    <script defer>window.flashTargetIds = <?= json_encode(LoginFlashTarget::getValues()); ?></script>
    <script defer>window.flashPrefix = <?= json_encode(LoginFlashTarget::FLASHMESSAGE_PREFIX); ?></script>
</head>
<body class="main-grid">
    <main class="login-container">
        <h1 class="inlogh1">INLOG FUTURE DASHBOARD</h1>

        <?php $GeneralMsg = flash($FlashHandler, LoginFlashTarget::General)?>

        <?php if ($GeneralMsg->hasMessage) :?>
        <div 
            class="<?= htmlspecialchars(LoginFlashTarget::getWrapperClass())?>"
        >
            <div 
                id="<?= htmlspecialchars(LoginFlashTarget::General->varName())?>" 
                class="future-flash-message flash-hide<?php echo htmlspecialchars($GeneralMsg->type);?>"
                data-message-type=<?= htmlspecialchars($GeneralMsg->type)?>
            >
                <?= htmlspecialchars($GeneralMsg->message); ?>
            </div>
        </div>
        <?php endif;?>

        <form method="POST" class="login-form" id="login-form">
            <input 
                type="hidden" 
                name="<?= htmlspecialchars(CSRF); ?>" 
                value="<?= $_SESSION[CSRF]; ?>">
            <section class="email">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Email"
                    autocomplete="email" 
                    required
                    inputmode="email"
                >
            </section>
            
            <section class="password">
                <label for="password">Wachtwoord</label>
                <input 
                    type="password" 
                    autocomplete="current-password" 
                    id="password" 
                    name="password" 
                    placeholder="Wachtwoord" 
                    required
                >

            </section>
          
            <!--De autocompletion is voor browsers zodat ze op een correcte manier het wachtwoord kunnen automatisch invulen-->
            <button 
                type="submit" 
                id="verzendknop" 
                name="loginSubmit"
                class="submit-button"
            >Inloggen</button>

            <?php $inlogSubmitMsg = flash($FlashHandler, LoginFlashTarget::Inlog_Submit)?>
            <?php if ($inlogSubmitMsg->hasMessage) :?>
            <div 
                class="<?= htmlspecialchars(LoginFlashTarget::getWrapperClass())?>"
            >
                <div 
                    id="<?= htmlspecialchars(LoginFlashTarget::Inlog_Submit->varName())?>" 
                    class="future-flash-message flash-hide <?php echo htmlspecialchars($inlogSubmitMsg->type);?>"
                    data-message-type=<?= htmlspecialchars($inlogSubmitMsg->type)?>
                >
                    <?= htmlspecialchars($inlogSubmitMsg->message); ?>
                </div>
            </div>
            <?php endif;?>

            <?php $inactiveMsg = flash($FlashHandler, LoginFlashTarget::InActive)?>

            <?php if ($inactiveMsg->hasMessage) :?>
            <div 
                class="<?= htmlspecialchars(LoginFlashTarget::getWrapperClass())?>"
            >
                <div 
                    id="<?= htmlspecialchars(LoginFlashTarget::InActive->varName())?>" 
                    class="future-flash-message flash-hide<?php echo htmlspecialchars($inactiveMsg->type);?>"
                    data-message-type=<?= htmlspecialchars($inactiveMsg->type)?>
                >
                    <?= htmlspecialchars($inactiveMsg->message); ?>
                </div>
            </div>
            <?php endif;?>
        </form>
    </main>
</body>
</html>
