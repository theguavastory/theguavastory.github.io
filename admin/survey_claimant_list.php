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
    <title>Survey Claimant List</title>
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
            <h2>Survey Claimant List</h2>
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
                    $sql = "SELECT * FROM surveyclaimant WHERE archive_status = '0'";
                    $result = $conn->query($sql);
                    

                    if ($result->num_rows > 0) {
                        // Output data of each row
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["claimantID"] . "</td>";
                            echo "<td>" . $row["claimantLastName"] . "</td>";
                            echo "<td>" . $row["claimantFirstName"] . "</td>";
                            echo "<td>" . $row["claimantMiddleName"] . "</td>";
                            echo "<td>" . $row["claimantExtension"] . "</td>";
                            echo "<td>" . $row["claimantSex"] . "</td>";
                            echo "<td>" . $row["claimantBirthday"] . "</td>";
                            echo "<td>" . $row["claimantAge"] . "</td>";
                            echo "<td>" . $row["claimantCivilStatus"] . "</td>";
                            echo "<td>" . $row["claimantSpouseName"] . "</td>";
                            echo "<td>" . $row["claimantContactNumber"] . "</td>";
                            echo "<td>
                                    <div class='btn-group' role='group'>
                                    <a href='edit_land.php?id=" . $row["verified_landID"] . "' class='btn btn-warning btn-sm onclick='return confirmEdit();'><i class='fas fa-eye'></i></a>
                                    </div>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10'>No survey claimant found</td></tr>";
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
            <form id="editForm" method="POST" action="update_claimant.php">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Survey Claimant</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editForm">
        <div class="modal-body">
          <input type="hidden" id="edit-id" name="claimantId">
          <div class="form-group">
            <label for="edit-last-name">Last Name</label>
            <input type="text" class="form-control" id="edit-last-name" name="claimantLastName" required>
          </div>
          <div class="form-group">
            <label for="edit-first-name">First Name</label>
            <input type="text" class="form-control" id="edit-first-name" name="claimantFirstName" required>
          </div>
          <div class="form-group">
            <label for="edit-middle-name">Middle Name</label>
            <input type="text" class="form-control" id="edit-middle-name" name="claimantMiddleName"required>
          </div>
          <div class="form-group">
            <label for="edit-sex">Sex</label>
            <input type="text" class="form-control" id="edit-sex" name="claimantSex"required>
          </div>
          <div class='form-group'>
                        <label for='edit-birthday'>Birthday</label>
                        <input type='date' class='form-control' id='edit-birthday' name='claimantBirthday' value='<?php echo $row["claimantBirthday"]; ?>' required>
                    </div>
                    <div class='form-group'>
                        <label for='edit-age'>Age</label>
                        <input type='number' class='form-control' id='edit-age' name='claimantAge' value='<?php echo $row["claimantAge"]; ?>' required>
          <div class="form-group">
            <label for="edit-contact-number">Contact Number</label>
            <input type="text" class="form-control" id="edit-contact-number" name="claimantContactNumber" required>
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
    // Function to handle edit button click
    $(document).ready(function(){
        $('.edit-btn').click(function(){
            var row = $(this).closest('tr');
            var id = row.find('td:eq(0)').text();
            var lastName = row.find('td:eq(1)').text();
            var firstName = row.find('td:eq(2)').text();
            var middleName = row.find('td:eq(3)').text();
            var sex = row.find('td:eq(4)').text();
            var birthday = row.find('td:eq(5)').text();
            var age = row.find('td:eq(6)').text();
            var contactNumber = row.find('td:eq(7)').text();

            // Populate modal form with data
            $('#edit-id').val(id);
            $('#edit-last-name').val(lastName);
            $('#edit-first-name').val(firstName);
            $('#edit-middle-name').val(middleName);
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
