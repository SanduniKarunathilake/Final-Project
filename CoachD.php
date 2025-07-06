<?php
// Connection to the database
$con = mysqli_connect("localhost", "root", "", "legacy_db");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Accept Form Data
$nic = $_POST['txtNIC'];
$cid = "C" . $nic; // Add 'C' in front
$name = $_POST['txtName'];
$age = $_POST['txtAge'];
$email = $_POST['txtEM'];
$address = $_POST['txtAdd'];
$password = $_POST['txtpassword'];
$contact = $_POST['txtNum'];
$qulific = $_POST['txtQulific'];
$sport = $_POST['cmbSport'];
$type = $_POST['cmbSessionType'];

// Check if Update Button was clicked
if (isset($_POST['btnUpdate'])) {
    // UPDATE existing record
    $sql = "UPDATE tblcoach 
            SET Name = '$name', Age = '$age', Address = '$address', Email = '$email', pwd = '$password', Sport = '$sport', TeleNum = '$contact', Qualific = '$qulific', Type = '$type'
            WHERE CID = '$cid'";
    $result = mysqli_query($con, $sql);

    if ($result) {
        showMessage("Update Successful");
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }

} elseif (isset($_POST['btnSubmit'])) {
    // Try INSERT (but handle if Duplicate Key error)
    $sql = "INSERT INTO tblcoach (Name, CID, Address, Email, Age, Sport, Qualific, Type, TeleNum, pwd) 
            VALUES ('$name', '$cid', '$address', '$email', '$age', '$sport', '$qulific', '$type', '$contact', '$password')";
    
    $result = mysqli_query($con, $sql);

    if ($result) {
        showMessage("Registration Successful", $cid, $password);
    } else {
        if (mysqli_errno($con) == 1062) {
            // 1062 = Duplicate Entry Error
            echo "<h2 style='color:red;text-align:center;'>Error: This NIC is already registered. Please use Update instead.</h2>";
        } else {
            echo "Error inserting record: " . mysqli_error($con);
        }
    }
}

// Disconnect
mysqli_close($con);

// Common function to show message
function showMessage($message, $cid = '', $password = '') {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Legacy Sports Academy</title>
        <style>
            body {
                text-align: center;
                background-image: url(\'subackground.jpg\');
                background-size: cover;
                background-attachment: fixed;
                background-position: center;
                color: black;
                padding: 50px;
            }
            h1, h2, p {
                color: black;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #4E342E;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <h1>' . $message . '</h1>
        <img src="logow.png" alt="Logo" style="width: 300px; height: auto; margin-top: 20px;"><br>';

    if ($cid != '' && $password != '') {
        echo '<h2>Your Login Details:</h2>
              <p><strong>Username:</strong> ' . $cid . '</p>   
              <p><strong>Password:</strong> ' . $password . '</p>';
    }

    echo '<br><a href="Index.html" class="button">Go Back to Home Page</a>
    </body>
    </html>';
}
?>
