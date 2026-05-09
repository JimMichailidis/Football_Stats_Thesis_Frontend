<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Scouting App - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-card { transition: 0.3s; cursor: pointer; border: none; }
        .main-card:hover { transform: translateY(-10px); box-shadow: 0 10px 20px rgba(0,0,0,0.2)!important; }
        .icon-box { font-size: 50px; color: #0d6efd; margin-bottom: 20px; }
    </style>
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container text-center">
        <h1 class="mb-5 fw-bold text-dark">Football Scouting Dashboard 24/25</h1>
        
        <div class="row justify-content-center g-4">

            <div class="col-md-5">
                <div class="card h-100 shadow main-card p-4" onclick="location.href='search.php'">
                    <div class="card-body d-flex flex-column">
                        <div class="icon-box">🔍</div>
                        <h3 class="card-title">Ανάλυση & Σύγκριση</h3>
                        <p class="text-muted">Βρες παρόμοιους παίκτες με βάση τα στατιστικά per 90.</p>
                        
                        <button class="btn btn-primary w-100 mt-auto">Είσοδος στην Αναζήτηση</button>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card h-100 shadow main-card p-4" onclick="location.href='manage_players.php'">
                    <div class="card-body d-flex flex-column">
                        <div class="icon-box">📊</div>
                        <h3 class="card-title">Διαχείριση Βάσης</h3>
                        <p class="text-muted">Πρόσθεσε νέους παίκτες και δες όλα τα δεδομένα της Premier League!</p>
                        <button class="btn btn-success w-100 mt-auto">Δες τη Βάση Δεδομένων</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>