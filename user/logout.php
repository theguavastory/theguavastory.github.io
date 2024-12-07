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
