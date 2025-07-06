<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "legacy_db");
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Handle delete request
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $pid = $_POST['txtPID'];
    $cid = $_POST['txtCID'];

    $sql = "DELETE FROM tblplayer_coach WHERE PID='$pid' AND CID='$cid'";
    mysqli_query($con, $sql);
    exit();
}

// Insert or Update evaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $beginDate = $_POST['txtBeginDate'];
    $duration = $_POST['txtDuration'];
    $pid = $_POST['txtPID'];
    $cid = $_POST['txtCID'];
    $feedback = $_POST['txtFeedback'];
    $isEditing = $_POST['isEditing'];

    // Check if this player-coach already exists
    $check = mysqli_query($con, "SELECT * FROM tblplayer_coach WHERE PID='$pid' AND CID='$cid'");

    if (mysqli_num_rows($check) > 0 && $isEditing == "1") {
        // Update existing
        $sql = "UPDATE tblplayer_coach 
                SET BDate='$beginDate', Duration='$duration', Feedback='$feedback' 
                WHERE PID='$pid' AND CID='$cid'";
    } else {
        // Insert new
        $sql = "INSERT INTO tblplayer_coach (BDate, Duration, PID, CID, Feedback) 
                VALUES ('$beginDate', '$duration', '$pid', '$cid', '$feedback')";
    }

    mysqli_query($con, $sql);
}

// Fetch all evaluations
$evaluations = [];
$query = "SELECT * FROM tblplayer_coach";
$result = mysqli_query($con, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $evaluations[] = $row;
}

// Return JSON
header('Content-Type: application/json');
echo json_encode($evaluations);
?>
