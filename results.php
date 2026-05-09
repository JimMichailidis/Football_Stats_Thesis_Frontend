<!DOCTYPE html>
<html lang="el">
 
<!-- Αποτελέσματα αναζήτησης παρόμοιων παικτών καλεί το Flask API και εμφανίζει τους top 5 παρόμοιους παίκτες -->
 
<?php
    // Validation 
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['metrics'])) {
        die("Σφάλμα: Παρακαλώ επιλέξτε παίκτη και τουλάχιστον ένα στατιστικό.");
    }
 
    // Λήψη δεδομένων φόρμας 
    $player_name       = $_POST['player_name'];
    $selected_metrics  = $_POST['metrics'];
    $selected_positions = $_POST['positions'] ?? [];
 
    // Κλήση Flask API (Python recommendation engine) 
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
 
    // Αποκωδικοποίηση απάντησης 
    $results = json_decode($response, true);
?>
 
<head>
    <meta charset="UTF-8">
    <title>Results — Scouting Tool</title>
 
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 
    <style>
        /*  Layout  */
        body {
            background: #f8f9fa;
        }
 
        /* Player result cards */
        .player-card {
            border: none;
            border-radius: 15px;
            margin-bottom: 20px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
 
        .player-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
 
        /* Similarity score display */
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
    </style>
</head>
 
<body>
    <div class="container py-5">
 
        <!-- Page title -->
        <div class="text-center mb-5">
            <h1 class="fw-bold">
                Players similar to
                <span class="text-primary"><?= htmlspecialchars($player_name) ?></span>
            </h1>
            <p class="text-muted">Αποτελέσματα βάσει Cosine Similarity &amp; Normalization</p>
            <a href="search.php" class="btn btn-outline-secondary btn-sm">
                Επιστροφή στην αναζήτηση
            </a>
        </div>
 
        <!-- API error -->
        <?php if ($http_status !== 200): ?>
            <div class="alert alert-danger">
                <strong>Σφάλμα API:</strong>
                <?= $results['error'] ?? "Η σύνδεση με το AI Engine (Python) απέτυχε. $curl_error" ?>
            </div>
 
        <!-- Results -->
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
 
                    <?php foreach ($results as $rank => $player): ?>
                        <div class="card player-card shadow-sm">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
 
                                    <!-- Rank badge -->
                                    <div class="col-auto">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                             style="width: 50px; height: 50px; font-weight: bold;">
                                            <?= $rank + 1 ?>
                                        </div>
                                    </div>
 
                                    <!-- Player info -->
                                    <div class="col">
                                        <h4 class="mb-0 fw-bold"><?= htmlspecialchars($player['name']) ?></h4>
                                        <span class="badge bg-secondary"><?= $player['position'] ?></span>
                                        <span class="text-muted ms-2"><?= $player['team'] ?></span>
                                    </div>
 
                                    <!-- Similarity score & progress bar -->
                                    <div class="col-md-4 text-end">
                                        <div class="similarity-score"><?= $player['similarity'] ?>%</div>
                                        <div class="progress mt-2">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                 role="progressbar"
                                                 style="width: <?= $player['similarity'] ?>%">
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
</body>
 
</html>