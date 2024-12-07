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

// Fetch notification count
$sql_count = "SELECT COUNT(*) AS count FROM notifications";
$result_count = $conn->query($sql_count);
$notification_count = 0;
if ($result_count->num_rows > 0) {
    $row_count = $result_count->fetch_assoc();
    $notification_count = $row_count['count'];
}

// Query to count the total approval requests
$query = "SELECT COUNT(*) as totalApprovalRequests FROM notifications"; // Adjust the table name accordingly
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$totalApprovalRequests = $row['totalApprovalRequests'];

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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'style.php'; ?>
</head>
<body onload="initializeMap()">
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



        <div class="dashboard">
        <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Dashboard</h3>
        <!-- Write Announcement Icon with Text in Custom Blue -->
        <a href="#" class="btn d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#announcementModal" title="Write Announcement" style="color: #007bff; border: 1px solid #007bff; padding: 5px 10px;">
            <i class="fas fa-edit me-2"></i>
            <span>Write Announcement</span>
        </a>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">DENR-CENRO Santa Cruz, Laguna</h5>
                    <div class="map-container" style="position: relative;">
                        <a href="#" class="btn btn-primary" onclick="resetMapLocation()" title="Home" style="position: absolute; top: 10px; right: 10px; z-index: 1000;">
                            <i class="fas fa-home"></i> <!-- Home icon -->
                        </a>
                        <div id="map" style="height: 280px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Start of the widget section -->
        <div class="col-md-3">
            <!-- Total Approval Requests Widget -->
            <a href="notifications.php" class="text-decoration-none" onclick="return confirmNavigation('notifications.php');">
            <div class="card-dashboard border-primary shadow-sm mb-3" style="border-radius: 8px;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon bg-primary text-white rounded-circle sm-3" style="width: 40px; height: 40px; display: flex; justify-content: center; align-items: center;">
                            <i class="fas fa-check-circle fa-lg"></i> <!-- FontAwesome check icon -->
                        </div>
                        <h5 class="card-title mb-0">Total for Approval Requests</h5>
                    </div>
                    <p class="card-text display-4 text-center mt-3">
                        <?php
                        // Query to count the total approval requests
                        $sql_total_approval_requests = "SELECT COUNT(*) as totalApprovalRequests FROM notifications"; // Adjust the table name accordingly
                        $result_total_approval_requests = $conn->query($sql_total_approval_requests);
                        
                        if ($result_total_approval_requests && $row = $result_total_approval_requests->fetch_assoc()) {
                            echo htmlspecialchars($row['totalApprovalRequests']);
                        } else {
                            echo "0"; // Default value if no requests found or query fails
                        }
                        ?>
                    </p>
                </div>
                </a>
            </div>

            <!-- Total Verified Land Titles Widget -->
            <a href="verified_land_list.php" class="text-decoration-none" onclick="return confirmNavigation('verified_land_list.php');">
            <div class="card-dashboard border-success shadow-sm" style="border-radius: 8px;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon bg-success text-white rounded-circle sm-3" style="width: 40px; height: 40px; display: flex; justify-content: center; align-items: center;">
                            <i class="fas fa-map-marked-alt fa-lg"></i> <!-- FontAwesome map icon -->
                        </div>
                        <h5 class="card-title mb-0">Total for Verified Land Titles</h5>
                    </div>
                    <p class="card-text display-4 text-center mt-3">
                        <?php
                        // Query to count the total verified land titles
                        $sql_total_verified_land = "SELECT COUNT(*) as totalVerifiedLand FROM verified_land WHERE archive_status = '0'"; // Adjust if necessary
                        $result_total_verified_land = $conn->query($sql_total_verified_land);
                        
                        if ($result_total_verified_land && $row = $result_total_verified_land->fetch_assoc()) {
                            echo htmlspecialchars($row['totalVerifiedLand']);
                        } else {
                            echo "0"; // Default value if no records found or query fails
                        }
                        ?>
                    </p>
                </div>
            </div>
            </a>
        </div>
        <!-- End of the widget section -->

        <div class="col-md-5">
            <div class="card-dashboard">
                <div class="card-body">
                    <h5 class="card-title">Land Category for Verified Land Titles</h5>
                    <div class="chart-container" style="height: 285px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card-dashboard">
                <div class="card-body">
                    <h5 class="card-title">Municipality Distribution for Verified Land Titles</h5>
                    <div class="chart-container" style="height: 285px;">
                        <canvas id="municipalityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Write Announcement Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="announcementModalLabel">Write Announcement</h5>
            </div>
            <div class="modal-body">
                <form id="announcementForm" action="save_announcement.php" method="POST">
                    <!-- Title Field -->
                    <div class="mb-3">
                        <label for="announcementTitle" class="form-label">Announcement Title</label>
                        <input type="text" class="form-control" id="announcementTitle" name="announcementTitle" placeholder="Enter title here..." required>
                    </div>
                    
                    <!-- Content Field -->
                    <div class="mb-3">
                        <label for="announcementContent" class="form-label">Announcement Content</label>
                        <textarea class="form-control" id="announcementContent" name="announcementContent" rows="5" placeholder="Write your announcement here..." required></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background-color: #007bff; border-color: #007bff;">Post Announcement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


    
    <!-- Full version of jQuery (for Bootstrap 5 compatibility) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Popper.js (required for Bootstrap 5 tooltips and popovers) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<!-- Bootstrap 5 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
// Function to confirm navigation
function confirmNavigation(url) {
    var confirmUpdate = confirm("Are you sure you want to navigate to this page?");
    if (confirmUpdate) {
        window.location.href = url; // If confirmed, navigate to the URL
    }
    return false; // Prevent the default anchor click behavior
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
    <!-- PHP to fetch data and convert to JSON -->
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "denr";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sqlStatus = "SELECT status, COUNT(*) as count FROM verified_land GROUP BY status";
    $resultStatus = $conn->query($sqlStatus);

    // Define the default status counts
    $statusCounts = [
        'SA (Survey Authority)' => 0,
        'RSA (Request for Survey Authority)' => 0,
        'No PLA (No Public Land Application)' => 0,
        'FPA (Free Patent Application)' => 0,
        'RFPA (Request for Free Patent Application)' => 0
    ];

    if ($resultStatus->num_rows > 0) {
        while ($row = $resultStatus->fetch_assoc()) {
            $status = $row['status'];
            if (isset($statusCounts[$status])) {
                $statusCounts[$status] = (int)$row['count'];
            }
        }
    }
        $sqlMunicipality = "SELECT municipality, COUNT(*) as count FROM verified_land GROUP BY municipality";
        $resultMunicipality = $conn->query($sqlMunicipality);

        $municipalityData = [];
        if ($resultMunicipality->num_rows > 0) {
            while ($row = $resultMunicipality->fetch_assoc()) {
                $municipalityData[$row['municipality']] = $row['count'];
            }
        }

        $conn->close();

        $statusCountsJSON = json_encode(array_values($statusCounts));
        $municipalityLabelsJSON = json_encode(array_keys($municipalityData));
        $municipalityCountsJSON = json_encode(array_values($municipalityData));
    ?>

    <!-- JavaScript code including Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <script>
 var statusChart = document.getElementById('statusChart').getContext('2d');
var myStatusChart = new Chart(statusChart, {
    type: 'doughnut', // Using 'doughnut' for a sleeker look
    data: {
        labels: [
            'Survey Authority (SA)', 
            'Request for Survey Authority (RSA)', 
            'No Public Land Application (No PLA)', 
            'Free Patent Application (FPA)', 
            'Request for Free Patent (RFPA)'
        ],
        datasets: [{
            label: 'Status of Land Titles',
            data: <?php echo $statusCountsJSON; ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',  // SA
                'rgba(75, 192, 192, 0.7)',  // RSA
                'rgba(255, 206, 86, 0.7)',  // No PLA
                'rgba(54, 162, 235, 0.7)',  // FPA
                'rgba(153, 102, 255, 0.7)'  // RFPA
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',   // SA
                'rgba(75, 192, 192, 1)',   // RSA
                'rgba(255, 206, 86, 1)',   // No PLA
                'rgba(54, 162, 235, 1)',   // FPA
                'rgba(153, 102, 255, 1)'   // RFPA
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 15,
                    padding: 10,
                    font: {
                        size: 10,
                        weight: 'bold'
                    },
                    color: '#333' // Darker label color for contrast
                }
            },
            tooltip: {
                enabled: true,
                backgroundColor: 'rgba(0, 0, 0, 0.7)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#fff',
                borderWidth: 1,
                callbacks: {
                    label: function(context) {
                        var label = context.label || '';
                        var value = context.raw || 0;
                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                        var percentage = ((value / total) * 100).toFixed(2);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: {
                    size: 14,
                    weight: 'bold'
                },
                formatter: function(value, context) {
                    var total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                    var percentage = ((value / total) * 100).toFixed(1);
                    return `${value} (${percentage}%)`;  // Show count and percentage
                },
                anchor: 'center',
                align: 'center',
                backgroundColor: 'rgba(0, 0, 0, 0.5)',
                borderRadius: 5,
                padding: 6
            }
        },
        animation: {
            animateScale: true, // Scales smoothly
            animateRotate: true // Rotates smoothly
        },
        layout: {
            padding: {
                top: 20,
                bottom: 20
            }
        }
    }
});



var municipalityChart = document.getElementById('municipalityChart').getContext('2d');
var myMunicipalityChart = new Chart(municipalityChart, {
    type: 'bar',
    data: {
        labels: <?php echo $municipalityLabelsJSON; ?>,
        datasets: [{
            label: 'Municipality Distribution',
            data: <?php echo $municipalityCountsJSON; ?>,
            backgroundColor: function(context) {
                // Generate color based on the index for a diverse palette
                const colors = [
                    'rgba(255, 99, 132, 0.6)', 
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)'
                ];
                return colors[context.dataIndex % colors.length];
            },
            borderColor: function(context) {
                // Generate matching border color with higher opacity
                const colors = [
                    'rgba(255, 99, 132, 1)', 
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ];
                return colors[context.dataIndex % colors.length];
            },
            borderWidth: 1.5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 14,
                        weight: 'bold'
                    },
                    color: '#333'
                }
            },
            tooltip: {
                enabled: true,
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#ddd',
                borderWidth: 1,
                callbacks: {
                    label: function(context) {
                        const value = context.raw;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(2);
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 12,
                        weight: 'bold'
                    },
                    color: '#555',
                    maxRotation: 45, // Angle labels to fit better
                    minRotation: 45
                },
            
            },
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    font: {
                        size: 12,
                        weight: 'bold'
                    },
                    color: '#555'
                },
                title: {
                    display: true,
                    text: 'Count',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeOutBounce'
        }
    }
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
    return false; // Prevent default action
}
</script>
</body>
</html>
