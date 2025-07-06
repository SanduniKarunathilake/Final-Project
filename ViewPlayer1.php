<?php
session_start();
$cid = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Player Details - Search Filter</title>
    <style>
        body {
            background: linear-gradient(to right, #f5f0e1, #e4d6c8);
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        input,
        select,
        button {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #bfa78a;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        table {
            margin: auto;
            width: 90%;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0px 0px 10px #ccc;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: #c3a384;
        }

        tr:nth-child(even) {
            background-color: #f0e5d8;
        }

        tr:nth-child(odd) {
            background-color: #e6ccb2;
        }
    </style>
</head>

<body>
    <h2>Player Details</h2>

    <!-- Search Form -->
    <form method="GET" action="">
        <input type="text" name="name" placeholder="Search by Name" value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
        <select name="sport">
            <option value="">All Sports</option>
            <?php
            // Dynamic sport dropdown
            $conn = new mysqli("localhost", "root", "", "legacy_db");
            $sportQuery = "SELECT DISTINCT Sport FROM tblplayer";
            $sportResult = $conn->query($sportQuery);
            while ($sportRow = $sportResult->fetch_assoc()) {
                $selected = ($_GET['sport'] ?? '') == $sportRow['Sport'] ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($sportRow['Sport']) . "' $selected>" . htmlspecialchars($sportRow['Sport']) . "</option>";
            }
            ?>
        </select>
        <button type="submit">Search</button>
    </form>

    <!-- Results Table -->
    <table>
        <tr>
            <th>Player ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Sport</th>
            <th>Address</th>
            <th>Type</th>
            <th>Status</th>
            <th>Contact Number</th>
        </tr>

        <?php
        // Fetch filtered data
        $name = $_GET['name'] ?? '';
        $sport = $_GET['sport'] ?? '';

        $sql = "SELECT PID, Name, Age, Sport, Address, Type, Status, TeleNum FROM tblplayer WHERE GNCertifi IS NOT NULL AND GNCertifi != ''";

        if (!empty($name)) {
            $safeName = $conn->real_escape_string($name);
            $sql .= " AND Name LIKE '%$safeName%'";
        }

        if (!empty($sport)) {
            $safeSport = $conn->real_escape_string($sport);
            $sql .= " AND Sport = '$safeSport'";
        }

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['PID']) . "</td>
                        <td>" . htmlspecialchars($row['Name']) . "</td>
                        <td>" . htmlspecialchars($row['Age']) . "</td>
                        <td>" . htmlspecialchars($row['Sport']) . "</td>
                        <td>" . htmlspecialchars($row['Address']) . "</td>
                        <td>" . htmlspecialchars($row['Type']) . "</td>
                        <td>" . htmlspecialchars($row['Status']) . "</td>
                        <td>" . htmlspecialchars($row['TeleNum']) . "</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No player records found.</td></tr>";
        }

        $conn->close();
        ?>
    </table>
</body>

</html>