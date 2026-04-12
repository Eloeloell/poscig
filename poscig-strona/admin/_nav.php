<?php
declare(strict_types=1);

function admin_nav(string $active = ''): void
{
    $role = (string) ($_SESSION['role'] ?? '');
    $username = (string) ($_SESSION['username'] ?? '');

    $items = [
        ['key' => 'dashboard', 'label' => 'Panel', 'href' => 'dashboard.php', 'adminOnly' => false],
        ['key' => 'users', 'label' => 'U&#380;ytkownicy', 'href' => 'users.php', 'adminOnly' => true],
        ['key' => 'points', 'label' => 'Punkty', 'href' => 'edit_points.php', 'adminOnly' => true],
    ];

    echo '<nav class="admin-nav">';
    echo '<span class="admin-nav__brand">Panel Poscigu</span>';
    if ($active !== 'dashboard') {
        echo '<a class="admin-link admin-link--back" href="dashboard.php">&larr; Pulpit</a>';
    }

    foreach ($items as $it) {
        if ($it['adminOnly'] && $role !== 'admin') {
            continue;
        }

        $classes = 'admin-link' . ($it['key'] === $active ? ' is-active' : '');
        echo '<a class="' . $classes . '" href="' . htmlspecialchars((string) $it['href']) . '">' . $it['label'] . '</a>';
    }

    echo '<span class="admin-nav__spacer"></span>';
    echo '<button class="admin-link admin-theme-toggle" type="button" data-theme-toggle aria-pressed="true">';
    echo '<span data-theme-label>Tryb jasny</span>';
    echo '</button>';
    if ($username !== '') {
        echo '<span class="admin-meta">' . htmlspecialchars($username) . '</span>';
    }
    echo '<a class="admin-link" href="logout.php">Wyloguj</a>';
    echo '</nav>';
}
