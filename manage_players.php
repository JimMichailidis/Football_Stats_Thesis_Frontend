<!DOCTYPE html>
<html lang="el">
 
<!-- Διαχείριση παικτών στη βάση δεδομένων (Λειτουργίες: Προσθήκη νέου παίκτη, Διαγραφή, Εμφάνιση πίνακα)-->
 
<?php
    // Σύνδεση με βάση δεδομένων
    $conn = new mysqli("localhost", "root", "", "footballdatabase");
    if ($conn->connect_error) die("Σφάλμα: " . $conn->connect_error);
    $conn->set_charset("utf8mb4");
 
    // Διαγραφή παίκτη (GET request)
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $conn->query("DELETE FROM epl_player_stats_24_25 WHERE ID = $id");
        header("Location: manage_players.php");
        exit();
    }
 
    // Προσθήκη νέου παίκτη (POST request) 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_player'])) {
    $data = $_POST;
    unset($data['add_player']);

    $new_id = intval($data['ID']);

    // Έλεγχος αν υπάρχει ήδη παίκτης με το ίδιο ID 
    $check = $conn->query("SELECT ID FROM epl_player_stats_24_25 WHERE ID = $new_id");

    if ($check->num_rows > 0) {
        $msg = "<div class='alert alert-danger'>❌ Υπάρχει ήδη παίκτης με ID <strong>$new_id</strong>. Χρησιμοποίησε διαφορετικό ID.</div>";
    } else {
        // Κανονική εισαγωγή 
        $columns = implode(", ", array_keys($data));
        $values  = implode("', '", array_map([$conn, 'real_escape_string'], array_values($data)));
        $sql     = "INSERT INTO epl_player_stats_24_25 ($columns) VALUES ('$values')";

        if ($conn->query($sql) === TRUE) {
            $msg = "<div class='alert alert-success'>✅ Ο παίκτης προστέθηκε!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>❌ Σφάλμα: " . $conn->error . "</div>";
        }
    }
}
 
    // Φόρτωση όλων των παικτών 
    $result = $conn->query("SELECT * FROM epl_player_stats_24_25 ORDER BY ID ASC");
?>
 
<head>
    <meta charset="UTF-8">
    <title>EPL Database Manager — 62 Fields</title>
 
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 
    <style>
        /* Layout */
        body {
            font-size: 0.8rem;
            background: #f0f2f5;
        }
 
        /* Form section boxes */
        .section-box {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-top: 3px solid #0d6efd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
 
        .section-title {
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            display: block;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
 
        .form-label {
            margin-bottom: 1px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #666;
        }
 
        /* Data table */
        .table-container {
            max-height: 500px;
            overflow: auto;
            background: white;
        }
 
        thead th {
            position: sticky;
            top: 0;
            background: #212529 !important;
            color: white;
            z-index: 10;
        }
 
        /* Freeze first column (Actions + ID/Name) */
        .sticky-col {
            position: sticky;
            left: 0;
            background: white;
            z-index: 5;
            border-right: 2px solid #ddd !important;
        }
 
        thead th.sticky-col {
            z-index: 11;
            background: #212529 !important;
        }
        
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
        input[type="number"],
        input[type="text"] {
        height: 31px;
}

    </style>
</head>
 

<body class="p-3">
 
    <!-- Back button -->
    <a href="index.php" name="Backbtn" class="btn btn-dark position-absolute top-0 end-0 m-3"  style="z-index: 9999;">
        Home
    </a>
    
 
    <div class="container-fluid">
 
        <h3 class="mb-3">📊 Full Stats Entry (62 Columns)</h3>
 
        <!-- Flash message (success / error) -->
        <?= $msg ?? '' ?>
 
        <!-- ΦΟΡΜΑ ΠΡΟΣΘΗΚΗΣ ΠΑΙΚΤΗ -->
        <form method="POST">
 
            <!-- Section 1: Identity & Participation -->
            <div class="section-box">
                <span class="section-title text-primary">1. Identity &amp; Participation</span>
                <div class="row g-2">
                    <div class="col-md-1"><label class="form-label">ID</label>
                        <input type="number" name="ID" class="form-control form-control-sm" required></div>
 
                    <div class="col-md-2"><label class="form-label">Player Name</label>
                        <input type="text" name="Player_Name" class="form-control form-control-sm" required></div>
 
                    <div class="col-md-2"><label class="form-label">Club</label>
                        <input type="text" name="Club" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">Nationality</label>
                        <input type="text" name="Nationality" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Position</label>
                        <input type="text" name="Position" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Apps</label>
                        <input type="number" name="Appearances" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Minutes</label>
                        <input type="number" name="Minutes" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">Market Value €M</label>
                        <input type="number" step="0.01" name="Market_Value_In_Millions" class="form-control form-control-sm"></div>
                </div>
            </div>
 
            <!-- Section 2: Attacking & Expected Goals -->
            <div class="section-box" style="border-top-color: #dc3545;">
                <span class="section-title text-danger">2. Attacking &amp; Expected Goals</span>
                <div class="row g-2">
                    <div class="col-md-1"><label class="form-label">Goals</label>
                        <input type="number" name="Goals" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Assists</label>
                        <input type="number" name="Assists" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Shots</label>
                        <input type="number" name="Shots" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">SOT</label>
                        <input type="number" name="Shots_On_Target" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Conv %</label>
                        <input type="number" step="0.01" name="Conversion_percentage" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">BigCh. Miss</label>
                        <input type="number" name="Big_Chances_Missed" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Woodwork</label>
                        <input type="number" name="Hit_Woodwork" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Offsides</label>
                        <input type="number" name="Offsides" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">xG</label>
                        <input type="number" step="0.01" name="xG" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">npxG</label>
                        <input type="number" step="0.01" name="npxG" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">xAG</label>
                        <input type="number" step="0.01" name="xAG" class="form-control form-control-sm"></div>
                </div>
            </div>
 
            <!-- Section 3: Passing & Distribution -->
            <div class="section-box" style="border-top-color: #198754;">
                <span class="section-title text-success">3. Passing &amp; Distribution</span>
                <div class="row g-2">
                    <div class="col-md-1"><label class="form-label">Touches</label>
                        <input type="number" name="Touches" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Passes</label>
                        <input type="number" name="Passes" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Succ. Pass</label>
                        <input type="number" name="Successful_Passes" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Pass %</label>
                        <input type="number" step="0.01" name="Passes_Percentage" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Crosses</label>
                        <input type="number" name="Crosses" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Succ. Cross</label>
                        <input type="number" name="Successful_Crosses" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Cross %</label>
                        <input type="number" step="0.01" name="Crosses_Percentage" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">F3rd Pass</label>
                        <input type="number" name="Final_Third_Passes" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Succ. F3rd</label>
                        <input type="number" name="Successful_Final_Third_Passes" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">F3rd %</label>
                        <input type="number" step="0.01" name="Final_Third_Passes_Percentage" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">Through Balls</label>
                        <input type="number" name="Through_Balls" class="form-control form-control-sm"></div>
                </div>
            </div>
 
            <!-- Section 4: Carries & Dribbling -->
            <div class="section-box" style="border-top-color: #fd7e14;">
                <span class="section-title" style="color: #fd7e14;">4. Carries &amp; Dribbling</span>
                <div class="row g-2">
                    <div class="col-md-1"><label class="form-label">Carries</label>
                        <input type="number" name="Carries" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">Prog. Carries</label>
                        <input type="number" name="Progressive_Carries" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Carr. Goal</label>
                        <input type="number" name="Carries_Ended_with_Goal" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Carr. Assist</label>
                        <input type="number" name="Carries_Ended_with_Assist" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Carr. Shot</label>
                        <input type="number" name="Carries_Ended_with_Shot" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">Carr. Chance</label>
                        <input type="number" name="Carries_Ended_with_Chance" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">Poss. Won</label>
                        <input type="number" name="Possession_Won" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">Dispossessed</label>
                        <input type="number" name="Dispossessed" class="form-control form-control-sm"></div>
                </div>
            </div>
 
            <!-- Section 5: Defense, Duels & Discipline -->
            <div class="section-box" style="border-top-color: #ffc107;">
                <span class="section-title text-warning">5. Defense, Duels &amp; Discipline</span>
                <div class="row g-2">
                    <div class="col-md-1"><label class="form-label">C. Sheets</label>
                        <input type="number" name="Clean_Sheets" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Clearance</label>
                        <input type="number" name="Clearances" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Intercept.</label>
                        <input type="number" name="Interceptions" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Blocks</label>
                        <input type="number" name="Blocks" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Tackles</label>
                        <input type="number" name="Tackles" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Gr. Duels</label>
                        <input type="number" name="Ground_Duels" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Gr. Won</label>
                        <input type="number" name="Ground_Duels_Won" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Gr. %</label>
                        <input type="number" step="0.01" name="gDuels_Percentage" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Aer. Duels</label>
                        <input type="number" name="Aerial_Duels" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Aer. Won</label>
                        <input type="number" name="Aerial_Duels_Won" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Aer. %</label>
                        <input type="number" step="0.01" name="Aerial_Duels_Percentage" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Fouls</label>
                        <input type="number" name="Fouls" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Yellow</label>
                        <input type="number" name="Yellow_Cards" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Red</label>
                        <input type="number" name="Red_Cards" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Own Goal</label>
                        <input type="number" name="Own_Goals" class="form-control form-control-sm"></div>
                </div>
            </div>
 
            <!-- Section 6: Goalkeeping & Goals Prevented -->
            <div class="section-box" style="border-top-color: #6f42c1;">
                <span class="section-title" style="color: #6f42c1;">6. Goalkeeping &amp; Goals Prevented</span>
                <div class="row g-2">
                    <div class="col-md-1"><label class="form-label">G. Conced.</label>
                        <input type="number" name="Goals_Conceded" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">xG Conc.</label>
                        <input type="number" step="0.01" name="xG_Threat_Conceded" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Saves</label>
                        <input type="number" name="Saves" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Save %</label>
                        <input type="number" step="0.01" name="Saves_Percentage" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">Pen. Saved</label>
                        <input type="number" name="Penalties_Saved" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">Clear. Off Line</label>
                        <input type="number" name="Clearances_Off_Line" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">Punches</label>
                        <input type="number" name="Punches" class="form-control form-control-sm"></div>
 
                    <div class="col-md-1"><label class="form-label">High Claims</label>
                        <input type="number" name="High_Claims" class="form-control form-control-sm"></div>
 
                    <div class="col-md-2"><label class="form-label">G. Prevented</label>
                        <input type="number" step="0.01" name="Goals_Prevented" class="form-control form-control-sm"></div>
                </div>
            </div>
 
            <!-- Submit -->
            <div class="mb-4">
                <button type="submit" name="add_player" class="btn btn-primary w-100 fw-bold shadow">
                    ΚΑΤΑΧΩΡΗΣΗ ΠΑΙΚΤΗ
                </button>
            </div>
 
        </form>
 
        <!-- ΠΙΝΑΚΑΣ ΠΑΙΚΤΩΝ -->
        <div class="table-container border rounded">
            <table class="table table-hover table-striped table-sm m-0">
                <thead>
                    <tr>
                        <!-- Frozen "Actions" column -->
                        <th class="sticky-col" style="color: white;">Actions</th>
 
                        <?php
                            // Render column headers — freeze ID & Player_Name columns
                            $fields = $result->fetch_fields();
                            foreach ($fields as $field) {
                                $freeze = ($field->name === 'ID' || $field->name === 'Player_Name')
                                    ? 'sticky-col text-white'
                                    : '';
                                echo "<th class='$freeze text-white'>{$field->name}</th>";
                            }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <!-- Delete action -->
                            <td class="sticky-col">
                                <a href="?delete=<?= $row['ID'] ?>"
                                   class="text-danger"
                                   onclick="return confirm('Διαγραφή παίκτη;')">🗑️</a>
                            </td>
 
                            <?php foreach ($row as $col => $value):
                                $freeze = ($col === 'ID' || $col === 'Player_Name')
                                    ? 'sticky-col fw-bold bg-white'
                                    : '';
                            ?>
                                <td class="<?= $freeze ?>"><?= $value ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div><!-- /.table-container -->
 
    </div><!-- /.container-fluid -->
    
    

</body>


 
</html>