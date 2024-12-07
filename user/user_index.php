<?php
session_start();

if(!isset($_SESSION['username']) || empty($_SESSION['username'])) {
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

// Fetch recent activity logs for the logged-in user
$sql_activity_logs = "SELECT activity, activity_time FROM activity_logs WHERE userId = ? AND activity LIKE 'Submitted land title with Lot Number:%' ORDER BY activity_time DESC LIMIT 5"; // Adjust the limit as needed
$stmt_activity_logs = $conn->prepare($sql_activity_logs);
$stmt_activity_logs->bind_param("i", $userId);
$stmt_activity_logs->execute();
$result_activity_logs = $stmt_activity_logs->get_result();

$activities = [];
if ($result_activity_logs->num_rows > 0) {
    while ($row = $result_activity_logs->fetch_assoc()) {
        $activities[] = $row;
    }
}

$stmt_activity_logs->close(); // Close the statement after fetching the logs

// Fetch the latest announcement content
$sql_announcement_details = "SELECT title, content FROM announcements ORDER BY created_at DESC LIMIT 1";
$stmt_announcement_details = $conn->prepare($sql_announcement_details);
$stmt_announcement_details->execute();
$result_announcement_details = $stmt_announcement_details->get_result();

$latest_announcement_content = "No detailed content available."; // Default message if no content
$latest_announcement_title = "No title available."; // Default title

if ($result_announcement_details->num_rows > 0) {
    $row_announcement_details = $result_announcement_details->fetch_assoc();
    $latest_announcement_title = $row_announcement_details['title']; // Get the title
    $latest_announcement_content = $row_announcement_details['content']; // Get the content
}

$stmt_announcement_details->close(); // Close the statement after fetching the content

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR-CENRO: Record Management for Verified Land Titles</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'user_style.php'; ?>
</head>
<body onload="initializeMap()">
<?php include 'user_sidebar.php'; ?>

<?php include 'user_navbar.php'; ?>

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


    <div class="dashboard">
    <h3>Your Feed</h3>
    <div class="row">
    <div class="col-lg-9 col-md-8 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">DENR-CENRO Santa Cruz, Laguna</h5>
                    <div class="map-container" style="position: relative;">
                        <a href="#" class="btn btn-primary" onclick="resetMapLocation()" title="Home" style="position: absolute; top: 10px; right: 10px; z-index: 1000;">
                            <i class="fas fa-home"></i> <!-- Home icon -->
                        </a>
                        <div id="map" style="height: 350px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-12">
            <div class="row mt-4">
                <!-- Messages Card -->
                <div class="col-lg-12 col-md-4 col-sm-12"> <!-- Full width for cards within this column -->
                    <a href="user_messages.php" class="text-decoration-none" title="View Messages">
                        <div class="card border-primary shadow-sm" style="border-radius: 8px; height: 180px;"> <!-- Increased height -->
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon bg-primary text-white rounded-circle sm-3" style="width: 40px; height: 40px; display: flex; justify-content: center; align-items: center;">
                                        <i class="fas fa-comment fa-lg"></i> <!-- FontAwesome comment icon -->
                                    </div>
                                    <h5 class="card-title mb-0">Messages</h5>
                                </div>
                                <p class="card-text text-center mt-3" style="font-size: 0.875em;"> <!-- Smaller text size -->
                                    You have <?php echo $unread_message_count; ?> unread messages.
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

                        <!-- Latest Announcements Card -->
                        <div class="col-lg-12 col-md-4 col-sm-12"> <!-- Full width for cards within this column -->
                    <a class="text-decoration-none" title="View Announcements" data-toggle="modal" data-target="#announcementModal">
                        <div class="card border-success shadow-sm" style="border-radius: 8px; height: 180px;"> <!-- Increased height -->
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon bg-success text-white rounded-circle sm-3" style="width: 40px; height: 40px; display: flex; justify-content: center; align-items: center;">
                                        <i class="fas fa-bullhorn fa-lg"></i> <!-- FontAwesome bullhorn icon -->
                                    </div>
                                    <h5 class="card-title mb-0">Latest Announcements</h5>
                                </div>
                                <p class="card-text text-center mt-3" style="font-size: 0.875em;"> <!-- Smaller text size -->
                                    <?php echo htmlspecialchars($latest_announcement_title); ?>
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4"> <!-- New row for Recent Activity and Quick Poll -->
        <!-- Recent Activity Card -->
        <div class="col-lg-6 col-md-4 col-sm-12"><!-- Keep the same column width as the map -->
            <div class="card border-info shadow-sm" style="border-radius: 8px; height: 250px;"> <!-- Similar styling with increased height -->
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon bg-info text-white rounded-circle sm-3" style="width: 40px; height: 40px; display: flex; justify-content: center; align-items: center; margin-right: 10px;">
                            <i class="fas fa-history fa-lg"></i> <!-- FontAwesome history icon -->
                        </div>
                        <h5 class="card-title mb-0">Recent Activity</h5>
                    </div>
                    <ul class="list-unstyled" style="max-height: 150px; overflow-y: auto;"> <!-- Add scroll if needed -->
                        <?php if (count($activities) > 0): ?>
                            <?php foreach ($activities as $activity): ?>
                                <li><?php echo htmlspecialchars($activity['activity']) . ' on ' . htmlspecialchars($activity['activity_time']); ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No recent activities.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

<!-- Quick Poll -->
<div class="col-lg-6 col-md-4 col-sm-12"> <!-- Adjust the column size to match Recent Activity -->
    <div class="card border-warning shadow-sm" style="border-radius: 8px; height: 250px;"> <!-- Similar styling with increased height -->
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="icon bg-warning text-white rounded-circle sm-3" style="width: 40px; height: 40px; display: flex; justify-content: center; align-items: center; margin-right: 10px;">
                    <i class="fas fa-poll fa-lg"></i> <!-- FontAwesome poll icon -->
                </div>
                <h5 class="card-title mb-0">Quick Poll</h5>
            </div>
            <p class="card-text" style="font-size: 0.875em;">How satisfied are you with the current land title processing system?</p>
            <form method="POST" action="submit_poll.php">
                <div>
                    <input type="radio" name="satisfaction" value="Very Satisfied"> Very Satisfied<br>
                    <input type="radio" name="satisfaction" value="Satisfied"> Satisfied<br>
                    <input type="radio" name="satisfaction" value="Neutral"> Neutral<br>
                    <input type="radio" name="satisfaction" value="Dissatisfied"> Dissatisfied<br>
                </div>
                <div class="text-right mt-1"> <!-- Adjusted margin to move the button higher -->
                    <button type="submit" class="btn btn-warning" style="font-size: 0.875em;">
                        <i class="fas fa-paper-plane"></i> Submit <!-- Added icon to the button -->
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Announcements -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="announcementModalLabel"><?php echo htmlspecialchars($latest_announcement_title); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><?php echo htmlspecialchars($latest_announcement_content); ?></p>
                <!-- You can add more details about the announcement here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

     
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    function setAnnouncementDetails(title, content) {
        // Set the announcement title and content in the modal
        document.getElementById('announcementTitle').textContent = title;
        document.getElementById('announcementContent').textContent = content;
    }
</script>
    <script>
    function setAnnouncementDetails(title, content) {
        // Set the announcement title and content in the modal
        document.getElementById('announcementTitle').textContent = title;
        document.getElementById('announcementContent').textContent = content;
    }
</script>
    <script>
    var map;
        var targetLocation = [14.251012686850501, 121.38095706488579]; // Set your target location coordinates

        function initializeMap() {
            map = L.map('map').setView(targetLocation, 14); // Initialize map with target location
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
            }).addTo(map);

            // Add a marker for the specific location
            L.marker(targetLocation).addTo(map)
                .bindPopup('DENR-CENRO Santa Cruz, Laguna')
                .openPopup();
        }

        function resetMapLocation() {
            map.setView(targetLocation, 14); // Reset the map view to the target location
        }
</script>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('show');
}
</script>

</script>

    <!-- JavaScript code including Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0/dist/chartjs-plugin-datalabels.min.js"></script>

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
