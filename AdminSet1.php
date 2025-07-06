<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "legacy_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add new admin logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin'])) {
    $aid = "A" . $_POST['aid']; // Prefix 'A'
    $name = $_POST['name'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $tel = $_POST['tele'];
    $pwd = $_POST['pwd'];

    $sql = "INSERT INTO tbladmin (AID, Name, Address, Email, Age, TeleNum, pwd, Type) 
            VALUES ('$aid', '$name', '$address', '$email', $age, '$tel', '$pwd', 'Active')";

    if ($conn->query($sql) === TRUE) {
    } else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
}

// Toggle status logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_status'])) {
    $aid = $_POST['aid'];
    $new_status = $_POST['new_status'];

    $sql = "UPDATE tbladmin SET Type = '$new_status' WHERE AID = '$aid'";
    $conn->query($sql);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Settings</title>
    <style>
        body {
            background-color: #f3e5d5;
            font-family: Arial, sans-serif;
            color: #5d4037;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background-color: #8d6e63;
            color: #fff;
            padding: 20px;
            height: 100vh;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .sidebar h2 {
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }

        .sidebar a {
            display: block;
            padding: 10px 0;
            color: #fbe9e7;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #6d4c41;
            padding-left: 10px;
        }

        .main-content {
            flex-grow: 1;
            padding: 30px;
        }

        h2 {
            color: #4e342e;
        }

        form,
        table {
            background-color: #efebe9;
            border: 1px solid #a1887f;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        input[type="text"],
        input[type="number"],
        input[type="email"] {
            width: 95%;
            padding: 8px;
            margin: 6px 0;
            border: 1px solid #a1887f;
            border-radius: 4px;
        }

        input[type="submit"],
        button {
            padding: 8px 14px;
            margin-right: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #bcaaa4;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #d7ccc8;
        }

        button.add-btn {
            background-color: #4CAF50;
            color: white;
        }

        button.remove-btn {
            background-color: #e57373;
            color: white;
        }

        button.add-btn:hover {
            background-color: #388e3c;
        }

        button.remove-btn:hover {
            background-color: #c62828;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>LEGACY</h2>
        <li><a href="AdminDash.php">Dashboard</a></li>
        <li><a href="AdminDash.php">Dashboard</a></li>
        <li><a href="tournement.php">Tournament Details</a></li>

        <li><a href="UserView.php">User Overview</a></li>
        <li><a href="AdminSet1.php" class="active">Admin setting</a></li>
        <li><a href="donor_management.php">Manage Donations & Sponsors</a></li>
        <li><a href="index.php">Home</a></li>

        <li><a href="logout.php"> LogOUT</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Add New Admin</h2>
        <form method="POST" action="">
            <input type="hidden" name="add_admin" value="1">
            <label>Admin ID (without 'A'):</label><br>
            <input type="text" name="aid" required><br>
            <label>Name:</label><br>
            <input type="text" name="name" required><br>
            <label>Address:</label><br>
            <input type="text" name="address" required><br>
            <label>Email:</label><br>
            <input type="email" name="email" required><br>
            <label>Age:</label><br>
            <input type="number" name="age" required><br>
            <label>Telephone Number:</label><br>
            <input type="text" name="tele" required><br>
            <label>Password:</label><br>
            <input type="text" name="pwd" required><br><br>
            <input type="submit" value="Add Admin" style="background-color:#8d6e63; color:white;">
        </form>

        <h2>All Admins</h2>
        <table>
            <tr>
                <th>AID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Email</th>
                <th>Age</th>
                <th>TeleNum</th>
                <th>Status</th>
                <!-- Empty header for actions column -->
            </tr>
            <?php
            $result = $conn->query("SELECT AID, Name, Address, Email, Age, TeleNum, Type FROM tbladmin");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                <td>{$row['AID']}</td>
                <td>{$row['Name']}</td>
                <td>{$row['Address']}</td>
                <td>{$row['Email']}</td>
                <td>{$row['Age']}</td>
                <td>{$row['TeleNum']}</td>
                <td>{$row['Type']}</td>
                <td>
                    <form method='POST' action='' style='display:inline;'>
                        <input type='hidden' name='toggle_status' value='1'>
                        <input type='hidden' name='aid' value='{$row['AID']}'>";
                if ($row['Type'] == 'Active') {
                    echo "<input type='hidden' name='new_status' value='Inactive'>
                      <button type='submit' class='remove-btn'>Remove</button>";
                } else {
                    echo "<input type='hidden' name='new_status' value='Active'>
                      <button type='submit' class='add-btn'>Add</button>";
                }
                echo "</form>
                </td>
            </tr>";
            }
            ?>
        </table>
    </div>

</body>

</html>

<?php $conn->close(); ?>