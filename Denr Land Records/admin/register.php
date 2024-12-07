<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
$servername = "localhost";
$username = "root";
$password = "";
$database = "denr";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (
    isset($_POST['username']) && isset($_POST['usertype']) && 
    isset($_POST['password']) && isset($_POST['first_name']) && 
    isset($_POST['last_name']) && isset($_POST['middle_name']) && 
    isset($_POST['contact_number']) && isset($_POST['email'])
) {
    $username = $_POST['username'];
    $userType = $_POST['usertype'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $userFirstName = $_POST['first_name'];
    $userLastName = $_POST['last_name'];
    $userMiddleName = $_POST['middle_name'];
    $userContactNumber = $_POST['contact_number'];
    $userEmail = $_POST['email'];

    // Insert the new user into the database with hashed password
    $stmt = $conn->prepare("INSERT INTO users (username, userType, userPassword, userLastName, userFirstName, userMiddleName, userContactNumber, userEmail) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $username, $userType, $password, $userLastName, $userFirstName, $userMiddleName, $userContactNumber, $userEmail);
    
    if ($stmt->execute()) {
        echo json_encode(array("success" => true));
    } else {
        echo json_encode(array("success" => false, "message" => $stmt->error));
    }

    $stmt->close();
} else {
    echo json_encode(array("success" => false, "message" => "All fields are required"));
}

$conn->close();
?>
