<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if donor is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.html");
    exit();
}

// Get donor ID from session
$donor_id = $_SESSION['user_id'];

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "legacy_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch players for dropdown
$players = [];
$player_query = $conn->query("SELECT PID, Name FROM tblplayer");
if ($player_query) {
    while ($row = $player_query->fetch_assoc()) {
        $players[] = $row;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // INSERT
    if (isset($_POST['btnSubmit'])) {
        $amount = $conn->real_escape_string($_POST['txtAmount'] ?? '');
        $pid = $conn->real_escape_string($_POST['txtPID'] ?? '');

        if (!empty($amount) && !empty($pid)) {
            // Instead of directly inserting, redirect to the Stripe checkout
            $_SESSION['donation_amount'] = $amount;
            $_SESSION['donation_player_id'] = $pid;
            $_SESSION['donation_donor_id'] = $donor_id;

            header("Location: create_donation_checkout.php");
            exit();
        } else {
            $error_message = "Amount and Player are required!";
        }
    }

    // UPDATE
    if (isset($_POST['btnupdate'])) {
        $id = $conn->real_escape_string($_POST['donationID'] ?? '');
        $amount = $conn->real_escape_string($_POST['txtAmount'] ?? '');
        $pid = $conn->real_escape_string($_POST['txtPID'] ?? '');

        if (!empty($id)) {
            try {
                $sql = "UPDATE tbldonation SET Amount='$amount', PID='$pid' WHERE DID='$id' AND SDID='$donor_id'";
                if ($conn->query($sql)) {
                    $success_message = "Donation updated successfully!";
                }
            } catch (mysqli_sql_exception $e) {
                $error_message = "Error updating donation: " . $e->getMessage();
            }
        }
    }

    // DELETE
    if (isset($_POST['btndelete'])) {
        $id = $conn->real_escape_string($_POST['donationID'] ?? '');
        if (!empty($id)) {
            try {
                $sql = "DELETE FROM tbldonation WHERE DID='$id' AND SDID='$donor_id'";
                if ($conn->query($sql)) {
                    $success_message = "Donation deleted successfully!";
                }
            } catch (mysqli_sql_exception $e) {
                $error_message = "Error deleting donation: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f5f0e1, #e4d6c8);
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #5d4037;
            text-align: center;
            margin-bottom: 20px;
        }

        .add-form {
            margin: 20px 0;
            padding: 20px;
            background: #ede0d4;
            border-radius: 10px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #5d4037;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }

        .add-btn {
            background: #8d6e63;
            color: white;
            font-weight: bold;
        }

        .add-btn:hover {
            background: #6d4c41;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
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

        .action-cell {
            display: flex;
            gap: 5px;
        }

        .edit-btn {
            background: #a1887f;
            color: white;
        }

        .edit-btn:hover {
            background: #8d6e63;
        }

        .delete-btn {
            background: #a94442;
            color: white;
        }

        .delete-btn:hover {
            background: #8c3535;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .error {
            background: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        #editModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        .modal-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .back-btn {
            background: #bbb;
            color: #333;
            font-weight: bold;
            text-decoration: none;
            padding: 8px 15px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: #999;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="donsponProfile.php" class="btn back-btn">← Back to Profile</a>
        <h2>Donation Management</h2>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Add New Donation Form -->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="add-form">
            <div class="form-group">
                <label for="txtAmount">Amount</label>
                <input type="number" step="0.01" name="txtAmount" id="txtAmount" placeholder="Enter amount" required>
            </div>

            <div class="form-group">
                <label for="txtPID">Player</label>
                <select name="txtPID" id="txtPID" required>
                    <option value="">Select Player</option>
                    <?php foreach ($players as $player): ?>
                        <option value="<?php echo htmlspecialchars($player['PID']); ?>">
                            <?php echo htmlspecialchars($player['Name']); ?> (<?php echo htmlspecialchars($player['PID']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" name="btnSubmit" class="btn add-btn">Add Donation</button>
        </form>

        <!-- Donations Table -->
        <table>
            <thead>
                <tr>
                    <th>Donation ID</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Player ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display only donations for this donor
                $sql = "SELECT d.*, p.Name AS PlayerName 
                        FROM tbldonation d
                        JOIN tblplayer p ON d.PID = p.PID
                        WHERE d.SDID = '$donor_id'
                        ORDER BY d.Date DESC";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['DID']) . "</td>
                            <td>" . htmlspecialchars($row['Date']) . "</td>
                            <td>" . htmlspecialchars($row['Amount']) . "</td>
                            <td>" . htmlspecialchars($row['PlayerName']) . " (" . htmlspecialchars($row['PID']) . ")</td>
                            <td class='action-cell'>
                                <form method='POST' action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "' style='display:inline;'>
                                    <input type='hidden' name='donationID' value='" . htmlspecialchars($row['DID']) . "'>
                                    <button type='submit' name='btndelete' class='btn delete-btn'>Delete</button>
                                </form>

                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No donations found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>

        <!-- Edit Modal -->
        <div id="editModal">
            <div class="modal-content">
                <h3>Edit Donation</h3>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="editForm">
                    <input type="hidden" name="donationID" id="editDonationID">

                    <div class="form-group">
                        <label for="editAmount">Amount</label>
                        <input type="number" step="0.01" name="txtAmount" id="editAmount" required>
                    </div>

                    <div class="form-group">
                        <label for="editPID">Player</label>
                        <select name="txtPID" id="editPID" required>
                            <option value="">Select Player</option>
                            <?php foreach ($players as $player): ?>
                                <option value="<?php echo htmlspecialchars($player['PID']); ?>">
                                    <?php echo htmlspecialchars($player['Name']); ?> (<?php echo htmlspecialchars($player['PID']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="modal-actions">
                        <button type="button" onclick="closeEditModal()" class="btn delete-btn">Cancel</button>
                        <button type="submit" name="btnupdate" class="btn edit-btn">Update</button>
                    </div>
                </form>
            </div>


        </div>

        <script>
            function openEditModal(id, amount, pid) {
                document.getElementById('editDonationID').value = id;
                document.getElementById('editAmount').value = amount;
                document.getElementById('editPID').value = pid;
                document.getElementById('editModal').style.display = 'flex';
            }

            function closeEditModal() {
                document.getElementById('editModal').style.display = 'none';
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target == document.getElementById('editModal')) {
                    closeEditModal();
                }
            }
        </script>
    </div>
</body>

</html>