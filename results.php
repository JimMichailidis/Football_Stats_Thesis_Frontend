<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['metrics'])) {
    die("Σφάλμα: Παρακαλώ επιλέξτε παίκτη και τουλάχιστον ένα στατιστικό.");
}

$player_name = $_POST['player_name'];
$selected_metrics = $_POST['metrics'];
$selected_positions = $_POST['positions'] ?? [];

// Κλήση στο Flask API
$payload = json_encode([
    "name" => $player_name,
    "metrics" => $selected_metrics,
    "positions" => $selected_positions
]);

$ch = curl_init("http://127.0.0.1:5001/recommend");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_msg = curl_error($ch);
curl_close($ch);

$results = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Results - Scouting Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .player-card { border: none; border-radius: 15px; margin-bottom: 20px; transition: 0.3s; }
        .player-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .similarity-score { font-size: 1.5rem; font-weight: 900; color: #0d6efd; }
        .progress { height: 10px; border-radius: 10px; background: #e9ecef; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Players similar to <span class="text-primary"><?= htmlspecialchars($player_name) ?></span></h1>
        <p class="text-muted">Αποτελέσματα βάσει Cosine Similarity & Normalization</p>
        <a href="search.php" class="btn btn-outline-secondary btn-sm">Επιστροφή στην αναζήτηση</a>
    </div>

    <?php if ($http_status !== 200): ?>
        <div class="alert alert-danger">
            <strong>Σφάλμα API:</strong> <?= $results['error'] ?? "Η σύνδεση με το AI Engine (Python) απέτυχε. $error_msg" ?>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php foreach($results as $index => $p): ?>
                    <div class="card player-card shadow-sm">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-weight: bold;">
                                        <?= $index + 1 ?>
                                    </div>
                                </div>
                                <div class="col">
                                    <h4 class="mb-0 fw-bold"><?= $p['name'] ?></h4>
                                    <span class="badge bg-secondary"><?= $p['position'] ?></span>
                                    <span class="text-muted ms-2"><?= $p['team'] ?></span>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="similarity-score"><?= $p['similarity'] ?>%</div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $p['similarity'] ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>