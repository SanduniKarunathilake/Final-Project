<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament Details</title>
    <link rel="stylesheet" href="tournement.css">
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Tournament Gallery</h1>
        </header>
        <div class="gallery">
            <?php
            $con = mysqli_connect("localhost", "root", "", "legacy_db");
            if (!$con) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $query = "SELECT * FROM tbltournament";
            $result = mysqli_query($con, $query);

            while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="tournament-card">';
                echo '<img src="tournament/' . htmlspecialchars($row['Picture']) . '" alt="' . htmlspecialchars($row['Tname']) . '">';
                echo '<div class="content">';
                echo '<h2>' . htmlspecialchars($row['Tname']) . '</h2>';
                echo '<p>' . htmlspecialchars($row['Description']) . '</p>';
                echo '<a href="' . htmlspecialchars($row['link']) . '" class="register-link" target="_blank">Register Now</a>';
                echo '</div>';
                echo '</div>';
            }

            mysqli_close($con);
            ?>
        </div>
    </div>
</body>
</html>