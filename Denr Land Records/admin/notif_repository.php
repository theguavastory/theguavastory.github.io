<?php
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

$username = $_SESSION['username'];
$userLastName = $_SESSION['userLastName'];
$userId = $_SESSION['userId'];
$user_type = $_SESSION['usertype'];

$servername = "localhost";
$db_username = "root"; // Default MySQL username
$db_password = ""; // Default MySQL password (empty)
$database = "denr"; // Your MySQL database name

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for approval and data insertion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approval_update'])) {
    $notif_id = $_POST['notif_id'];

    // Retrieve data from the notifications table
    $notificationSql = "SELECT * FROM notifications WHERE notif_id = ?";
    $notificationStmt = $conn->prepare($notificationSql);
    $notificationStmt->bind_param("i", $notif_id);
    $notificationStmt->execute();
    $notificationResult = $notificationStmt->get_result();

    if ($notificationResult->num_rows > 0) {
        $notificationRow = $notificationResult->fetch_assoc();

        // Move the row to notif_repository table
        $moveSql = "INSERT INTO notif_repository (
            notif_id, notif_status, userId, userLastName, time_sent, lot_number, status, date_approved, municipality, barangay, applicant_name, 
            survey_claimant_name, applicantLastName, applicantFirstName, applicantMiddleName, applicantExtension, applicantSex, applicantBirthday, 
            applicantAge, applicantCivilStatus, applicantSpouseName, applicantContactNumber, 
            claimantLastName, claimantFirstName, claimantMiddleName, claimantExtension, claimantSex, claimantBirthday, claimantAge, 
            claimantCivilStatus, claimantSpouseName, claimantContactNumber
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
        $moveStmt = $conn->prepare($moveSql);
        $moveStmt->bind_param("isississssssssssissssssssssissi", 
            $notificationRow['notif_id'], 
            $notificationRow['notif_status'], 
            $notificationRow['userId'], 
            $notificationRow['userLastName'], 
            $notificationRow['time_sent'], 
            $notificationRow['lot_number'], 
            $notificationRow['status'], 
            $notificationRow['date_approved'], 
            $notificationRow['municipality'], 
            $notificationRow['barangay'], 
            $notificationRow['applicant_name'], 
            $notificationRow['survey_claimant_name'], 
            $notificationRow['applicantLastName'], 
            $notificationRow['applicantFirstName'], 
            $notificationRow['applicantMiddleName'], 
            $notificationRow['applicantExtension'], 
            $notificationRow['applicantSex'], 
            $notificationRow['applicantBirthday'], 
            $notificationRow['applicantAge'], 
            $notificationRow['applicantCivilStatus'], 
            $notificationRow['applicantSpouseName'], 
            $notificationRow['applicantContactNumber'], 
            $notificationRow['claimantLastName'], 
            $notificationRow['claimantFirstName'], 
            $notificationRow['claimantMiddleName'], 
            $notificationRow['claimantExtension'], 
            $notificationRow['claimantSex'], 
            $notificationRow['claimantBirthday'], 
            $notificationRow['claimantAge'], 
            $notificationRow['claimantCivilStatus'], 
            $notificationRow['claimantSpouseName'],  
            $notificationRow['claimantContactNumber']);
        $moveStmt->execute();
        $moveStmt->close();

        // Delete the row from the notifications table
        $deleteSql = "DELETE FROM notifications WHERE notif_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $notif_id);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Redirect after processing
        header("Location: notifications.php");
        exit;
    } else {
        echo "Error: Notification not found";
    }

    $notificationStmt->close();
}

$conn->close();
?>
