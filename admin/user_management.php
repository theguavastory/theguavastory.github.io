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
    <title>User Management</title>
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
            <h3>User Management</h3>
            <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>User Type</th>
                        <th>Username</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Contact Number</th>
                        <th>Email</th>
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
                    $sql = "SELECT * FROM users";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        // Output data of each row
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["userId"] . "</td>";
                            echo "<td>" . $row["userType"] . "</td>";
                            echo "<td>" . $row["username"] . "</td>";
                            echo "<td>" . $row["userLastName"] . "</td>";
                            echo "<td>" . $row["userFirstName"] . "</td>";
                            echo "<td>" . $row["userMiddleName"] . "</td>";
                            echo "<td>" . $row["userContactNumber"] . "</td>";
                            echo "<td>" . $row["userEmail"] . "</td>";
                            echo "<td>
                                    <div class='btn-group' role='group'>
                                        <button type='button' class='btn btn-warning btn-sm edit-btn'><i class='fas fa-edit'></i> Edit</button>
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
            <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">Add User</button>
        </div>
    </div>

    <!-- Register Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form for adding a new user -->
                <form id="register-form" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="usertype">User Type</label>
                        <select id="usertype" name="usertype" class="form-control" required>
                            <option value="">Select User Type</option>
                            <option value="Admin">Admin</option>
                            <option value="User">User</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" maxlength="11" minlength="11" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveChanges">Save changes</button>
            </div>
        </div>
    </div>
</div>


<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form for editing a user -->
                <form id="edit-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="edit_userId" name="userId">
                    <div class="form-group">
                        <label for="edit_username">Username</label>
                        <input type="text" id="edit_username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_usertype">User Type</label>
                        <select id="edit_usertype" name="usertype" class="form-control" required>
                            <option value="">Select User Type</option>
                            <option value="Admin">Admin</option>
                            <option value="User">User</option>
                            <option value="Deactivate">Deactivate</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_first_name">First Name</label>
                        <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Last Name</label>
                        <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_middle_name">Middle Name</label>
                        <input type="text" id="edit_middle_name" name="middle_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_contact_number">Contact Number</label>
                        <input type="text" id="edit_contact_number" name="contact_number" maxlength="11" minlength="11" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateChanges">Update changes</button>
            </div>
        </div>
    </div>
</div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
       $(document).ready(function() {
    $('#saveChanges').click(function() {
        var formData = $('#register-form').serialize();
        $.ajax({
            type: 'POST',
            url: 'register.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('User registered successfully.');
                    $('#addUserModal').modal('hide');
                    // Optionally reset the form
                    $('#register-form')[0].reset();
                } else {
                    alert('Registration failed: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Server Response:', xhr.responseText);
                alert('An error occurred while registering.');
            }
        });
    });
});
    </script>
    <script>
        $(document).on('click', '.edit-btn', function() {
    var userId = $(this).closest('tr').find('td:first').text();
    $.ajax({
        type: 'POST',
        url: 'fetch_user.php', // Endpoint to fetch user data
        data: { userId: userId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#edit_userId').val(response.data.userId);
                $('#edit_username').val(response.data.username);
                $('#edit_usertype').val(response.data.userType);
                $('#edit_first_name').val(response.data.userFirstName);
                $('#edit_last_name').val(response.data.userLastName);
                $('#edit_middle_name').val(response.data.userMiddleName);
                $('#edit_contact_number').val(response.data.userContactNumber);
                $('#edit_email').val(response.data.userEmail);
                $('#editUserModal').modal('show');
            } else {
                alert('Failed to fetch user data: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.error('Server Response:', xhr.responseText);
            alert('An error occurred while fetching user data.');
        }
    });
});
    </script>
    <script>
        $(document).ready(function() {
    $('#updateChanges').click(function() {
        var formData = $('#edit-form').serialize();
        $.ajax({
            type: 'POST',
            url: 'update_user.php', // Endpoint to update user data
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('User updated successfully.');
                    $('#editUserModal').modal('hide');
                    // Optionally refresh the table or update the row
                    location.reload();
                } else {
                    alert('Update failed: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Server Response:', xhr.responseText);
                alert('An error occurred while updating.');
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
