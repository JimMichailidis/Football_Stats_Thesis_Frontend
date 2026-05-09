<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Advanced Football Scouting Tool - Full 47 Stats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { background: #f0f2f5; font-size: 0.9rem; }
        .metric-group { background: white; border-radius: 12px; padding: 18px; margin-bottom: 20px; border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .group-title { font-weight: 800; text-transform: uppercase; font-size: 0.8rem; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #eee; display: flex; align-items: center; }
        .group-title i { margin-right: 8px; }
        .form-check { margin-bottom: 8px; }
        .form-check-label { font-size: 0.85rem; color: #444; cursor: pointer; user-select: none; }
        .form-check-input { cursor: pointer; }
        .select2-container--default .select2-selection--single { height: 50px; padding: 10px; border-radius: 8px; border: 1px solid #ced4da; }
        .card-header-custom { background: linear-gradient(135deg, #0d6efd 0%, #004dc7 100%); color: white; border-radius: 12px 12px 0 0 !important; }
    </style>
</head>
<body class="py-5">

<a href="index.php" class="btn btn-dark position-fixed top-0 end-0 m-3" style="z-index:9999;">
    Αρχική
</a>

<div class="container">
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header card-header-custom p-4 text-center">
            <h2 class="mb-0 fw-bold">Player Similarity Analytics</h2>
            <p class="mb-0 opacity-75">Βασισμένο σε Per 90 Stats (Season 24/25) - Πλήρης Βάση 47 Πεδίων</p>
        </div>
        <div class="card-body p-4 p-md-5">
            <form action="results.php" method="POST">
                
                <div class="mb-5 p-4 bg-white border rounded-3">
                    <label class="form-label fw-bold fs-5 text-primary mb-3">1. Επίλεξε Παίκτη-Πρότυπο:</label>
                    <select name="player_name" id="playerSearch" class="form-select" required>
                        <option value=""></option> 
                        <?php
                            $conn = new mysqli("localhost", "root", "", "footballdatabase");
                            if($conn->connect_error) die("Σφάλμα σύνδεσης");
                            $res = $conn->query("SELECT Player_Name FROM epl_player_stats_24_25_per90 ORDER BY Player_Name ASC");
                            while($p = $res->fetch_assoc()) {
                                echo "<option value=\"".htmlspecialchars($p['Player_Name'])."\">".$p['Player_Name']."</option>";
                            }
                            $conn->close();
                        ?>
                    </select>
                </div>

                <div class="metric-group mb-5">
                    <div class="group-title text-primary"><i class="bi bi-filter-circle"></i> 2. Περιορισμός σε Θέσεις (Προαιρετικό)</div>
                    <div class="d-flex flex-wrap gap-3">
                        <?php foreach(['ST','RW','LW','AM','CM','DM','RB','LB','CB','GK'] as $pos): ?>
                            <div class="form-check">
                                <input type="checkbox" name="positions[]" value="<?= $pos ?>" class="form-check-input" id="pos_<?= $pos ?>">
                                <label class="form-check-label fw-bold" for="pos_<?= $pos ?>"><?= $pos ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <label class="form-label fw-bold fs-5 mb-4 text-primary">3. Επίλεξε Στατιστικά για τη Σύγκριση (Κάνε check αυτά που θες):</label>
                
                <div class="row g-4">
                    <div class="col-md-4 col-lg-3">
                        <div class="metric-group h-100" style="border-top:3px solid #fd1414 ">
                            <div class="group-title" style="color: #fd1414;">Goals & Attacking</div>
                            <?php 
                            $att = [
                                'Goals_per90'=>'Goals', 'Assists_per90'=>'Assists', 'xG_per90'=>'xG', 
                                'npxG_per90'=>'Non-Pen xG', 'xAG_per90'=>'xAG', 'Shots_per90'=>'Shots', 
                                'Shots_On_Target_per90'=>'Shots on Target', 'Conversion_percentage'=>'Conversion %', 
                                'Big_Chances_Missed_per90'=>'Big Chances Missed', 'Hit_Woodwork_per90'=>'Hit Woodwork', 
                                'Offsides_per90'=>'Offsides'
                            ];
                            foreach($att as $val => $lbl) echo "<div class='form-check'><input type='checkbox' name='metrics[]' value='$val' class='form-check-input' checked><label class='form-check-label'>$lbl</label></div>";
                            ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <div class="metric-group h-100" style="border-top:3px solid #16a009 ">
                            <div class="group-title" style="color: #16a009;">Passing & Build-up</div>
                            <?php 
                            $pas = [
                                'Passes_per90'=>'Total Passes', 'Successful_Passes_per90'=>'Successful Passes', 
                                'Passes_Percentage'=>'Pass Accuracy %', 'Final_Third_Passes_per90'=>'Final 3rd Passes', 
                                'Successful_Final_Third_Passes_per90'=>'Succ. F3rd Passes', 'Final_Third_Passes_Percentage'=>'F3rd Pass %', 
                                'Through_Balls_per90'=>'Through Balls', 'Crosses_per90'=>'Crosses', 
                                'Successful_Crosses_per90'=>'Succ. Crosses', 'Crosses_Percentage'=>'Cross %', 
                                'Touches_per90'=>'Touches'
                            ];
                            foreach($pas as $val => $lbl) echo "<div class='form-check'><input type='checkbox' name='metrics[]' value='$val' class='form-check-input'><label class='form-check-label'>$lbl</label></div>";
                            ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <div class="metric-group h-100" style="border-top: 3px solid #fd7e14;">
                            <div class="group-title" style="color: #fd7e14;">Carrying & Dribbling</div>
                            <?php 
                            $car = [
                                'Carries_per90'=>'Carries', 'Progressive_Carries_per90'=>'Prog. Carries', 
                                'Carries_Ended_with_Goal_per90'=>'Carr. to Goal', 'Carries_Ended_with_Assist_per90'=>'Carr. to Assist', 
                                'Carries_Ended_with_Shot_per90'=>'Carr. to Shot', 'Carries_Ended_with_Chance_per90'=>'Carr. to Chance', 
                                'Possession_Won_per90'=>'Possession Won', 'Dispossessed_per90'=>'Dispossessed'
                            ];
                            foreach($car as $val => $lbl) echo "<div class='form-check'><input type='checkbox' name='metrics[]' value='$val' class='form-check-input'><label class='form-check-label'>$lbl</label></div>";
                            ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <div class="metric-group h-100" style="border-top: 3px solid #ffc107;">
                            <div class="group-title text-warning">Defensive & Duels</div>
                            <?php 
                            $def = [
                                'Tackles_per90'=>'Tackles', 'Interceptions_per90'=>'Interceptions', 
                                'Blocks_per90'=>'Blocks', 'Clearances_per90'=>'Clearances', 
                                'Ground_Duels_per90'=>'Ground Duels', 'Ground_Duels_Won_per90'=>'Gr. Duels Won', 
                                'Aerial_Duels_per90'=>'Aerial Duels', 'Aerial_Duels_Won_per90'=>'Aer. Duels Won', 
                                'Clean_Sheets_per90'=>'Clean Sheets'
                            ];
                            foreach($def as $val => $lbl) echo "<div class='form-check'><input type='checkbox' name='metrics[]' value='$val' class='form-check-input'><label class='form-check-label'>$lbl</label></div>";
                            ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-3 mx-auto">
                        <div class="metric-group h-100" style="border-top: 3px solid #6f42c1;">
                            <div class="group-title" style="color: #6f42c1;">Goalkeeping (GK Only)</div>
                            <?php 
                            $gk = [
                                'Saves_per90'=>'Saves', 'Goals_Conceded_per90'=>'Goals Conceded', 
                                'Penalties_Saved_per90'=>'Penalties Saved', 'Punches_per90'=>'Punches', 
                                'High_Claims_per90'=>'High Claims'
                            ];
                            foreach($gk as $val => $lbl) echo "<div class='form-check'><input type='checkbox' name='metrics[]' value='$val' class='form-check-input'><label class='form-check-label'>$lbl</label></div>";
                            ?>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-top text-center">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow fw-bold py-3 fs-5 rounded-pill">
                        <i class="bi bi-search me-2"></i> ΕΚΤΕΛΕΣΗ ΠΡΟΗΓΜΕΝΗΣ ΑΝΑΛΥΣΗΣ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<script>
    $(document).ready(function() {
        $('#playerSearch').select2({ placeholder: "Πληκτρολόγησε όνομα παίκτη...", allowClear: true, width: '100%' });
    });
</script>
</body>
</html>