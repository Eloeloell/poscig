<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require __DIR__ . '/_nav.php';
require __DIR__ . '/../config/db.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Brak dostępu');
}

function quoteIdentifier(string $name): string
{
    return '`' . str_replace('`', '``', $name) . '`';
}

function point_label(string $key): string
{
    static $labels = [
        '1' => 'Doliwa',
        '2' => 'Korsak',
        '3' => 'Krzywda',
        'Doliwa' => 'Doliwa',
        'Korsak' => 'Korsak',
        'Krzywda' => 'Krzywda',
    ];

    return $labels[$key] ?? $key;
}

function detectPointsKeyColumn(PDO $pdo): string
{
    $cols = $pdo->query('SHOW COLUMNS FROM points')->fetchAll(PDO::FETCH_COLUMN, 0);

    foreach (['zastep'] as $preferred) {
        if (in_array($preferred, $cols, true)) {
            return $preferred;
        }
    }

    foreach ($cols as $col) {
        if ($col !== 'value') {
            return (string) $col;
        }
    }

    return 'zastep';
}

$pointsKeyCol = detectPointsKeyColumn($pdo);
$keySql = quoteIdentifier($pointsKeyCol);

$stmt = $pdo->query("SELECT {$keySql}, value FROM points ORDER BY {$keySql}");
$pointsArr = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string) $_POST['csrf'])) {
        exit('Błąd zabezpieczeń (CSRF)');
    }

    $pointsPost = $_POST['points'] ?? null;
    if (!is_array($pointsPost)) {
        exit('Nieprawidłowe żądanie');
    }

    foreach ($pointsPost as $zastep => $val) {
        $zastep = (string) $zastep;
        $newVal = (int) $val;
        $oldVal = (int) ($pointsArr[$zastep] ?? 0);
        $diff = $newVal - $oldVal;

        $stmt = $pdo->prepare("UPDATE points SET value = ? WHERE {$keySql} = ?");
        $stmt->execute([$newVal, $zastep]);

        if ($diff !== 0) {
            $desc = (string) ($_POST['description'][$zastep] ?? '.');
            $desc = substr($desc, 0, 200);

            $stmt = $pdo->prepare('INSERT INTO points_history (zastep, description, points) VALUES (?, ?, ?)');
            $stmt->execute([$zastep, $desc, $diff]);
        }
    }

    $stmt = $pdo->query("SELECT {$keySql}, value FROM points ORDER BY {$keySql}");
    $pointsArr = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $success = true;
}

$stmt = $pdo->query('SELECT * FROM points_history ORDER BY created_at DESC LIMIT 50');
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
$csrf = (string) ($_SESSION['csrf_token'] ?? '');
?>
<!doctype html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <title>Punkty zastępów</title>
    <link rel="stylesheet" href="/poscig-strona/src/strony/style.css">
    <link rel="stylesheet" href="admin.css?v=2">
    <script src="theme.js?v=2" defer></script>
</head>

<body class="admin-page" data-theme="dark">
    <div class="admin-wrap">
        <?php admin_nav('points'); ?>

        <?php if ($success): ?>
            <div class="admin-success">Punkty zaktualizowane.</div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Edytuj punkty zastępów</h2>
                    <p class="admin-subtitle">Zmieniaj wartości i zapisuj opis, który trafi do historii.</p>
                </div>
            </div>

            <form method="post" class="admin-form-grid">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                <div class="admin-point-list">
                    <?php foreach ($pointsArr as $zastep => $val): ?>
                        <div class="admin-point-row">
                            <div class="admin-point-row__label"><?= htmlspecialchars(point_label((string) $zastep)) ?></div>
                            <input type="number" name="points[<?= htmlspecialchars((string) $zastep) ?>]"
                                value="<?= (int) $val ?>" required>
                            <input type="text" name="description[<?= htmlspecialchars((string) $zastep) ?>]"
                                placeholder="Powód zmiany">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="admin-form-actions">
                    <button class="admin-btn" type="submit">Zapisz zmiany</button>
                    <span class="admin-muted">Każda korekta tworzy wpis w historii.</span>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <div class="admin-section-header">
                <div>
                    <h2 class="admin-title">Ostatnie zmiany</h2>
                    <p class="admin-subtitle">50 najnowszych wpisów z dziennika punktowego.</p>
                </div>
            </div>

            <div class="admin-table-shell">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Zastęp</th>
                            <th>Za co</th>
                            <th>Punkty</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars(point_label((string) $row['zastep'])) ?></td>
                                <td><?= htmlspecialchars((string) $row['description']) ?></td>
                                <td><?= ((int) $row['points'] > 0 ? '+' : '') . (int) $row['points'] ?></td>
                                <td><?= date('d.m.Y H:i', strtotime((string) $row['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>