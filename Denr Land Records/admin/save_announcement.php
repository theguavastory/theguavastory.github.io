<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

// Database connection setup
$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "denr";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form input
    $title = $_POST['announcementTitle'];
    $content = $_POST['announcementContent'];

    // Prepare and execute the insert statement
    $stmt = $conn->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);

    if ($stmt->execute()) {
        // Redirect to admin dashboard with a success message
        header("Location: index.php?message=Announcement posted successfully");
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
