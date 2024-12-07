<?php
session_start();

// Database connection details
$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "denr";

// Create MySQLi connection
$conn = new mysqli($servername, $db_username, $db_password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variables
$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];

    // Check if the username exists in the database
    $stmt = $conn->prepare("SELECT userId FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a reset token
        $reset_token = bin2hex(random_bytes(16));
        $stmt->bind_result($user_id);
        $stmt->fetch();

        // Save the reset token in the database
        $update_stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE userId = ?");
        $update_stmt->bind_param('si', $reset_token, $user_id);
        $update_stmt->execute();

        // Optionally redirect to the reset password form with the token
        header("Location: reset_password.php?token=$reset_token");
        exit;
    } else {
        // Set error message for toast
        $message = "No user found with that username.";
        $messageType = "danger";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <form action="forgot_password.php" method="POST">
            <div class="form-group">
                <label for="username">Enter your username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <button type="submit" id="request_reset" class="btn btn-primary">Request Reset</button>
        </form>
    </div>

    <!-- Bootstrap Toast -->
    <div class="toast" id="errorToast" style="position: absolute; top: 20px; right: 20px;" data-autohide="true" data-delay="5000">
        <div class="toast-header">
            <strong class="mr-auto text-primary">Notification</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body">
            <?php echo $message; ?>
        </div>
    </div>

    <script>
    // JavaScript to show a confirmation dialog
    document.getElementById('request_reset').addEventListener('click', function(event) {
        // Show confirmation dialog
        var confirmUpdate = confirm("Are you sure you want to request reset token?");
        
        // If the user clicks "Cancel", prevent the form from submitting
        if (!confirmUpdate) {
            event.preventDefault();
        }
    });

    // Show toast if there's a message
    $(document).ready(function() {
        <?php if ($messageType == "danger"): ?>
            $('#errorToast').toast('show');
        <?php endif; ?>
    });
    </script>
</body>
</html>
