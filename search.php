<!DOCTYPE html>
<html lang="el">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScoutIQ — Advanced Player Similarity &amp; Scouting Engine</title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <!-- Theme CSS -->
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
                        <span class="brand-sub">Player Similarity Engine</span>
                    </div>
                </a>

                <div class="d-flex align-items-center gap-2">
                    <a href="index.php"
                        class="btn btn-sm btn-outline-light rounded-pill px-3 py-2 d-flex align-items-center gap-1"
                        style="border-color: rgba(255,255,255,0.15);">
                        <i class="bi bi-house"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="container my-auto py-5" style="max-width: 1050px;">

            <!-- Page Header -->
            <div class="text-center mb-5">
                <div class="hero-pill mb-3">
                    <i class="bi bi-crosshair2 text-success"></i>
                    <span>Cosine Vector Similarity • 47 Per-90 Metrics</span>
                </div>
                <h2 class="hero-title" style="font-size: 2.4rem;">
                    Find Statistical Clones &amp; <br>
                    <span class="gradient-text-emerald">Benchmark Talent</span>
                </h2>
                <p class="hero-subtitle mb-0">
                    Επίλεξε έναν παίκτη της Premier League, όρισε τα κριτήρια σύγκρισης και ανακάλυψε τους 5 πιο όμοιους
                    παίκτες με διαδραστικό ραντάρ.
                </p>
            </div>

            <form action="results.php" method="POST">

                <!-- Βήμα 1: Επιλογή παίκτη -->
                <div class="scout-card">
                    <div class="step-title">
                        <span class="step-badge">1</span>
                        <span>Choose Target Player</span>
                    </div>
                    <p class="text-secondary small mb-3">
                        Αναζήτησε οποιονδήποτε παίκτη της Premier League (σεζόν 2024/2025).
                    </p>

                    <select name="player_name" id="playerSearch" class="form-select" required>
                        <option value=""></option>
                        <?php
                        $conn = new mysqli("localhost", "root", "", "footballdatabase");
                        if ($conn->connect_error) die("Σφάλμα σύνδεσης");
                        $sql = "
                            SELECT p.Player_Name, p.Position, p.Club, 
                                   COALESCE(t.Market_Value_In_Millions, 0) AS Market_Value
                            FROM epl_player_stats_24_25_per90 p
                            LEFT JOIN epl_player_stats_24_25 t ON p.Player_Name = t.Player_Name
                            ORDER BY p.Player_Name ASC
                        ";
                        $res = $conn->query($sql);
                        while ($player = $res->fetch_assoc()) {
                            $name  = htmlspecialchars($player['Player_Name']);
                            $club  = htmlspecialchars($player['Club'] ?? '-');
                            $pos   = htmlspecialchars($player['Position'] ?? '-');
                            $val   = floatval($player['Market_Value']);
                            $valText = $val > 0 ? "€" . number_format($val, 1) . "M" : "N/A";
                            echo "<option value=\"$name\" 
                                          data-club=\"$club\" 
                                          data-pos=\"$pos\"  
                                          data-val=\"$valText\">
                                      $name
                                  </option>";
                        }
                        $conn->close();
                        ?>
                    </select>
                </div>

                <!-- Βήμα 2: Φίλτρο θέσεων (προαιρετικό) -->
                <div class="scout-card">
                    <div class="step-title">
                        <span class="step-badge">2</span>
                        <span>Position Filter (Optional)</span>
                    </div>
                    <p class="text-secondary small mb-3">
                        Αν δεν επιλέξεις θέσεις, η σύγκριση θα γίνει ανάμεσα σε όλους τους διαθέσιμους παίκτες του
                        πρωταθλήματος.
                    </p>

                    <!-- Κουμπιά Presets -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button"
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold btn-pos-preset"
                            data-positions="ST,RW,LW" style="border-color: rgba(239, 68, 68, 0.4);">
                            ⚽ Attackers (ST, RW, LW)
                        </button>
                        <button type="button"
                            class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold btn-pos-preset"
                            data-positions="CM,AM,DM" style="border-color: rgba(59, 130, 246, 0.4);">
                            🎯 Midfielders (CM, AM, DM)
                        </button>
                        <button type="button"
                            class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold btn-pos-preset"
                            data-positions="RB,LB,CB" style="border-color: rgba(16, 185, 129, 0.4);">
                            🛡️ Defenders (RB, LB, CB)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold"
                            id="clearPositionsBtn" style="border-color: rgba(255, 255, 255, 0.2); color: #94a3b8;">
                            ✕ Clear All
                        </button>
                    </div>

                    <!-- Checkboxes Θέσεων -->
                    <div class="d-flex flex-wrap gap-3 pt-2">
                        <?php
                        $positions = ['ST', 'RW', 'LW', 'AM', 'CM', 'DM', 'RB', 'LB', 'CB', 'GK'];
                        foreach ($positions as $pos) {
                            echo "
                            <div class='form-check scout-check'>
                                <input type='checkbox' name='positions[]' value='$pos' class='form-check-input pos-checkbox' id='pos_$pos'>
                                <label class='form-check-label fw-bold' for='pos_$pos'>$pos</label>
                            </div>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Βήμα 3: Επιλογή στατιστικών -->
                <div class="mb-4">
                    <div class="step-title mb-1">
                        <span class="step-badge">3</span>
                        <span>Select Comparison Metrics (47 Per-90 Stats)</span>
                    </div>
                    <p class="text-secondary small mb-4">
                        Επίλεξε τα στατιστικά χαρακτηριστικά στα οποία θέλεις να βασιστεί ο μαθηματικός υπολογισμός
                        ομοιότητας (Cosine Similarity).
                    </p>

                    <?php
                    $metric_groups = [
                        [
                            'color'   => '#f43f5e',
                            'title'   => 'Goals &amp; Attacking',
                            'checked' => false,
                            'metrics' => [
                                'Goals_per90'              => 'Goals',
                                'Assists_per90'            => 'Assists',
                                'xG_per90'                 => 'xG',
                                'npxG_per90'               => 'Non-Pen xG',
                                'xAG_per90'                => 'xAG',
                                'Shots_per90'              => 'Shots',
                                'Shots_On_Target_per90'    => 'Shots on Target',
                                'Conversion_percentage'    => 'Conversion %',
                                'Big_Chances_Missed_per90' => 'Big Chances Missed',
                                'Hit_Woodwork_per90'       => 'Hit Woodwork',
                                'Offsides_per90'           => 'Offsides',
                            ],
                        ],
                        [
                            'color'   => '#38bdf8',
                            'title'   => 'Passing &amp; Build-up',
                            'checked' => false,
                            'metrics' => [
                                'Passes_per90'                         => 'Total Passes',
                                'Successful_Passes_per90'              => 'Successful Passes',
                                'Passes_Percentage'                    => 'Pass Accuracy %',
                                'Final_Third_Passes_per90'             => 'Final 3rd Passes',
                                'Successful_Final_Third_Passes_per90'  => 'Succ. F3rd Passes',
                                'Final_Third_Passes_Percentage'        => 'F3rd Pass %',
                                'Through_Balls_per90'                  => 'Through Balls',
                                'Crosses_per90'                        => 'Crosses',
                                'Successful_Crosses_per90'             => 'Succ. Crosses',
                                'Crosses_Percentage'                   => 'Cross %',
                                'Touches_per90'                        => 'Touches',
                            ],
                        ],
                        [
                            'color'   => '#fbbf24',
                            'title'   => 'Carrying &amp; Progression',
                            'checked' => false,
                            'metrics' => [
                                'Carries_per90'                   => 'Carries',
                                'Progressive_Carries_per90'       => 'Prog. Carries',
                                'Carries_Ended_with_Goal_per90'   => 'Carr. to Goal',
                                'Carries_Ended_with_Assist_per90' => 'Carr. to Assist',
                                'Carries_Ended_with_Shot_per90'   => 'Carr. to Shot',
                                'Carries_Ended_with_Chance_per90' => 'Carr. to Chance',
                                'Dispossessed_per90'              => 'Dispossessed',
                            ],
                        ],
                        [
                            'color'   => '#34d399',
                            'title'   => 'Defending &amp; Duels',
                            'checked' => false,
                            'metrics' => [
                                'Tackles_per90'             => 'Tackles',
                                'Interceptions_per90'       => 'Interceptions',
                                'Blocks_per90'              => 'Blocks',
                                'Clearances_per90'          => 'Clearances',
                                'Ground_Duels_per90'        => 'Ground Duels',
                                'Ground_Duels_Won_per90'    => 'Gr. Duels Won',
                                'Aerial_Duels_per90'        => 'Aerial Duels',
                                'Aerial_Duels_Won_per90'    => 'Aer. Duels Won',
                                'Possession_Won_per90'      => 'Possession Won',
                                'Fouls_per90'               => 'Fouls',
                                'Own_Goals_per90'           => 'Own Goals',
                                'Clearances_Off_Line'       => 'Clearances Off Line',
                                'Clean_Sheets_per90'        => 'Clean Sheets',
                            ],
                        ],
                        [
                            'color'   => '#a78bfa',
                            'title'   => 'Goalkeeping (GK)',
                            'checked' => false,
                            'metrics' => [
                                'Saves_per90'          => 'Saves',
                                'Saves_Percentage'     => 'Save %',
                                'Goals_Conceded_per90' => 'Goals Conceded',
                                'Goals_Prevented'      => 'Goals Prevented',
                                'Penalties_Saved_per90'=> 'Penalties Saved',
                                'Punches_per90'        => 'Punches',
                                'High_Claims_per90'    => 'High Claims',
                                'xG_Threat_Conceded'   => 'xG Threat Conceded',
                            ],
                        ],
                    ];
                    ?>

                    <div class="row g-3">
                        <?php foreach ($metric_groups as $group): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="scout-metric-group" style="border-top: 3px solid <?= $group['color'] ?>;">

                                <!-- Group Header & Toggle Button -->
                                <div class="metric-group-header">
                                    <span class="metric-group-title" style="color: <?= $group['color'] ?>;">
                                        <?= $group['title'] ?>
                                    </span>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-light rounded-pill py-0 px-2 toggle-group-btn"
                                        style="font-size: 0.72rem; font-weight: 700; border-color: rgba(255,255,255,0.2);">
                                        All
                                    </button>
                                </div>

                                <!-- Checkboxes -->
                                <?php foreach ($group['metrics'] as $value => $label): ?>
                                <div class="form-check scout-check">
                                    <input type="checkbox" name="metrics[]" value="<?= $value ?>"
                                        class="form-check-input" <?= $group['checked'] ? 'checked' : '' ?>
                                        id="m_<?= $value ?>">
                                    <label class="form-check-label" for="m_<?= $value ?>"><?= $label ?></label>
                                </div>
                                <?php endforeach; ?>

                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-5 text-center">
                    <button type="submit"
                        class="btn btn-lg px-5 py-3 fw-bold rounded-pill text-white shadow-lg d-inline-flex align-items-center gap-2"
                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; font-size: 1.1rem; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.35);">
                        <i class="bi bi-radar"></i>
                        <span>Search Similar Players (Calculate Similarity)</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

            </form>
        </main>
    </div> <!-- /app-wrapper -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {

        function formatPlayerOption(state) {
            if (!state.id) return state.text;
            const el = $(state.element);
            const club = el.data('club') || '';
            const pos = el.data('pos') || '';
            const val = el.data('val') || '';

            return $(`
                <div class="d-flex justify-content-between align-items-center py-2 px-1">
                    <span class="fw-bold text-white fs-6">${state.text}</span>
                    <span class="d-flex align-items-center gap-2">
                        <!-- Όνομα Ομάδας: Καθαρό φωτεινό λευκό/silver badge -->
                        <span class="badge rounded-pill px-2 py-1" style="background: rgba(255, 255, 255, 0.12); color: #f8fafc; font-size: 0.8rem; font-weight: 600;">
                            <i class="bi bi-shield me-1 text-info"></i>${club}
                        </span>
                        <!-- Θέση -->
                        <span class="badge rounded-pill px-2 py-1" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.75rem;">
                            ${pos}
                        </span>
                        <!-- Αξία (€M) -->
                        <span class="badge rounded-pill px-2 py-1" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.75rem;">
                            ${val}
                        </span>
                    </span>
                </div>
            `);
        }

        function formatPlayerSelection(state) {
            if (!state.id) return state.text;
            const el = $(state.element);
            const club = el.data('club') || '';
            const pos = el.data('pos') || '';
            const val = el.data('val') || '';

            // Εμφάνιση στη μπάρα μόλις επιλεγεί: π.χ. Erling Haaland (Manchester City • ST • €180.0M)
            return `${state.text} (${club} • ${pos} • ${val})`;
        }

        $('#playerSearch').select2({
            placeholder: "Search player",
            allowClear: true,
            width: '100%',
            templateResult: formatPlayerOption,
            templateSelection: formatPlayerSelection
        });

        // 2. Βήμα 3: Επιλογή / Καθαρισμός στατιστικών ΑΝΑ ΚΑΤΗΓΟΡΙΑ
        $('.toggle-group-btn').on('click', function() {
            const card = $(this).closest('.scout-metric-group'); // Διορθώθηκε εδώ!
            const checkboxes = card.find('input[name="metrics[]"]');
            const allChecked = checkboxes.length === checkboxes.filter(':checked').length;

            // Αν είναι όλα τσεκαρισμένα τα ξετσεκάρει, αλλιώς τα τσεκάρει όλα
            checkboxes.prop('checked', !allChecked);
            $(this).text(allChecked ? 'All' : 'None');
            $(this).toggleClass('btn-primary text-white btn-outline-light');
        });

        // ── Presets Θέσεων (Attackers, Midfield, Defenders) ───────────────────
        $('.btn-pos-preset').on('click', function() {
            const targetPositions = $(this).data('positions').split(',');

            // Ελέγχουμε αν είναι ήδη όλα τσεκαρισμένα
            let allChecked = true;
            targetPositions.forEach(function(pos) {
                if (!$('#pos_' + pos).is(':checked')) {
                    allChecked = false;
                }
            });
            // Αν είναι όλα τσεκαρισμένα, τα ξετσεκάρει (toggle off)
            // Αλλιώς, τσεκάρει όλες τις θέσεις αυτού του group
            targetPositions.forEach(function(pos) {
                $('#pos_' + pos).prop('checked', !allChecked);
            });
            $(this).toggleClass('active', !allChecked);
        });
        // Κουμπί Καθαρισμού όλων των θέσεων
        $('#clearPositionsBtn').on('click', function() {
            $('.pos-checkbox').prop('checked', false);
            $('.btn-pos-preset').removeClass('active');
        });
    });
    </script>

</body>

</html>