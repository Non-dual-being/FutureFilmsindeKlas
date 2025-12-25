<?php 
declare(strict_types=1);
$navItems = $navItems ?? [];
$activePage = $activePage ?? '';
?>
<aside class="dash-sidebar" id="dash-sidebar">
    <div class="dash-brand">
        <div class="dash-brand__name">FutureFilms</div>
        <div class="dash-brand__sub">GeoFort</div>
    </div>

    <nav class="dash-nav">
        <?php foreach ($navItems as $href => $label) : ?>
            <a
                class="dash-nav__item <?= $href === $activePage ? 'is-active' : '' ?>"
                href="<?= htmlspecialchars(asset($href))?>"
            > 
            <?= htmlspecialchars($label) ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <form 
        class="dash-logout"
        method="post"
        action="<?= htmlspecialchars(asset('auth/logout.php')) ?>"
        >
        <input 
            type="hidden" 
            name="csrf" 
            value="<?= htmlspecialchars($_SESSION['csrf']) ?? '' ?>"
        >
        <button 
            type="submit"
        >Logout</button>
    </form>
</aside>