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

$sql = "SELECT * FROM users WHERE userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$response = array('success' => false, 'data' => array());

if ($result->num_rows > 0) {
    $response['success'] = true;
    $response['data'] = $result->fetch_assoc();
} else {
    $response['message'] = "User not found.";
}

echo json_encode($response);

$conn->close();

?>