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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #006769;
            color: #fff;
            padding: 20px;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            z-index: 1100;
        }
        .sidebar img {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 10px 0;
            display: flex;
            align-items: center;
        }
        .sidebar a:hover {
            background-color: #1272d3;
            text-decoration: none;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        .logout-btn {
            margin-top: auto;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            transition: margin-left 0.3s ease;
        }
        .navbar-custom {
            background-color: #007bff;
            width: 100%;
            position: fixed;
            top: 0;
            left: 250px;
            z-index: 1000;
            padding-right: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: left 0.3s ease;
        }
        .navbar-custom .navbar-brand {
            color: #fff;
        }
        .search-bar {
            margin-top: 80px;
            margin-bottom: 20px;
        }
        .search-bar input {
            flex: 1;
        }
        .dashboard {
            margin-top: 20px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
                width: 100%;
            }
            .navbar-custom {
                left: 0;
            }
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-left: flex;
            position: flex;
            margin-right: -15%;
        }

        .user-info .badge {
            background-color: #28a745;
            margin-right: 5px; /* Adjust margin as needed */
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.9em;
            color: #fff;
        }

        .user-info .username {
            color: #fff;
            font-family:Arial, Helvetica, sans-serif ;
        }
        .notification-count {
        display: none; /* Initially hidden */
        background-color: #00999c;
        color: #fff;
        padding: 3px 8px;
        border-radius: 80%;
        font-size: 0.8em;
        position: flex;
        top: 410px;
        right: 81px;
    }

    /* Display the notification count if it's greater than 0 */
    <?php if ($notification_count > 0): ?>
    .notification-count {
        display: inline-block;
    }
    <?php endif; ?>

    /* Define hover style for the notification button */
    .sidebar a:hover .notification-count {
        display: inline-block; /* Show when the notification button is hovered */
    }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>


    <div class="content">
        <nav class="navbar navbar-custom">
            <span class="navbar-brand mb-0 h1">Record Management of Verified Land Records</span>
            <span class="user-info">
                <span class="badge">Logged in as:</span>
                <span class="username"><?php echo htmlspecialchars($username); ?></span>
            </span>
            <button class="navbar-toggler" type="button" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </nav>

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
            <h2>Applicant List</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Extension</th>
                        <th>Sex</th>
                        <th>Birthday</th>
                        <th>Age</th>
                        <th>Civil Status</th>
                        <th>Name of Spouse</th>
                        <th>Contact Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Database connection
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

                    // Fetch applicant data
                    $sql = "SELECT * FROM applicant WHERE archive_status = '0'";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        // Output data of each row
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["applicantID"] . "</td>";
                            echo "<td>" . $row["applicantLastName"] . "</td>";
                            echo "<td>" . $row["applicantFirstName"] . "</td>";
                            echo "<td>" . $row["applicantMiddleName"] . "</td>";
                            echo "<td>" . $row["applicantExtension"] . "</td>";
                            echo "<td>" . $row["applicantSex"] . "</td>";
                            echo "<td>" . $row["applicantBirthday"] . "</td>";
                            echo "<td>" . $row["applicantAge"] . "</td>";
                            echo "<td>" . $row["applicantCivilStatus"] . "</td>";
                            echo "<td>" . $row["applicantSpouseName"] . "</td>";
                            echo "<td>" . $row["applicantContactNumber"] . "</td>";
                            echo "<td>
                                    <div class='btn-group' role='group'>
                                    <a href='edit_land.php?id=" . $row["verified_landID"] . "' class='btn btn-warning btn-sm onclick='return confirmEdit();'><i class='fas fa-eye'></i></a>
                                    </div>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>No applicants found</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editForm" method="POST" action="update_applicant.php">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Applicant</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editForm">
        <div class="modal-body">
          <input type="hidden" id="edit-id" name="id">
          <div class="form-group">
            <label for="edit-last-name">Last Name</label>
            <input type="text" class="form-control" id="edit-last-name" name="applicantLastName" required>
          </div>
          <div class="form-group">
            <label for="edit-first-name">First Name</label>
            <input type="text" class="form-control" id="edit-first-name" name="applicantFirstName" required>
          </div>
          <div class="form-group">
            <label for="edit-middle-name">Middle Name</label>
            <input type="text" class="form-control" id="edit-middle-name" name="applicantMiddleName"required>
          </div>
          <div class="form-group">
            <label for="edit-middle-name">Extension</label>
            <input type="text" class="form-control" id="edit-middle-name" name="applicantExtension">
          </div>
          <div class="form-group">
            <label for="edit-sex">Sex</label>
            <input type="text" class="form-control" id="edit-sex" name="applicantSex"required>
          </div>
          <div class="form-group">
            <label for="edit-birthday">Birthday</label>
            <input type="date" class="form-control" id="edit-birthday" name="applicantBirthday"required>
          </div>
          <div class="form-group">
            <label for="edit-age">Age</label>
            <input type="number" class="form-control" id="edit-age" name="applicantAge" required>
          </div>
          <div class="form-group">
            <label for="edit-contact-number">Contact Number</label>
            <input type="text" class="form-control" id="edit-contact-number" name="applicantContactNumber" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
    // Function to handle edit button click
    $(document).ready(function(){
        $('.edit-btn').click(function(){
            var row = $(this).closest('tr');
            var id = row.find('td:eq(0)').text();
            var lastName = row.find('td:eq(1)').text();
            var firstName = row.find('td:eq(2)').text();
            var middleName = row.find('td:eq(3)').text();
            var extension = row.find('td:eq(4)').text();
            var sex = row.find('td:eq(5)').text();
            var birthday = row.find('td:eq(6)').text();
            var age = row.find('td:eq(7)').text();
            var contactNumber = row.find('td:eq(8)').text();

            // Populate modal form with data
            $('#edit-id').val(id);
            $('#edit-last-name').val(lastName);
            $('#edit-first-name').val(firstName);
            $('#edit-middle-name').val(middleName);
            $('#edit-extension').val(extension);
            $('#edit-sex').val(sex);
            $('#edit-birthday').val(birthday);
            $('#edit-age').val(age);
            $('#edit-contact-number').val(contactNumber);

            // Show the modal
            $('#editModal').modal('show');
        });
    });
</script>
<script>
    function calculateAge() {
        var dob = new Date(document.getElementById("edit-birthday").value);
        var today = new Date();
        var age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
        document.getElementById("edit-age").value = age;
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
