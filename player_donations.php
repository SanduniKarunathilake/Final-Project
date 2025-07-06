<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'player') {
    header("Location: login4.html");
    exit();
}

$player_id = $_SESSION['user_id'];

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "legacy_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$donations = [];
$sql = "SELECT d.DID, d.Date, d.Amount, s.Name, s.Email, s.TeleNum 
        FROM tbldonation d
        JOIN tbl_sponsor_donor s ON d.SDID = s.SDID
        WHERE d.PID = '$player_id'
        ORDER BY d.Date DESC";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $donations[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Received Donations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f5f0e1, #e4d6c8);
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
        }

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background: #d9b99b;
            padding: 20px;
            height: 100vh;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-btn {
            display: block;
            background: #8d6e63;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            transition: background 0.3s;
        }

        .sidebar-btn:hover {
            background: #6d4c41;
        }

        .sidebar-btn i {
            margin-right: 8px;
        }

        /* Main content styles */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #5d4037;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #d9b99b;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #8d6e63;
            color: white;
        }

        tr:nth-child(even) {
            background: #f5f0e1;
        }

        tr:hover {
            background: #e6d5c3;
        }

        .no-donations {
            text-align: center;
            padding: 20px;
            color: #8d6e63;
            font-style: italic;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                padding: 15px;
            }

            .main-content {
                padding: 20px;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="sidebar">
        <a href="submit_schedule.php" class="sidebar-btn">
            <i class="fas fa-arrow-left"></i> Back to Schedule
        </a>
    </div>

    <div class="main-content">
        <div class="container">
            <h2>Received Donations</h2>

            <?php if (!empty($donations)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Date</th>
                            <th>Amount($)</th>
                            <th>Donor Name</th>
                            <th>Donor Email</th>
                            <th>Donor Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donation['DID']); ?></td>
                                <td><?php echo htmlspecialchars($donation['Date']); ?></td>
                                <td><?php echo htmlspecialchars($donation['Amount']); ?></td>
                                <td><?php echo htmlspecialchars($donation['Name']); ?></td>
                                <td><?php echo htmlspecialchars($donation['Email']); ?></td>
                                <td><?php echo htmlspecialchars($donation['TeleNum']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-donations">
                    No donations received yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>