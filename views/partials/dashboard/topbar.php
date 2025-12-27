<?php 
declare(strict_types=1);
$email = $currentUserEmail ?? '';
?>
<header class="dash-topbar">
    <div class="dash-topbar__left">
        <button 
            class="dash-btn dash-btn--ghost dash-burger"
            type="button"
            data-action="toggle-sidebar"
            aria-controls="dash-sidebar"
            aria-expanded="false"
        >
            Menu
        </button>
    </div>
   
    <div class="dash-topbar__title">
        <h1>Future Dashboard</h1>
        <p>GeoFort Analytics</p>
    </div>

    <div class="dash-topbar__right">
        <div class="dash-user">
            <div class="dash-user__label">Signed in</div>
            <div class="dash-user__value"><?= htmlspecialchars($email)?></div>
        </div>

        <form 
            action="<?= htmlspecialchars(asset('auth/logout.php')) ?>" 
            class="dash-logout dash-logout--topbar"
            method="post"
        >
            <input 
                type="hidden"
                name="csrf"
                value="<?= htmlspecialchars($_SESSION['csrf']) ?? ''; ?>"
            >
            <button type="submit" class="dash-btn dash-btn--ghost">Logout</button>
        </form>
    </div>
</header>