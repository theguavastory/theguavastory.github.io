<?php
session_start();
if (!isset($_SESSION['userId'])) {
    exit(json_encode(['success' => false, 'message' => 'No session found.']));
}

$userId = $_SESSION['userId'];
if (!isset($_POST['contactId']) || !isset($_POST['message'])) {
    exit(json_encode(['success' => false, 'message' => 'Required data not provided.']));
}

$contactId = $_POST['contactId'];
$messageContent = trim($_POST['message']);

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "denr";

$conn = new mysqli($servername, $db_username, $db_password, $database);
if ($conn->connect_error) {
    exit(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Insert the new message into the messages table
$sql = "INSERT INTO messages (senderId, receiverId, messageContent, messageTime) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $userId, $contactId, $messageContent);

$response = ['success' => false];
if ($stmt->execute()) {
    // Fetch the contact's full name
    $sql_contact = "SELECT userFirstName, userLastName, userMiddleName FROM users WHERE userId = ?";
    $stmt_contact = $conn->prepare($sql_contact);
    $stmt_contact->bind_param("i", $contactId);
    $stmt_contact->execute();
    $result_contact = $stmt_contact->get_result();

    if ($contact = $result_contact->fetch_assoc()) {
        $fullName = htmlspecialchars($contact['userLastName'] . ', ' . $contact['userFirstName'] . ' ' . $contact['userMiddleName']);
        $response['fullName'] = $fullName; // Add full name to the response
    }

    $response['success'] = true;
    $response['message'] = 'Message sent successfully.';
} else {
    $response['message'] = 'Error: ' . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Return a JSON response
echo json_encode($response);
?>
