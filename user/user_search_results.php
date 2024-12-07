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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR-CENRO: List of Verified Land Titles</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
<tbody>
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "denr";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if a search query is set
    if (isset($_GET['query'])) {
        $query = $_GET['query'];

        // Prepare the SQL statement to match the same columns as your other table
        $sql = "
        SELECT notif_id, 
               time_sent, 
               lot_number, 
               status AS land_category,  -- Renaming 'status' to 'land_category' for display
               date_approved, 
               applicant_name, 
               survey_claimant_name, 
               barangay, 
               municipality
        FROM notifications
        WHERE lot_number LIKE ? OR 
              applicant_name LIKE ? OR 
              survey_claimant_name LIKE ? OR 
              status LIKE ? OR 
              municipality LIKE ? OR 
              barangay LIKE ?
        ORDER BY lot_number ASC, land_category ASC, applicant_name ASC, survey_claimant_name ASC, municipality ASC, barangay ASC";

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare($sql);
        $searchTerm = "%$query%";
        $stmt->bind_param("ssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);

        // Execute the query
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if there are any results
        if ($result->num_rows > 0) {
            // Output data of each row
            echo "<table class='table' id='printableTable'>";
            echo "<thead><tr><th>Date Submitted</th><th>Lot Number</th><th>Land Category</th><th>Date Approved</th><th>Applicant</th><th>Survey Claimant</th><th>Barangay</th><th>Municipality</th><th class='actions-column'>Actions</th></tr></thead>";
            echo "<tbody>";
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["time_sent"] . "</td><td>" . $row["lot_number"] . "</td><td>" . $row["land_category"] . "</td><td>" . $row["date_approved"] . "</td><td>" . $row["applicant_name"] . "</td><td>" . $row["survey_claimant_name"] . "</td><td>" . $row["barangay"] . "</td><td>" . $row["municipality"] . "</td><td class='actions-column'><a href='user_edit_land.php?id=" . $row["notif_id"] . "' class='btn btn-warning btn-sm' onclick='return confirmEdit();'><i class='fas fa-eye'></i></a></td></tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "No results found.";
        }
        $stmt->close();
    }

    $conn->close();
    ?>
</tbody>
</table>
 
    </div>
</div>

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
            document.querySelector('.sidebar').classList.toggle('show');
        }
        // Function to determine active page and apply 'active' class to corresponding link
        $(document).ready(function(){
            var currentLocation = window.location.href;
            $('.sidebar a').each(function(){
                var href = $(this).attr('href');
                if(currentLocation.includes(href)){
                    $(this).addClass('active');
                }
            });
        });
    </script>
    <script>
    $(document).ready(function() {
        $('.btn-warning').on('click', function() {
            const row = $(this).closest('tr');
            const id = row.find('td').eq(0).text();
            const lotNumber = row.find('td').eq(1).text();
            const status = row.find('td').eq(2).text();
            const dateApproved = row.find('td').eq(3).text();
            const applicantName = row.find('td').eq(4).text();
            const surveyClaimantName = row.find('td').eq(5).text();
            const barangay = row.find('td').eq(6).text();
            const municipality = row.find('td').eq(7).text();

            $('#editId').val(id);
            $('#editLotNumber').val(lotNumber);
            $('#editStatus').val(status);
            $('#editDateApproved').val(dateApproved);
            $('#editApplicantName').val(applicantName);
            $('#editSurveyClaimantName').val(surveyClaimantName);
            $('#editBarangay').val(barangay);
            $('#editMunicipality').val(municipality);

            $('#editModal').modal('show');
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
