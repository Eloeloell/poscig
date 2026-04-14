<?php
declare(strict_types=1);

require_once __DIR__ . '/permissions.php';

function admin_nav(string $active = ''): void
{
    $role = (string) ($_SESSION['role'] ?? '');
    $username = (string) ($_SESSION['username'] ?? '');
    $firstName = trim((string) ($_SESSION['first_name'] ?? ''));
    $lastName = trim((string) ($_SESSION['last_name'] ?? ''));
    $displayName = trim($firstName . ' ' . $lastName);
    if ($displayName === '') {
        $displayName = $username;
    }

    $items = [
        ['key' => 'dashboard', 'label' => 'Panel', 'href' => 'dashboard.php', 'minLevel' => 1],
        ['key' => 'profile', 'label' => 'Profil', 'href' => 'profile.php', 'minLevel' => 1],
        ['key' => 'users', 'label' => 'Użytkownicy', 'href' => 'users.php', 'minLevel' => 2],
        ['key' => 'points', 'label' => 'Punkty', 'href' => 'edit_points.php', 'minLevel' => 4],
    ];

    echo '<nav class="admin-nav">';
    echo '<span class="admin-nav__brand">Panel Pościgu</span>';
    if ($active !== 'dashboard') {
        echo '<a class="admin-link admin-link--back" href="dashboard.php">&larr; Panel</a>';
    }

    foreach ($items as $it) {
        if (role_level($role) < (int) $it['minLevel']) {
            continue;
        }

        $classes = 'admin-link' . ($it['key'] === $active ? ' is-active' : '');
        echo '<a class="' . $classes . '" href="' . htmlspecialchars((string) $it['href']) . '">' . $it['label'] . '</a>';
    }

    echo '<span class="admin-nav__spacer"></span>';
    echo '<button class="admin-link admin-theme-toggle" type="button" data-theme-toggle aria-pressed="true">';
    echo '<span data-theme-label>Tryb jasny</span>';
    echo '</button>';
    if ($displayName !== '') {
        echo '<span class="admin-meta">' . htmlspecialchars($displayName) . '</span>';
    }
    echo '<a class="admin-link" href="logout.php">Wyloguj</a>';
    echo '</nav>';
}