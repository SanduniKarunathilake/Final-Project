<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'legacy_db';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userInput = $_POST['username'];
        $passwordInput = $_POST['password'];

        // First try coach login
        $stmt = $conn->prepare("SELECT CID, Name, Sport, Status FROM tblcoach WHERE CID = ? AND pwd = ?");
        $stmt->bind_param("ss", $userInput, $passwordInput);
        $stmt->execute();
        $coachResult = $stmt->get_result();

        if ($coachResult->num_rows === 1) {
            $coach = $coachResult->fetch_assoc();

            // Check if coach is active
            if ($coach['Status'] === 'Inactive') {
                header("Location: login4.html?error=You have been removed from the system. Please contact administrator.");
                exit();
            }

            // Set coach session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $coach['CID'];
            $_SESSION['name'] = $coach['Name'];
            $_SESSION['sport'] = $coach['Sport'];
            $_SESSION['role'] = 'coach';
            $_SESSION['status'] = $coach['Status'];

            header("Location: Coach_Schedule.php");
            exit();
        }

        // If not a coach, try admin login
        $stmt = $conn->prepare("SELECT AID, Name, Type FROM tbladmin WHERE AID = ? AND pwd = ?");
        $stmt->bind_param("ss", $userInput, $passwordInput);
        $stmt->execute();
        $adminResult = $stmt->get_result();

        if ($adminResult->num_rows === 1) {
            $admin = $adminResult->fetch_assoc();

            // Check if admin is active
            if ($admin['Type'] === 'Inactive') {
                header("Location: login4.html?error=Your admin account has been deactivated.");
                exit();
            }

            // Set admin session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $admin['AID'];
            $_SESSION['name'] = $admin['Name'];
            $_SESSION['role'] = 'admin';
            $_SESSION['status'] = $admin['Type'];

            header("Location: AdminDash.php");
            exit();
        }

        // If not a coach or admin, try donor/sponsor login
        $stmt = $conn->prepare("SELECT SDID, Name FROM tbl_sponsor_donor WHERE SDID = ? AND pwd = ?");
        $stmt->bind_param("ss", $userInput, $passwordInput);
        $stmt->execute();
        $donorResult = $stmt->get_result();

        if ($donorResult->num_rows === 1) {
            $donor = $donorResult->fetch_assoc();

            // Set donor session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $donor['SDID'];
            $_SESSION['name'] = $donor['Name'];
            $_SESSION['role'] = 'donor';  // Changed from 'admin' to 'donor'

            header("Location: donsponProfile.php");
            exit();
        }

        // If not a coach or admin, donor/sponsor try player  login
        $stmt = $conn->prepare("SELECT PID, Name FROM tblplayer WHERE PID = ? AND pwd = ?");
        $stmt->bind_param("ss", $userInput, $passwordInput);
        $stmt->execute();
        $playerResult = $stmt->get_result();

        if ($playerResult->num_rows === 1) {
            $player = $playerResult->fetch_assoc();

            // Set donor session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $player['PID'];
            $_SESSION['name'] = $player['Name'];
            $_SESSION['role'] = 'player';  // Changed from 'admin' to 'donor'

            header("Location:submit_schedule.php");
            exit();
        }

        // If neither coach nor admin credentials matched
        header("Location: login4.html?error=Invalid credentials");
        exit();
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
