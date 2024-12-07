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
        <h3>Approval Request</h3>
        <div class="d-flex">
            <button type="button" class="btn btn-info btn-sm p-2 me-6" data-toggle="modal" data-target="#advancedFilterModal" title="Advanced Filter">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </div>
</div>
    <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee's Name</th>
                        <th>Date Submitted</th>
                        <th>Lot Number</th>
                        <th>Land Category</th>
                        <th>Date Approved</th>
                        <th>Applicant</th>
                        <th>Survey Claimant</th>
                        <th>Barangay</th>
                        <th>Municipality</th>
                        <th>Approval Status</th>
                    </tr>
                </thead>
                <tbody id="notificationTableBody">
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

                        // Fetch notifications
            $sql = "SELECT notif_id, 
            CONCAT(u.userLastName, ', ', u.userFirstName, ' ', u.userMiddleName) AS employee_name, 
            time_sent, 
            lot_number, 
            status, 
            date_approved, 
            applicant_name, 
            survey_claimant_name, 
            barangay, 
            municipality, 
            notif_status 
            FROM notifications n
            JOIN users u ON n.userId = u.userId
            ORDER BY time_sent DESC";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
            echo "<tr id='row-" . $row["notif_id"] . "'>
                    <td>" . $row["employee_name"] . "</td>
                    <td>" . $row["time_sent"] . "</td>
                    <td>" . $row["lot_number"] . "</td>
                    <td>" . $row["status"] . "</td>
                    <td>" . $row["date_approved"] . "</td>
                    <td>" . $row["applicant_name"] . "</td>
                    <td>" . $row["survey_claimant_name"] . "</td>
                    <td>" . $row["barangay"] . "</td>
                    <td>" . $row["municipality"] . "</td>
                     <td>
                        <form method='POST' action='notification_submit.php'>
                            <input type='hidden' name='notif_id' value='" . $row["notif_id"] . "'>
                            <button class='btn btn-primary btn-sm' id='approveButton-" . $row["notif_id"] . "' type='submit' name='approval_update' value='Approve'>
                                <i class='fas fa-check'></i> Approve
                            </button>
                        </form>
                        <br> 
                    </td>
                </tr>";
            }
            } else {
            echo "<tr><td colspan='10'>No records found</td></tr>";
            }
            ?>

             <!-- Disapproval Modal -->
                <div class="modal fade" id="disapproveModal" tabindex="-1" role="dialog" aria-labelledby="disapproveModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="disapproveModalLabel">Disapprove Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="disapprove_submit.php">
                    <div class="modal-body">
                    <!-- Hidden field to hold notif_id -->
                    <input type="hidden" name="notif_id" id="disapproveNotifId">
                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="4"></textarea>
                    </div>
                    <div id="error-message" style="color: red;"></div> <!-- Error message element -->
                </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger" id="disapprove_update" name="disapprove_update" value="disapprove_update">Disapprove</button>
                        </div>
                    </form>
                    </div>
                </div>
                </div>
                </tbody>
                 </table>
             </div>
        </div>
    </div>

   

    <!-- Advanced Filter Modal -->
    <?php include 'notif_advance_filter_modal.php'; ?>




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
