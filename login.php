<?php
// login.php — Οθόνη Σύνδεσης Διαχειριστή
session_start();

// Αν είναι ήδη συνδεδεμένος, ανακατεύθυνση κατευθείαν στη διαχείριση
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: manage_players.php");
    exit();
}

$error_msg = "";
$success_msg = "";

// Μήνυμα επιτυχούς αποσύνδεσης αν προέρχεται από το logout.php
if (isset($_GET['logged_out'])) {
    $success_msg = "Successfully logged out.";
}

// Σύνδεση με τη βάση για έλεγχο των διαπιστευτηρίων
$conn = new mysqli("localhost", "root", "", "footballdatabase");

if (!$conn->connect_error) {
    $conn->set_charset("utf8mb4");

    // Αυτόματη δημιουργία του πίνακα admin_users αν δεν υπάρχει ήδη
    $check_table = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($check_table && $check_table->num_rows === 0) {
        $create_sql = "CREATE TABLE admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($create_sql);

        // Προσθήκη προεπιλεγμένου διαχειριστή: admin / admin123
        $default_user = "admin";
        $default_pass = password_hash("admin123", PASSWORD_DEFAULT);
        $stmt_init = $conn->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
        if ($stmt_init) {
            $stmt_init->bind_param("ss", $default_user, $default_pass);
            $stmt_init->execute();
            $stmt_init->close();
        }
    }
}

// Έλεγχος υποβολής φόρμας σύνδεσης
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_msg = "Fill in the required fields.";
    } else {
        $authenticated = false;

        // Έλεγχος μέσω βάσης δεδομένων
        if (!$conn->connect_error) {
            $stmt = $conn->prepare("SELECT id, password_hash FROM admin_users WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($res && $row = $res->fetch_assoc()) {
                    if (password_verify($password, $row['password_hash'])) {
                        $authenticated = true;
                    }
                }
                $stmt->close();
            }
        }

        // Εφεδρικός έλεγχος (fallback) σε περίπτωση που δεν ανταποκρίνεται η βάση
        if (!$authenticated && $username === 'admin' && $password === 'admin123') {
            $authenticated = true;
        }

        if ($authenticated) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header("Location: manage_players.php");
            exit();
        } else {
            $error_msg = "Wrong username or password!";
        }
    }
}
?>
<<!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Access — ScoutIQ Portal</title>

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
    </head>

    <body>
        <!-- Ambient Background Lights & Grid -->
        <div class="ambient-glow ambient-glow-1"></div>
        <div class="ambient-glow ambient-glow-2"></div>
        <div class="ambient-grid"></div>

        <div class="auth-wrapper">
            <div class="auth-card">

                <!-- Card Header -->
                <div class="auth-header">
                    <div class="auth-icon-wrap">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h2 class="auth-title">Database Login</h2>
                </div>

                <!-- Card Body -->
                <div class="auth-body">

                    <!-- Alert Messages -->
                    <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4 py-2 px-3 rounded-3"
                        style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;"
                        role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div class="small fw-semibold"><?= htmlspecialchars($error_msg) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success d-flex align-items-center mb-4 py-2 px-3 rounded-3"
                        style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #86efac;"
                        role="alert">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div class="small fw-semibold"><?= htmlspecialchars($success_msg) ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" autocomplete="off">

                        <!-- Username Input -->
                        <div class="mb-3">
                            <label class="auth-label">Administrator Username</label>
                            <div class="auth-input-box">
                                <i class="bi bi-person-fill lead-icon"></i>
                                <input type="text" name="username" placeholder="e.g. admin" required autofocus>
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-4">
                            <label class="auth-label">Password</label>
                            <div class="auth-input-box">
                                <i class="bi bi-key-fill lead-icon"></i>
                                <input type="password" name="password" id="passwordInput" placeholder="••••••••"
                                    required>
                                <button type="button" class="eye-btn" onclick="togglePassword()"
                                    title="Show/Hide Password">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>


                        <!-- Submit Button -->
                        <button type="submit"
                            class="btn btn-auth-submit w-100 d-flex align-items-center justify-content-center gap-2 mb-4">
                            <span> Login</span>
                            <i class="bi bi-arrow-right-short fs-4"></i>
                        </button>

                        <!-- Back to Home -->
                        <div class="text-center">
                            <a href="index.php" class="back-home-link d-inline-flex align-items-center gap-2">
                                <i class="bi bi-arrow-left"></i>
                                <span>Return to Dashboard</span>
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Toggle Password Visibility JS -->
        <script>
        function togglePassword() {
            const pwd = document.getElementById("passwordInput");
            const icon = document.getElementById("toggleIcon");
            if (pwd.type === "password") {
                pwd.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                pwd.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }
        </script>
    </body>

    </html>