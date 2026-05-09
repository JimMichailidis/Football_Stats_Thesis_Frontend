<!DOCTYPE html>
<html lang="el">

<!-- Φόρμα αναζήτησης παρόμοιων παικτών. Επιλογή παίκτη, θέσεων & στατιστικών Και αποστολή στο results.php-->

<head>
    <meta charset="UTF-8">
    <title>Advanced Football Scouting Tool — 47 Stats</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <style>
        /* Layout */
        body {
            background: #f0f2f5;
            font-size: 0.9rem;
        }

        /* Metric group cards */
        .metric-group {
            background: white;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .group-title {
            font-weight: 800;
            font-size: 1rem;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #eee;
            display: flex;
            align-items: center;
        }

        .group-title i {
            margin-right: 8px;
        }

        /* Checkboxes */
        .form-check {
            margin-bottom: 8px;
        }

        .form-check-label {
            font-size: 0.85rem;
            color: #444;
            cursor: pointer;
            user-select: none;
        }

        .form-check-input {
            cursor: pointer;
        }

        /* Select2 override */
        .select2-container--default .select2-selection--single {
            height: 50px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }

        /* Card header gradient */
        .card-header-custom {
            background: linear-gradient(135deg, #0d6efd 0%, #004dc7 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
        }
    </style>
</head>

<body class="py-5">

    <!-- Back button -->
    <a href="index.php" name="Backbtn" class="btn btn-dark position-absolute top-0 end-0 m-3"  style="z-index: 9999;">
        Home
    </a>

    <div class="container">
        <div class="card shadow-lg border-0 rounded-3">

            <!-- Header -->
            <div class="card-header card-header-custom p-4 text-center">
                <h2 class="mb-0 fw-bold">Player Similarity Analytics</h2>
                <p class="mb-0 opacity-75">
                    Βασισμένο σε Per 90 Stats (Season 24/25)
                </p>
            </div>

            <div class="card-body p-4 p-md-5">
                <form action="results.php" method="POST">

                    <!-- Step 1: Επιλογή παίκτη -->
                    <div class="mb-5 p-4 bg-white border rounded-3">
                        <label class="form-label fw-bold fs-5 text-primary mb-3">
                            1. Επίλεξε Παίκτη-Πρότυπο:
                        </label>

                        <select name="player_name" id="playerSearch" class="form-select" required>
                            <option value=""></option>
                            <?php
                                // Σύνδεση & φόρτωση παικτών αλφαβητικά
                                $conn = new mysqli("localhost", "root", "", "footballdatabase");
                                if ($conn->connect_error) die("Σφάλμα σύνδεσης");

                                $res = $conn->query("
                                    SELECT Player_Name
                                    FROM epl_player_stats_24_25_per90
                                    ORDER BY Player_Name ASC
                                ");

                                while ($player = $res->fetch_assoc()) {
                                    $name = htmlspecialchars($player['Player_Name']);
                                    echo "<option value=\"$name\">$name</option>";
                                }

                                $conn->close();
                            ?>
                        </select>
                    </div>

                    <!-- Step 2: Φίλτρο θέσεων (προαιρετικό) -->
                    <div class="metric-group mb-5">
                        <div class="group-title text-primary">
                            <i class="form-label fw-bold fs-5 text-primary mb-3"></i>
                            2. Περιορισμός σε Θέσεις (Προαιρετικό)
                        </div>

                        <div class="d-flex flex-wrap gap-3">
                            <?php
                                $positions = ['ST', 'RW', 'LW', 'AM', 'CM', 'DM', 'RB', 'LB', 'CB', 'GK'];

                                foreach ($positions as $pos) {
                                    echo "
                                    <div class='form-check'>
                                        <input type='checkbox' name='positions[]' value='$pos'
                                               class='form-check-input' id='pos_$pos'>
                                        <label class='form-check-label fw-bold' for='pos_$pos'>$pos</label>
                                    </div>";
                                }
                            ?>
                        </div>
                    </div>

                    <!-- Step 3: Επιλογή στατιστικών -->
                    <label class="form-label fw-bold fs-5 mb-4 text-primary">
                        3. Επίλεξε Στατιστικά για τη Σύγκριση:
                    </label>

                    <?php
                        // Ορισμός ομάδων στατιστικών: [color, title, [metric => label], default_checked]
                        $metric_groups = [
                            [
                                'color'   => '#fd1414',
                                'title'   => 'Goals &amp; Attacking',
                                'checked' => false,
                                'metrics' => [
                                'Goals_per90' => 'Goals',
                                'Assists_per90' => 'Assists',
                                'xG_per90' => 'xG',
                                'npxG_per90' => 'Non-Pen xG',
                                'xAG_per90' => 'xAG',
                                'Shots_per90' => 'Shots',
                                'Shots_On_Target_per90' => 'Shots on Target',
                                'Conversion_percentage' => 'Conversion %',
                                'Big_Chances_Missed_per90' => 'Big Chances Missed',
                                'Hit_Woodwork_per90' => 'Hit Woodwork',
                                'Offsides_per90' => 'Offsides',
                                ],
                            ],
                            [
                                'color'   => '#16a009',
                                'title'   => 'Passing &amp; Build-up',
                                'checked' => false,
                                'metrics' => [
                                    'Passes_per90' => 'Total Passes',
                                    'Successful_Passes_per90' => 'Successful Passes',
                                    'Passes_Percentage' => 'Pass Accuracy %',
                                    'Final_Third_Passes_per90' => 'Final 3rd Passes',
                                    'Successful_Final_Third_Passes_per90' => 'Succ. F3rd Passes',
                                    'Final_Third_Passes_Percentage' => 'F3rd Pass %',
                                    'Through_Balls_per90' => 'Through Balls',
                                    'Crosses_per90' => 'Crosses',
                                    'Successful_Crosses_per90' => 'Succ. Crosses',
                                    'Crosses_Percentage' => 'Cross %',
                                    'Touches_per90' => 'Touches',
                                ],
                            ],
                            [
                                'color' => '#fd7e14',
                                'title' => 'Carrying &amp; Dribbling',
                                'checked' => false,
                                'metrics' => [
                                    'Carries_per90' => 'Carries',
                                    'Progressive_Carries_per90' => 'Prog. Carries',
                                    'Carries_Ended_with_Goal_per90' => 'Carr. to Goal',
                                    'Carries_Ended_with_Assist_per90' => 'Carr. to Assist',
                                    'Carries_Ended_with_Shot_per90' => 'Carr. to Shot',
                                    'Carries_Ended_with_Chance_per90' => 'Carr. to Chance',
                                    'Dispossessed_per90' => 'Dispossessed',
                                ],
                            ],
                            [
                                'color' => '#ffc107',
                                'title' => 'Defensive &amp; Duels',
                                'checked' => false,
                                'metrics' => [
                                    'Tackles_per90' => 'Tackles',
                                    'Interceptions_per90' => 'Interceptions',
                                    'Blocks_per90' => 'Blocks',
                                    'Clearances_per90' => 'Clearances',
                                    'Ground_Duels_per90' => 'Ground Duels',
                                    'Ground_Duels_Won_per90' => 'Gr. Duels Won',
                                    'Aerial_Duels_per90' => 'Aerial Duels',
                                    'Aerial_Duels_Won_per90' => 'Aer. Duels Won',
                                    'Possession_Won_per90' => 'Possession Won',
                                    'Fouls' => 'Fouls',
                                    'Own_Goals' => 'Own Goals',
                                    'Clearances_Off_Line' => 'Clearances Off Line',
                                    'Clean_Sheets_per90' => 'Clean Sheets',
                                ],
                            ],
                            [
                                'color' => '#6f42c1',
                                'title' => 'Goalkeeping (GK Only)',
                                'checked' => false,
                                'metrics' => [
                                    'Saves_per90' => 'Saves',
                                    'Saves_Percentage' => 'Save %',
                                    'Goals_Conceded_per90' => 'Goals Conceded',
                                    'Goals_Prevented' => 'Goals Prevented',
                                    'Penalties_Saved_per90' => 'Penalties Saved',
                                    'Punches_per90' => 'Punches',
                                    'High_Claims_per90' => 'High Claims',
                                    'xG_Threat_Conceded' => 'xG Threat Conceded',
                                ],
                            ],
                        ];
                    ?>

                    <div class="row g-4">
                        <?php foreach ($metric_groups as $group): ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="metric-group h-100" style="border-top: 3px solid <?= $group['color'] ?>;">

                                    <!-- Group title -->
                                    <div class="group-title" style="color: <?= $group['color'] ?>;">
                                        <?= $group['title'] ?>
                                    </div>

                                    <!-- Checkboxes -->
                                    <?php foreach ($group['metrics'] as $value => $label): ?>
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                name="metrics[]"
                                                value="<?= $value ?>"
                                                class="form-check-input"
                                                <?= $group['checked'] ? 'checked' : '' ?>
                                            >
                                            <label class="form-check-label"><?= $label ?></label>
                                        </div>
                                    <?php endforeach; ?>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!--  Submit  -->
                    <div class="mt-5 pt-3 border-top text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow fw-bold py-3 fs-5 rounded-pill">
                            <i class="bi bi-search me-2"></i> Αναζήτηση Παρόμοιων Παικτών
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Αρχικοποίηση Select για το dropdown παίκτη
        $(document).ready(function () {
            $('#playerSearch').select2({
                placeholder: "Πληκτρολόγησε όνομα παίκτη...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

</body>
</html>