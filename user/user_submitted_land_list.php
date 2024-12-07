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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'user_style.php'; ?>
</head>
<body>
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
    

    <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>My Submitted Land Title</h3>
    </div>
</div>
<div class="table-responsive">
<table class="table table-striped">
    <thead>
        <tr>
            <th>Date Submitted</th>
            <th>Lot Number</th>
            <th>Land Category</th>
            <th>Status</th>
            <th>Date Approved</th>
            <th>Applicant</th>
            <th>Survey Claimant</th>
            <th>Barangay</th>
            <th>Municipality</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="SubmittedTableBody">
        <?php
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
        
        $userId = $_SESSION['userId']; // Ensure the user's ID is stored in session upon login

        // Updated SQL query to match the first table
        $sql = "
        SELECT notif_id, 
               time_sent, 
               lot_number, 
               status AS land_category,  -- Renamed 'status' to 'land_category'
               date_approved, 
               applicant_name, 
               survey_claimant_name, 
               barangay, 
               municipality
        FROM notifications n
        JOIN users u ON n.userId = u.userId
        WHERE u.userId = ?  -- Only show records submitted by the logged-in user
        ORDER BY time_sent DESC"; // Adjusted ORDER BY to match first table

        $stmt = $conn->prepare($sql);  // Prepare the statement to avoid SQL injection
        $stmt->bind_param("i", $userId); // Bind the current user's ID
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr id='row-" . $row["notif_id"] . "'>
                    <td>" . $row["time_sent"] . "</td>
                    <td>" . $row["lot_number"] . "</td>
                    <td>" . $row["land_category"] . "</td>
                    <td> Pending </td>
                    <td>" . $row["date_approved"] . "</td>
                    <td>" . $row["applicant_name"] . "</td>
                    <td>" . $row["survey_claimant_name"] . "</td>
                    <td>" . $row["barangay"] . "</td>
                    <td>" . $row["municipality"] . "</td>
                    <td>
                        <a href='user_edit_land.php?id=" . $row["notif_id"] . "' class='btn btn-warning btn-sm' onclick='return confirmEdit();'><i class='fas fa-eye'></i></a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='9'>No records found</td></tr>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </tbody>
</table>
             </div>
        </div>
    </div>

   

   


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all disapprove buttons
        document.querySelectorAll('.disapproveBtn').forEach(function(button) {
            button.addEventListener('click', function() {
                // Get the notification ID from the button's data attribute
                const notif_id = this.getAttribute('data-notif-id');
                
                // Set the notification ID in the hidden input field of the modal
                document.getElementById('disapproveNotifId').value = notif_id;

                // Clear any previous error messages
                document.getElementById('error-message').textContent = '';
                
                // Show the modal
                $('#disapproveModal').modal('show');
            });
        });

        // Form submission handling
        const disapproveForm = document.querySelector('#disapproveModal form');
        disapproveForm.addEventListener('submit', function(event) {
            // Get the remarks value
            const remarks = document.getElementById('remarks').value;

           // Validate remarks length only if remarks are provided
                if (remarks.trim().length > 0 && remarks.trim().length < 5) {
                    event.preventDefault(); // Prevent form submission
                    document.getElementById('error-message').textContent = 'Remarks must be at least 5 characters long if provided.';
                    return;
                }

            // Optionally disable the submit button to prevent multiple submissions
            const submitButton = disapproveForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            // Show a loading indicator if desired
        });
    });
</script>

     <!-- JavaScript for Confirmation Dialog -->
     <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Select all approve buttons using attribute selector for IDs starting with 'approveButton-'
        document.querySelectorAll('button[id^="approveButton-"]').forEach(button => {
            button.addEventListener('click', function(event) {
                // Show confirmation dialog
                var confirmApprove = confirm("Are you sure you want to approve this request?");
                
                // If the user clicks "Cancel", prevent the form from submitting
                if (!confirmApprove) {
                    event.preventDefault();
                }
            });
        });
    });
    </script>
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
        if (confirm("Are you sure you want to logout?")) {
            window.location.href = 'logout.php';
        }
        return false; // Prevent default action
    }
    </script>
</body>
</html>
