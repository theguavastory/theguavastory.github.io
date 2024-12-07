<?php
$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "denr";

$conn = new mysqli($servername, $db_username, $db_password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_POST['userId'];
$username = $_POST['username'];
$userType = $_POST['usertype'];
$userFirstName = $_POST['first_name'];
$userLastName = $_POST['last_name'];
$userMiddleName = $_POST['middle_name'];
$userContactNumber = $_POST['contact_number'];
$userEmail = $_POST['email'];

$sql = "UPDATE users SET username = ?, userType = ?, userFirstName = ?, userLastName = ?, userMiddleName = ?, userContactNumber = ?, userEmail = ? WHERE userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssi", $username, $userType, $userFirstName, $userLastName, $userMiddleName, $userContactNumber, $userEmail, $userId);

$response = array('success' => false);

if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['message'] = "Update failed: " . $stmt->error;
}

echo json_encode($response);

$conn->close();
?>
