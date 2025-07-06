<?php
session_start();
// Database connection
$con = mysqli_connect("localhost", "root", "", "legacy_db");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch coach data
$coach_data = [];
$result = mysqli_query($con, "SELECT * FROM tblcoach");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $coach_data[] = $row;
    }
}
$player_id = $_SESSION['user_id'];
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Management System</title>
    <style>
        :root {
            --primary-brown: #6D4C41;
            --dark-brown: #4E342E;
            --medium-brown: #8D6E63;
            --light-brown: #D7CCC8;
            --lighter-brown: #EFEBE9;
            --text-light: #FFFFFF;
            --text-dark: #3E2723;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--lighter-brown);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation - Updated to match reference image */
        .sidebar {
            width: 220px;
            background-color: var(--dark-brown);
            padding: 0;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            color: var(--light-brown);
            font-size: 20px;
            font-weight: bold;
            border-bottom: 1px solid var(--medium-brown);
        }

        .nav-menu {
            list-style: none;
            padding: 10px 0;
        }

        .nav-item {
            padding: 12px 20px;
            color: var(--light-brown);
            cursor: pointer;
            transition: all 0.2s;
            font-size: 15px;
            border-left: 4px solid transparent;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .nav-item.active {
            background-color: rgba(0, 0, 0, 0.2);
            border-left: 4px solid var(--light-brown);
            color: white;
            font-weight: 500;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 220px;
            padding: 30px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-brown);
        }

        input[type="text"],
        input[type="date"],
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-brown);
            border-radius: 6px;
            background-color: var(--lighter-brown);
            font-size: 15px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--medium-brown);
        }

        button {
            background-color: var(--primary-brown);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            font-weight: 600;
        }

        button:hover {
            background-color: var(--dark-brown);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-brown);
        }

        th {
            background-color: var(--primary-brown);
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: var(--lighter-brown);
        }

        tr:hover {
            background-color: rgba(141, 110, 99, 0.1);
        }

        /* Tabs */
        .tabs {
            display: flex;
            margin-bottom: 25px;
            border-bottom: 2px solid var(--light-brown);
        }

        .tab {
            padding: 12px 25px;
            cursor: pointer;
            background-color: var(--lighter-brown);
            border: 1px solid var(--light-brown);
            border-bottom: none;
            margin-right: 5px;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            font-weight: 600;
            color: var(--medium-brown);
        }

        .tab:hover {
            background-color: rgba(109, 76, 65, 0.1);
        }

        .tab.active {
            background-color: white;
            border-bottom: 2px solid white;
            margin-bottom: -2px;
            color: var(--primary-brown);
        }

        .tab-content {
            display: none;
            padding: 25px 0;
        }

        .tab-content.active {
            display: block;
        }

        h1 {
            color: var(--primary-brown);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-brown);
        }

        h2 {
            color: var(--medium-brown);
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <!-- Navigation Sidebar - Updated to match reference image -->
    <div class="sidebar">
        <div class="sidebar-header">Player Dashboard</div>
        <ul class="nav-menu">
            
            <a href="playerProfile.php"><li class="nav-item">Profile</li></a>
           
            <a href="tournamentView.php"><li class="nav-item">View Tournaments</li>
            
            
            <a href="logout.php"><li class="nav-item">Logout</li>
        </ul></a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1>Coach Management System</h1>

            <div class="tabs">
                <div class="tab active" onclick="openTab('addCoach')">Add Coach</div>
                <div class="tab" onclick="openTab('viewCoaches')">View Coaches</div>
            </div>

            <div id="addCoach" class="tab-content active">
                <h2>Add New Coach</h2>
                <form id="coachForm" action="process_coach.php" method="post">
                    <div class="form-group">
                        <label for="pid">Player ID:</label>
                        <input type="text" id="pid" name="pid" value="<?= htmlspecialchars($player_id) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="cid">Select Coach:</label>
                        <select id="cid" name="cid" required>
                            <option value="">-- Select a Coach --</option>
                            <?php foreach ($coach_data as $coach): ?>
                                <option value="<?= htmlspecialchars($coach['CID']) ?>">
                                    <?= htmlspecialchars($coach['Name']) ?> - <?= htmlspecialchars($coach['Sport']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="bdate">Booking Date:</label>
                        <input type="date" id="bdate" name="bdate" required>
                    </div>

                    <div class="form-group">
                        <label for="duration">End Date:</label>
                        <input type="date" id="duration" name="duration" required>
                    </div>

                    <button type="submit">Add Coach</button>
                </form>
            </div>

            <div id="viewCoaches" class="tab-content">
                <h2>Coach Details</h2>
                <table id="coachesTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>CID</th>
                            <th>Email</th>
                            <th>Age</th>
                            <th>Qualifications</th>
                            <th>Sport</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coach_data as $coach): ?>
                            <tr data-cid="<?= htmlspecialchars($coach['CID']) ?>">
                                <td><?= htmlspecialchars($coach['Name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($coach['CID'] ?? '') ?></td>
                                <td><?= htmlspecialchars($coach['Email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($coach['Age'] ?? '') ?></td>
                                <td><?= htmlspecialchars($coach['Qualific'] ?? '') ?></td>
                                <td><?= htmlspecialchars($coach['Sport'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($coach_data)): ?>
                            <tr>
                                <td colspan="6">No coach data found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        function openTab(tabName) {
            // Hide all tab contents
            const tabContents = document.getElementsByClassName("tab-content");
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove("active");
            }

            // Remove active class from all tabs
            const tabs = document.getElementsByClassName("tab");
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active");
            }

            // Show the selected tab content and mark its tab as active
            document.getElementById(tabName).classList.add("active");
            event.currentTarget.classList.add("active");
        }

        // Navigation item click handler
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remove active class from all nav items
                document.querySelectorAll('.nav-item').forEach(navItem => {
                    navItem.classList.remove('active');
                });
                // Add active class to clicked item
                this.classList.add('active');
                
                // Here you would typically also load the appropriate content for the clicked nav item
                // For example: window.location.href = this.getAttribute('data-url');
            });
        });
    </script>
</body>
</html>