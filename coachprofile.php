<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'coach') {
    header("Location: login4.html");
    exit();
}

// Connection to the database
$host = "localhost";
$dbname = "legacy_db";
$user = "root";
$pass = "";
$id = $_SESSION['user_id'];
$con = new mysqli($host, $user, $pass, $dbname);

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
        $type = trim($_POST['type']);
        $qualific = trim($_POST['qualific']);
        $pwd = trim($_POST['pwd']);

        // Validate inputs
        if (empty($name)) {
            $_SESSION['error_message'] = "Name is required";
        } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "Valid email is required";
        } elseif (empty($teleNum)) {
            $_SESSION['error_message'] = "Telephone number is required";
        } elseif (empty($address)) {
            $_SESSION['error_message'] = "Address is required";
        } elseif ($age <= 0 || $age > 120) {
            $_SESSION['error_message'] = "Valid age is required";
        } elseif (empty($sport)) {
            $_SESSION['error_message'] = "Sport is required";
        } elseif (empty($type)) {
            $_SESSION['error_message'] = "Type is required";
        } elseif (empty($qualific)) {
            $_SESSION['error_message'] = "Qualification is required";
        } elseif (empty($pwd)) {
            $_SESSION['error_message'] = "Password is required";
        } else {
            $update_sql = "UPDATE tblcoach SET Name = ?, Email = ?, TeleNum = ?, Address = ?, Age = ?, Sport = ?, Type = ?, Qualific = ?, pwd = ? WHERE CID = ?";
            $stmt = $con->prepare($update_sql);
            $stmt->bind_param("ssssisssss", $name, $email, $teleNum, $address, $age, $sport, $type, $qualific, $pwd, $id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Profile updated successfully!";
                // Refresh the data
                $res_c = $con->query("SELECT * FROM tblcoach WHERE CID = '$id'");
                if ($res_c && $res_c->num_rows > 0) {
                    $coach_data = $res_c->fetch_assoc();
                    // Update variables
                    $cid = $coach_data['CID'] ?? '';
                    $teleNum = $coach_data['TeleNum'] ?? '';
                    $address = $coach_data['Address'] ?? '';
                    $email = $coach_data['Email'] ?? '';
                    $age = $coach_data['Age'] ?? '';
                    $sport = $coach_data['Sport'] ?? '';
                    $name = $coach_data['Name'] ?? '';
                    $type = $coach_data['Type'] ?? '';
                    $qualific = $coach_data['Qualific'] ?? '';
                    $pwd = $coach_data['pwd'] ?? '';
                    $status = $coach_data['Status'] ?? '';
                }
            } else {
                $_SESSION['error_message'] = "Error updating profile: " . $con->error;
            }
            $stmt->close();
        }

        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

$res_c = mysqli_query($con, "SELECT * FROM tblcoach WHERE CID = '$id'");

// Check if query was successful and if a row was returned
if ($res_c && mysqli_num_rows($res_c) > 0) {
    $coach_data = mysqli_fetch_assoc($res_c);

    // Assign each column value to its corresponding variable
    $cid = $coach_data['CID'] ?? '';
    $teleNum = $coach_data['TeleNum'] ?? '';
    $address = $coach_data['Address'] ?? '';
    $email = $coach_data['Email'] ?? '';
    $age = $coach_data['Age'] ?? '';
    $sport = $coach_data['Sport'] ?? '';
    $name = $coach_data['Name'] ?? '';
    $type = $coach_data['Type'] ?? '';
    $qualific = $coach_data['Qualific'] ?? '';
    $pwd = $coach_data['pwd'] ?? '';
    $status = $coach_data['Status'] ?? '';
} else {
    // Handle case when no coach is found
    $cid = $teleNum = $address = $email = $age = $sport = $name = $type = $qualific = $pwd = $status = '';
    $_SESSION['error_message'] = "Coach not found in database";
}

$p_data = [];
$rp = mysqli_query($con, "SELECT * FROM tblplayer where CID='$id'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Profile | Legacy Sports</title>
    <style>
        /* CSS Styles with Pastel Brown Shades */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f9f5f0;
            color: #6b5b4d;
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar/Navigation */
        .sidebar {
            width: 250px;
            background-color: #d8c8b8;
            color: #5a4a42;
            padding: 20px 0;
            height: 100vh;
            position: sticky;
            top: 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
        }

        .logo {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid #c8b8a8;
            margin-bottom: 20px;
        }

        .logo h1 {
            font-size: 24px;
            font-weight: bold;
            color: #5a4a42;
            letter-spacing: 1px;
        }

        .nav-menu {
            list-style: none;
            padding: 0 20px;
        }

        .nav-menu li {
            margin-bottom: 12px;
        }

        .nav-menu a {
            color: #5a4a42;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background-color: #c8b8a8;
            color: #fff;
        }

        .nav-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 25px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background-color: #d8c8b8;
            color: #5a4a42;
            padding: 18px 0;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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
            background: #fffcf9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            padding: 25px;
            border: 1px solid #e0d5cc;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0d5cc;
            padding-bottom: 15px;
        }

        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #c8b8a8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 36px;
            margin-right: 20px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
        }

        .profile-info h2 {
            color: #6b5b4d;
            margin-bottom: 5px;
        }

        .profile-info p {
            color: #8a7b6d;
            font-style: italic;
        }

        .profile-actions {
            margin-top: 15px;
        }

        .edit-profile-btn {
            background-color: #c8b8a8;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .edit-profile-btn:hover {
            background-color: #b8a898;
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
            color: #8a7b6d;
            font-weight: 500;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .detail-item p {
            color: #6b5b4d;
            font-weight: 600;
        }

        /* Form Styles */
        .update-form {
            margin-top: 15px;
            padding: 20px;
            background-color: #f9f5f0;
            border-radius: 8px;
            border: 1px solid #e0d5cc;
            display: none;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: #6b5b4d;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #c8b8a8;
            background-color: #fff;
            color: #5a4a42;
            font-size: 14px;
        }

        .form-input:focus {
            outline: none;
            border-color: #b8a898;
            box-shadow: 0 0 5px rgba(184, 168, 152, 0.3);
        }

        .form-select {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #c8b8a8;
            background-color: #fff;
            color: #5a4a42;
        }

        .btn {
            background-color: #c8b8a8;
            color: #fff;
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
            background-color: #b8a898;
        }

        .btn-secondary {
            background-color: #8a7b6d;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #7a6b5d;
        }

        .btn i {
            font-size: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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

        .players-section {
            background: #fffcf9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            padding: 25px;
            overflow-x: auto;
            border: 1px solid #e0d5cc;
        }

        .section-title {
            color: #6b5b4d;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0d5cc;
        }

        .players-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .players-table th {
            background-color: #d8c8b8;
            color: #5a4a42;
            text-align: left;
            padding: 12px 15px;
            font-weight: 500;
        }

        .players-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0d5cc;
        }

        .players-table tr:nth-child(even) {
            background-color: #f9f5f0;
        }

        .players-table tr:hover {
            background-color: #f0e6dc;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }

        .status-active {
            background-color: #a3c4a5;
        }

        .status-inactive {
            background-color: #d4a59a;
        }

        .player-link {
            color: #8a7b6d;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .player-link:hover {
            color: #6b5b4d;
            text-decoration: underline;
        }

        .no-players {
            text-align: center;
            color: #8a7b6d;
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

            .players-table {
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
            <li><a href="coachprofile.html" class="active"> Profile</a></li>
            <li><a href="Viewplayer.php"> Players</a></li>
            <li><a href="Edit trainee.html">Edit Trainee Evaluation</a></li>
            <li><a href="Coach_Schedule.php">Schedule</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <header>
                <h1>Coach Profile</h1>
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
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($name); ?></h2>
                            <p><?php echo htmlspecialchars($sport); ?> Coach</p>
                            <div class="profile-actions">
                                <button class="edit-profile-btn" onclick="toggleEditForm()" id="editProfileBtn">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Edit Form -->
                    <form method="post" class="update-form" id="profileEditForm">
                        <h3 style="color: #6b5b4d; margin-bottom: 15px; border-bottom: 1px solid #e0d5cc; padding-bottom: 10px;">
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

                        <div class="form-row">
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
                            <div class="form-group">
                                <label for="type" class="form-label">Coach Type *</label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="Individual Session" <?php echo ($type === 'Individual Session') ? 'selected' : ''; ?>>Individual Session</option>
                                    <option value="Group Session" <?php echo ($type === 'Group Session') ? 'selected' : ''; ?>>Group Session</option>>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="qualific" class="form-label">Qualifications *</label>
                            <textarea name="qualific" id="qualific" class="form-input" rows="3" required><?php echo htmlspecialchars($qualific); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="pwd" class="form-label">Password *</label>
                            <input type="text" name="pwd" id="pwd" class="form-input"
                                value="<?php echo htmlspecialchars($pwd); ?>" required>
                        </div>

                        <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #e0d5cc; text-align: right;">
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
                            <h4>Coach ID</h4>
                            <p><?php echo htmlspecialchars($cid); ?></p>
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
                            <h4>Type</h4>
                            <p><?php echo htmlspecialchars($type); ?></p>
                        </div>
                        <div class="detail-item">
                            <h4>Qualifications</h4>
                            <p><?php echo htmlspecialchars($qualific); ?></p>
                        </div>
                        <div class="detail-item">
                            <h4>Password</h4>
                            <p><?php echo htmlspecialchars($pwd); ?></p>
                        </div>
                        <div class="detail-item">
                            <h4>Status</h4>
                            <p><?php echo htmlspecialchars($status); ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="players-section">
                <h2 class="section-title">Assigned Players</h2>

                <table class="players-table">
                    <thead>
                        <tr>
                            <th>Player ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Sport</th>
                            <th>Duration</th>
                            <th>Begin Date</th>
                            <th>Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all rows from the result set
                        $p_data = mysqli_fetch_all($rp, MYSQLI_ASSOC);

                        foreach ($p_data as $player):
                            $pid = $player['PID'];
                            $pc = mysqli_query($con, "SELECT * FROM tblplayer_coach WHERE CID='$id' AND PID='$pid'");

                            // Fetch the first row (if exists) from the player_coach table
                            $pc_data = mysqli_fetch_assoc($pc);
                        ?>
                            <tr data-pid="<?= htmlspecialchars($player['PID']) ?>">
                                <td><?= htmlspecialchars($player['PID'] ?? '') ?></td>
                                <td><?= htmlspecialchars($player['Name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($player['Age'] ?? '') ?></td>
                                <td><?= htmlspecialchars($player['Sport'] ?? '') ?></td>
                                <td><?= htmlspecialchars($pc_data['Duration'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($pc_data['BDate'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($pc_data['Feedback'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($p_data)): ?>
                            <tr>
                                <td colspan="7">No player data found.</td>
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
            const editBtn = document.getElementById('editProfileBtn');

            if (editForm.style.display === 'none' || editForm.style.display === '') {
                // Show edit form, hide profile display
                editForm.style.display = 'block';
                profileDisplay.style.display = 'none';
                editBtn.innerHTML = '<i class="fas fa-times"></i> Cancel Edit';
            } else {
                // Hide edit form, show profile display
                editForm.style.display = 'none';
                profileDisplay.style.display = 'grid';
                editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
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

            // Initialize form state
            document.getElementById('profileEditForm').style.display = 'none';
        });

        // Form validation
        document.getElementById('profileEditForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const teleNum = document.getElementById('teleNum').value.trim();
            const address = document.getElementById('address').value.trim();
            const age = parseInt(document.getElementById('age').value);
            const sport = document.getElementById('sport').value;
            const type = document.getElementById('type').value;
            const qualific = document.getElementById('qualific').value.trim();
            const pwd = document.getElementById('pwd').value.trim();

            let errors = [];

            if (!name) errors.push('Name is required');
            if (!email || !isValidEmail(email)) errors.push('Valid email is required');
            if (!teleNum) errors.push('Contact number is required');
            if (!address) errors.push('Address is required');
            if (!age || age <= 0 || age > 120) errors.push('Valid age is required');
            if (!sport) errors.push('Sport selection is required');
            if (!type) errors.push('Coach type is required');
            if (!qualific) errors.push('Qualifications are required');
            if (!pwd) errors.push('Password is required');

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
    <!-- Font Awesome for the chalkboard-user icon -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>

</html>