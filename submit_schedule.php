  <?php
    // Database connection
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "legacy_db";



    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    session_start();
    $id = $_SESSION['user_id']

    ?>


  <!DOCTYPE html>
  <html lang="en">

  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Coach Schedule</title>
      <style>
          body {
              font-family: Arial, sans-serif;
              background: linear-gradient(135deg, #f5f0e1, #e4d6c8);
              color: #333;
              margin: 0;
              padding: 0;
              display: flex;
          }

          .sidebar {
              width: 250px;
              background: #c3a384;
              height: 100vh;
              padding: 20px;
              box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
              position: fixed;
          }

          .sidebar-header {
              text-align: center;
              padding-bottom: 20px;
              border-bottom: 1px solid #a38e74;
              margin-bottom: 20px;
          }

          .sidebar-header h3 {
              color: #333;
              margin: 10px 0;
          }

          .sidebar-menu {
              list-style: none;
              padding: 0;
              margin: 0;
          }

          .sidebar-menu li {
              margin-bottom: 10px;
          }

          .sidebar-menu a {
              display: block;
              padding: 10px 15px;
              color: #333;
              text-decoration: none;
              border-radius: 5px;
              transition: 0.3s;
              font-weight: bold;
          }

          .sidebar-menu a:hover,
          .sidebar-menu a.active {
              background: #a38e74;
              color: white;
          }

          .sidebar-menu i {
              margin-right: 10px;
          }

          .main-content {
              margin-left: 290px;
              padding: 20px;
              width: calc(100% - 290px);
          }

          .container {
              margin: 20px auto;
              width: 90%;
          }

          table {
              width: 100%;
              border-collapse: collapse;
              background: #ffffff;
              color: #333;
              border-radius: 10px;
              overflow: hidden;
              box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
          }

          th,
          td {
              padding: 15px;
              text-align: center;
              border-bottom: 2px solid #ddd;
          }

          th {
              background: #c3a384;
              color: #333;
          }

          tr:nth-child(even) {
              background: #f0e5d8;
          }

          tr:nth-child(odd) {
              background: #e6ccb2;
          }

          .back-btn {
              display: inline-block;
              margin: 20px 0;
              padding: 10px 20px;
              background: #c3a384;
              color: #333;
              text-decoration: none;
              font-weight: bold;
              border-radius: 5px;
              transition: 0.3s;
          }

          .back-btn:hover {
              background: #a38e74;
              color: white;
          }

          .header {
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin-bottom: 20px;
          }

          .logout-btn {
              padding: 8px 15px;
              background: #a38e74;
              color: white;
              text-decoration: none;
              border-radius: 5px;
              font-weight: bold;
              transition: 0.3s;
          }

          .logout-btn:hover {
              background: #8a7862;
          }
      </style>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  </head>

  <body>
      <!-- Dashboard Sidebar -->
      <div class="sidebar">
          <div class="sidebar-header">
              <h3>Player Dashboard</h3>
          </div>
          <ul class="sidebar-menu">

              <li><a href="submit_schedule.php">Coach Schedule</a></li>
              <li><a href="playerProfile.php" class="active">Profile</a></li>
              <li><a href="chosCoach.php" class="active">Coaches</a></li>
              <li><a href="player_donations.php" class="active">Donations</a></li>

              <li><a href="tournamentView.php">View Tournaments</a></li>
              <li><a href="payment_form.php"><i class="fas fa-user"></i> Payment</a></li>
              <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>

              <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
          </ul>
      </div>

      <!-- Main Content Area -->
      <div class="main-content">
          <div class="header">
              <h2><?php echo "Your ID is: " . $id ?> </h2>
              <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>

          <div class="container">

              <!-- Schedule Table -->
              <table>
                  <thead>
                      <tr>
                          <th>Date</th>
                          <th>Time</th>
                          <th>Details</th>
                          <th>Coach ID</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php

                        $sql1 = "SELECT CID FROM tblplayer WHERE PID='$id'";
                        $res1 = $conn->query($sql1);

                        if ($res1 && $res1->num_rows > 0) {
                            $row1 = $res1->fetch_assoc();
                            $cid = $row1['CID'];

                            $sql = "SELECT * FROM tblschedule WHERE CID = '$cid' ORDER BY Date ASC, Time ASC";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                <td>{$row['Date']}</td>
                                <td>{$row['Time']}</td>
                                <td>{$row['Dtls']}</td>
                                <td>{$row['CID']}</td>
                            </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No schedules found for your coach</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>Coach not assigned to this player</td></tr>";
                        }

                        $conn->close();
                        ?>

                  </tbody>
              </table>
          </div>
      </div>
  </body>

  </html>