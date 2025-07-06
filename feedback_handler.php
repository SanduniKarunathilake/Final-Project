<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['player_id'])) {
    header("Location: login4.php");
    exit();
}

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "legacy_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get feedback data
$feedbackData = [];
if (isset($_GET['coach_id']) && isset($_GET['session_date'])) {
    $coachId = $_GET['coach_id'];
    $sessionDate = $_GET['session_date'];
    
    $stmt = $conn->prepare("SELECT * 
    $stmt->bind_param("ss", $coachId, $sessionDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $feedbackData = $result->fetch_assoc();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Feedback</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f1e9;
            margin: 0;
            padding: 0;
            color: #5a4a42;
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #f9f3ee;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(149, 117, 89, 0.1);
            border: 1px solid #e0d1c5;
        }
        
        h2 {
            color: #8b6b4a;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }
        
        .feedback-header {
            background-color: #e6ccb2;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .feedback-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .feedback-meta div {
            background-color: #f0e5d8;
            padding: 8px 12px;
            border-radius: 4px;
            color: #7a5c44;
        }
        
        .feedback-content {
            background-color: #fdfaf7;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #d4c4b8;
            min-height: 150px;
            color: #5a4a42;
            line-height: 1.6;
        }
        
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #b89b84;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #a08670;
        }
        
        .btn-secondary {
            background-color: #f0e6dd;
            color: #7a5c44;
            border: 1px solid #d4c4b8;
        }
        
        .btn-secondary:hover {
            background-color: #e0d1c5;
        }
        
        .no-feedback {
            text-align: center;
            padding: 30px;
            color: #7a5c44;
            font-style: italic;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-comment-alt"></i> Coach Feedback</h2>
        
        <?php if (!empty($feedbackData)): ?>
            <div class="feedback-header">
                <div class="feedback-meta">
                    <div>
                        <i class="fas fa-user-tie"></i> 
                        <strong>Coach:</strong> <?php echo htmlspecialchars($feedbackData['CoachName']); ?>
                    </div>
                    <div>
                        <i class="fas fa-calendar-day"></i> 
                        <strong>Session Date:</strong> <?php echo htmlspecialchars($feedbackData['session_date']); ?>
                    </div>
                </div>
            </div>
            
            <div class="feedback-content">
                <?php echo nl2br(htmlspecialchars($feedbackData['feedback_text'])); ?>
            </div>
        <?php else: ?>
            <div class="no-feedback">
                <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                <p>No feedback available for this session.</p>
            </div>
        <?php endif; ?>
        
        <div class="btn-group">
            <a href="submit_schedule.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Schedule
            </a>
            <?php if (!empty($feedbackData)): ?>
                <a href="#" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Feedback
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>