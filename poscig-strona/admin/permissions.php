<?php
declare(strict_types=1);

function role_level(string $role): int
{
    static $levels = [
        'druh' => 1,
        'zastepowy' => 2,
        'druzynowy' => 3,
        'admin' => 4,
    ];

    return $levels[$role] ?? 0;
}

function can_manage_all_profiles(string $role): bool
{
    return role_level($role) >= 2;
}

function can_edit_other_profile(string $editorRole, string $targetRole): bool
{
    if ($editorRole === 'admin') {
        return true;
    }

    return role_level($editorRole) > role_level($targetRole);
}

function can_assign_senior_ranks(string $role): bool
{
    return role_level($role) >= 3;
}

function harcerski_rank_options(): array
{
    return [
        'mlodzik' => 'Mlodzik',
        'wywiadowca' => 'Wywiadowca',
        'cwik' => 'Cwik',
        'harcerz_orli' => 'Harcerz Orli',
        'harcerz_rzeczypospolitej' => 'Harcerz Rzeczypospolitej',
    ];
}

function instruktorski_rank_options(): array
{
    return [
        'pwd' => 'PWD',
        'phm' => 'PHM',
        'hm' => 'HM',
    ];
}

function all_rank_options(): array
{
    return harcerski_rank_options() + instruktorski_rank_options();
}

function rank_label(string $rank): string
{
    $options = all_rank_options();
    return $options[$rank] ?? ($rank !== '' ? $rank : 'Brak');
}

function rank_summary(string $harcerskiRank, string $instruktorskiRank): string
{
    $parts = [];

    if ($harcerskiRank !== '') {
        $parts[] = rank_label($harcerskiRank);
    }

    if ($instruktorskiRank !== '') {
        $parts[] = rank_label($instruktorskiRank);
    }

    if (!$parts) {
        return 'Brak';
    }

    return implode(' / ', $parts);
}