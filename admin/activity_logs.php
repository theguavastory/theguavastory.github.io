<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

// Get the username from the session
$username = $_SESSION['username'];
$userId = $_SESSION['userId'];

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "denr";

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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <?php include 'style.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>

<?php include 'navbar.php'; ?>

        <div class="search-bar">
            <form method="GET" action="search_results.php" class="form-inline">
                <div class="input-group w-100">
                    <input type="text" name="query" class="form-control" placeholder="Search by lot number, applicant, survey claimant, status, municipality, barangay...">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container">
        <h3>Activity Logs</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>User Type</th>
                    <th>Username</th>
                    <th>Time</th>
                    <th>Date</th>
                    <th>Recent Activity</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_fetch_logs = "SELECT userId, userType, username, 
                                    DATE_FORMAT(activity_time, '%H:%i:%s') AS time, 
                                    DATE_FORMAT(activity_time, '%Y-%m-%d') AS date, 
                                    activity, userContactNumber, userEmail 
                                    FROM activity_logs ORDER BY activity_time DESC";
                $result_logs = $conn->query($sql_fetch_logs);

                if ($result_logs->num_rows > 0) {
                    while ($row = $result_logs->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['userId'] . "</td>";
                        echo "<td>" . $row['userType'] . "</td>";
                        echo "<td>" . $row['username'] . "</td>";
                        echo "<td>" . $row['time'] . "</td>";
                        echo "<td>" . $row['date'] . "</td>";
                        echo "<td>" . $row['activity'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No logs found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
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
    return false; // Prevent default action
}
</script>
</body>
</html>
