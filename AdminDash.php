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

// Initialize counts
$admin_count = $player_count = $coach_count = $donor_count = 0;


if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Count queries
$admin_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(AID) AS total FROM tbladmin"))['total'];
$player_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(PID) AS total FROM tblplayer"))['total'];
$coach_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(CID) AS total FROM tblcoach"))['total'];
$donor_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(SDID) AS total FROM tbl_sponsor_donor"))['total'];

// Fetch coach data
$coach_data = [];
$result = mysqli_query($con, "SELECT CID, Name, Status, Sport FROM tblcoach");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $coach_data[] = $row;
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Coach Dashboard</title>
    <link rel="stylesheet" href="AdminDash.css">
    <style>
        .add {
            color: green;
            cursor: pointer;
            text-decoration: underline;
        }

        .remove {
            color: red;
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <h2>LEGACY</h2>
        </div>
        <ul>
            <li><a href="AdminDash.php" class="active">Dashboard</a></li>
            <li><a href="tournement.php">Tournament Details</a></li>

            <li><a href="UserView.php">User Overview</a></li>
            <li><a href="AdminSet1.php">Admin setting</a></li>
            <li><a href="donor_management.php">Manage Donations & Sponsors</a></li>
            <li><a href="index.php">Home</a></li>

            <li><a href="logout.php"> LogOUT</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="dashboard">
            <h1>Dashboard</h1>
            <div class="stats">
                <div class="stat">
                    <h3><?= $player_count ?></h3>
                    <p>Players</p>
                </div>
                <div class="stat">
                    <h3><?= $coach_count ?></h3>
                    <p>Coaches</p>
                </div>
                <div class="stat">
                    <h3><?= $donor_count ?></h3>
                    <p>Donors & Sponsors</p>
                </div>
                <div class="stat">
                    <h3><?= $admin_count ?></h3>
                    <p>Admins</p>
                </div>
            </div>

            <h2>Coach List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Sport</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coach_data as $coach): ?>
                        <tr data-cid="<?= $coach['CID'] ?>">
                            <td><?= htmlspecialchars($coach['Name']) ?></td>
                            <td>Coach</td>
                            <td class="status"><?= htmlspecialchars($coach['Status']) ?></td>
                            <td><?= htmlspecialchars($coach['Sport']) ?></td>
                            <td>
                                <span class="action <?= $coach['Status'] === 'Active' ? 'remove' : 'add' ?>">
                                    <?= $coach['Status'] === 'Active' ? 'Remove' : 'Add' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($coach_data)): ?>
                        <tr>
                            <td colspan="5">No coach data found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".action").forEach(button => {
                button.addEventListener("click", function() {
                    const row = this.closest("tr");
                    const cid = row.getAttribute("data-cid");
                    const currentStatus = row.querySelector(".status").innerText;

                    fetch("ActionAdDash.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                cid: cid,
                                status: currentStatus
                            })
                        })


                        .then(response => response.text())
                        .then(newStatus => {
                            row.querySelector(".status").innerText = newStatus;
                            this.innerText = newStatus === "Active" ? "Remove" : "Add";
                            this.className = `action ${newStatus === "Active" ? 'remove' : 'add'}`;
                        })
                        .catch(err => console.error("AJAX error:", err));
                });
            });
        });
    </script>
</body>

</html>