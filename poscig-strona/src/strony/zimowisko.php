<?php
require $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';

$pointsArr = [
  'Doliwa' => 0,
  'Korsak' => 0,
  'Krzywda' => 0
];
try {
  $stmt = $pdo->query("SELECT zastęp, value FROM points");
  $pointsArr = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
}

$history = [];
try {
  $stmt = $pdo->query("SELECT * FROM points_history ORDER BY created_at DESC LIMIT 50");
  $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

$lastDiffArr = [
  'Doliwa' => 0,
  'Korsak' => 0,
  'Krzywda' => 0
];
try {
  $stmt = $pdo->query("
        SELECT zastep, points 
        FROM points_history 
        WHERE created_at = (SELECT MAX(created_at) FROM points_history ph2 WHERE ph2.zastep = points_history.zastep)
    ");
  $lastDiff = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
  foreach ($lastDiff as $zastep => $diff) {
    $lastDiffArr[$zastep] = (int) $diff;
  }
} catch (Exception $e) {
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Zimowisko 2025 – Punktacja Zastępów</title>

  <meta name="description" content="Punktacja zastępów podczas zimowiska 1. Jarosławskiej Drużyny Harcerzy Pościg" />

  <link rel="stylesheet" href="/poscig-strona/src/strony/style.css" />
  <link rel="icon" href="/poscig-strona/public/img/logo.png" />

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>

<body>

  <div id="loader">
    <div class="loader-spinner"></div>
  </div>

  <div id="header"></div>

  <section class="hero" style="min-height:45vh">
    <div class="hero-content">
      <h1>Zimowisko 2025</h1>
      <p>Punktacja zastępów</p>
    </div>
  </section>

  <main class="container">

    <section id="ranking-zastepow">
      <h2>Ranking zastępów</h2>

      <div class="zastepy-container">

        <div class="zastepy-card" data-points="<?= $pointsArr['Doliwa'] ?? 0 ?>">
          <div class="zastepy-content">
            <h3>🥇 Zastęp Herbu Doliwa</h3>
            <p><strong><span class="points"
                  data-value="<?= $pointsArr['Doliwa'] ?? 0 ?>"><?= $pointsArr['Doliwa'] ?? 0 ?></span> pkt</strong></p>
            <p>+ <?= $lastDiffArr['Doliwa'] ?? 0 ?> pkt</p>
          </div>
        </div>

        <div class="zastepy-card" data-points="<?= $pointsArr['Korsak'] ?? 0 ?>">
          <div class="zastepy-content">
            <h3>🥈 Zastęp Herbu Korsak</h3>
            <p><strong><span class="points"
                  data-value="<?= $pointsArr['Korsak'] ?? 0 ?>"><?= $pointsArr['Korsak'] ?? 0 ?></span> pkt</strong></p>
            <p>+ <?= $lastDiffArr['Korsak'] ?? 0 ?> pkt</p>
          </div>
        </div>

        <div class="zastepy-card" data-points="<?= $pointsArr['Krzywda'] ?? 0 ?>">
          <div class="zastepy-content">
            <h3>🥉 Zastęp Herbu Krzywda</h3>
            <p><strong><span class="points"
                  data-value="<?= $pointsArr['Krzywda'] ?? 0 ?>"><?= $pointsArr['Krzywda'] ?? 0 ?></span> pkt</strong>
            </p>
            <p>+ <?= $lastDiffArr['Krzywda'] ?? 0 ?> pkt</p>
          </div>
        </div>

      </div>
    </section>

    <hr />

    <section id="punktacja">
      <h2>Szczegóły punktacji</h2>
      <div style="overflow-x:auto">
        <table style="width:100%; border-collapse:collapse">
          <thead>
            <tr style="background:#eaf5e3">
              <th>Zastęp</th>
              <th>Za co</th>
              <th>Punkty</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($history)): ?>
              <?php foreach ($history as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['zastep']) ?></td>
                  <td><?= htmlspecialchars($row['description']) ?></td>
                  <td><?= ($row['points'] > 0 ? '+' : '') . (int) $row['points'] ?></td>
                  <td><?= date('d.m.Y H:i', strtotime($row['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4">Brak historii punktów</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <hr />

  </main>

  <div id="footer"></div>

  <script>
    document.querySelectorAll(".points").forEach(el => {
      const target = +el.dataset.value;
      let current = 0;
      const step = Math.max(1, target / 40);
      const interval = setInterval(() => {
        current += step;
        if (current >= target) {
          el.textContent = target;
          clearInterval(interval);
        } else {
          el.textContent = Math.floor(current);
        }
      }, 20);
    });
  </script>


  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const container = document.querySelector(".zastepy-container");
      if (!container) return;
      const cards = Array.from(container.querySelectorAll(".zastepy-card"));
      cards
        .sort((a, b) => Number(b.dataset.points) - Number(a.dataset.points))
        .forEach((card, index) => {
          const title = card.querySelector("h3");
          if (!title) return;
          const medals = ["🥇", "🥈", "🥉"];
          title.innerHTML = `${medals[index] || ""} ${title.textContent.replace(/^🥇|🥈|🥉/, "").trim()}`;
          container.appendChild(card);
        });
    });
  </script>

  <script src="/poscig-strona/src/strony/script.js"></script>
  <script>
    window.addEventListener("load", () => {
      const loader = document.getElementById("loader");
      if (loader) loader.classList.add("hidden");
    });
  </script>
</body>

</html>