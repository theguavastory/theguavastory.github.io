<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "denr";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify the hashed password
        if (password_verify($password, $row['userPassword'])) {
            $_SESSION["userId"] = $row["userId"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["userLastName"] = $row["userLastName"];
            $_SESSION["usertype"] = $row["userType"];

            if ($row['userType'] == 'Admin') { 
                header('Location: admin/index.php');        // Header
                exit();
            } elseif ($row['userType'] == 'Superuser') {
                header('Location: superuser/superuser_index.php'); // Header
                exit();
            } elseif ($row['userType'] == 'User') {
                header('Location: user/user_index.php');
                exit();
            } else {
                header('Location: login_page.php?error=Unknown user type'); // Header
                exit();
            }
        } else {
            header('Location: login_page.php?error=Invalid password');
            exit();
        }
    } else {
        header('Location: login_page.php?error=Invalid username');
        exit();
    }

    $stmt->close();
} else {
    header('Location: login_page.php?error=Username or password not provided');
    exit();
}

$conn->close();
?>
