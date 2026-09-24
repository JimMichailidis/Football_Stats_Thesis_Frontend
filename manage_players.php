<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<!-- Διαχείριση παικτών στη βάση δεδομένων (Λειτουργίες: Προσθήκη νέου παίκτη, Διαγραφή, Εμφάνιση πίνακα)-->

<?php
    // Σύνδεση με βάση δεδομένων
    $conn = new mysqli("localhost", "root", "", "footballdatabase");
    if ($conn->connect_error) die("Error: " . $conn->connect_error);
    $conn->set_charset("utf8mb4");

    // Ορισμός επιτρεπόμενων στηλών για εισαγωγή
    $allowed_columns = [
    'ID', 'Player_Name', 'Club', 'Nationality', 'Position',
    'Appearances', 'Minutes', 'Goals', 'Assists', 'Shots',
    'Shots_On_Target', 'Conversion_percentage', 'Big_Chances_Missed',
    'Hit_Woodwork', 'Offsides', 'Touches', 'Passes', 'Successful_Passes',
    'Passes_Percentage', 'Crosses', 'Successful_Crosses', 'Crosses_Percentage',
    'Final_Third_Passes', 'Successful_Final_Third_Passes',
    'Final_Third_Passes_Percentage', 'Through_Balls', 'Carries',
    'Progressive_Carries', 'Carries_Ended_with_Goal', 'Carries_Ended_with_Assist',
    'Carries_Ended_with_Shot', 'Carries_Ended_with_Chance', 'Possession_Won',
    'Dispossessed', 'Clean_Sheets', 'Clearances', 'Interceptions', 'Blocks',
    'Tackles', 'Ground_Duels', 'Ground_Duels_Won', 'gDuels_Percentage',
    'Aerial_Duels', 'Aerial_Duels_Won', 'Aerial_Duels_Percentage',
    'Goals_Conceded', 'xG_Threat_Conceded', 'Own_Goals', 'Fouls',
    'Yellow_Cards', 'Red_Cards', 'Saves', 'Saves_Percentage',
    'Penalties_Saved', 'Clearances_Off_Line', 'Punches', 'High_Claims',
    'Goals_Prevented', 'xG', 'npxG', 'xAG', 'Market_Value_In_Millions'
    ];
 
        // Διαγραφή παίκτη (και από τους 2 πίνακες ταυτόχρονα)
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $conn->query("DELETE FROM epl_player_stats_24_25_per90 WHERE ID = $id");
        $conn->query("DELETE FROM epl_player_stats_24_25 WHERE ID = $id");
        header("Location: manage_players.php");
        exit();
    }
 
    // Προσθήκη νέου παίκτη & Αυτόματος υπολογισμός Per-90
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_player'])) {
        $data = $_POST;
        unset($data['add_player']);

        // Φιλτράρισμα μόνο των επιτρεπόμενων στηλών
        $data = array_intersect_key($data, array_flip($allowed_columns));
        
        $new_id = intval($data['ID'] ?? 0);

        // Έλεγχος αν υπάρχει ήδη παίκτης με το ίδιο ID 
        $check = $conn->query("SELECT ID FROM epl_player_stats_24_25 WHERE ID = $new_id");

        if ($check && $check->num_rows > 0) {
            $msg = "<div class='alert alert-danger'>❌ Player with ID <strong>$new_id</strong> already exists. Use a different ID.</div>";
        } else {
            // Μηδενισμός κενών αριθμητικών πεδίων για αποφυγή MySQL strict errors
            $text_columns = ['Player_Name', 'Club', 'Nationality', 'Position'];
            foreach ($data as $col => $val) {
                if (!in_array($col, $text_columns) && trim((string)$val) === '') {
                    $data[$col] = 0;
                }
            }

            // Εισαγωγή στον βασικό πίνακα (epl_player_stats_24_25)
            $columns = implode(", ", array_keys($data));
            $values  = implode("', '", array_map([$conn, 'real_escape_string'], array_values($data)));
            $sql     = "INSERT INTO epl_player_stats_24_25 ($columns) VALUES ('$values')";

            if ($conn->query($sql) === TRUE) {
                // Διαγραφή τυχόν παλιάς per90 εγγραφής με το ίδιο ID (ασφάλεια)
                $conn->query("DELETE FROM epl_player_stats_24_25_per90 WHERE ID = $new_id");

                // Αυτόματος υπολογισμός και εισαγωγή στον epl_player_stats_24_25_per90
                $sql_per90 = "
                INSERT INTO epl_player_stats_24_25_per90 (
                    ID, Player_Name, Club, Nationality, Position, Minutes,
                    Goals_per90, Assists_per90, Shots_per90, Shots_On_Target_per90,
                    Conversion_percentage, Big_Chances_Missed_per90, Hit_Woodwork_per90, Offsides_per90,
                    Touches_per90, Passes_per90, Successful_Passes_per90, Passes_Percentage,
                    Crosses_per90, Successful_Crosses_per90, Crosses_Percentage,
                    Final_Third_Passes_per90, Successful_Final_Third_Passes_per90, Final_Third_Passes_Percentage,
                    Through_Balls_per90, Carries_per90, Progressive_Carries_per90, Carries_Ended_with_Goal_per90,
                    Carries_Ended_with_Assist_per90, Carries_Ended_with_Shot_per90, Carries_Ended_with_Chance_per90,
                    Possession_Won_per90, Dispossessed_per90, Clean_Sheets_per90, Clearances_per90,
                    Interceptions_per90, Blocks_per90, Tackles_per90, Ground_Duels_per90, Ground_Duels_Won_per90,
                    Aerial_Duels_per90, Aerial_Duels_Won_per90, Goals_Conceded_per90, Saves_per90,
                    Penalties_Saved_per90, Punches_per90, High_Claims_per90, xG_per90, npxG_per90, xAG_per90
                )
                SELECT 
                    ID, Player_Name, Club, Nationality, Position, Minutes,
                    COALESCE(Goals / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Assists / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Shots / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Shots_On_Target / NULLIF(Minutes/90.0, 0), 0),
                    Conversion_percentage,
                    COALESCE(Big_Chances_Missed / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Hit_Woodwork / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Offsides / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Touches / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Passes / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Successful_Passes / NULLIF(Minutes/90.0, 0), 0),
                    Passes_Percentage,
                    COALESCE(Crosses / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Successful_Crosses / NULLIF(Minutes/90.0, 0), 0),
                    Crosses_Percentage,
                    COALESCE(Final_Third_Passes / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Successful_Final_Third_Passes / NULLIF(Minutes/90.0, 0), 0),
                    Final_Third_Passes_Percentage,
                    COALESCE(Through_Balls / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Carries / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Progressive_Carries / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Carries_Ended_with_Goal / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Carries_Ended_with_Assist / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Carries_Ended_with_Shot / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Carries_Ended_with_Chance / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Possession_Won / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Dispossessed / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Clean_Sheets / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Clearances / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Interceptions / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Blocks / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Tackles / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Ground_Duels / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Ground_Duels_Won / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Aerial_Duels / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Aerial_Duels_Won / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Goals_Conceded / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Saves / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Penalties_Saved / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Punches / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(High_Claims / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(xG / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(npxG / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(xAG / NULLIF(Minutes/90.0, 0), 0)
                FROM epl_player_stats_24_25 
                WHERE ID = $new_id";

                $conn->query($sql_per90);

                $msg = "<div class='alert alert-success'>✅ Player added successfully!</div>";
            } else {
                $msg = "<div class='alert alert-danger'>❌ Error: " . $conn->error . "</div>";
            }
        }
    }
    
        // 3. Ενημέρωση υπάρχοντος παίκτη (Update) & Επαναϋπολογισμός Per-90
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_player'])) {
        $data = $_POST;
        unset($data['update_player']);
        $data = array_intersect_key($data, array_flip($allowed_columns));
        $player_id = intval($data['ID'] ?? 0);

        if ($player_id > 0) {
            $text_columns = ['Player_Name', 'Club', 'Nationality', 'Position'];
            foreach ($data as $col => $val) {
                if (!in_array($col, $text_columns) && trim((string)$val) === '') {
                    $data[$col] = 0;
                }
            }

            // Δημιουργία του UPDATE SQL
            $updates = [];
            foreach ($data as $col => $val) {
                if ($col !== 'ID') {
                    $escaped = $conn->real_escape_string($val);
                    $updates[] = "$col = '$escaped'";
                }
            }
            $update_sql = "UPDATE epl_player_stats_24_25 SET " . implode(", ", $updates) . " WHERE ID = $player_id";

            if ($conn->query($update_sql) === TRUE) {
                // Επανυπολογισμός στον πίνακα per-90
                $conn->query("DELETE FROM epl_player_stats_24_25_per90 WHERE ID = $player_id");

                $sql_per90 = "
                INSERT INTO epl_player_stats_24_25_per90 (
                    ID, Player_Name, Club, Nationality, Position, Minutes,
                    Goals_per90, Assists_per90, Shots_per90, Shots_On_Target_per90,
                    Conversion_percentage, Big_Chances_Missed_per90, Hit_Woodwork_per90, Offsides_per90,
                    Touches_per90, Passes_per90, Successful_Passes_per90, Passes_Percentage,
                    Crosses_per90, Successful_Crosses_per90, Crosses_Percentage,
                    Final_Third_Passes_per90, Successful_Final_Third_Passes_per90, Final_Third_Passes_Percentage,
                    Through_Balls_per90, Carries_per90, Progressive_Carries_per90, Carries_Ended_with_Goal_per90,
                    Carries_Ended_with_Assist_per90, Carries_Ended_with_Shot_per90, Carries_Ended_with_Chance_per90,
                    Possession_Won_per90, Dispossessed_per90, Clean_Sheets_per90, Clearances_per90,
                    Interceptions_per90, Blocks_per90, Tackles_per90, Ground_Duels_per90, Ground_Duels_Won_per90,
                    Aerial_Duels_per90, Aerial_Duels_Won_per90, Goals_Conceded_per90, Saves_per90,
                    Penalties_Saved_per90, Punches_per90, High_Claims_per90, xG_per90, npxG_per90, xAG_per90
                )
                SELECT 
                    ID, Player_Name, Club, Nationality, Position, Minutes,
                    COALESCE(Goals / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Assists / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Shots / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Shots_On_Target / NULLIF(Minutes/90.0, 0), 0),
                    Conversion_percentage,
                    COALESCE(Big_Chances_Missed / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Hit_Woodwork / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Offsides / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Touches / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Passes / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Successful_Passes / NULLIF(Minutes/90.0, 0), 0),
                    Passes_Percentage,
                    COALESCE(Crosses / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Successful_Crosses / NULLIF(Minutes/90.0, 0), 0),
                    Crosses_Percentage,
                    COALESCE(Final_Third_Passes / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Successful_Final_Third_Passes / NULLIF(Minutes/90.0, 0), 0),
                    Final_Third_Passes_Percentage,
                    COALESCE(Through_Balls / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Carries / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Progressive_Carries / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Carries_Ended_with_Goal / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Carries_Ended_with_Assist / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Carries_Ended_with_Shot / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Carries_Ended_with_Chance / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Possession_Won / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Dispossessed / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Clean_Sheets / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Clearances / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Interceptions / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Blocks / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Tackles / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Ground_Duels / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Ground_Duels_Won / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Aerial_Duels / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Aerial_Duels_Won / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Goals_Conceded / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Saves / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Penalties_Saved / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(Punches / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(High_Claims / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(xG / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(npxG / NULLIF(Minutes/90.0, 0), 0),
                    COALESCE(xAG / NULLIF(Minutes/90.0, 0), 0)
                FROM epl_player_stats_24_25 
                WHERE ID = $player_id";

                $conn->query($sql_per90);

                $msg = "<div class='alert alert-success'>✅ Player updated and per-90 stats synced successfully!</div>";
            } else {
                $msg = "<div class='alert alert-danger'>❌ Error updating: " . $conn->error . "</div>";
            }
        }
    }

    // Φόρτωση όλων των παικτών 
    $result = $conn->query("SELECT * FROM epl_player_stats_24_25 ORDER BY ID ASC");
    // Εύρεση επόμενου διαθέσιμου ID
    $id_res = $conn->query("SELECT COALESCE(MAX(ID), 0) + 1 AS next_id FROM epl_player_stats_24_25");
    $next_id = ($id_res && $row = $id_res->fetch_assoc()) ? $row['next_id'] : 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Studio — ScoutIQ Premier League</title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Theme CSS -->
    <link href="assets/css/theme.css" rel="stylesheet">

    <style>
    /* Section Boxes inside Form */
    .section-box {
        background: rgba(15, 23, 42, 0.7);
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 12px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-top: 3px solid #0d6efd;
    }

    .section-title {
        font-weight: 800;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: block;
        margin-bottom: 8px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding-bottom: 4px;
    }

    .form-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #94a3b8;
        margin-bottom: 2px;
    }

    .form-control-sm {
        background: rgba(9, 13, 22, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #fff !important;
        font-size: 0.8rem;
        height: 30px;
        border-radius: 6px;
    }

    .form-control-sm:focus {
        background: rgba(9, 13, 22, 0.95) !important;
        border-color: rgba(59, 130, 246, 0.5) !important;
        box-shadow: none !important;
        color: #fff !important;
    }

    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }
    </style>
</head>

<body>
    <!-- Ambient Background Lights & Grid -->
    <div class="ambient-glow ambient-glow-1"></div>
    <div class="ambient-glow ambient-glow-2"></div>
    <div class="ambient-grid"></div>

    <div class="db-container">

        <!-- Top Navigation / Header Bar -->
        <div class="db-toolbar d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="brand-badge text-decoration-none">
                    <div class="brand-icon">
                        <i class="bi bi-database-fill-gear"></i>
                    </div>
                    <div>
                        <h1 class="brand-title" style="font-size: 1.15rem;">Database Studio</h1>
                        <span class="brand-sub">Premier League Roster Manager</span>
                    </div>
                </a>

                <button
                    class="btn btn-sm btn-primary fw-bold rounded-pill px-3 py-2 d-flex align-items-center gap-2 shadow-sm"
                    type="button" data-bs-toggle="collapse" data-bs-target="#addPlayerCollapse" aria-expanded="false"
                    aria-controls="addPlayerCollapse" id="toggleFormBtn"
                    style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border: none;">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Add New Player</span>
                </button>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-3 py-2 d-flex align-items-center gap-2"
                    style="background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1); color: #cbd5e1;">
                    <i class="bi bi-shield-check text-success"></i>
                    <span><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                </span>
                <a href="index.php"
                    class="btn btn-sm btn-outline-light rounded-pill px-3 py-2 d-flex align-items-center gap-1"
                    style="border-color: rgba(255,255,255,0.15);">
                    <i class="bi bi-house"></i>
                    <span class="d-none d-sm-inline">Home</span>
                </a>
                <a href="logout.php"
                    class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 d-flex align-items-center gap-1"
                    style="border-color: rgba(239,68,68,0.3);">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-sm-inline">Logout</span>
                </a>
            </div>
        </div>

        <!-- Flash message (success / error) -->
        <?= $msg ?? '' ?>

        <!-- Collapsible Form for Add / Edit -->
        <div class="collapse <?= (isset($_POST['add_player']) || isset($_POST['update_player'])) ? 'show' : '' ?>"
            id="addPlayerCollapse">
            <div class="db-form-card mb-4">

                <!-- Form Header Mode -->
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2"
                    style="border-color: rgba(255,255,255,0.1) !important;">
                    <h5 class="m-0 fw-bold text-white" id="formModeTitle">➕ Add New Player</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="cancelEditBtn"
                        onclick="resetFormToAdd()">
                        ✖ Cancel Edit
                    </button>
                </div>


                <form method="POST">
                    <!-- Section 1: Identity & Participation -->
                    <div class="section-box">
                        <span class="section-title text-primary">1. Identity &amp; Participation</span>
                        <div class="row g-2">
                            <div class="col-md-1"><label class="form-label">ID</label>
                                <input type="number" name="ID" value="<?= $next_id ?>"
                                    class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-2"><label class="form-label">Player Name</label>
                                <input type="text" name="Player_Name" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-2"><label class="form-label">Club</label>
                                <input type="text" name="Club" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">Nationality</label>
                                <input type="text" name="Nationality" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Position</label>
                                <input type="text" name="Position" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Apps</label>
                                <input type="number" name="Appearances" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Minutes</label>
                                <input type="number" name="Minutes" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">Market Value €M</label>
                                <input type="number" step="0.01" name="Market_Value_In_Millions"
                                    class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>



                    <!-- Section 2: Attacking & Expected Goals -->
                    <div class="section-box" style="border-top-color: #dc3545;">
                        <span class="section-title text-danger">2. Attacking &amp; Expected Goals</span>
                        <div class="row g-2">
                            <div class="col-md-1"><label class="form-label">Goals</label>
                                <input type="number" name="Goals" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Assists</label>
                                <input type="number" name="Assists" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Shots</label>
                                <input type="number" name="Shots" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">SOT</label>
                                <input type="number" name="Shots_On_Target" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Conv %</label>
                                <input type="number" step="0.01" name="Conversion_percentage"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">BigCh. Miss</label>
                                <input type="number" name="Big_Chances_Missed" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Woodwork</label>
                                <input type="number" name="Hit_Woodwork" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Offsides</label>
                                <input type="number" name="Offsides" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">xG</label>
                                <input type="number" step="0.01" name="xG" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">npxG</label>
                                <input type="number" step="0.01" name="npxG" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">xAG</label>
                                <input type="number" step="0.01" name="xAG" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Passing & Distribution -->
                    <div class="section-box" style="border-top-color: #198754;">
                        <span class="section-title text-success">3. Passing &amp; Distribution</span>
                        <div class="row g-2">
                            <div class="col-md-1"><label class="form-label">Touches</label>
                                <input type="number" name="Touches" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Passes</label>
                                <input type="number" name="Passes" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Succ. Pass</label>
                                <input type="number" name="Successful_Passes" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Pass %</label>
                                <input type="number" step="0.01" name="Passes_Percentage"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Crosses</label>
                                <input type="number" name="Crosses" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Succ. Cross</label>
                                <input type="number" name="Successful_Crosses" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Cross %</label>
                                <input type="number" step="0.01" name="Crosses_Percentage"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">F3rd Pass</label>
                                <input type="number" name="Final_Third_Passes" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Succ. F3rd</label>
                                <input type="number" name="Successful_Final_Third_Passes"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">F3rd %</label>
                                <input type="number" step="0.01" name="Final_Third_Passes_Percentage"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">Through Balls</label>
                                <input type="number" name="Through_Balls" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Carries & Dribbling -->
                    <div class="section-box" style="border-top-color: #fd7e14;">
                        <span class="section-title" style="color: #fd7e14;">4. Carries &amp; Dribbling</span>
                        <div class="row g-2">
                            <div class="col-md-1"><label class="form-label">Carries</label>
                                <input type="number" name="Carries" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">Prog. Carries</label>
                                <input type="number" name="Progressive_Carries" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Carr. Goal</label>
                                <input type="number" name="Carries_Ended_with_Goal"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Carr. Assist</label>
                                <input type="number" name="Carries_Ended_with_Assist"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Carr. Shot</label>
                                <input type="number" name="Carries_Ended_with_Shot"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">Carr. Chance</label>
                                <input type="number" name="Carries_Ended_with_Chance"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">Poss. Won</label>
                                <input type="number" name="Possession_Won" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">Dispossessed</label>
                                <input type="number" name="Dispossessed" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Defense, Duels & Discipline -->
                    <div class="section-box" style="border-top-color: #ffc107;">
                        <span class="section-title text-warning">5. Defense, Duels &amp; Discipline</span>
                        <div class="row g-2">
                            <div class="col-md-1"><label class="form-label">C. Sheets</label>
                                <input type="number" name="Clean_Sheets" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Clearance</label>
                                <input type="number" name="Clearances" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Intercept.</label>
                                <input type="number" name="Interceptions" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Blocks</label>
                                <input type="number" name="Blocks" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Tackles</label>
                                <input type="number" name="Tackles" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Gr. Duels</label>
                                <input type="number" name="Ground_Duels" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Gr. Won</label>
                                <input type="number" name="Ground_Duels_Won" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Gr. %</label>
                                <input type="number" step="0.01" name="gDuels_Percentage"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Aer. Duels</label>
                                <input type="number" name="Aerial_Duels" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Aer. Won</label>
                                <input type="number" name="Aerial_Duels_Won" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Aer. %</label>
                                <input type="number" step="0.01" name="Aerial_Duels_Percentage"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Fouls</label>
                                <input type="number" name="Fouls" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Yellow</label>
                                <input type="number" name="Yellow_Cards" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Red</label>
                                <input type="number" name="Red_Cards" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Own Goal</label>
                                <input type="number" name="Own_Goals" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Section 6: Goalkeeping & Goals Prevented -->
                    <div class="section-box" style="border-top-color: #6f42c1;">
                        <span class="section-title" style="color: #6f42c1;">6. Goalkeeping &amp; Goals
                            Prevented</span>
                        <div class="row g-2">
                            <div class="col-md-1"><label class="form-label">G. Conced.</label>
                                <input type="number" name="Goals_Conceded" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">xG Conc.</label>
                                <input type="number" step="0.01" name="xG_Threat_Conceded"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Saves</label>
                                <input type="number" name="Saves" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Save %</label>
                                <input type="number" step="0.01" name="Saves_Percentage"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">Pen. Saved</label>
                                <input type="number" name="Penalties_Saved" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">Clear. Off Line</label>
                                <input type="number" name="Clearances_Off_Line" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">Punches</label>
                                <input type="number" name="Punches" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-1"><label class="form-label">High Claims</label>
                                <input type="number" name="High_Claims" class="form-control form-control-sm">
                            </div>

                            <div class="col-md-2"><label class="form-label">G. Prevented</label>
                                <input type="number" step="0.01" name="Goals_Prevented"
                                    class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" name="add_player" id="formSubmitBtn"
                        class="btn btn-primary w-100 fw-bold shadow">
                        Submit Player
                    </button>
                </form>
            </div>
        </div><!-- /#addPlayerCollapse -->

        <!-- SEARCH / FILTER BAR -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="m-0 fw-bold text-white d-flex align-items-center gap-2">
                <i class="bi bi-table text-primary"></i>
                <span>Players Database</span>
            </h5>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm" style="width: 320px;">
                    <span class="input-group-text"
                        style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-right: none; color: #94a3b8;">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="playerSearch" class="form-control"
                        style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); color: #fff;"
                        placeholder="Search player, club, position..." oninput="filterTable()">
                    <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()"
                        style="border-color: rgba(255,255,255,0.1); color: #94a3b8;" title="Clear">✖</button>
                </div>
                <span class="badge rounded-pill py-2 px-3"
                    style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">
                    Showing <span id="visibleCount" class="fw-bold"><?= $result->num_rows ?></span> of
                    <?= $result->num_rows ?>
                </span>
            </div>
        </div>

        <!-- ΠΙΝΑΚΑΣ ΠΑΙΚΤΩΝ (Dark High-Tech Studio Table) -->
        <div class="db-table-wrapper">
            <table id="playersTable" class="table db-table table-hover table-sm m-0">
                <thead>
                    <tr>
                        <!-- Frozen "Actions" column -->
                        <th class="db-sticky-col text-white">Actions</th>

                        <?php
                        // Render column headers — freeze ID & Player_Name columns
                        $fields = $result->fetch_fields();
                        foreach ($fields as $field) {
                            $freeze = ($field->name === 'ID' || $field->name === 'Player_Name')
                                ? 'db-sticky-col text-white'
                                : 'text-white';
                            echo "<th class='$freeze'>{$field->name}</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <!-- Actions: Edit & Delete -->
                        <td class="db-sticky-col text-nowrap">
                            <button type="button" class="action-btn-edit me-1"
                                onclick='editPlayer(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)'
                                title="Edit Player">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <a href="?delete=<?= $row['ID'] ?>" class="action-btn-delete"
                                onclick="return confirm('Delete player?');" title="Delete Player">
                                <i class="bi bi-trash3"></i>
                            </a>
                        </td>

                        <?php foreach ($row as $col => $value):
                            $freeze = ($col === 'ID' || $col === 'Player_Name')
                                ? 'db-sticky-col fw-bold text-white'
                                : '';
                        ?>
                        <td class="<?= $freeze ?>"><?= htmlspecialchars($value ?? '') ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div><!-- /.db-table-wrapper -->


    </div><!-- /.container-fluid -->

    <!-- Live Table Filter Script -->
    <script>
    function filterTable() {
        const input = document.getElementById("playerSearch");
        const filter = input.value.toLowerCase().trim();
        const table = document.getElementById("playersTable");
        const tbody = table.getElementsByTagName("tbody")[0];
        const rows = tbody.getElementsByTagName("tr");
        let visibleCount = 0;

        for (let i = 0; i < rows.length; i++) {
            // Ελέγχει ολόκληρο το περιεχόμενο της γραμμής (Όνομα, Ομάδα, Θέση κλπ)
            const text = rows[i].textContent.toLowerCase();
            if (text.indexOf(filter) > -1) {
                rows[i].style.display = "";
                visibleCount++;
            } else {
                rows[i].style.display = "none";
            }
        }

        document.getElementById("visibleCount").textContent = visibleCount;
    }

    function clearSearch() {
        const input = document.getElementById("playerSearch");
        input.value = "";
        filterTable();
        input.focus();
    }
    </script>



    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    const formCollapse = document.getElementById('addPlayerCollapse');
    const toggleBtn = document.getElementById('toggleFormBtn');

    if (formCollapse && toggleBtn) {
        formCollapse.addEventListener('show.bs.collapse', function() {
            toggleBtn.innerHTML = '✖ Close Form';
            toggleBtn.classList.replace('btn-primary', 'btn-secondary');
        });
        formCollapse.addEventListener('hide.bs.collapse', function() {
            toggleBtn.innerHTML = '➕ Add New Player';
            toggleBtn.classList.replace('btn-secondary', 'btn-primary');
        });
    }

    // 1. Συνάρτηση Επεξεργασίας Παίκτη
    function editPlayer(data) {
        // Άνοιγμα της φόρμας
        const collapseEl = document.getElementById('addPlayerCollapse');
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl);
        bsCollapse.show();

        // Συμπλήρωση όλων των πεδίων με τα υπάρχοντα δεδομένα του παίκτη
        const form = document.querySelector('#addPlayerCollapse form');
        for (const key in data) {
            if (form.elements[key]) {
                form.elements[key].value = (data[key] !== null) ? data[key] : '';
            }
        }

        // Κλείδωμα του ID κατά την επεξεργασία (για να μην αλλάξει το ID του)
        if (form.elements['ID']) {
            form.elements['ID'].readOnly = true;
        }

        // Αλλαγή τίτλου και κουμπιού σε "Update"
        document.getElementById('formModeTitle').innerHTML = '✏️ Edit Player: <span class="text-primary">' + data[
            'Player_Name'] + '</span> (ID: ' + data['ID'] + ')';

        const submitBtn = document.getElementById('formSubmitBtn');
        submitBtn.name = 'update_player';
        submitBtn.className = 'btn btn-warning w-100 fw-bold shadow text-dark';
        submitBtn.innerHTML = '💾 Update Player Stats';

        document.getElementById('cancelEditBtn').classList.remove('d-none');

        // Scroll στην αρχή της φόρμας
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // 2. Συνάρτηση Επαναφοράς Φόρμας σε "Add"
    function resetFormToAdd() {
        const form = document.querySelector('#addPlayerCollapse form');
        form.reset();

        if (form.elements['ID']) {
            form.elements['ID'].readOnly = false;
            form.elements['ID'].value = '<?= $next_id ?>';
        }

        document.getElementById('formModeTitle').innerHTML = '➕ Add New Player';

        const submitBtn = document.getElementById('formSubmitBtn');
        submitBtn.name = 'add_player';
        submitBtn.className = 'btn btn-primary w-100 fw-bold shadow';
        submitBtn.innerHTML = 'Submit Player';

        document.getElementById('cancelEditBtn').classList.add('d-none');
    }
    </script>


</body>



</html>