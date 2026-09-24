<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScoutIQ — Premier League 24/25 Scouting Dashboard</title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Theme CSS -->
    <link href="assets/css/theme.css" rel="stylesheet">
</head>


<body>
    <!-- Background Glows & Grid -->
    <div class="ambient-glow ambient-glow-1"></div>
    <div class="ambient-glow ambient-glow-2"></div>
    <div class="ambient-grid"></div>

    <div class="app-wrapper">


        <!-- Top Navbar -->
        <nav class="navbar-custom">
            <div class="container d-flex align-items-center justify-content-between">
                <a href="index.php" class="brand-badge">
                    <div class="brand-icon">
                        <i class="bi bi-crosshair2"></i>
                    </div>
                    <div>
                        <h1 class="brand-title">ScoutIQ</h1>
                        <span class="brand-sub">Premier League 24/25 Analytics</span>
                    </div>
                </a>

                <div class="d-flex align-items-center gap-3">
                    <div class="nav-status-pill">
                        <span class="pulse-dot"></span>
                        <span>Premier League Season 24/25</span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Εδώ θα μπει το κυρίως περιεχόμενο (Hero + Cards) -->
        <!-- Main Content -->
        <main class="container my-auto py-4">

            <!-- Hero Title -->
            <div class="hero-section text-center">
                <div class="hero-pill">
                    <i class="bi bi-cpu-fill text-info"></i>
                    <span>AI-Driven Football Scouting Platform</span>
                </div>

                <h2 class="hero-title">
                    Discover Mathematical Twins in <br>
                    <span class="gradient-text-emerald">Modern Football Analytics</span>
                </h2>

                <p class="hero-subtitle">
                    Algorithmic scouting based on <strong>47 statistics per 90 minutes</strong> and <strong>Cosine
                        Similarity</strong> for the Premier League 2024/2025 Season.
                </p>
            </div>

            <!-- Navigation Cards -->
            <div class="row g-4 justify-content-center text-start">

                <!-- Κάρτα 1: Scouting & Similarity -->
                <div class="col-lg-5 col-md-6">
                    <div class="hub-card hub-card-scout" onclick="location.href='search.php'">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="hub-card-icon-wrap icon-emerald">
                                <i class="bi bi-radar"></i>
                            </div>
                            <span
                                class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                Core Engine
                            </span>
                        </div>

                        <span class="hub-card-tag tag-emerald">Scouting &amp; Recommendations</span>
                        <h3 class="hub-card-title">Analysis &amp; Comparison</h3>
                        <p class="hub-card-desc">
                            Select a player, then select through 47 different statistics per 90 minutes and find the 5
                            most similar player profiles with an interactive radar chart.
                        </p>

                        <div class="feature-pills">
                            <span class="pill-sm"><i class="bi bi-check2 text-success me-1"></i>47 Different
                                Metrics</span>
                            <span class="pill-sm"><i class="bi bi-check2 text-success me-1"></i>Position Filter</span>
                            <span class="pill-sm"><i class="bi bi-check2 text-success me-1"></i>Radar Charts</span>
                        </div>

                        <a href="search.php" class="hub-btn hub-btn-emerald">
                            <span>Launch Scouting Tool</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Κάρτα 2: Database Management -->
                <div class="col-lg-5 col-md-6">
                    <div class="hub-card hub-card-db" onclick="location.href='manage_players.php'">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="hub-card-icon-wrap icon-blue">
                                <i class="bi bi-database-fill-gear"></i>
                            </div>
                            <span
                                class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                                Admin Portal
                            </span>
                        </div>

                        <span class="hub-card-tag tag-blue">League Roster &amp; Data</span>
                        <h3 class="hub-card-title">Database Management</h3>
                        <p class="hub-card-desc">
                            View and manage all Premier League players, add new players with an automatic per-90
                            calculation and manage database records.
                        </p>

                        <div class="feature-pills">
                            <span class="pill-sm"><i class="bi bi-shield-lock text-info me-1"></i>Secure Login</span>
                            <span class="pill-sm"><i class="bi bi-sliders text-info me-1"></i>Auto Per-90</span>
                            <span class="pill-sm"><i class="bi bi-table text-info me-1"></i>Full League Roster</span>
                        </div>

                        <a href="manage_players.php" class="hub-btn hub-btn-blue">
                            <span>Manage Database</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

            </div>
        </main>


    </div> <!-- τέλος του .app-wrapper -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>