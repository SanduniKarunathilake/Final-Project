<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login4.html");
    exit();
}

// Database connection
$host = "localhost";
$dbname = "legacy_db";
$user = "root";
$pass = "";

$con = new mysqli($host, $user, $pass, $dbname);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sdid']) && isset($_POST['status'])) {
    $sdid = $_POST['sdid'];
    $newStatus = ($_POST['status'] === 'Active') ? 'Inactive' : 'Active';

    $stmt = $con->prepare("UPDATE tbl_sponsor_donor SET Status = ? WHERE SDID = ?");
    $stmt->bind_param("ss", $newStatus, $sdid);
    $stmt->execute();
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch donor data
$donor_data = [];
$result = $con->query("SELECT SDID, Name, Type, Status FROM tbl_sponsor_donor");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $donor_data[] = $row;
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Donor & Sponsor Management</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .sidebar {
            width: 250px;
            background-color: #6F4E37;
            color: white;
            position: fixed;
            height: 100%;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            color: #F8F1E9;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar li {
            margin-bottom: 15px;
        }

        .sidebar a {
            color: #F8F1E9;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #5D4037;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .dashboard {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #3B141C;
            margin-bottom: 20px;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .stat {
            background-color: #F8F1E9;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            width: 23%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .stat h3 {
            color: #7E392F;
            margin: 0;
            font-size: 24px;
        }

        .stat p {
            color: #6F4E37;
            margin: 5px 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #6F4E37;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .add {
            color: green;
            cursor: pointer;
            text-decoration: underline;
            font-weight: bold;
        }

        .remove {
            color: red;
            cursor: pointer;
            text-decoration: underline;
            font-weight: bold;
        }

        .active-status {
            color: green;
            font-weight: bold;
        }

        .inactive-status {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>LEGACY</h2>
        <ul>
            <li><a href="AdminDash.php">Dashboard</a></li>
            <li><a href="tournement.php">Tournament Details</a></li>

            <li><a href="UserView.php">User Overview</a></li>
            <li><a href="AdminSet1.php">Admin setting</a></li>
            <li><a href="donor_management.php" class="active">Manage Donations & Sponsors</a></li>
            <li><a href="index.php">Home</a></li>

            <li><a href="logout.php"> LogOUT</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="dashboard">
            <h1>Donor & Sponsor Management</h1>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donor_data as $donor): ?>
                        <tr data-sdid="<?= $donor['SDID'] ?>">
                            <td><?= htmlspecialchars($donor['SDID']) ?></td>
                            <td><?= htmlspecialchars($donor['Name']) ?></td>
                            <td><?= htmlspecialchars($donor['Type']) ?></td>
                            <td class="status <?= $donor['Status'] === 'Active' ? 'active-status' : 'inactive-status' ?>">
                                <?= htmlspecialchars($donor['Status']) ?>
                            </td>
                            <td>
                                <span class="action <?= $donor['Status'] === 'Active' ? 'remove' : 'add' ?>">
                                    <?= $donor['Status'] === 'Active' ? 'Remove' : 'Add' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($donor_data)): ?>
                        <tr>
                            <td colspan="5">No donor/sponsor data found.</td>
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
                    const sdid = row.getAttribute("data-sdid");
                    const currentStatus = row.querySelector(".status").innerText;

                    fetch(window.location.href, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded",
                            },
                            body: new URLSearchParams({
                                sdid: sdid,
                                status: currentStatus
                            })
                        })
                        .then(response => {
                            if (response.ok) {
                                return response.text();
                            }
                            throw new Error('Network response was not ok');
                        })
                        .then(() => {
                            // Update the status display
                            const statusCell = row.querySelector(".status");
                            const newStatus = currentStatus === "Active" ? "Inactive" : "Active";

                            statusCell.innerText = newStatus;
                            statusCell.className = `status ${newStatus === "Active" ? 'active-status' : 'inactive-status'}`;

                            // Update the action button
                            this.innerText = newStatus === "Active" ? "Remove" : "Add";
                            this.className = `action ${newStatus === "Active" ? 'remove' : 'add'}`;
                        })
                        .catch(err => console.error("Error updating status:", err));
                });
            });
        });
    </script>
</body>

</html>