<?php
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

$username = $_SESSION['username'];
$userId = $_SESSION['userId'];

// Database connection
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

// Fetch notification count
$sql_count = "SELECT COUNT(*) AS count FROM notifications";
$result_count = $conn->query($sql_count);
$notification_count = 0;
if ($result_count->num_rows > 0) {
    $row_count = $result_count->fetch_assoc();
    $notification_count = $row_count['count'];
}

// Fetch unread message count
$sql_unread_message_count = "SELECT COUNT(*) AS count FROM messages WHERE (receiverId = ? AND isRead = FALSE)";
$stmt_unread_message_count = $conn->prepare($sql_unread_message_count);
$stmt_unread_message_count->bind_param("i", $userId);
$stmt_unread_message_count->execute();
$result_unread_message_count = $stmt_unread_message_count->get_result();

$unread_message_count = 0;
if ($result_unread_message_count->num_rows > 0) {
    $row_unread_message_count = $result_unread_message_count->fetch_assoc();
    $unread_message_count = $row_unread_message_count['count'];
}

$stmt_unread_message_count->close(); // Close the statement after fetching the count
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR-CENRO: Record Management for Verified Land Titles</title>
   <!-- Bootstrap 4 CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'user_style.php'; ?>

</head>

    <?php include 'user_sidebar.php'; ?>

    <?php include 'user_navbar.php'; ?>
    
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="user_search_results.php" class="form-inline">
                <div class="input-group w-100">
                    <input type="text" name="query" class="form-control" placeholder="Search by lot number, applicant, survey claimant, status, municipality, barangay...">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="inbox" id="inbox">
    <div class="messages-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Inbox</h3>
        </div>
        <div class="inbox-content">
            <?php
            // Fetch distinct conversations by latest message time
            $sql_inbox = "SELECT DISTINCT receiverId, senderId, MAX(messageTime) AS latestMessageTime, messageContent 
                          FROM messages 
                          WHERE senderId = ? OR receiverId = ? 
                          GROUP BY CASE WHEN senderId = ? THEN receiverId ELSE senderId END 
                          ORDER BY latestMessageTime DESC";

            $stmt_inbox = $conn->prepare($sql_inbox);
            $stmt_inbox->bind_param("iii", $userId, $userId, $userId);
            $stmt_inbox->execute();
            $result_inbox = $stmt_inbox->get_result();

            if ($result_inbox->num_rows > 0) {
                while ($conversation = $result_inbox->fetch_assoc()) {
                    $contactId = ($conversation['senderId'] == $userId) ? $conversation['receiverId'] : $conversation['senderId'];
                    
                    // Fetch contact's name
                    $contactSql = "SELECT userLastName, userFirstName, userMiddleName FROM users WHERE userId = ?";
                    $contactStmt = $conn->prepare($contactSql);
                    $contactStmt->bind_param("i", $contactId);
                    $contactStmt->execute();
                    $contactResult = $contactStmt->get_result();
                    $contact = $contactResult->fetch_assoc();
                    
                    // Full name of contact
                    $fullName = htmlspecialchars($contact['userLastName'] . ', ' . $contact['userFirstName'] . ' ' . $contact['userMiddleName']);
                    $lastMessage = htmlspecialchars($conversation['messageContent']);
                    
                    echo "<div class='conversation-item d-flex justify-content-between align-items-center' 
                           data-toggle='modal' data-target='#conversationModal' 
                           data-contactid='$contactId'>
                            <div>
                                <strong>$fullName</strong>
                                <div class='last-message'>$lastMessage</div>
                            </div>
                            <div class='text-right'>
                                <span class='message-time'>" . htmlspecialchars($conversation['latestMessageTime']) . "</span>
                            </div>
                          </div>";
                    
                    $contactStmt->close();
                }
            } else {
                echo "<p>No conversations found.</p>";
            }

            $stmt_inbox->close();
            ?>
        </div>
    </div>
</div>

<!-- Modal for viewing conversation -->
<div class="modal fade" id="conversationModal" tabindex="-1" role="dialog" aria-labelledby="conversationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="conversationModalLabel">Conversation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="conversationMessageDisplay" class="message-display">
                    <!-- Messages will be loaded here -->
                </div>
            </div>
            <div class="modal-footer message-input-footer">
                <input type="text" id="conversationMessageInput" class="form-control" placeholder="Type a message...">
                <button type="button" class="btn btn-primary" id="conversationSendMessageButton">Send</button>
            </div>
        </div>
    </div>
</div>



 <!-- jQuery (for Bootstrap 4) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Popper.js (necessary for Bootstrap's dropdowns and tooltips) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>
<!-- Bootstrap 4 JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Chart.js Data Labels Plugin -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentContactId = null;

    // Handle click on conversation items
    $(document).on('click', '.conversation-item', function() {
        currentContactId = $(this).data('contactid');
        $('#conversationMessageDisplay').html("Loading messages...");

        // Fetch messages for selected conversation
        $.get('fetch_conversations.php', { contactId: currentContactId }, function(data) {
            $('#conversationMessageDisplay').html(data);

            // Use setTimeout to ensure scrolling after rendering
            setTimeout(scrollToBottom, 50); // Short delay for scrolling to work consistently
        });
    });

    // Handle sending new messages
    document.getElementById("conversationSendMessageButton").addEventListener("click", function() {
        const messageInput = document.getElementById("conversationMessageInput");
        const messageContent = messageInput.value.trim();
        const contactId = currentContactId;

        if (!messageContent || !contactId) {
            alert("Please select a contact and enter a message.");
            return;
        }

        fetch("send_message.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                contactId: contactId,
                message: messageContent
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const messageDisplay = document.getElementById("conversationMessageDisplay");
                const newMessage = document.createElement("div");
                newMessage.className = "message-container sent";
                
                const currentTime = new Date();
                const formattedTime = currentTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

                newMessage.innerHTML = `
                    <div class="message-bubble"><strong>You:</strong> ${messageContent}</div>
                    <div class="message-time">${formattedTime}</div>
                `;

                messageDisplay.appendChild(newMessage);
                scrollToBottom();  // Scroll to the latest message after sending
                messageInput.value = "";  // Clear input field
            } else {
                alert("Failed to send message: " + data.message);
            }
        })
        .catch(error => console.error("Error sending message:", error));
    });

    // Function to scroll to the bottom of the message display
    function scrollToBottom() {
        const messageDisplay = document.getElementById('conversationMessageDisplay');
        messageDisplay.scrollTop = messageDisplay.scrollHeight;
    }

    // Ensure scroll to bottom when modal is shown
    $('#conversationModal').on('shown.bs.modal', function () {
        scrollToBottom();
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sendMessageForm = document.getElementById('sendMessageForm');
    sendMessageForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        const messageInput = document.getElementById('messageInput');
        const contactId = document.getElementById('recipient').value; // Get selected contactId from the dropdown
        const messageContent = messageInput.value.trim(); // Get the message from the input

        if (messageContent && contactId) {
            sendMessage(contactId, messageContent); // Use the selected contactId and message
        } else {
            alert("Please select a contact and enter a message.");
        }
    });

    // Function to send a message
    function sendMessage(contactId, messageContent) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'send_message.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    document.getElementById('messageInput').value = ''; // Clear the input field after sending the message
                    alert('Message sent successfully!');
                    displaySentMessage(contactId, messageContent); // Display the sent message
                    // Optionally, you might want to reload the conversation here
                    loadConversation(contactId); // Reload the conversation (if necessary)
                } else {
                    alert("Error sending message: " + response.message);
                }
            } else {
                console.error('Error sending message:', xhr.statusText);
            }
        };
        xhr.send('contactId=' + contactId + '&message=' + encodeURIComponent(messageContent));
    }

    // Function to display the sent message immediately
    function displaySentMessage(contactId, messageContent) {
        const messageDisplay = document.getElementById('conversationMessageDisplay');
        const newMessage = document.createElement("div");
        newMessage.className = "message-container sent";
        newMessage.innerHTML = `
            <div class="message-bubble"><strong>You:</strong> ${htmlspecialchars(messageContent)}</div>
            <div class="message-time">${new Date().toLocaleTimeString()}</div>
        `;
        messageDisplay.appendChild(newMessage);
        scrollToBottom(); // Scroll to the bottom to show the new message
    }

    // Function to scroll to the bottom of the message display
    function scrollToBottom() {
        const messageDisplay = document.getElementById('conversationMessageDisplay');
        messageDisplay.scrollTop = messageDisplay.scrollHeight; // Scroll to the bottom
    }

    // Filtering recipients in the dropdown
    const recipientSearch = document.getElementById('recipientSearch');
    recipientSearch.addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const options = document.getElementById('recipient').options;

        for (let i = 0; i < options.length; i++) {
            const optionText = options[i].text.toLowerCase();
            options[i].style.display = optionText.includes(searchValue) ? '' : 'none'; // Show or hide option
        }
    });
});
</script>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('show');
}
</script>

   <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = 'logout.php';
            }
            return false;
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
        }
    </script>
</body>
</html>
