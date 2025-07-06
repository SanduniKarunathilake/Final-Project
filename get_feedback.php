<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'username', 'password', 'your_database');

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed."]));
}

$sql = "SELECT pid, cid, date, feedback FROM tblplayer_coach";
$result = $conn->query($sql);

$feedbacks = [];
while ($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
}

echo json_encode($feedbacks);
$conn->close();
?>
