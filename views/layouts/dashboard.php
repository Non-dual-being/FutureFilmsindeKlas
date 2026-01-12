<?php
declare(strict_types=1);
use GeoFort\Enums\FlashTarget\DashboardFlashTarget;
/**
 * links zijn relatief aan het de pagina van waaruit het wordt opgeroepen en niet waar dit bestand staat
 * daarom via een helper de baselink krijgen en dan het subpath zodat je productien en env altijd oke doorverwijzen
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/dashboard/index.css'))?>" >

    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src= "<?= htmlspecialchars(asset('scripts/dashboard/shell.js')) ?>"></script>
    <script defer src= "<?= htmlspecialchars(asset('scripts/shared/resize.js')) ?>" type="module"></script>
    <title>Future Dashboard</title>
</head>
<body class="dashboard">
    <div 
        class="dash-shell" 
        id="dash-body-container" 
        data-sidebar-open="false"
    >
        <?php render(VIEW_PATH . '/partials/dashboard/sidebar.php', [
                'navItems' => $navItems,
                'activePage' => $activePage
            ]); 
        ?>
        <div class="dash-backdrop" data-action="toggle-sidebar"></div>
    

        <div class="dash-content">
            <?php render(VIEW_PATH . '/partials/dashboard/topbar.php', [
                    'currentUserEmail' => $currentUserEmail,
                ]); 
            ?>

            <main class="dash-main">
                <?php render(VIEW_PATH . '/partials/dashboard/flash.php', [
                    'target'  => DashboardFlashTarget::Result,
                ])?>

                <?php require VIEW_PATH . '/dashboard/overview.php'; ?>
            </main>

            <?php require VIEW_PATH . '/partials/dashboard/footer.php' ?>
        </div>
    </div>
</body>
</html>