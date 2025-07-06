<?php
session_start();

// Check if player is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'player') {
    header("Location: login4.html");
    exit();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'legacy_db');

// File upload configuration
define('MED_RECORDS_DIR', 'Medical_Records/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_MEDIA_TYPES', ['application/pdf', 'image/jpeg', 'image/png']);

// Create database connection
$con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($con->connect_error) {
    die("Database connection failed: " . $con->connect_error);
}

// Create directories if they don't exist
if (!file_exists(MED_RECORDS_DIR)) {
    if (!mkdir(MED_RECORDS_DIR, 0755, true)) {
        die("Failed to create directory for medical records");
    }
}

// Initialize variables
$id = $_SESSION['user_id'];
$errors = [];
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile information update
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $teleNum = trim($_POST['teleNum']);
        $address = trim($_POST['address']);
        $age = intval($_POST['age']);
        $sport = trim($_POST['sport']);

        // Validate inputs
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }
        if (empty($teleNum)) {
            $errors[] = "Telephone number is required";
        }
        if (empty($address)) {
            $errors[] = "Address is required";
        }
        if ($age <= 0 || $age > 120) {
            $errors[] = "Valid age is required";
        }
        if (empty($sport)) {
            $errors[] = "Sport is required";
        }

        if (empty($errors)) {
            $update_sql = "UPDATE tblplayer SET Name = ?, Email = ?, TeleNum = ?, Address = ?, Age = ?, Sport = ? WHERE PID = ?";
            $stmt = $con->prepare($update_sql);
            $stmt->bind_param("ssssiss", $name, $email, $teleNum, $address, $age, $sport, $id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Profile updated successfully!";
            } else {
                $_SESSION['error_message'] = "Error updating profile: " . $con->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = implode("<br>", $errors);
        }
    }

    // Handle blood type update
    if (isset($_POST['update_blood_type'])) {
        $new_blood_type = $_POST['blood_type'];

        $update_sql = "UPDATE tblplayer SET blood_type = ? WHERE PID = ?";
        $stmt = $con->prepare($update_sql);
        $stmt->bind_param("ss", $new_blood_type, $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Blood type updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating blood type: " . $con->error;
        }

        $stmt->close();
    }

    // Handle medical record upload
    if (isset($_POST['upload_med_record']) && isset($_FILES['med_record_file'])) {
        $file = $_FILES['med_record_file'];

        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error: " . $file['error'];
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = "File size exceeds maximum allowed size (5MB)";
        } elseif (!in_array($file['type'], ALLOWED_MEDIA_TYPES)) {
            $errors[] = "Only PDF, JPEG, and PNG files are allowed";
        } else {
            // Generate unique filename
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $id . '_MedRec_' . time() . '.' . $fileExtension;
            $targetPath = MED_RECORDS_DIR . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Update database with new file path
                $update_sql = "UPDATE tblplayer SET MedReco = ? WHERE PID = ?";
                $stmt = $con->prepare($update_sql);
                $stmt->bind_param("ss", $targetPath, $id);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Medical record uploaded successfully!";
                } else {
                    // Delete the uploaded file if DB update fails
                    unlink($targetPath);
                    $_SESSION['error_message'] = "Error updating medical record: " . $con->error;
                }

                $stmt->close();
            } else {
                $errors[] = "Error uploading file";
            }
        }

        if (!empty($errors)) {
            $_SESSION['error_message'] = implode("<br>", $errors);
        }
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch player data
$res_p = $con->query("SELECT * FROM tblplayer WHERE PID = '$id'");

if ($res_p && $res_p->num_rows > 0) {
    $player_data = $res_p->fetch_assoc();

    // Assign player data to variables
    $pid = $player_data['PID'] ?? '';
    $teleNum = $player_data['TeleNum'] ?? '';
    $address = $player_data['Address'] ?? '';
    $email = $player_data['Email'] ?? '';
    $age = $player_data['Age'] ?? '';
    $sport = $player_data['Sport'] ?? '';
    $name = $player_data['Name'] ?? '';
    $medReco = $player_data['MedReco'] ?? '';
    $GN = $player_data['GNCertifi'] ?? '';
    $bt = $player_data['blood_type'] ?? '';
} else {
    // Handle case when no player is found
    $pid = $teleNum = $address = $email = $age = $sport = $name = $medReco = $GN = $bt = '';
    $_SESSION['error_message'] = "Player not found in database";
}

// Fetch tournament data
$t_data = [];
$rt = $con->query("
    SELECT 
        t.Sport, 
        t.Tname, 
        t.link, 
        p.TID, 
        p.Details 
    FROM 
        tblplayer_tournmt p
    JOIN 
        tbltournament t ON p.TID = t.TID
    WHERE
        p.PID = '$id'
");

if ($rt) {
    $t_data = $rt->fetch_all(MYSQLI_ASSOC);
}
// Fetch latest donation data
$donation_data = [];
$donation_query = $con->query("
    SELECT Amount, Date 
    FROM tbldonation 
    WHERE PID = '$id' 
    AND Date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ORDER BY Date DESC 
    LIMIT 1
");

if ($donation_query && $donation_query->num_rows > 0) {
    $donation_data = $donation_query->fetch_assoc();
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile | Legacy Sports</title>
    <style>
        /* CSS Styles with Rich Brown Shades */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .donation-notification {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            margin-top: 5px;
            font-size: 14px;
            display: inline-block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        body {
            background-color: #f5f0e6;
            color: #3e2723;
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar/Navigation */
        .sidebar {
            width: 250px;
            background-color: #5d4037;
            color: #efebe9;
            padding: 20px 0;
            height: 100vh;
            position: sticky;
            top: 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }

        .logo {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid #8d6e63;
            margin-bottom: 20px;
        }

        .logo h1 {
            font-size: 24px;
            font-weight: bold;
            color: #efebe9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-menu {
            list-style: none;
            padding: 0 20px;
        }

        .nav-menu li {
            margin-bottom: 15px;
        }

        .nav-menu a {
            color: #efebe9;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background-color: #8d6e63;
            transform: translateX(5px);
        }

        .nav-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background-color: #6d4c41;
            color: #efebe9;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-section {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 40px;
        }

        .profile-card {
            flex: 1;
            min-width: 300px;
            background: #efebe9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 25px;
            border: 1px solid #d7ccc8;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #d7ccc8;
            padding-bottom: 15px;
        }

        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #8d6e63;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #efebe9;
            font-size: 36px;
            margin-right: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .profile-info h2 {
            color: #4e342e;
            margin-bottom: 5px;
        }

        .profile-info p {
            color: #8d6e63;
            font-style: italic;
        }

        .profile-actions {
            margin-top: 15px;
        }

        .edit-profile-btn {
            background-color: #8d6e63;
            color: #efebe9;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            margin-right: 10px;
        }

        .edit-profile-btn:hover {
            background-color: #6d4c41;
            transform: translateY(-2px);
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .detail-item {
            margin-bottom: 10px;
        }

        .detail-item h4 {
            color: #8d6e63;
            font-weight: 500;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .detail-item p {
            color: #4e342e;
            font-weight: 600;
        }

        /* Form Styles */
        .update-form {
            margin-top: 15px;
            padding: 20px;
            background-color: #f5f0e6;
            border-radius: 8px;
            border: 1px solid #d7ccc8;
            display: none;
        }

        .update-form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: #5d4037;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #8d6e63;
            background-color: #fff;
            color: #3e2723;
            font-size: 14px;
        }

        .form-input:focus {
            outline: none;
            border-color: #6d4c41;
            box-shadow: 0 0 5px rgba(109, 76, 65, 0.3);
        }

        .form-select {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #8d6e63;
            background-color: #fff;
            color: #3e2723;
        }

        .form-file {
            width: 100%;
            padding: 5px 0;
        }

        .btn {
            background-color: #8d6e63;
            color: #efebe9;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-right: 10px;
        }

        .btn:hover {
            background-color: #6d4c41;
        }

        .btn-secondary {
            background-color: #8d6e63;
            color: #efebe9;
        }

        .btn-secondary:hover {
            background-color: #a1887f;
        }

        .btn i {
            font-size: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* Document preview styles */
        .document-preview {
            margin-top: 10px;
        }

        .document-link {
            color: #6d4c41;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

        .document-link:hover {
            text-decoration: underline;
        }

        /* Message styles */
        .message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Tournaments section */
        .tournaments-section {
            background: #efebe9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 25px;
            overflow-x: auto;
            border: 1px solid #d7ccc8;
        }

        .section-title {
            color: #4e342e;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #d7ccc8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .add-tournament-btn {
            background-color: #8d6e63;
            color: #efebe9;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .add-tournament-btn:hover {
            background-color: #6d4c41;
            transform: translateY(-2px);
        }

        .tournaments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .tournaments-table th {
            background-color: #6d4c41;
            color: #efebe9;
            text-align: left;
            padding: 12px 15px;
            font-weight: 500;
        }

        .tournaments-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #d7ccc8;
        }

        .tournaments-table tr:nth-child(even) {
            background-color: #f5f0e6;
        }

        .tournaments-table tr:hover {
            background-color: #e0d5ce;
        }

        .sport-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-tennis {
            background-color: #a1887f;
        }

        .badge-swimming {
            background-color: #8d6e63;
        }

        .badge-chess {
            background-color: #6d4c41;
        }

        .badge-badminton {
            background-color: #5d4037;
        }

        .badge-rugby {
            background-color: #4e342e;
        }

        .badge-default {
            background-color: #3e2723;
        }

        .tournament-link {
            color: #6d4c41;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tournament-link:hover {
            color: #3e2723;
            text-decoration: underline;
        }

        .no-tournaments {
            text-align: center;
            color: #8d6e63;
            padding: 20px;
            font-style: italic;
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .profile-card {
                min-width: 100%;
            }

            .tournaments-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <!-- Sidebar/Navigation -->
    <div class="sidebar">
        <div class="logo">
            <h1>LEGACY</h1>
        </div>
        <ul class="nav-menu">
            <li><a href="submit_schedule.php">Back</a></li>
            <li><a href="logout.php"> LogOUT</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <header>
                <h1>Player Profile</h1>
            </header>

            <!-- Display success/error messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="message success">
                    <?php echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="message error">
                    <?php echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <section class="profile-section">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-pic">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($name); ?></h2>
                            <p><?php echo htmlspecialchars($sport); ?> Player</p>
                            <?php if (!empty($donation_data)): ?>
                                <div class="donation-notification">
                                    You have <?php echo htmlspecialchars($donation_data['Amount']); ?> $ Donation
                                </div>
                            <?php endif; ?>
                            <div class="profile-actions">
                                <button class="edit-profile-btn" onclick="toggleEditForm()" id="editProfileBtn">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Edit Form -->
                    <!-- Profile Edit Form -->
                    <form method="post" class="update-form" id="profileEditForm">
                        <h3 style="color: #4e342e; margin-bottom: 20px; border-bottom: 1px solid #d7ccc8; padding-bottom: 10px;">
                            <i class="fas fa-user-edit"></i> Edit Profile Information
                        </h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" name="name" id="name" class="form-input"
                                    value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" name="email" id="email" class="form-input"
                                    value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="teleNum" class="form-label">Contact Number *</label>
                                <input type="tel" name="teleNum" id="teleNum" class="form-input"
                                    value="<?php echo htmlspecialchars($teleNum); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="age" class="form-label">Age *</label>
                                <input type="number" name="age" id="age" class="form-input" min="1" max="120"
                                    value="<?php echo htmlspecialchars($age); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address" class="form-label">Address *</label>
                            <textarea name="address" id="address" class="form-input" rows="3" required><?php echo htmlspecialchars($address); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="sport" class="form-label">Sport *</label>
                            <select name="sport" id="sport" class="form-select" required>
                                <option value="">Select Sport</option>
                                <option value="Tennis" <?php echo ($sport === 'Tennis') ? 'selected' : ''; ?>>Tennis</option>
                                <option value="Swimming" <?php echo ($sport === 'Swimming') ? 'selected' : ''; ?>>Swimming</option>
                                <option value="Chess" <?php echo ($sport === 'Chess') ? 'selected' : ''; ?>>Chess</option>
                                <option value="Badminton" <?php echo ($sport === 'Badminton') ? 'selected' : ''; ?>>Badminton</option>
                                <option value="Rugby" <?php echo ($sport === 'Rugby') ? 'selected' : ''; ?>>Rugby</option>
                                <option value="Basketball" <?php echo ($sport === 'Basketball') ? 'selected' : ''; ?>>Basketball</option>
                                <option value="Soccer" <?php echo ($sport === 'Soccer') ? 'selected' : ''; ?>>Soccer</option>
                                <option value="Volleyball" <?php echo ($sport === 'Volleyball') ? 'selected' : ''; ?>>Volleyball</option>
                                <option value="Cricket" <?php echo ($sport === 'Cricket') ? 'selected' : ''; ?>>Cricket</option>
                                <option value="Other" <?php echo ($sport === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #d7ccc8;">
                            <button type="submit" name="update_profile" class="btn">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleEditForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>

                    <!-- Profile Display -->
                    <div class="details-grid" id="profileDisplay">
                        <div class="detail-item">
                            <h4>Player ID</h4>
                            <p><?php echo htmlspecialchars($pid); ?></p>
                        </div>
                        <div class="detail-item">
                            <h4>Name</h4>
                            <p><?php echo htmlspecialchars($name); ?></p>
                        </div>
                        <div class="detail-item">
                            <h4>Age</h4>
                            <p><?php echo htmlspecialchars($age); ?></p>
                        </div>
                        <div class="detail-item">
                            <h4>Email</h4>
                            <p><?php echo htmlspecialchars($email); ?></p>
                        </div>
                        <div class="detail-item">
                            <h4>Contact</h4>
                            <p><?php echo htmlspecialchars($teleNum); ?></p>
                        </div>
                        <div class="detail-item">
                            <h4>Address</h4>
                            <p><?php echo htmlspecialchars($address); ?></p>
                        </div>
                        <div class="detail-item">
                            <h4>Sport</h4>
                            <p><?php echo htmlspecialchars($sport); ?></p>
                        </div>
                        <div class="detail-item">
                            <h4>Blood Type</h4>
                            <p><?php echo htmlspecialchars($bt); ?></p>
                            <form method="post" class="update-form" style="display: block; margin-top: 10px; padding: 10px;">
                                <div class="form-group">
                                    <select name="blood_type" class="form-select" required>
                                        <option value="">Select Blood Type</option>
                                        <option value="A+" <?php echo ($bt === 'A+') ? 'selected' : ''; ?>>A+</option>
                                        <option value="A-" <?php echo ($bt === 'A-') ? 'selected' : ''; ?>>A-</option>
                                        <option value="B+" <?php echo ($bt === 'B+') ? 'selected' : ''; ?>>B+</option>
                                        <option value="B-" <?php echo ($bt === 'B-') ? 'selected' : ''; ?>>B-</option>
                                        <option value="AB+" <?php echo ($bt === 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                        <option value="AB-" <?php echo ($bt === 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                        <option value="O+" <?php echo ($bt === 'O+') ? 'selected' : ''; ?>>O+</option>
                                        <option value="O-" <?php echo ($bt === 'O-') ? 'selected' : ''; ?>>O-</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_blood_type" class="btn">
                                    <i class="fas fa-save"></i> Update Blood Type
                                </button>
                            </form>
                        </div>
                        <div class="detail-item">
                            <h4>Medical Records</h4>
                            <?php if (!empty($medReco)): ?>
                                <div class="document-preview">
                                    <p>Current document:</p>
                                    <a href="<?php echo htmlspecialchars($medReco); ?>" target="_blank" class="document-link">
                                        <i class="fas fa-file-alt"></i> View Medical Record
                                    </a>
                                </div>
                            <?php else: ?>
                                <p>No medical record uploaded</p>
                            <?php endif; ?>
                            <form method="post" enctype="multipart/form-data" class="update-form" style="display: block; margin-top: 10px; padding: 10px;">
                                <div class="form-group">
                                    <label for="med_record_file" class="form-label">Upload New Medical Record (PDF or Image, max 5MB):</label>
                                    <input type="file" name="med_record_file" id="med_record_file" class="form-file" accept=".pdf,.jpg,.jpeg,.png" required>
                                </div>
                                <button type="submit" name="upload_med_record" class="btn">
                                    <i class="fas fa-upload"></i> Upload Medical Record
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section class="tournaments-section">
                <h2 class="section-title">
                    <span>Registered Tournaments</span>
                    <a href="addtourForm.php?pid=<?php echo htmlspecialchars($id); ?>" class="add-tournament-btn">
                        <i class="fas fa-plus"></i> Add Tournament
                    </a>
                </h2>

                <table class="tournaments-table">
                    <thead>
                        <tr>
                            <th>Tournament ID</th>
                            <th>Name</th>
                            <th>Sport</th>
                            <th>Description</th>
                            <th>Registration Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($t_data)): ?>
                            <?php foreach ($t_data as $tp): ?>
                                <tr data-tid="<?= htmlspecialchars($tp['TID']) ?>">
                                    <td><?= htmlspecialchars($tp['TID']) ?></td>
                                    <td><?= htmlspecialchars($tp['Tname']) ?></td>
                                    <td>
                                        <span class="sport-badge badge-<?= strtolower($tp['Sport']) ?>">
                                            <?= htmlspecialchars($tp['Sport']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($tp['Details']) ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($tp['link']) ?>" target="_blank" class="tournament-link">
                                            <i class="fas fa-external-link-alt"></i> Register
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="no-tournaments">You haven't registered for any tournaments yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <script>
        function toggleEditForm() {
            const editForm = document.getElementById('profileEditForm');
            const profileDisplay = document.getElementById('profileDisplay');
            const editBtn = document.querySelector('.edit-profile-btn');

            if (editForm.classList.contains('active')) {
                // Hide edit form, show profile display
                editForm.classList.remove('active');
                profileDisplay.style.display = 'grid';
                editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
            } else {
                // Show edit form, hide profile display
                editForm.classList.add('active');
                profileDisplay.style.display = 'none';
                editBtn.innerHTML = '<i class="fas fa-times"></i> Cancel Edit';
            }
        }

        // Auto-hide messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s ease-out';
                    setTimeout(function() {
                        message.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });

        // Form validation
        document.getElementById('profileEditForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const teleNum = document.getElementById('teleNum').value.trim();
            const address = document.getElementById('address').value.trim();
            const age = parseInt(document.getElementById('age').value);
            const sport = document.getElementById('sport').value;

            let errors = [];

            if (!name) errors.push('Name is required');
            if (!email || !isValidEmail(email)) errors.push('Valid email is required');
            if (!teleNum) errors.push('Contact number is required');
            if (!address) errors.push('Address is required');
            if (!age || age <= 0 || age > 120) errors.push('Valid age is required');
            if (!sport) errors.push('Sport selection is required');

            if (errors.length > 0) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errors.join('\n'));
            }
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    </script>
</body>

</html>