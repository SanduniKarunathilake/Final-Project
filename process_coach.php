<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'player') {
    header("Location: login4.html");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "legacy_db");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$pid = mysqli_real_escape_string($con, $_POST['pid']);
$cid = mysqli_real_escape_string($con, $_POST['cid']);
$bdate_raw = $_POST['bdate'];
$end_date_raw = $_POST['duration'];

$start = new DateTime($bdate_raw);
$end = new DateTime($end_date_raw);
$interval = $start->diff($end);
$days = $interval->days;

if ($days < 7) {
    $duration_formatted = $days . ' ' . ($days == 1 ? 'day' : 'days');
} elseif ($days < 30) {
    $weeks = floor($days / 7);
    $duration_formatted = $weeks . ' ' . ($weeks == 1 ? 'week' : 'weeks');
} elseif ($days < 365) {
    $months = floor($days / 30);
    $duration_formatted = $months . ' ' . ($months == 1 ? 'month' : 'months');
} else {
    $years = floor($days / 365);
    $duration_formatted = $years . ' ' . ($years == 1 ? 'year' : 'years');
}

// Escape for DB
$bdate = mysqli_real_escape_string($con, $bdate_raw);
$duration = mysqli_real_escape_string($con, $duration_formatted);

// Insert into tblplayer_coach
$sql = "INSERT INTO tblplayer_coach (PID, CID, Duration, BDate)
        VALUES ('$pid', '$cid', '$duration', '$bdate')";

$message = "";
$type = "";

if (mysqli_query($con, $sql)) {
    $update_sql = "UPDATE tblplayer SET CID = '$cid' WHERE PID = '$pid'";
    if (mysqli_query($con, $update_sql)) {
        $message = "✅ Coach assigned successfully for $duration_formatted!";
        $type = "success";
    } else {
        $message = "❌failed to update player record: " . htmlspecialchars(mysqli_error($con));
        $type = "error";
    }
} else {
    $message = " Failed to assign coach: " . htmlspecialchars(mysqli_error($con));
    $type = "error";
}

mysqli_close($con);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Coach Assignment Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .message-box {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
        }

        .success {
            border-left: 6px solid #4CAF50;
            color: #2e7d32;
        }

        .error {
            border-left: 6px solid #f44336;
            color: #c62828;
        }

        h2 {
            margin: 0 0 15px;
        }

        p {
            margin: 10px 0;
        }
    </style>
    <script>
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = "chosCoach.php";
        }, 3000);
    </script>
</head>

<body>
    <div class="message-box <?= $type ?>">
        <h2><?= $type === 'success' ? 'Success!' : 'Error' ?></h2>
        <p><?= $message ?></p>
        <p>You will be redirected shortly...</p>
        </d