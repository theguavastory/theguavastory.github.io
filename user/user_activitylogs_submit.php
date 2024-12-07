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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lot_number = $_POST['lot_number'];
    $status = $_POST['status'];
    $municipality = $_POST['municipality'];
    $barangay = $_POST['barangay'];
    $date_approved = $_POST['date_approved'];

    $applicantLastName = $_POST['applicantLastName'];
    $applicantFirstName = $_POST['applicantFirstName'];
    $applicantMiddleName = $_POST['applicantMiddleName'];
    $applicantSex = $_POST['applicantSex'];
    $applicantBirthday = $_POST['applicantBirthday'];
    $applicantAge = $_POST['applicantAge'];
    $applicantContactNumber = $_POST['applicantContactNumber'];

    $claimantLastName = $_POST['claimantLastName'];
    $claimantFirstName = $_POST['claimantFirstName'];
    $claimantMiddleName = $_POST['claimantMiddleName'];
    $claimantSex = $_POST['claimantSex'];
    $claimantBirthday = $_POST['claimantBirthday'];
    $claimantAge = $_POST['claimantAge'];
    $claimantContactNumber = $_POST['claimantContactNumber'];

    // Concatenate full names
    $applicantFullName = "$applicantLastName $applicantFirstName $applicantMiddleName";
    $claimantFullName = "$claimantLastName $claimantFirstName $claimantMiddleName";

    // Insert into notifications table
    $notif_status = 'Approval needed';
    $time_sent = date('Y-m-d H:i:s');

    $notificationQuery = $conn->prepare("INSERT INTO notifications (notif_status, userId, userLastName, time_sent, lot_number, status, date_approved, municipality, barangay, applicant_name, survey_claimant_name, applicantLastName, applicantFirstName, applicantMiddleName, applicantSex, applicantBirthday, applicantAge, applicantContactNumber, claimantLastName, claimantFirstName, claimantMiddleName, claimantSex, claimantBirthday, claimantAge, claimantContactNumber) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $notificationQuery->bind_param("sisssssssssssssssssssssss", $notif_status, $userId, $userLastName, $time_sent, $lot_number, $status, $date_approved, $municipality, $barangay, $applicantFullName, $claimantFullName, $applicantLastName, $applicantFirstName, $applicantMiddleName, $applicantSex, $applicantBirthday, $applicantAge, $applicantContactNumber, $claimantLastName, $claimantFirstName, $claimantMiddleName, $claimantSex, $claimantBirthday, $claimantAge, $claimantContactNumber);



    $notificationQuery->close();
}

$conn->close();
?>
