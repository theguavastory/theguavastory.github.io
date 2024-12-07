<?php
session_start();
if (!isset($_SESSION['userId'])) {
    exit("No session found."); // No user session
}

$userId = $_SESSION['userId'];
if (!isset($_GET['contactId'])) {
    exit("Contact ID not set."); // Contact ID not provided
}

$contactId = $_GET['contactId'];

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "denr";

$conn = new mysqli($servername, $db_username, $db_password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mark messages as read when fetched
$update_sql = "UPDATE messages SET isRead = TRUE WHERE receiverId = ? AND senderId = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ii", $userId, $contactId);
$update_stmt->execute();
$update_stmt->close();

// Fetch messages between the user and the selected contact
$sql = "SELECT u1.userLastname, u1.userFirstname, u1.userMiddleName, m.messageContent, m.messageTime, m.senderId
        FROM messages m
        JOIN users u1 ON m.senderId = u1.userId
        WHERE (m.senderId = ? AND m.receiverId = ?) OR (m.senderId = ? AND m.receiverId = ?)
        ORDER BY m.messageTime ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $userId, $contactId, $contactId, $userId);
$stmt->execute();
$result_messages = $stmt->get_result();

// Display messages in a chat-like format
if ($result_messages->num_rows > 0) {
    while ($message = $result_messages->fetch_assoc()) {
        $isSentByUser = ($message['senderId'] == $userId);
        $class = $isSentByUser ? 'sent' : 'received';

        // Format the message time to include both date (MM/DD/YYYY) and AM/PM time
        $formattedDateTime = date("m/d/Y, h:i A", strtotime($message['messageTime']));

        // Concatenate the full name
        $fullName = htmlspecialchars(trim($message['userLastname'] . ', ' . $message['userFirstname'] . ' ' . $message['userMiddleName']));

        echo "<div class='message-container $class'>";
        echo "<div class='message-bubble'><strong>$fullName:</strong> " . htmlspecialchars($message['messageContent']) . "</div>";
        echo "<div class='message-time'>" . htmlspecialchars($formattedDateTime) . "</div>"; // Updated line
        echo "</div>";
    }
} else {
    echo "<p>No messages yet.</p>";
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
