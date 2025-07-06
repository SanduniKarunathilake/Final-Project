<?php
session_start();
// In addtourForm.php
$id = $_SESSION['user_id'];

// Check if player is logged in
if ($_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'player') {
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

$showAlert = false; // Flag to control alert display
$alertMessage = ''; // Alert message content
$alertType = ''; // 'success' or 'error'

$message = ''; // Variable to store success/error messages

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $tid = $_POST['tid'] ?? '';
    $description = $_POST['description'] ?? '';
    $tournament_date = $_POST['tournament-date'] ?? '';

    // Validate inputs
    if (empty($tid) || empty($description) || empty($tournament_date)) {
        $message = '<div style="color: red; margin-bottom: 15px;">All fields are required!</div>';
    } else {
        // Check if player is already registered for this tournament
        $checkStmt = $con->prepare("SELECT * FROM tblplayer_tournmt WHERE PID = ? AND TID = ?");
        $checkStmt->bind_param("ss", $id, $tid);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = '<div style="color: red; margin-bottom: 15px;">You are already registered for this tournament!</div>';
        } else {
            // Prepare and execute the insert query
            $stmt = $con->prepare("INSERT INTO tblplayer_tournmt (Date, PID, TID, Details) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $tournament_date, $id, $tid, $description);

            if ($stmt->execute()) {
                $message = '<div style="color: green; margin-bottom: 15px;">Tournament registration saved successfully!</div>';
                // Clear form if needed
                $_POST = array();
            } else {
                $message = '<div style="color: red; margin-bottom: 15px;">Error saving tournament: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}

// Get tournaments that the player hasn't registered for yet
$query = "SELECT t.TID, t.Tname 
          FROM tbltournament t
          WHERE t.TID NOT IN (
              SELECT pt.TID 
              FROM tblplayer_tournmt pt 
              WHERE pt.PID = ?
          )
          ORDER BY t.Tname";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $id);
$stmt->execute();
$res = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tournament | Legacy Sports</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #faf6f0;
            color: #6b5b4d;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .form-container {
            background: #fffcf9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            padding: 30px;
            border: 1px solid #e8d9cc;
            width: 100%;
            max-width: 500px;
        }

        .form-title {
            color: #6b5b4d;
            margin-bottom: 25px;
            text-align: center;
            font-size: 22px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #6b5b4d;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e8d9cc;
            border-radius: 6px;
            background-color: #faf6f0;
            color: #6b5b4d;
            font-size: 16px;
        }

        .form-control:focus {
            outline: none;
            border-color: #c0a99b;
            box-shadow: 0 0 0 3px rgba(193, 169, 155, 0.2);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            background-color: #c0a99b;
            color: #fffcf9;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }

        .btn:hover {
            background-color: #b39b8d;
        }

        .btn-back {
            background-color: #6b5b4d;
        }

        .btn-back:hover {
            background-color: #5a4c40;
        }

        .no-tournaments {
            color: #6b5b4d;
            text-align: center;
            padding: 20px;
            font-style: italic;
        }

        .button-group {
            margin-top: 20px;
        }
    </style>

</head>

<body>
    <div class="form-container">
        <h2 class="form-title">Add Tournament</h2>

        <?php
        // Display success/error messages
        if (!empty($message)) {
            echo $message;
        }

        // Check if there are tournaments available for registration
        if ($res->num_rows === 0) {
            echo '<div class="no-tournaments">You have registered for all available tournaments.</div>';
            echo '<div class="button-group">';
            echo '<a href="playerProfile.php" class="btn btn-back">Back to Profile</a>';
            echo '</div>';
        } else {
        ?>
            <form method="POST">
                <div class="form-group">
                    <label for="tid">Select Tournament</label>
                    <select id="tid" name="tid" class="form-control" required>
                        <option value="">-- Select a Tournament --</option>
                        <?php
                        while ($tournament = $res->fetch_assoc()) {
                            $selected = (isset($_POST['tid']) && $_POST['tid'] == $tournament['TID']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($tournament['TID']) . '" ' . $selected . '>'
                                . htmlspecialchars($tournament['TID'] . ' - ' . $tournament['Tname'])
                                . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tournament-date">Tournament Date</label>
                    <input type="date" id="tournament-date" name="tournament-date" value="<?php echo htmlspecialchars($_POST['tournament-date'] ?? ''); ?>" class="form-control" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn">Save Tournament</button>
                    <a href="playerProfile.php" class="btn btn-back">Back to Profile</a>
                </div>
            </form>
        <?php } ?>
    </div>
</body>

</html>