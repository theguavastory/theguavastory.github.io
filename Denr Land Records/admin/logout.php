<?php
session_start();

// Capture user info before destroying the session
if (isset($_SESSION['username']) && isset($_SESSION['userId']) && isset($_SESSION['usertype'])) {
    $username = $_SESSION['username'];
    $userId = $_SESSION['userId'];
    $user_type = $_SESSION['usertype'];
    $userContactNumber = $_SESSION['contactNumber']; // Assuming it's stored in session
    $userEmail = $_SESSION['email']; // Assuming it's stored in session

    // Database connection
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $database = "denr";

    $conn = new mysqli($servername, $db_username, $db_password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Function to log user activity
    function logActivity($conn, $userId, $userType, $username, $activity, $userContactNumber = null, $userEmail = null) {
        $activity_time = date('Y-m-d H:i:s'); // Capture current timestamp

        $logQuery = $conn->prepare("INSERT INTO activity_logs (userId, userType, username, activity, activity_time, userContactNumber, userEmail) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $logQuery->bind_param("issssss", $userId, $userType, $username, $activity, $activity_time, $userContactNumber, $userEmail);

        if (!$logQuery->execute()) {
            echo "Error inserting into activity_logs table: " . $logQuery->error;
        }

        $logQuery->close();
    }

    // Log the logout activity
    logActivity($conn, $userId, $user_type, $username, "User logged out", $userContactNumber, $userEmail);

    // Close the database connection
    $conn->close();
}

// Unset and destroy the session after logging the activity
session_unset();
session_destroy();

// Redirect to login page
header("Location: ../login_page.php"); // Adjust this path as needed
exit();
?>
