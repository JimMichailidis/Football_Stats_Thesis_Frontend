<!DOCTYPE html>
<html lang="el">

<!-- Αποτελέσματα αναζήτησης παρόμοιων παικτών — Top 5 + Radar Chart -->

<?php
    //Validation
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['metrics'])) {
        die("Σφάλμα: Παρακαλώ επιλέξτε παίκτη και τουλάχιστον ένα στατιστικό.");
    }

    //Λήψη δεδομένων φόρμας
    $player_name        = $_POST['player_name'];
    $selected_metrics   = $_POST['metrics'];
    $selected_positions = $_POST['positions'] ?? [];

    // Κλήση Flask API
    $payload = json_encode([
        "name"      => $player_name,
        "metrics"   => $selected_metrics,
        "positions" => $selected_positions,
    ]);

    $ch = curl_init("http://127.0.0.1:5001/recommend");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);

    $response    = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error  = curl_error($ch);
    curl_close($ch);

    // Αποκωδικοποίηση — το API επιστρέφει τώρα object με results/radar/metrics/target
    $api_response = json_decode($response, true);
    $results      = $api_response['results']  ?? [];
    $radar_data   = $api_response['radar']    ?? [];
    $metrics      = $api_response['metrics']  ?? [];
    $target       = $api_response['target']   ?? $player_name;

    // Καθαρά labels για το chart (αφαιρούμε _per90 suffix)
    $labels = array_map(function($m) {
        $m = str_replace('_per90', '', $m);
        $m = str_replace('_percentage', ' %', $m);
        $m = str_replace('_', ' ', $m);
        return ucwords(strtolower($m));
    }, $metrics);

        // Χρώματα για target + top5 (Vibrant Dark Theme Palette)
    $colors = [
        'target' => ['border' => '#38bdf8', 'bg' => 'rgba(56, 189, 248, 0.20)'], // Electric Cyan
        0        => ['border' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.15)'], // Emerald (#1)
        1        => ['border' => '#f43f5e', 'bg' => 'rgba(244, 63, 94, 0.15)'],  // Rose (#2)
        2        => ['border' => '#fbbf24', 'bg' => 'rgba(251, 191, 36, 0.15)'], // Amber (#3)
        3        => ['border' => '#a78bfa', 'bg' => 'rgba(167, 139, 250, 0.15)'],// Purple (#4)
        4        => ['border' => '#2dd4bf', 'bg' => 'rgba(45, 212, 191, 0.15)'], // Teal (#5)
    ];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scouting Results — <?= htmlspecialchars($player_name) ?></title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Custom Theme CSS -->
    <link href="assets/css/theme.css" rel="stylesheet">
</head>

<body>
    <!-- Ambient Background Lights & Grid -->
    <div class="ambient-glow ambient-glow-1"></div>
    <div class="ambient-glow ambient-glow-2"></div>
    <div class="ambient-grid"></div>

    <div class="app-wrapper">

        <!-- Top Navbar -->
        <nav class="navbar-custom">
            <div class="container d-flex align-items-center justify-content-between">
                <a href="index.php" class="brand-badge text-decoration-none">
                    <div class="brand-icon">
                        <i class="bi bi-radar"></i>
                    </div>
                    <div>
                        <h1 class="brand-title">ScoutIQ</h1>
                        <span class="brand-sub">Similarity Intelligence</span>
                    </div>
                </a>

                <div class="d-flex align-items-center gap-2">
                    <a href="search.php"
                        class="btn btn-sm btn-outline-light rounded-pill px-3 py-2 d-flex align-items-center gap-1"
                        style="border-color: rgba(255,255,255,0.15);">
                        <i class="bi bi-arrow-left"></i>
                        <span>New Search</span>
                    </a>
                    <a href="index.php"
                        class="btn btn-sm btn-outline-light rounded-pill px-3 py-2 d-flex align-items-center gap-1"
                        style="border-color: rgba(255,255,255,0.15);">
                        <i class="bi bi-house"></i>
                        <span>Home</span>
                    </a>
                </div>
            </div>
        </nav>

        <main class="container my-auto py-5" style="max-width: 1050px;">

            <!-- Target Player Benchmark Banner -->
            <div class="target-player-banner d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge rounded-pill px-3 py-1"
                            style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-size: 0.75rem;">
                            Benchmark Target
                        </span>
                        <span class="text-secondary small">• Cosine Similarity Model</span>
                    </div>
                    <h2 class="text-white fw-bold mb-0" style="font-size: 2rem;">
                        <?= htmlspecialchars($player_name) ?>
                    </h2>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="text-end">
                        <div class="text-white fw-bold fs-5"><?= count($metrics) ?> Metrics</div>
                        <div class="text-secondary small">Evaluated Per 90</div>
                    </div>
                    <div class="vr bg-secondary opacity-50" style="height: 35px;"></div>
                    <div class="text-end">
                        <div class="text-success fw-bold fs-5">Top 5</div>
                        <div class="text-secondary small">Similar Clones</div>
                    </div>
                </div>
            </div>

            <!-- API Error Alert -->
            <?php if ($http_status !== 200): ?>
            <div class="alert alert-danger d-flex align-items-center p-3 rounded-4"
                style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;"
                role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <div class="fw-bold">Σφάλμα Σύνδεσης με το AI API (Python / Flask)</div>
                    <div class="small opacity-75">
                        <?= $api_response['error'] ?? "Βεβαιώσου ότι το backend_api.py τρέχει στο port 5001. $curl_error" ?>
                    </div>
                </div>
            </div>

            <?php else: ?>

            <!-- ── Radar Chart Card ─────────────────────────────────────── -->
            <?php if (!empty($radar_data) && !empty($metrics)): ?>
            <div class="radar-card">
                <div class="radar-header">
                    <h4 class="text-white fw-bold mb-1">
                        <i class="bi bi-radar text-info me-2"></i>Multidimensional Radar Profile
                    </h4>
                    <p class="text-secondary small mb-3">
                        Πάτησε πάνω σε οποιονδήποτε παίκτη στο legend για να απομονώσεις ή να συγκρίνεις το πολύγωνό
                        του.
                    </p>

                    <!-- Quick View Presets -->
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1"
                            style="font-size: 0.75rem;" onclick="setPreset('duel')">
                            <i class="bi bi-person-fill-check me-1"></i>Target vs #1
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1"
                            style="font-size: 0.75rem; border-color: rgba(255,255,255,0.15);"
                            onclick="setPreset('target')">
                            <i class="bi bi-person me-1"></i>Target Only
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1"
                            style="font-size: 0.75rem; border-color: rgba(255,255,255,0.15);"
                            onclick="setPreset('all')">
                            <i class="bi bi-people me-1"></i>Show All (6)
                        </button>
                    </div>

                    <!-- Interactive Legend Chips (Αρχικά μόνο Target & #1 ενεργοί) -->
                    <div class="radar-legend-container" id="radarLegend">
                        <div class="radar-chip" data-idx="0" onclick="toggleDataset(0, this)">
                            <span class="radar-chip-dot"
                                style="background: <?= $colors['target']['border'] ?>; box-shadow: 0 0 8px <?= $colors['target']['border'] ?>;"></span>
                            <span><?= htmlspecialchars($target) ?> (Target)</span>
                        </div>
                        <?php foreach ($results as $i => $p): ?>
                        <div class="radar-chip <?= $i > 0 ? 'disabled' : '' ?>" data-idx="<?= $i + 1 ?>"
                            onclick="toggleDataset(<?= $i + 1 ?>, this)">
                            <span class="radar-chip-dot" style="background: <?= $colors[$i]['border'] ?>;"></span>
                            <span>#<?= $i + 1 ?> <?= htmlspecialchars($p['name']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="position: relative; height: 550px;">
                    <canvas id="radarChart"></canvas>
                </div>

            </div>
            <?php endif; ?>

            <!-- ── Top 5 Similar Player Cards ────────────────────────────── -->
            <div class="mb-4">
                <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-trophy-fill text-warning"></i>
                    <span>Top 5 Statistical Recommendations</span>
                </h5>

                <?php foreach ($results as $rank => $player): ?>
                <div class="result-player-card">
                    <div class="row align-items-center g-3">

                        <!-- Rank Badge -->
                        <div class="col-auto">
                            <div class="rank-badge" style="background: <?= $colors[$rank]['border'] ?>;">
                                <?= $rank + 1 ?>
                            </div>
                        </div>

                        <!-- Player Info -->
                        <div class="col">
                            <h4 class="mb-1 fw-bold text-white fs-5">
                                <?= htmlspecialchars($player['name']) ?>
                            </h4>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill px-2 py-1"
                                    style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.75rem;">
                                    <?= htmlspecialchars($player['position']) ?>
                                </span>
                                <span class="badge rounded-pill px-2 py-1"
                                    style="background: rgba(255, 255, 255, 0.08); color: #cbd5e1; font-size: 0.75rem;">
                                    <i class="bi bi-shield me-1 text-info"></i><?= htmlspecialchars($player['team']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Similarity Score -->
                        <div class="col-12 col-md-4 text-md-end">
                            <div class="similarity-score-text" style="color: <?= $colors[$rank]['border'] ?>;">
                                <?= $player['similarity'] ?>%
                            </div>
                            <div class="similarity-progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: <?= $player['similarity'] ?>%; background: <?= $colors[$rank]['border'] ?>; height: 100%;">
                                </div>
                            </div>
                            <small class="text-secondary" style="font-size: 0.72rem;">Mathematical Similarity</small>
                        </div>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php endif; ?>

        </main>
    </div> <!-- /app-wrapper -->

    <!-- ── Chart.js Radar ─────────────────────────────────────────────────────── -->
    <?php if (!empty($radar_data) && !empty($metrics)): ?>
    <script>
    const labels = <?= json_encode(array_values($labels)) ?>;
    const radar = <?= json_encode($radar_data) ?>;
    const target = <?= json_encode($target) ?>;
    const players = <?= json_encode(array_column($results, 'name')) ?>;
    const colors = <?= json_encode($colors) ?>;

    const datasets = [];

    // 1. Target Player (Πάντα ορατός)
    if (radar[target]) {
        datasets.push({
            label: target,
            data: radar[target],
            borderColor: colors['target']['border'],
            backgroundColor: colors['target']['bg'],
            borderWidth: 3,
            pointBackgroundColor: colors['target']['border'],
            pointBorderColor: '#ffffff',
            pointRadius: 2.5,
            pointHoverRadius: 6,
            hidden: false
        });
    }

    // 2. Top 5 Similar Players (Μόνο ο #1 ορατός αρχικά, #2..#5 hidden)
    players.forEach((name, i) => {
        if (radar[name]) {
            datasets.push({
                label: name,
                data: radar[name],
                borderColor: colors[i]['border'],
                backgroundColor: colors[i]['bg'],
                borderWidth: 2,
                pointBackgroundColor: colors[i]['border'],
                pointBorderColor: '#ffffff',
                pointRadius: 2,
                pointHoverRadius: 5,
                hidden: i > 0 // Μόνο ο #1 φαίνεται by default!
            });
        }
    });

    // Dark-Theme Radar Configuration
    window.chartInstance = new Chart(document.getElementById('radarChart'), {
        type: 'radar',
        data: {
            labels,
            datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    min: 0,
                    max: 1,
                    ticks: {
                        stepSize: 0.2,
                        display: true,
                        color: '#94a3b8',
                        backdropColor: 'transparent',
                        font: {
                            family: "'Plus Jakarta Sans', sans-serif",
                            size: 10
                        },
                        callback: function(val) {
                            return (val * 100).toFixed(0) + '%';
                        }
                    },
                    pointLabels: {
                        color: '#f8fafc',
                        font: {
                            family: "'Plus Jakarta Sans', sans-serif",
                            size: labels.length > 14 ? 10 : 11,
                            weight: '700'
                        },
                        padding: 8
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.12)',
                        lineWidth: 1
                    },
                    angleLines: {
                        color: 'rgba(255, 255, 255, 0.18)',
                        lineWidth: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#ffffff',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(255, 255, 255, 0.15)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 10,
                    titleFont: {
                        family: "'Plus Jakarta Sans', sans-serif",
                        weight: 'bold'
                    },
                    bodyFont: {
                        family: "'Plus Jakarta Sans', sans-serif"
                    },
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${(ctx.raw * 100).toFixed(1)}%`
                    }
                }
            }
        }
    });

    // Toggle μεμονωμένου παίκτη από το chip
    function toggleDataset(index, chipEl) {
        if (!window.chartInstance) return;
        const meta = window.chartInstance.getDatasetMeta(index);
        const isHidden = meta.hidden === null ? window.chartInstance.data.datasets[index].hidden : meta.hidden;
        meta.hidden = !isHidden;
        chipEl.classList.toggle('disabled', meta.hidden);
        window.chartInstance.update();
    }

    // Γρήγορα φίλτρα (Presets)
    function setPreset(mode) {
        if (!window.chartInstance) return;
        const chips = document.querySelectorAll('#radarLegend .radar-chip');

        window.chartInstance.data.datasets.forEach((ds, idx) => {
            const meta = window.chartInstance.getDatasetMeta(idx);
            let hide = false;

            if (mode === 'target') {
                hide = (idx !== 0); // μόνο Target
            } else if (mode === 'duel') {
                hide = (idx > 1); // Target (0) και #1 (1)
            } else if (mode === 'all') {
                hide = false; // όλοι
            }

            meta.hidden = hide;

            // Ενημέρωση των chips
            chips.forEach(chip => {
                if (parseInt(chip.getAttribute('data-idx')) === idx) {
                    if (hide) {
                        chip.classList.add('disabled');
                    } else {
                        chip.classList.remove('disabled');
                    }
                }
            });
        });

        window.chartInstance.update();
    }
    </script>
    <?php endif; ?>

</body>

</html>