<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f5f0e1, #e4d6c8);
            color: #333;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: #d9b99b;
            padding: 15px;
            display: flex;
            justify-content: center;
            gap: 30px;
        }

        .navbar a {
            text-decoration: none;
            color: #4b3832;
            font-weight: bold;
            font-size: 18px;
            padding: 10px 20px;
            background: #ede0d4;
            border-radius: 5px;
            transition: 0.3s;
        }

        .navbar a:hover {
            background: #d2b48c;
        }

        .container {
            margin: 50px auto;
            width: 80%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            color: #333;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 15px;
            text-align: center;
            border-bottom: 2px solid #ddd;
        }

        th {
            background: #c3a384;
            color: #333;
        }

        tr:nth-child(even) {
            background: #f0e5d8;
        }

        tr:nth-child(odd) {
            background: #e6ccb2;
        }

        .btn {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: 0.3s;
        }

        .edit-btn {
            background: #e4b07a;
        }

        .edit-btn:hover {
            background: #d49a5f;
        }

        .delete-btn {
            background: #a9746e;
            color: white;
        }

        .delete-btn:hover {
            background: #8c5b56;
        }

        .add-form {
            margin: 20px auto;
            padding: 20px;
            background: #ede0d4;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .add-form input,
        .add-form select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            min-width: 150px;
        }

        .add-btn {
            background: #bfa78a;
            color: white;
            font-weight: bold;
            padding: 8px 15px;
        }

        .add-btn:hover {
            background: #a38e74;
        }

        .error {
            color: #a94442;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            padding: 10px;
            border-radius: 5px;
            margin: 10px auto;
            width: 80%;
        }

        .success {
            color: #3c763d;
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            padding: 10px;
            border-radius: 5px;
            margin: 10px auto;
            width: 80%;
        }

        .action-cell {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <a href="Viewplayer.php">View Player Details</a>
        <a href="Edit trainee.html">Edit Trainee Evaluation</a>
        <a href="coachprofile.php">Profile</a>
        <a href="logout.php">logout</a>
    </div>
    <div class="container">
        <h2>Coach's Schedule</h2>

        <?php
        // Database connection
        $host = "localhost";
        $user = "root";
        $pass = "";
        $dbname = "legacy_db";

        $conn = new mysqli($host, $user, $pass, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // INSERT
            if (isset($_POST['btnSubmit'])) {
                $date = $conn->real_escape_string($_POST['txtDate'] ?? '');
                $time = $conn->real_escape_string($_POST['txtTime'] ?? '');
                $details = $conn->real_escape_string($_POST['txtDetails'] ?? '');
                $cid = $conn->real_escape_string($_POST['txtCID'] ?? '');

                if (!empty($date) && !empty($time) && !empty($details) && !empty($cid)) {
                    $sql = "INSERT INTO tblschedule (Date, Time, Dtls, CID) VALUES ('$date', '$time', '$details', '$cid')";
                    if ($conn->query($sql)) {
                        echo "<div class='success'>Schedule added successfully!</div>";
                    } else {
                        echo "<div class='error'>Error adding schedule: " . $conn->error . "</div>";
                    }
                } else {
                    echo "<div class='error'>All fields are required!</div>";
                }
            }

            // UPDATE
            if (isset($_POST['btnupdate'])) {
                $id = $conn->real_escape_string($_POST['schID'] ?? '');
                $date = $conn->real_escape_string($_POST['txtDate'] ?? '');
                $time = $conn->real_escape_string($_POST['txtTime'] ?? '');
                $details = $conn->real_escape_string($_POST['txtDetails'] ?? '');

                if (!empty($id)) {
                    $sql = "UPDATE tblschedule SET Date='$date', Time='$time', Dtls='$details' WHERE SchID='$id'";
                    if ($conn->query($sql)) {
                        echo "<div class='success'>Schedule updated successfully!</div>";
                    } else {
                        echo "<div class='error'>Error updating schedule: " . $conn->error . "</div>";
                    }
                }
            }

            // DELETE
            if (isset($_POST['btndelete'])) {
                $id = $conn->real_escape_string($_POST['schID'] ?? '');
                if (!empty($id)) {
                    $sql = "DELETE FROM tblschedule WHERE SchID='$id'";
                    if ($conn->query($sql)) {
                        echo "<div class='success'>Schedule deleted successfully!</div>";
                    } else {
                        echo "<div class='error'>Error deleting schedule: " . $conn->error . "</div>";
                    }
                }
            }
        }
        ?>

        <!-- Add New Schedule Form -->
        <form method="POST" action="Coach_Schedule.php" class="add-form">
            <input type="date" name="txtDate" required>
            <input type="time" name="txtTime" required>
            <input type="text" name="txtDetails" placeholder="Enter details" required>
            <input type="text" name="txtCID" placeholder="Enter Coach ID" required>
            <button type="submit" name="btnSubmit" class="btn add-btn">Add Schedule</button>
        </form>

        <!-- Schedule Table -->
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Details</th>
                    <th>Coach ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display all records
                $sql = "SELECT * FROM tblschedule ORDER BY Date ASC, Time ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['Date']}</td>
                            <td>{$row['Time']}</td>
                            <td>{$row['Dtls']}</td>
                            <td>{$row['CID']}</td>
                            <td class='action-cell'>
                                <form method='POST' action='Coach_Schedule.php' style='display:inline;'>
                                    <input type='hidden' name='schID' value='{$row['SchID']}'>
                                    <input type='hidden' name='txtDetails' value='{$row['Dtls']}'>
                                    <input type='hidden' name='txtDate' value='{$row['Date']}'>
                                    <input type='hidden' name='txtTime' value='{$row['Time']}'>
                                    
                                </form>
                                <button onclick='openEditModal(
                                    \"{$row['SchID']}\",
                                    \"{$row['Date']}\",
                                    \"{$row['Time']}\",
                                    \"{$row['Dtls']}\"
                                )' class='btn edit-btn'>Edit</button>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No schedules found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>

        <!-- Edit Modal (hidden by default) -->
        <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
            <div style="background:#f5f0e1; padding:20px; border-radius:10px; width:400px;">
                <h3>Edit Schedule</h3>
                <form method="POST" action="Coach_Schedule.php" id="editForm">
                    <input type="hidden" name="schID" id="editSchID">
                    <div style="margin:10px 0;">
                        <label>Date:</label>
                        <input type="date" name="txtDate" id="editDate" required style="width:100%; padding:8px;">
                    </div>
                    <div style="margin:10px 0;">
                        <label>Time:</label>
                        <input type="time" name="txtTime" id="editTime" required style="width:100%; padding:8px;">
                    </div>
                    <div style="margin:10px 0;">
                        <label>Details:</label>
                        <input type="text" name="txtDetails" id="editDetails" required style="width:100%; padding:8px;">
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-top:20px;">
                        <button type="button" onclick="closeEditModal()" style="padding:8px 15px; background:#a9746e; color:white; border:none; border-radius:5px; cursor:pointer;">Cancel</button>
                        <button type="submit" name="btnupdate" style="padding:8px 15px; background:#e4b07a; border:none; border-radius:5px; cursor:pointer;">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openEditModal(id, date, time, details) {
                document.getElementById('editSchID').value = id;
                document.getElementById('editDate').value = date;
                document.getElementById('editTime').value = time;
                document.getElementById('editDetails').value = details;
                document.getElementById('editModal').style.display = 'flex';
            }

            function closeEditModal() {
                document.getElementById('editModal').style.display = 'none';
            }
        </script>
    </div>
</body>

</html>