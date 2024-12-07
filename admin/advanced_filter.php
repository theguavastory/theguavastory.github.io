<?php
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "denr";

$conn = new mysqli($servername, $db_username, $db_password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user ID
$userId = $_SESSION['userId'];

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

$results = []; // Array to store the filtered results

// Base SQL query
$sql = "SELECT * FROM verified_land WHERE 1=1";
$types = ""; // To hold the types for the bind_param function
$values = []; // To hold the values for the bind_param function

// Filter by query
if (!empty($_GET['query'])) {
    $query = "%" . $conn->real_escape_string($_GET['query']) . "%";
    $sql .= " AND (lot_number LIKE ? OR status LIKE ? OR applicant_name LIKE ? OR survey_claimant_name LIKE ? OR municipality LIKE ? OR barangay LIKE ?)";
    $types .= "ssssss";
    array_push($values, $query, $query, $query, $query, $query, $query);
}

// Filter by municipalities
if (!empty($_GET['municipalities'])) {
    $municipalities = $_GET['municipalities'];
    if (!is_array($municipalities)) {
        $municipalities = [$municipalities]; // Convert string to array
    }
    $municipalityPlaceholders = implode(',', array_fill(0, count($municipalities), '?'));
    $sql .= " AND municipality IN ($municipalityPlaceholders)";
    $types .= str_repeat("s", count($municipalities));
    $values = array_merge($values, $municipalities);
}

// Filter by barangays
if (!empty($_GET['barangays'])) {
    $barangays = $_GET['barangays'];
    if (!is_array($barangays)) {
        $barangays = [$barangays]; // Convert string to array
    }
    $barangayPlaceholders = implode(',', array_fill(0, count($barangays), '?'));
    $sql .= " AND barangay IN ($barangayPlaceholders)";
    $types .= str_repeat("s", count($barangays));
    $values = array_merge($values, $barangays);
}

// Filter by status
if (!empty($_GET['status'])) {
    $status = $_GET['status'];
    if (!is_array($status)) {
        $status = [$status]; // Convert string to array if only one value is selected
    }
    $statusPlaceholders = implode(',', array_fill(0, count($status), '?'));
    $sql .= " AND status IN ($statusPlaceholders)";
    $types .= str_repeat("s", count($status));
    $values = array_merge($values, $status);
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if ($types && $values) {
    $stmt->bind_param($types, ...$values);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $results[] = $row; // Store results in an array
    }
} else {
    echo "";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR-CENRO: List of Verified Land Titles</title>
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

        <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>List of Verified Land Title</h3>
        <div class="d-flex">
            <button type="button" class="btn btn-success btn-sm p-2" style="margin-right: 5px;" onclick="printTable()" title="Print">
                <i class="fas fa-print"></i>
            </button>
            <button type="button" class="btn btn-info btn-sm p-2 me-6" data-toggle="modal" data-target="#advancedFilterModal" title="Advanced Filter">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </div>
</div>
    <div class="table-container">
            <table class="table table-striped" id="printableTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lot Number</th>
                        <th>Status</th>
                        <th>Date Approved</th>
                        <th>Applicant</th>
                        <th>Survey Claimant</th>
                        <th>Barangay</th>
                        <th>Municipality</th>
                        <th class="actions-column">Actions</th>
                    </tr>
            </thead>
            <tbody>
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['verified_landID']); ?></td>
                        <td><?php echo htmlspecialchars($row['lot_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_approved']); ?></td>
                        <td><?php echo htmlspecialchars($row['applicant_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['survey_claimant_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['barangay']); ?></td>
                        <td><?php echo htmlspecialchars($row['municipality']); ?></td>
                        <td class='actions-column'>
                            <a href="edit_land.php?id=<?php echo htmlspecialchars($row['verified_landID']); ?>" class="btn btn-warning btn-sm" onclick="return confirmEdit();">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9">No results found.</td></tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<!-- Advanced Filter Modal -->
<?php include 'advance_filter_modal.php'; ?>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    function printTable() {
        const headerHTML = `
            <div class="header">
                <img src="../images/logo.png" alt="DENR Logo">
                <h6>Republic of the Philippines</h6>
                <h3>Department of Natural Resources</h3>
                <h4>Community Environment and Natural Resources Office</h4>
                <p>Brgy. Duhat, Santa Cruz, Laguna</p>
            </div>
        `;
        const tableHTML = document.getElementById('printableTable').outerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.open();
        printWindow.document.write(`
            <html>
            <head>
                <title>Print Table</title>
                <style>
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: left; page-break-inside: auto; }
                    th { background-color: #f2f2f2; }
                    @media print {
                        .actions-column { display: none; }
                        .header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .header img {
                            width: 80px; /* Adjust as needed */
                            height: auto;
                        }
                        .header h6, .header h3, .header h4 {
                            margin: 0;
                        }
                            .header p {
                            margin: 0;
                            font-size: 10px; /* Reduced font size */
                        }
                        tr {
                            page-break-inside: avoid;
                        }
                    }
                </style>
            </head>
            <body>
                ${headerHTML}
                ${tableHTML}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.onload = function() {
            printWindow.print();
        };
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const municipalityCheckboxes = document.querySelectorAll('input[name="municipalities[]"]');

        municipalityCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                // Sanitize the value to match the ID
                const sanitizedValue = this.value.replace(/\s+/g, '').replace(/[^a-zA-Z0-9]/g, '');
                const barangayDiv = document.getElementById('barangays' + sanitizedValue);

                if (barangayDiv) {
                    barangayDiv.style.display = this.checked ? 'block' : 'none';
                }
            });
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
        return confirm('Are you sure you want to logout?');
    }
</script>
</body>
</html>
