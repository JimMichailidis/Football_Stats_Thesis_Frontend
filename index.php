<html lang="el">

<!-- index.php  —  Football Scouting Dashboard (24/25)
     Αρχική σελίδα: πλοήγηση στις 2 κύριες λειτουργίες -->

<head>
    <meta charset="UTF-8">
    <title>Scouting App — Αρχική</title>

    
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        /* Layout */
        body {
            min-height: 100vh;
            background: #f8f9fa;
            display: flex;
            align-items: center;
        }

        /* Navigation cards */
        .nav-card {
            border: none;
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .nav-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
        }

        .nav-card .icon {
            font-size: 50px;
            color: #0d6efd;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container text-center">

        <!-- Page title -->
        <h1 class="mb-5 fw-bold text-dark">
            Football Scouting Dashboard 24/25
        </h1>

        <!-- Navigation cards -->
        <div class="row justify-content-center g-4">

            <!-- Αναζήτηση & Σύγκριση -->
            <div class="col-md-5">
                <div
                    class="card h-100 shadow nav-card p-4"
                    onclick="location.href='search.php'"
                >
                    <div class="card-body d-flex flex-column">
                        <div class="icon">🔍</div>
                        <h3 class="card-title">Ανάλυση &amp; Σύγκριση</h3>
                        <p class="text-muted">
                            Βρες παρόμοιους παίκτες με βάση τα στατιστικά per 90.
                        </p>
                        <button class="btn btn-primary w-100 mt-auto">
                            Είσοδος στην Αναζήτηση
                        </button>
                    </div>
                </div>
            </div>

            <!-- Διαχείριση Βάσης -->
            <div class="col-md-5">
                <div
                    class="card h-100 shadow nav-card p-4"
                    onclick="location.href='manage_players.php'"
                >
                    <div class="card-body d-flex flex-column">
                        <div class="icon">📊</div>
                        <h3 class="card-title">Διαχείριση Βάσης</h3>
                        <p class="text-muted">
                            Πρόσθεσε νέους παίκτες και δες όλα τα δεδομένα της Premier League!
                        </p>
                        <button class="btn btn-success w-100 mt-auto">
                            Δες τη Βάση Δεδομένων
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>