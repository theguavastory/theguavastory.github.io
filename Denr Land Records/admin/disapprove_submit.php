<?php
// Start the session
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

// Database connection setup
$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "denr";

// Create a new MySQLi connection
$conn = new mysqli($servername, $db_username, $db_password, $database);

// Check if connection failed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the logActivity function for activity logging
function logActivity($conn, $userId, $userType, $username, $activity, $userContactNumber, $userEmail) {
    $logSql = "INSERT INTO activity_logs (userId, userType, username, activity, activity_time, userContactNumber, userEmail) 
               VALUES (?, ?, ?, ?, NOW(), ?, ?)";
    $logStmt = $conn->prepare($logSql);
    if (!$logStmt) {
        error_log("Prepare failed: " . $conn->error);
        return;
    }
    $logStmt->bind_param("isssss", $userId, $userType, $username, $activity, $userContactNumber, $userEmail);
    $logStmt->execute();
    $logStmt->close();
}

// Check if the form has been submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['disapprove_update'])) {
    // Get values from POST and session
    $notif_id = $_POST['notif_id'];
    $remarks = $_POST['remarks'];
    $userId = $_SESSION['userId'];
    $username = $_SESSION['username'];

    // Debugging: Log POST data received
    error_log("Received POST data: notif_id = $notif_id, remarks = $remarks");

    // Validate notif_id
    if (empty($notif_id) || !filter_var($notif_id, FILTER_VALIDATE_INT)) {
        error_log("Invalid notif_id: $notif_id");
        header("Location: notifications.php?status=invalid_notif_id");
        exit;
    }

    try {
        // Begin database transaction
        $conn->begin_transaction();

        // Fetch notification details
        $notificationSql = "SELECT * FROM notifications WHERE notif_id = ?";
        $notificationStmt = $conn->prepare($notificationSql);
        if (!$notificationStmt) {
            error_log("Prepare failed: " . $conn->error);
            header("Location: notifications.php?status=error");
            exit;
        }
        $notificationStmt->bind_param("i", $notif_id);
        $notificationStmt->execute();
        $notificationResult = $notificationStmt->get_result();

        if ($notificationResult->num_rows > 0) {
            $notificationRow = $notificationResult->fetch_assoc();

            // Your existing checks for applicant and claimant IDs...

            // Insert into disapproved table
            $insertDisapproveSql = "INSERT INTO disapproved (notif_id, notif_status, userId, userLastName, userFirstName, userMiddleName, time_sent, lot_number, status, date_approved, municipality, barangay, applicant_name, survey_claimant_name, applicantID, applicantLastName, applicantFirstName, applicantMiddleName, applicantExtension, applicantSex, applicantBirthday, applicantAge, applicantCivilStatus, applicantSpouseName, applicantContactNumber, claimantID, claimantLastName, claimantFirstName, claimantMiddleName, claimantExtension, claimantSex, claimantBirthday, claimantAge, claimantCivilStatus, claimantSpouseName, claimantContactNumber, remarks) 
            VALUES (?, 'Disapproved', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insertStmt = $conn->prepare($insertDisapproveSql);
            if (!$insertStmt) {
                throw new Exception("Database error.");
            }

            $insertStmt->bind_param("iisssssssssssssssssssssssssssssssssssss", 
                $notif_id, $userId, $notificationRow['userLastName'], 
                $notificationRow['userFirstName'], $notificationRow['userMiddleName'], 
                $notificationRow['time_sent'], $notificationRow['lot_number'], 
                $notificationRow['status'], $notificationRow['date_approved'], 
                $notificationRow['municipality'], $notificationRow['barangay'], 
                $notificationRow['applicant_name'], $notificationRow['survey_claimant_name'], 
                $notificationRow['applicantID'], $notificationRow['applicantLastName'], 
                $notificationRow['applicantFirstName'], $notificationRow['applicantMiddleName'], 
                $notificationRow['applicantExtension'], $notificationRow['applicantSex'], 
                $notificationRow['applicantBirthday'], $notificationRow['applicantAge'], 
                $notificationRow['applicantCivilStatus'], $notificationRow['applicantSpouseName'], 
                $notificationRow['applicantContactNumber'], $notificationRow['claimantID'], 
                $notificationRow['claimantLastName'], $notificationRow['claimantFirstName'], 
                $notificationRow['claimantMiddleName'], $notificationRow['claimantExtension'], 
                $notificationRow['claimantSex'], $notificationRow['claimantBirthday'], 
                $notificationRow['claimantAge'], $notificationRow['claimantCivilStatus'], 
                $notificationRow['claimantSpouseName'], $notificationRow['claimantContactNumber'], 
                $remarks ?: ""); // Use an empty string if remarks is null or empty

            $insertStmt->execute();
            $insertStmt->close();

            // Delete notification after disapproval
            $deleteNotifSql = "DELETE FROM notifications WHERE notif_id = ?";
            $deleteStmt = $conn->prepare($deleteNotifSql);
            $deleteStmt->bind_param("i", $notif_id);
            $deleteStmt->execute();
            $deleteStmt->close();

            // Log disapproval activity
            logActivity($conn, $userId, $_SESSION['usertype'], $username, 
                "Disapproved Lot Number: " . $notificationRow['lot_number'], 
                $_SESSION['userContactNumber'], $_SESSION['userEmail']);

            // Commit transaction
            $conn->commit();

            // Redirect to notifications page with status
            header("Location: notifications.php?status=disapproved");
            exit;
        } else {
            error_log("No notification found with ID: $notif_id");
            header("Location: notifications.php?status=error");
            exit;
        }
    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        $conn->rollback();

        // Log error activity
        logActivity($conn, $userId, $_SESSION['usertype'], $username, 
            "Failed to disapprove notification ID: $notif_id. Error: " . $e->getMessage(), 
            $_SESSION['userContactNumber'], $_SESSION['userEmail']);

        // Redirect to notifications page with error status
        header("Location: notifications.php?status=error");
        exit;
    } finally {
        // Close the connection
        $conn->close();
    }
} else {
    // Invalid request, redirect to notifications page
    header("Location: notifications.php?status=invalid_request");
    exit;
}
