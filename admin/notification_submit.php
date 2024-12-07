<?php
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "denr";

$conn = new mysqli($servername, $db_username, $db_password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the logActivity function
function logActivity($conn, $userId, $userType, $username, $activity, $userContactNumber, $userEmail) {
    $logSql = "INSERT INTO activity_logs (userId, userType, username, activity, activity_time, userContactNumber, userEmail) VALUES (?, ?, ?, ?, NOW(), ?, ?)";
    $logStmt = $conn->prepare($logSql);
    $logStmt->bind_param("isssss", $userId, $userType, $username, $activity, $userContactNumber, $userEmail);
    $logStmt->execute();
    $logStmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approval_update'])) {
    $notif_id = $_POST['notif_id'];
    $userId = $_SESSION['userId'];
    $userType = $_SESSION['usertype'];
    $username = $_SESSION['username'];

    try {
        $conn->begin_transaction();

        $notificationSql = "SELECT * FROM notifications WHERE notif_id = ?";
        $notificationStmt = $conn->prepare($notificationSql);
        $notificationStmt->bind_param("i", $notif_id);
        $notificationStmt->execute();
        $notificationResult = $notificationStmt->get_result();

        if ($notificationResult->num_rows > 0) {
            $notificationRow = $notificationResult->fetch_assoc();
            $notificationUserId = $notificationRow['userId'];

            $lot_number = $notificationRow['lot_number'];
            $status = $notificationRow['status'];
            $municipality = $notificationRow['municipality'];
            $barangay = $notificationRow['barangay'];
            $date_approved = $notificationRow['date_approved'];
            $applicantFullName = $notificationRow['applicant_name'];
            $claimantFullName = $notificationRow['survey_claimant_name'];
            $latitude = $notificationRow['latitude'];
            $longitude = $notificationRow['longitude'];

            // Insert into surveyclaimant table
            $claimantLastName = $notificationRow['claimantLastName'];
            $claimantFirstName = $notificationRow['claimantFirstName'];
            $claimantMiddleName = $notificationRow['claimantMiddleName'];
            $claimantExtension = $notificationRow['claimantExtension'];
            $claimantSex = $notificationRow['claimantSex'];
            $claimantBirthday = $notificationRow['claimantBirthday'];
            $claimantAge = $notificationRow['claimantAge'];
            $claimantCivilStatus = $notificationRow['claimantCivilStatus'];
            $claimantSpouseName = $notificationRow['claimantSpouseName'];
            $claimantContactNumber = $notificationRow['claimantContactNumber'];

            $surveyClaimantQuery = $conn->prepare("INSERT INTO surveyclaimant (claimantLastName, claimantFirstName, claimantMiddleName, claimantExtension, claimantSex, claimantBirthday, claimantAge, claimantCivilStatus, claimantSpouseName, claimantContactNumber) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $surveyClaimantQuery->bind_param("sssssssssi", $claimantLastName, $claimantFirstName, $claimantMiddleName, $claimantExtension, $claimantSex, $claimantBirthday, $claimantAge, $claimantCivilStatus, $claimantSpouseName, $claimantContactNumber);
            if (!$surveyClaimantQuery->execute()) {
                throw new Exception("Failed to insert into surveyclaimant: " . $surveyClaimantQuery->error);
            }
            $claimantID = $conn->insert_id;
            $surveyClaimantQuery->close();

            // Insert into applicant table
            $applicantLastName = $notificationRow['applicantLastName'];
            $applicantFirstName = $notificationRow['applicantFirstName'];
            $applicantMiddleName = $notificationRow['applicantMiddleName'];
            $applicantExtension = $notificationRow['applicantExtension'];
            $applicantSex = $notificationRow['applicantSex'];
            $applicantBirthday = $notificationRow['applicantBirthday'];
            $applicantAge = $notificationRow['applicantAge'];
            $applicantCivilStatus = $notificationRow['applicantCivilStatus'];
            $applicantSpouseName = $notificationRow['applicantSpouseName'];
            $applicantContactNumber = $notificationRow['applicantContactNumber'];

            $applicantQuery = $conn->prepare("INSERT INTO applicant (applicantLastName, applicantFirstName, applicantMiddleName, applicantExtension, applicantSex, applicantBirthday, applicantAge, applicantCivilStatus, applicantSpouseName, applicantContactNumber, claimantID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $applicantQuery->bind_param("sssssssssii", $applicantLastName, $applicantFirstName, $applicantMiddleName, $applicantExtension, $applicantSex, $applicantBirthday, $applicantAge, $applicantCivilStatus, $applicantSpouseName, $applicantContactNumber, $claimantID);
            if (!$applicantQuery->execute()) {
                throw new Exception("Failed to insert into applicant: " . $applicantQuery->error);
            }
            $applicantID = $conn->insert_id;
            $applicantQuery->close();

            // Update surveyclaimant table to include applicantID
            $updateSurveyClaimantQuery = $conn->prepare("UPDATE surveyclaimant SET applicantID = ? WHERE claimantID = ?");
            $updateSurveyClaimantQuery->bind_param("ii", $applicantID, $claimantID);
            if (!$updateSurveyClaimantQuery->execute()) {
                throw new Exception("Failed to update surveyclaimant: " . $updateSurveyClaimantQuery->error);
            }
            $updateSurveyClaimantQuery->close();

          // Insert into verified_land table
            $stmt = $conn->prepare("INSERT INTO verified_land (lot_number, status, municipality, barangay, date_approved, applicant_name, survey_claimant_name, applicantID, claimantID, userId, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssiiidd", $lot_number, $status, $municipality, $barangay, $date_approved, $applicantFullName, $claimantFullName, $applicantID, $claimantID, $notificationUserId, $latitude, $longitude);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert into verified_land: " . $stmt->error);
            }
            $verified_landID = $conn->insert_id;
            $stmt->close();

            // Update surveyclaimant table with verified_landID
            $updateSurveyClaimantVerifiedQuery = $conn->prepare("UPDATE surveyclaimant SET verified_landID = ? WHERE claimantID = ?");
            $updateSurveyClaimantVerifiedQuery->bind_param("ii", $verified_landID, $claimantID);
            if (!$updateSurveyClaimantVerifiedQuery->execute()) {
                throw new Exception("Failed to update surveyclaimant: " . $updateSurveyClaimantVerifiedQuery->error);
            }
            $updateSurveyClaimantVerifiedQuery->close();

            // Update applicant table with verified_landID
            $updateApplicantVerifiedQuery = $conn->prepare("UPDATE applicant SET verified_landID = ? WHERE applicantID = ?");
            $updateApplicantVerifiedQuery->bind_param("ii", $verified_landID, $applicantID);
            if (!$updateApplicantVerifiedQuery->execute()) {
                throw new Exception("Failed to update applicant: " . $updateApplicantVerifiedQuery->error);
            }
            $updateApplicantVerifiedQuery->close();

            // Move data to notif_repository table
            $moveSql = "INSERT INTO notif_repository (notif_id, notif_status, userId, userLastName, lot_number, status, municipality, barangay, date_approved, applicant_name, survey_claimant_name, claimantLastName, claimantFirstName, claimantMiddleName, claimantExtension, claimantSex, claimantBirthday, claimantAge, claimantCivilStatus, claimantSpouseName, claimantContactNumber, applicantLastName, applicantFirstName, applicantMiddleName, applicantExtension, applicantSex, applicantBirthday, applicantAge, applicantCivilStatus, applicantSpouseName, applicantContactNumber) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $moveStmt = $conn->prepare($moveSql);
            $moveStmt->bind_param("issssssssssssssssssssssssssssss", $notif_id, $notificationRow['notif_status'], $notificationRow['userId'], $notificationRow['userLastName'], $lot_number, $status, $municipality, $barangay, $date_approved, $applicantFullName, $claimantFullName, $claimantLastName, $claimantFirstName, $claimantMiddleName, $claimantExtension, $claimantSex, $claimantBirthday, $claimantAge, $claimantCivilStatus, $claimantSpouseName, $claimantContactNumber, $applicantLastName, $applicantFirstName, $applicantMiddleName, $applicantExtension, $applicantSex, $applicantBirthday, $applicantAge, $applicantCivilStatus, $applicantSpouseName, $applicantContactNumber);
            if (!$moveStmt->execute()) {
                throw new Exception("Failed to move data to notif_repository: " . $moveStmt->error);
            }
            $moveStmt->close();

            // Delete from notifications table
            $deleteSql = "DELETE FROM notifications WHERE notif_id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $notif_id);
            if (!$deleteStmt->execute()) {
                throw new Exception("Failed to delete from notifications: " . $deleteStmt->error);
            }
            $deleteStmt->close();

            $conn->commit();

            // Log the approval action
            logActivity($conn, $userId, $userType, $username, "Approved Lot Number: $lot_number", $userContactNumber, $userEmail);

            header("Location: notifications.php");
            exit;
        } else {
            header("Location: approval_status=error");
            exit;
        }
    } catch (Exception $e) {
        $conn->rollback();

        // Log the error
        logActivity($conn, $userId, $userType, $username, "Failed to approve notification ID: $notif_id. Error: " . $e->getMessage(), $userContactNumber, $userEmail);

        error_log("Transaction failed: " . $e->getMessage());
        header("Location: approval_status=error");
        exit;
    } finally {
        $notificationStmt->close();
        $conn->close();
    }
} else {
    header("Location: index.php");
    exit;
}

?>
