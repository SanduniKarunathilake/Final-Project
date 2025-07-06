<?php
// Connect to the database
$con = mysqli_connect("localhost", "root", "", "legacy_db");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize variables with default values
    $tournament_name = $_POST['tournament-name'] ?? '';
    $sport = $_POST['sport'] ?? '';
    $description = $_POST['tournament-description'] ?? '';
    $link = $_POST['register-link'] ?? '';
    $picture = '';

    // Validate required fields
    if (empty($tournament_name) || empty($sport) || empty($description)) {
        die("<h2 style='color:red;text-align:center;'>Error: Required fields are missing.</h2>");
    }

    // File upload handling
    if (isset($_FILES['imgfile']) && $_FILES['imgfile']['error'] === UPLOAD_ERR_OK) {
        $picture = $_FILES['imgfile']['name'];
        $tmp_name = $_FILES['imgfile']['tmp_name'];
        
        // Ensure the folder exists
        if (!is_dir("tournament")) {
            mkdir("tournament");
        }

        move_uploaded_file($tmp_name, "tournament/" . $picture);
    }

    // Generate new TID
    $tid_result = mysqli_query($con, "SELECT TID FROM tbltournament ORDER BY TID DESC LIMIT 1");

    if (mysqli_num_rows($tid_result) > 0) {
        $row = mysqli_fetch_assoc($tid_result);
        $last_tid = $row['TID']; 
        $num = intval(substr($last_tid, 1)) + 1;
        $new_tid = 'T' . str_pad($num, 3, '0', STR_PAD_LEFT); 
    } else {
        $new_tid = "T001";
    }

    // Insert into database (using prepared statements for security)
    $stmt = $con->prepare("INSERT INTO tbltournament (TID, Tname, Sport, Description, Picture, link) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $new_tid, $tournament_name, $sport, $description, $picture, $link);
    $result = $stmt->execute();

    if ($result) {
        showMessage("Tournament Added Successfully");
    } else {
        if (mysqli_errno($con) == 1062) {
            echo "<h2 style='color:red;text-align:center;'>Error: This tournament already exists.</h2>";
        } else {
            echo "<h2 style='color:red;text-align:center;'>Error inserting tournament: " . mysqli_error($con) . "</h2>";
        }
    }
    
    $stmt->close();
} else {
    // If not a POST request, show error
    echo "<h2 style='color:red;text-align:center;'>Error: Form not submitted properly.</h2>";
    echo "<p><a href='addtournament.html'>Go back to form</a></p>";
}

mysqli_close($con);

// Success Message
function showMessage($message) {
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
        
        <br><a href="addtournement.html" class="button">Add Another Tournament</a>
        <br><a href="tournamentView.php" class="button">View All Tournaments</a>
        <br><a href="AdminDash.php" class="button">Dashboard</a>
    </body>
    </html>';
}
?>