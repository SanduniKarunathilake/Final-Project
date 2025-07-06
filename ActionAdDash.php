<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $con = mysqli_connect("localhost", "root", "", "legacy_db");

    if (!$con) {
        http_response_code(500);
        echo "Database connection failed.";
        exit;
    }

    $cid = $_POST['cid'];
    $current = $_POST['status'];
    $new = ($current === 'Active') ? 'Inactive' : 'Active';

    $sql = "UPDATE tblcoach SET Status = '$new' WHERE CID = '$cid'";
    if (mysqli_query($con, $sql)) {
        echo $new;
    } else {
        http_response_code(500);
        echo "Error updating status.";
    }

    mysqli_close($con);
}
?>
