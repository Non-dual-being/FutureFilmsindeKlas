<?php
declare(strict_types=1);
function render(string $path, array $vars = []): void {
    extract($vars, EXTR_SKIP);
    require $path;
}
/**
 * skip wil not overwrite the var if the vars already exists
 */
?>