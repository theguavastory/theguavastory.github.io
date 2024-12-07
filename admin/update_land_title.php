<?php
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}


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

// Function to log user activity
function logActivity($conn, $userId, $userType, $username, $activity, $userContactNumber = null, $userEmail = null) {
    $activity_time = date('Y-m-d H:i:s'); // Capture current timestamp

    $logQuery = $conn->prepare("INSERT INTO activity_logs (userId, userType, username, activity, activity_time, userContactNumber, userEmail) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $logQuery->bind_param("issssss", $userId, $userType, $username, $activity, $activity_time, $userContactNumber, $userEmail);

    if (!$logQuery->execute()) {
        echo "Error inserting into activity_logs table: " . $logQuery->error;
    }

    $logQuery->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['archive'])) {
        // Retrieve verified_landID from form
        $verified_landID = $_POST['verified_landID'];

        // Fetch claimantID and applicantID from verified_land table
        $stmt = $conn->prepare("SELECT claimantID, applicantID FROM verified_land WHERE verified_landID = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $verified_landID);
        $stmt->execute();
        $stmt->bind_result($claimantID, $applicantID);
        $stmt->fetch();
        $stmt->close();

        if (!$claimantID || !$applicantID) {
            die("Failed to fetch claimantID or applicantID from verified_land table.");
        }

        // Prepare and bind the statement for updating the archive status
        $archiveStatus = true;

        // Update verified_land table
        $stmt = $conn->prepare("UPDATE verified_land SET archive_status = ? WHERE verified_landID = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ii", $archiveStatus, $verified_landID);

        
        if ($stmt->execute()) {
            echo "Verified Land table archived successfully.\n";

            // Log the archive action
            $userId = $_SESSION['userId'];
            $userType = $_SESSION['usertype'];
            $username = $_SESSION['username'];

            logActivity($conn, $userId, $userType, $username, "Archived land title with Verified Land ID:  $verified_landID", $userContactNumber, $userEmail);
        } else {
            echo "Error archiving verified_land table: " . $stmt->error;
        }
        $stmt->close();

        // Update surveyclaimant table
        $surveyClaimantQuery = $conn->prepare("UPDATE surveyclaimant SET archive_status = ? WHERE claimantID = ?");
        if (!$surveyClaimantQuery) {
            die("Prepare failed: " . $conn->error);
        }
        $surveyClaimantQuery->bind_param("ii", $archiveStatus, $claimantID);

        if ($surveyClaimantQuery->execute()) {
            echo "Survey Claimant table archived successfully.\n";
        } else {
            echo "Error archiving surveyclaimant table: " . $surveyClaimantQuery->error;
        }
        $surveyClaimantQuery->close();

        // Update applicant table
        $applicantQuery = $conn->prepare("UPDATE applicant SET archive_status = ? WHERE applicantID = ?");
        if (!$applicantQuery) {
            die("Prepare failed: " . $conn->error);
        }
        $applicantQuery->bind_param("ii", $archiveStatus, $applicantID);

        if ($applicantQuery->execute()) {
            echo "Applicant table archived successfully.\n";
        } else {
            echo "Error archiving applicant table: " . $applicantQuery->error;
        }
        $applicantQuery->close();

        // Redirect after processing
        header("Location: verified_land_list.php");
        exit;
    } else {
        // Retrieve form data
        $lot_number = $_POST['lot_number'];
        $status = $_POST['status'];
        $municipality = $_POST['municipality'];
        $barangay = $_POST['barangay'];
        $date_approved = $_POST['date_approved'];

        $applicantLastName = $_POST['applicantLastName'];
        $applicantFirstName = $_POST['applicantFirstName'];
        $applicantMiddleName = $_POST['applicantMiddleName'];
        $applicantExtension = $_POST['applicantExtension'];
        $applicantSex = $_POST['applicantSex'];
        $applicantBirthday = $_POST['applicantBirthday'];
        $applicantAge = $_POST['applicantAge'];
        $applicantCivilStatus = $_POST['applicantCivilStatus'];
        $applicantSpouseName = $_POST['applicantSpouseName'];
        $applicantContactNumber = $_POST['applicantContactNumber'];

        $claimantLastName = $_POST['claimantLastName'];
        $claimantFirstName = $_POST['claimantFirstName'];
        $claimantMiddleName = $_POST['claimantMiddleName'];
        $claimantExtension = $_POST['claimantExtension'];
        $claimantSex = $_POST['claimantSex'];
        $claimantBirthday = $_POST['claimantBirthday'];
        $claimantAge = $_POST['claimantAge'];
        $claimantCivilStatus = $_POST['claimantCivilStatus'];
        $claimantSpouseName = $_POST['claimantSpouseName'];
        $claimantContactNumber = $_POST['claimantContactNumber'];

        // Retrieve coordinates from form (latitude and longitude)
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];

        // Concatenate full names
        $applicantFullName = "$applicantLastName $applicantFirstName $applicantMiddleName";
        $claimantFullName = "$claimantLastName $claimantFirstName $claimantMiddleName";

        // Get verified_landID from form
        $verified_landID = $_POST['verified_landID'];

        // Debugging: Print the values
        echo "Lot Number: $lot_number\n";
        echo "Status: $status\n";
        echo "Municipality: $municipality\n";
        echo "Barangay: $barangay\n";
        echo "Date Approved: $date_approved\n";
        echo "Applicant Full Name: $applicantFullName\n";
        echo "Claimant Full Name: $claimantFullName\n";
        echo "Verified Land ID: $verified_landID\n";

          // Prepare and bind the statement for updating the verified_land table with coordinates
          $stmt = $conn->prepare("UPDATE verified_land SET lot_number = ?, status = ?, municipality = ?, barangay = ?, date_approved = ?, applicant_name = ?, survey_claimant_name = ?, latitude = ?, longitude = ? WHERE verified_landID = ?");
          if (!$stmt) {
              die("Prepare failed: " . $conn->error);
          }
          $stmt->bind_param("sssssssddi", $lot_number, $status, $municipality, $barangay, $date_approved, $applicantFullName, $claimantFullName, $latitude, $longitude, $verified_landID);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Verified Land table updated successfully.\n";

            // Fetch claimantID and applicantID from verified_land table for further updates
            $stmt = $conn->prepare("SELECT claimantID, applicantID FROM verified_land WHERE verified_landID = ?");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("i", $verified_landID);
            $stmt->execute();
            $stmt->bind_result($claimantID, $applicantID);
            $stmt->fetch();
            $stmt->close();

            if (!$claimantID || !$applicantID) {
                die("Failed to fetch claimantID or applicantID from verified_land table.");
            }

            // Prepare and bind the statement for updating the surveyclaimant table
            $surveyClaimantQuery = $conn->prepare("UPDATE surveyclaimant SET claimantLastName = ?, claimantFirstName = ?, claimantMiddleName = ?, claimantExtension = ?, claimantSex = ?, claimantBirthday = ?, claimantAge = ?, claimantCivilStatus = ?, claimantSpouseName = ?, claimantContactNumber = ? WHERE claimantID = ?");
            if (!$surveyClaimantQuery) {
                die("Prepare failed: " . $conn->error);
            }
            $surveyClaimantQuery->bind_param("ssssssssssi", $claimantLastName, $claimantFirstName, $claimantMiddleName, $claimantExtension, $claimantSex, $claimantBirthday, $claimantAge, $claimantCivilStatus, $claimantSpouseName, $claimantContactNumber, $claimantID);

            // Execute the statement
            if ($surveyClaimantQuery->execute()) {
                echo "Survey Claimant table updated successfully.\n";

                // Prepare and bind the statement for updating the applicant table
                $applicantQuery = $conn->prepare("UPDATE applicant SET applicantLastName = ?, applicantFirstName = ?, applicantMiddleName = ?, applicantExtension = ?, applicantSex = ?, applicantBirthday = ?, applicantAge = ?, applicantCivilStatus = ?, applicantSpouseName = ?, applicantContactNumber = ? WHERE applicantID = ?");
                if (!$applicantQuery) {
                    die("Prepare failed: " . $conn->error);
                }
                $applicantQuery->bind_param("ssssssssssi", $applicantLastName, $applicantFirstName, $applicantMiddleName, $applicantExtension, $applicantSex, $applicantBirthday, $applicantAge, $applicantCivilStatus, $applicantSpouseName, $applicantContactNumber, $applicantID);

                // Execute the statement
                if ($applicantQuery->execute()) {
                    echo "Applicant table updated successfully.\n";

                    // Log the update action
                    $userId = $_SESSION['userId'];
                    $userType = $_SESSION['usertype'];
                    $username = $_SESSION['username'];
                    $userContactNumber = $_SESSION['contactNumber'];
                    $userEmail = $_SESSION['email'];

                    logActivity($conn, $userId, $userType, $username, "Updated land title with Lot Number: $lot_number", $userContactNumber, $userEmail);



                    // Redirect after processing
                    header("Location: verified_land_list.php");
                    exit;
                } else {
                    echo "Error updating applicant table: " . $applicantQuery->error;
                }
            } else {
                echo "Error updating surveyclaimant table: " . $surveyClaimantQuery->error;
            }
        } else {
            echo "Error updating verified_land table: " . $stmt->error;
        }

        // Close the statements
        $stmt->close();
        $surveyClaimantQuery->close();
        $applicantQuery->close();
    }
}

$conn->close();
?>
