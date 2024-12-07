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

// Check if token is provided
if (isset($_GET['token'])) {
    $reset_token = $_GET['token'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_password = $_POST['new_password'];

        // Validate the reset token and update the password in the database
        $stmt = $conn->prepare("SELECT userId FROM users WHERE reset_token = ?");
        $stmt->bind_param('s', $reset_token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id);
            $stmt->fetch();

            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the user's password and remove the reset token
            $update_stmt = $conn->prepare("UPDATE users SET userPassword = ?, reset_token = NULL WHERE userId = ?");
            $update_stmt->bind_param('si', $hashed_password, $user_id);
            $update_stmt->execute();

            echo "Password has been reset successfully!";

            header("Location: login_page.php");
            exit;
        } else {
            echo "Invalid reset token.";
        }
    }
} else {
    echo "No reset token provided.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <form action="reset_password.php?token=<?php echo htmlspecialchars($_GET['token']); ?>" method="POST">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password"  name="new_password" class="form-control" required>
            </div>
            <button type="submit" id="new_password" class="btn btn-primary">Reset Password</button>
        </form>
    </div>


    <script>
    // JavaScript to show a confirmation dialog
    document.getElementById('new_password').addEventListener('click', function(event) {
        // Show confirmation dialog
        var confirmUpdate = confirm("Are you sure you want to change your password?");
        
        // If the user clicks "Cancel", prevent the form from submitting
        if (!confirmUpdate) {
            event.preventDefault();
        }
    });
</script>
</body>
</html>
