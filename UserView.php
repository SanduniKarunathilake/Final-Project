<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: login4.html");
    exit();
}

// Connection to the database
$host = "localhost";
$dbname = "legacy_db";
$user = "root";
$pass = ""; // or your MySQL password

$con = new mysqli($host, $user, $pass, $dbname);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle status update for specific player
if (isset($_GET['toggle_status']) && isset($_GET['pid'])) {
    $pid = $_GET['pid']; // Get the player ID from the URL

    // Get current status for THIS player
    $stmt = $con->prepare("SELECT Status FROM tblplayer WHERE PID = ?");
    $stmt->bind_param("s", $pid); // Changed from "i" to "s" since PID is string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $newStatus = ($row['Status'] == 'Active') ? 'Inactive' : 'Active';

        // Update only THIS player
        $updateStmt = $con->prepare("UPDATE tblplayer SET Status = ? WHERE PID = ?");
        $updateStmt->bind_param("ss", $newStatus, $pid); // Changed from "si" to "ss" since PID is string
        $updateStmt->execute();
        $updateStmt->close();
    }
    $stmt->close();
    header("Location: UserView.php"); // Refresh to show changes
    exit();
}



// Fetch coach data
$p_data = [];
$t_data = [];
$pay_data = [];

$rc = mysqli_query($con, "SELECT * FROM tblcoach");
$rp = mysqli_query($con, "SELECT * FROM tblplayer");
$rt = mysqli_query($con, "SELECT * FROM tbltournament");
$rpay = mysqli_query($con, "SELECT * FROM tblpayment");

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Legacy Sports</title>
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
            /* Warm pastel cream */
            color: #6b5b4d;
            /* Soft brown text */
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar/Navigation */
        .sidebar {
            width: 250px;
            background-color: #d8c8b8;
            /* Light pastel brown */
            color: #5a4a42;
            /* Darker pastel brown */
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

        .document-link {
            color: #1a73e8;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }

        .document-link:hover {
            color: #0d5bbc;
            text-decoration: underline;
        }

        .document-link i {
            font-size: 14px;
        }

        .no-document {
            color: #666;
            font-style: italic;
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

        .section {
            background: #fffcf9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #e0d5cc;
        }

        .section-title {
            color: #6b5b4d;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0d5cc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .add-btn {
            background-color: #c8b8a8;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .add-btn:hover {
            background-color: #b3a293;
            transform: translateY(-1px);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table th {
            background-color: #d8c8b8;
            color: #5a4a42;
            text-align: left;
            padding: 12px 15px;
            font-weight: 500;
        }

        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0d5cc;
        }

        .data-table tr:nth-child(even) {
            background-color: #f9f5f0;
        }

        .data-table tr:hover {
            background-color: #f0e6dc;
        }

        .action-link {
            color: #8a7b6d;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 10px;
        }

        .action-link:hover {
            color: #6b5b4d;
            text-decoration: underline;
        }

        .no-data {
            text-align: center;
            color: #8a7b6d;
            padding: 20px;
            font-style: italic;
        }

        /* Status Toggle Button Styles */
        .status-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-active {
            background-color: #d4a59a;
            color: white;
        }

        .btn-inactive {
            background-color: #a3c4a5;
            color: white;
        }

        .status-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
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

            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
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
            <li><a href="AdminDash.php">Dashboard</a></li>
            <li><a href="tournement.php">Tournament Details</a></li>
            <li><a href="UserView.php" class="active">User Overview</a></li>
            <li><a href="AdminSet1.php">Admin setting</a></li>
            <li><a href="donor_management.php">Manage Donations & Sponsors</a></li>
            <li><a href="index.php">Home</a></li>
            <li><a href="logout.php"> LogOUT</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <header>
                <h1>Admin Dashboard</h1>
            </header>

            <!-- Players Table Section -->
            <section id="players" class="section">
                <h2 class="section-title">
                    <span>Player Management</span>
                </h2>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Player ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Sport</th>
                            <th>Type</th>
                            <th>GN Certificate</th>
                            <th>Medical Record</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $p_data = mysqli_fetch_all($rp, MYSQLI_ASSOC);

                        foreach ($p_data as $player): ?>
                            <tr>
                                <td><?= htmlspecialchars($player['PID'] ?? '') ?></td>
                                <td><?= htmlspecialchars($player['Name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($player['Age'] ?? '') ?></td>
                                <td><?= htmlspecialchars($player['Sport'] ?? '') ?></td>
                                <td><?= htmlspecialchars($player['Type'] ?? '') ?></td>

                                <td>
                                    <?php if (!empty($player['GNCertifi'])): ?>
                                        <a href="<?= htmlspecialchars($player['GNCertifi']) ?>" target="_blank" class="document-link">
                                            <i class="fas fa-file-alt"></i> View GN Certificate
                                        </a>
                                    <?php else: ?>
                                        <span class="no-document">No certificate uploaded</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($player['MedReco'])): ?>
                                        <a href="<?= htmlspecialchars($player['MedReco']) ?>" target="_blank" class="document-link">
                                            <i class="fas fa-file-alt"></i> View Medical Record
                                        </a>
                                    <?php else: ?>
                                        <span class="no-document">No record uploaded</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="UserView.php?toggle_status=1&pid=<?= htmlspecialchars($player['PID']) ?>"
                                        class="status-btn <?= $player['Status'] === 'Active' ? 'btn-active' : 'btn-inactive' ?>">
                                        <?= $player['Status'] === 'Active' ? 'Deactivate' : 'Activate' ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($p_data)): ?>
                            <tr>
                                <td colspan="8">No player data found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <!-- Payment Table Section -->
            <section id="payments" class="section">
                <h2 class="section-title">
                    <span>Payment Management</span>
                </h2>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Player ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pay_data = mysqli_fetch_all($rpay, MYSQLI_ASSOC);
                        if (!empty($pay_data)) {
                            foreach ($pay_data as $payment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($payment['PayID'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($payment['Amount'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($payment['Date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($payment['PID'] ?? '') ?></td>
                                </tr>
                            <?php endforeach;
                        } else { ?>
                            <tr>
                                <td colspan="4" class="no-data">No payment records found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>

            <!-- Coaches Table Section -->
            <section id="coaches" class="section">
                <h2 class="section-title">
                    <span>Coach Management</span>
                </h2>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Coach ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Sport</th>
                            <th>Session Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $c_data = mysqli_fetch_all($rc, MYSQLI_ASSOC);
                        // Check if coach data exists
                        if (!empty($c_data)) {
                            foreach ($c_data as $coach): ?>
                                <tr>
                                    <td><?= htmlspecialchars($coach['CID'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($coach['Name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($coach['Age'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($coach['Sport'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($coach['Type'] ?? '') ?></td>
                                    <td>
                                        <?= htmlspecialchars($coach['Status'] ?? '') ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                        } else { ?>
                            <tr>
                                <td colspan="6" class="no-data">No coach data found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>

            <!-- Tournaments Table Section -->
            <section id="tournaments" class="section">
                <h2 class="section-title">
                    <span>Tournament Management</span>
                </h2>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tournament ID</th>
                            <th>Name</th>
                            <th>Sport</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $t_data = mysqli_fetch_all($rt, MYSQLI_ASSOC);
                        // Check if coach data exists
                        if (!empty($t_data)) {
                            foreach ($t_data as $tlmnt): ?>
                                <tr>
                                    <td><?= htmlspecialchars($tlmnt['TID'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($tlmnt['Tname'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($tlmnt['Sport'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($tlmnt['Description'] ?? '') ?></td>
                                </tr>
                            <?php endforeach;
                        } else { ?>
                            <tr>
                                <td colspan="4" class="no-data">No Tournament data found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</body>

</html>