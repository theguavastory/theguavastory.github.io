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

// Define the logActivity function
function logActivity($conn, $userId, $userType, $username, $activity, $userContactNumber, $userEmail) {
    $logSql = "INSERT INTO activity_logs (userId, userType, username, activity, activity_time, userContactNumber, userEmail) VALUES (?, ?, ?, ?, NOW(), ?, ?)";
    $logStmt = $conn->prepare($logSql);
    if ($logStmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $logStmt->bind_param("isssss", $userId, $userType, $username, $activity, $userContactNumber, $userEmail);
    $logStmt->execute();
    $logStmt->close();
}
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $verified_landID = $_POST['verified_landID'];

    $userId = $_SESSION['userId'];
    $userType = $_SESSION['usertype'];
    $username = $_SESSION['username'];

    // Prepare and bind the statement for updating the archive status
    $archiveStatus = false;

    // Update verified_land table
    $stmt = $conn->prepare("UPDATE verified_land SET archive_status = ? WHERE verified_landID = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $archiveStatus, $verified_landID);

    if ($stmt->execute()) {
        echo "Verified Land table archived successfully.\n";

        // Log the action
        logActivity($conn, $userId, $userType, $username, "Unarchived Verified Land ID: $verified_landID", $userContactNumber, $userEmail);
        
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

        if ($claimantID && $applicantID) {
            // Update surveyclaimant table
            $surveyClaimantQuery = $conn->prepare("UPDATE surveyclaimant SET archive_status = ? WHERE claimantID = ?");
            if (!$surveyClaimantQuery) {
                die("Prepare failed: " . $conn->error);
            }
            $surveyClaimantQuery->bind_param("ii", $archiveStatus, $claimantID);

            if ($surveyClaimantQuery->execute()) {
                echo "Survey Claimant table archived successfully.\n";
                $surveyClaimantQuery->close();

                // Log the action
                logActivity($conn, $userId, $userType, $username, "Unarchived Survey Claimant ID: $claimantID", $userContactNumber, $userEmail);

            } else {
                echo "Error archiving surveyclaimant table: " . $surveyClaimantQuery->error;
            }

            // Update applicant table
            $applicantQuery = $conn->prepare("UPDATE applicant SET archive_status = ? WHERE applicantID = ?");
            if (!$applicantQuery) {
                die("Prepare failed: " . $conn->error);
            }
            $applicantQuery->bind_param("ii", $archiveStatus, $applicantID);

            if ($applicantQuery->execute()) {
                echo "Applicant table archived successfully.\n";
                $applicantQuery->close();

                // Log the action
                logActivity($conn, $userId, $userType, $username, "Unarchived Applicant ID: $applicantID", $userContactNumber, $userEmail);

            } else {
                echo "Error archiving applicant table: " . $applicantQuery->error;
            }
        } else {
            echo "Failed to fetch claimantID or applicantID from verified_land table.";
        }

        // Redirect after processing
        header("Location: archive.php");
        exit;
    } else {
        echo "Error archiving verified_land table: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
