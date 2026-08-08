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

    // Χρώματα για target + top5
    $colors = [
        'target' => ['border' => '#0d6efd', 'bg' => 'rgba(13,110,253,0.15)'],
        0        => ['border' => '#dc3545', 'bg' => 'rgba(220,53,69,0.10)'],
        1        => ['border' => '#198754', 'bg' => 'rgba(25,135,84,0.10)'],
        2        => ['border' => '#fd7e14', 'bg' => 'rgba(253,126,20,0.10)'],
        3        => ['border' => '#6f42c1', 'bg' => 'rgba(111,66,193,0.10)'],
        4        => ['border' => '#20c997', 'bg' => 'rgba(32,201,151,0.10)'],
    ];
?>

<head>
    <meta charset="UTF-8">
    <title>Results — Scouting Tool</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        body { background: #f8f9fa; }

        /* Player cards */
        .player-card {
            border: none;
            border-radius: 15px;
            margin-bottom: 20px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .player-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        /* Similarity score */
        .similarity-score {
            font-size: 1.5rem;
            font-weight: 900;
            color: #0d6efd;
        }

        .progress {
            height: 10px;
            border-radius: 10px;
            background: #e9ecef;
        }

        /* Radar chart container */
        .radar-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }

        .radar-container canvas {
            max-height: 500px;
        }

        /* Legend */
        .legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
    </style>
</head>

<body>
<div class="container py-5">

    <!-- Τίτλος -->
    <div class="text-center mb-5">
        <h1 class="fw-bold">
            Players similar to
            <span class="text-primary"><?= htmlspecialchars($player_name) ?></span>
        </h1>
        <p class="text-muted">Αποτελέσματα βάσει Cosine Similarity &amp; MinMax Normalization</p>
        <a href="search.php" class="btn btn-outline-secondary btn-sm">
            ← Επιστροφή στην αναζήτηση
        </a>
    </div>

    <!-- API Error -->
    <?php if ($http_status !== 200): ?>
        <div class="alert alert-danger">
            <strong>Σφάλμα API:</strong>
            <?= $api_response['error'] ?? "Η σύνδεση με το AI Engine (Python) απέτυχε. $curl_error" ?>
        </div>

    <?php else: ?>

        <!-- ── Radar Chart ─────────────────────────────────────────────── -->
        <?php if (!empty($radar_data) && !empty($metrics)): ?>
        <div class="radar-container">
            <h5 class="fw-bold text-center mb-4">📊 Statistical Profile Comparison</h5>

            <!-- Legend -->
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                <span>
                    <span class="legend-dot" style="background: <?= $colors['target']['border'] ?>;"></span>
                    <strong><?= htmlspecialchars($target) ?></strong>
                </span>
                <?php foreach ($results as $i => $p): ?>
                    <span>
                        <span class="legend-dot" style="background: <?= $colors[$i]['border'] ?>;"></span>
                        <?= htmlspecialchars($p['name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <canvas id="radarChart"></canvas>
        </div>
        <?php endif; ?>

        <!-- ── Player Cards ────────────────────────────────────────────── -->
        <div class="row justify-content-center">
            <div class="col-md-8">

                <?php foreach ($results as $rank => $player): ?>
                    <div class="card player-card shadow-sm">
                        <div class="card-body p-4">
                            <div class="row align-items-center">

                                <!-- Rank badge -->
                                <div class="col-auto">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                                         style="width:50px; height:50px; background:<?= $colors[$rank]['border'] ?>;">
                                        <?= $rank + 1 ?>
                                    </div>
                                </div>

                                <!-- Player info -->
                                <div class="col">
                                    <h4 class="mb-0 fw-bold"><?= htmlspecialchars($player['name']) ?></h4>
                                    <span class="badge bg-secondary"><?= $player['position'] ?></span>
                                    <span class="text-muted ms-2"><?= $player['team'] ?></span>
                                </div>

                                <!-- Similarity score -->
                                <div class="col-md-4 text-end">
                                    <div class="similarity-score"><?= $player['similarity'] ?>%</div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                             role="progressbar"
                                             style="width:<?= $player['similarity'] ?>%; background:<?= $colors[$rank]['border'] ?>;">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

    <?php endif; ?>

</div><!-- container -->

<!-- ── Chart.js Radar ─────────────────────────────────────────────────────── -->
<?php if (!empty($radar_data) && !empty($metrics)): ?>
<script>
    const labels  = <?= json_encode(array_values($labels)) ?>;
    const radar   = <?= json_encode($radar_data) ?>;
    const target  = <?= json_encode($target) ?>;
    const players = <?= json_encode(array_column($results, 'name')) ?>;
    const colors  = <?= json_encode($colors) ?>;

    // Χτίζουμε datasets — target πρώτος, μετά top5
    const datasets = [];

    // Target player
    if (radar[target]) {
        datasets.push({
            label:           target,
            data:            radar[target],
            borderColor:     colors['target']['border'],
            backgroundColor: colors['target']['bg'],
            borderWidth:     3,
            pointRadius:     4,
        });
    }

    // Top 5
    players.forEach((name, i) => {
        if (radar[name]) {
            datasets.push({
                label:           name,
                data:            radar[name],
                borderColor:     colors[i]['border'],
                backgroundColor: colors[i]['bg'],
                borderWidth:     2,
                pointRadius:     3,
            });
        }
    });

    new Chart(document.getElementById('radarChart'), {
        type: 'radar',
        data: { labels, datasets },
        options: {
            responsive: true,
            scales: {
                r: {
                    min: 0,
                    max: 1,
                    ticks: {
                        stepSize: 0.2,
                        font: { size: 10 },
                        backdropColor: 'transparent',
                    },
                    pointLabels: {
                        font: { size: 11, weight: 'bold' },
                    },
                    grid:      { color: 'rgba(0,0,0,0.08)' },
                    angleLines: { color: 'rgba(0,0,0,0.1)' },
                }
            },
            plugins: {
                legend: { display: false }, // χρησιμοποιούμε το custom legend
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${(ctx.raw * 100).toFixed(0)}%`
                    }
                }
            }
        }
    });
</script>
<?php endif; ?>

</body>
</html>