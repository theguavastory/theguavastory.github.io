<?php
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

$servername = "localhost";
$username = "root"; // Default MySQL username
$password = ""; // Default MySQL password (empty)
$database = "denr"; // Your MySQL database name

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

    // Concatenate full names
    $applicantFullName = "$applicantLastName  $applicantFirstName $applicantMiddleName";
    $claimantFullName = "$claimantLastName $claimantFirstName $claimantMiddleName";

    $userId = $_SESSION['userId'];
// Retrieve coordinates
$coordinates = $_POST['hiddenCoordinates']; // Get the raw coordinates (Latitude, Longitude)

// Split coordinates into latitude and longitude
list($latitude, $longitude) = explode(',', $coordinates);

// Prepare and bind the statement for inserting into the verified_land table
$stmt = $conn->prepare("INSERT INTO verified_land (userId, lot_number, status, municipality, barangay, date_approved, applicant_name, survey_claimant_name, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssssdd", $userId, $lot_number, $status, $municipality, $barangay, $date_approved, $applicantFullName, $claimantFullName, $latitude, $longitude);
    // Execute the statement
    if ($stmt->execute()) {
        $verified_landID = $conn->insert_id; // Get the last inserted ID

        // Prepare and bind the statement for inserting into the surveyclaimant table
        $surveyClaimantQuery = $conn->prepare("INSERT INTO surveyclaimant (claimantLastName, claimantFirstName, claimantMiddleName, claimantExtension, claimantSex, claimantBirthday, claimantAge, claimantContactNumber, claimantCivilStatus, claimantSpouseName, verified_landID, applicantID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $surveyClaimantQuery->bind_param("ssssssssssii", $claimantLastName, $claimantFirstName, $claimantMiddleName, $claimantExtension, $claimantSex, $claimantBirthday, $claimantAge, $claimantContactNumber, $claimantCivilStatus, $claimantSpouseName, $verified_landID, $applicantID);

        // Execute the statement
        if ($surveyClaimantQuery->execute()) {
            $claimantID = $conn->insert_id; // Get the last inserted claimant ID
            
            // Prepare and bind the statement for inserting into the applicant table
            $applicantQuery = $conn->prepare("INSERT INTO applicant (applicantLastName, applicantFirstName, applicantExtension, applicantMiddleName, applicantSex, applicantBirthday, applicantAge, applicantContactNumber, applicantCivilStatus, applicantSpouseName, verified_landID, claimantID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $applicantQuery->bind_param("ssssssssssii", $applicantLastName, $applicantFirstName, $applicantMiddleName, $applicantExtension, $applicantSex, $applicantBirthday, $applicantAge, $applicantContactNumber, $applicantCivilStatus, $applicantSpouseName, $verified_landID, $claimantID);

            // Execute the statement
            if ($applicantQuery->execute()) {
                $applicantID = $conn->insert_id; // Get the last inserted applicant ID

                // Update verified_land table with applicantID and claimantID
                $updateVerifiedLandQuery = $conn->prepare("UPDATE verified_land SET applicantID = ?, claimantID = ? WHERE verified_landID = ?");
                $updateVerifiedLandQuery->bind_param("iii", $applicantID, $claimantID, $verified_landID);
                if ($updateVerifiedLandQuery->execute()) {

                     // Log the activity
                     $userId = $_SESSION['userId']; // Get from session
                     $userType = $_SESSION['usertype']; // Get from session
                     $username = $_SESSION['username']; // Get from session
                     $userContactNumber = $_SESSION['contactNumber']; // Assuming it's stored in session
                     $userEmail = $_SESSION['email']; // Assuming it's stored in session
 
                     logActivity($conn, $userId, $userType, $username, "Submitted land title with Lot Number: $lot_number", $userContactNumber, $userEmail);



                    // Redirect after processing
                    header("Location: add_land_title.php");
                    exit;
                } else {
                    echo "Error updating verified_land table: " . $updateVerifiedLandQuery->error;
                }
            } else {
                echo "Error inserting into applicant table: " . $applicantQuery->error;
            }
        } else {
            echo "Error inserting into surveyclaimant table: " . $surveyClaimantQuery->error;
        }
    } else {
        echo "Error inserting into verified_land table: " . $stmt->error;
    }

    // Close the statements
    $stmt->close();
    $surveyClaimantQuery->close();
    $applicantQuery->close();
    $updateVerifiedLandQuery->close();
}

$conn->close();
?>
