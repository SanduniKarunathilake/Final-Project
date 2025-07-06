<?php
// Connection to the database
$con = mysqli_connect("localhost", "root", "", "legacy_db");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Accept Form Data
$nic = $_POST['txtNIC'];
$pid = "P" . $nic;
$name = $_POST['txtName'];
$age = $_POST['txtAge'];
$email = $_POST['txtEM'];
$address = $_POST['txtAdd'];
$password = $_POST['txtpassword'];
$contact = $_POST['txtNum'];
$sport = $_POST['cmbSport'];
$type = $_POST['cmbPlayerType'];
$status = isset($_POST['radiolowIncome']) ? $_POST['radiolowIncome'] : '';

// Handle profile image upload
$image = $_FILES['imgfile']['name'];
move_uploaded_file($_FILES['imgfile']['tmp_name'], "Player/" . $image);

// Handle GN Certificate upload
$gnCertifiPath = '';
if (!empty($_FILES['imgfile']['name'])) {
    $gnCertifiName = $_FILES['imgfile']['name'];
    $gnCertifiTemp = $_FILES['imgfile']['tmp_name'];
    $gnCertifiPath = "GNCertificates/" . $gnCertifiName;
    move_uploaded_file($gnCertifiTemp, $gnCertifiPath);
}

// Check if Update Button was clicked
if (isset($_POST['btnUpdate'])) {
    // UPDATE existing record
    $sql = "UPDATE tblplayer
            SET Name = '$name', Age = '$age', Address = '$address', Email = '$email', 
                pwd = '$password', Sport = '$sport', TeleNum = '$contact', 
                Status = '$status', Type = '$type'";

    // Add GN Certificate to update if it was uploaded
    if (!empty($gnCertifiPath)) {
        $sql .= ", GNCertifi = '$gnCertifiPath'";
    }

    $sql .= " WHERE PID = '$pid'";

    $result = mysqli_query($con, $sql);

    if ($result) {
        showMessage("Update Successful");
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
} elseif (isset($_POST['btnSubmit'])) {
    // Try INSERT (but handle if Duplicate Key error)
    $sql = "INSERT INTO tblplayer (Name, PID, Address, Email, Age, Sport, Status, Type, TeleNum, pwd, GNCertifi) 
            VALUES ('$name', '$pid', '$address', '$email', '$age', '$sport', '$status', '$type', '$contact', '$password', ";

    //Add GN Certificate path if uploaded, otherwise set to empty
    if (!empty($gnCertifiPath)) {
        $sql .= "'$gnCertifiPath')";
    } else {
        $sql .= "NULL)";
    }

    $result = mysqli_query($con, $sql);

    if ($result) {
        showMessage("Registration Successful", $pid, $password);
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
function showMessage($message, $pid = '', $password = '')
{
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

    if ($pid != '' && $password != '') {
        echo '<h2>Your Login Details:</h2>
              <p><strong>Username:</strong> ' . $pid . '</p>   
              <p><strong>Password:</strong> ' . $password . '</p>';
    }

    echo '<br><a href="Index.html" class="button">Go Back to Home Page</a>
    </body>
    </html>';
}
