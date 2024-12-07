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
$db_username = "root";
$db_password = "";
$database = "denr";

$conn = new mysqli($servername, $db_username, $db_password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define logActivity function
function logActivity($conn, $userId, $userType, $username, $activity, $userContactNumber = null, $userEmail = null) {
    $activity_time = date('Y-m-d H:i:s');
    
    $logQuery = $conn->prepare("INSERT INTO activity_logs (userId, userType, username, activity, activity_time, userContactNumber, userEmail) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $logQuery->bind_param("issssss", $userId, $userType, $username, $activity, $activity_time, $userContactNumber, $userEmail);

    if (!$logQuery->execute()) {
        echo "Error inserting into activity_logs table: " . $logQuery->error;
    }

    $logQuery->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data from form submission
    $lot_number = $_POST['lot_number'];
    $status = $_POST['status'];
    $municipality = $_POST['municipality'];
    $barangay = $_POST['barangay'];
    $date_approved = $_POST['date_approved'];

    $applicantLastName = $_POST['applicantLastName'];
    $applicantFirstName = $_POST['applicantFirstName'];
    $applicantMiddleName = $_POST['applicantMiddleName'];
    $applicantExtension = isset($_POST['applicantExtension']) ? $_POST['applicantExtension'] : ''; // Default if missing
    $applicantSex = $_POST['applicantSex'];
    $applicantBirthday = $_POST['applicantBirthday'];
    $applicantAge = $_POST['applicantAge'];
    $applicantCivilStatus = $_POST['applicantCivilStatus'];
    $applicantSpouseName = $_POST['applicantSpouseName'];
    $applicantContactNumber = $_POST['applicantContactNumber'];

    $claimantLastName = $_POST['claimantLastName'];
    $claimantFirstName = $_POST['claimantFirstName'];
    $claimantMiddleName = $_POST['claimantMiddleName'];
    $claimantExtension = isset($_POST['claimantExtension']) ? $_POST['claimantExtension'] : ''; // Default if missing
    $claimantSex = $_POST['claimantSex'];
    $claimantBirthday = $_POST['claimantBirthday'];
    $claimantAge = $_POST['claimantAge'];
    $claimantCivilStatus = $_POST['claimantCivilStatus'];
    $claimantSpouseName = $_POST['claimantSpouseName'];
    $claimantContactNumber = $_POST['claimantContactNumber'];

    $applicantFullName = "$applicantLastName $applicantFirstName $applicantMiddleName";
    $claimantFullName = "$claimantLastName $claimantFirstName $claimantMiddleName";

    $notif_status = 'Approval needed';
    $time_sent = date('Y-m-d H:i:s');

    // Handling coordinates input
    $coordinates = isset($_POST['coordinates']) ? $_POST['coordinates'] : ''; // Get coordinates from form (if any)
    $latitude = $longitude = null;

    if (!empty($coordinates)) {
        // Split the coordinates input (e.g., "14.268779630287273, 121.4445162728201")
        $coordsArray = explode(',', $coordinates);

        if (count($coordsArray) === 2) {
            $latitude = trim($coordsArray[0]);
            $longitude = trim($coordsArray[1]);
        }
    }

    // Insert the data into the notifications table
    $notificationQuery = $conn->prepare("INSERT INTO notifications 
        (notif_status, userId, userLastName, time_sent, lot_number, status, date_approved, 
        municipality, barangay, applicant_name, survey_claimant_name, 
        applicantLastName, applicantFirstName, applicantMiddleName, applicantExtension, 
        applicantSex, applicantBirthday, applicantAge, applicantCivilStatus, applicantSpouseName, 
        applicantContactNumber, claimantLastName, claimantFirstName, claimantMiddleName, claimantExtension, 
        claimantSex, claimantBirthday, claimantAge, claimantCivilStatus, claimantSpouseName, claimantContactNumber, 
        latitude, longitude) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Bind parameters, ensuring the correct types
    $notificationQuery->bind_param("sisssssssssssssssssssssssssssssdd", 
        $notif_status, $userId, $userLastName, $time_sent, $lot_number, $status, $date_approved, 
        $municipality, $barangay, $applicantFullName, $claimantFullName, 
        $applicantLastName, $applicantFirstName, $applicantMiddleName, $applicantExtension, 
        $applicantSex, $applicantBirthday, $applicantAge, $applicantCivilStatus, $applicantSpouseName, 
        $applicantContactNumber, $claimantLastName, $claimantFirstName, $claimantMiddleName, $claimantExtension, 
        $claimantSex, $claimantBirthday, $claimantAge, $claimantCivilStatus, $claimantSpouseName, $claimantContactNumber, 
        $latitude, $longitude);

    if ($notificationQuery->execute()) {
        logActivity($conn, $userId, $user_type, $username, "Submitted land title with Lot Number: $lot_number to notifications");

        header("Location: user_add_land_title.php");
        exit;
    } else {
        echo "Error inserting into notifications table: " . $notificationQuery->error;
    }

    $notificationQuery->close();
}

$conn->close();
?>
