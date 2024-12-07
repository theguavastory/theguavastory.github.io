<?php
session_start();

// Check if the user is logged in
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Debugging: Check if notif_id is set
    if (!isset($_POST['notif_id'])) {
        die("notif_id is not set in POST data");
    }

    // Retrieve notif_id from form
    $notif_id = $_POST['notif_id'];

    // Retrieve form data
    $lot_number = $_POST['lot_number'];
    $status = $_POST['status'];
    $municipality = $_POST['municipality'];
    $barangay = $_POST['barangay'];
    $date_approved = $_POST['date_approved'];

    // Applicant data
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

    // Claimant data
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
    $applicantFullName = "$applicantLastName $applicantFirstName $applicantMiddleName";
    $claimantFullName = "$claimantLastName $claimantFirstName $claimantMiddleName";

    // Prepare and bind the statement for updating the notifications table
    $stmt = $conn->prepare("UPDATE notifications SET lot_number = ?, status = ?, municipality = ?, barangay = ?, date_approved = ?, applicant_name = ?, survey_claimant_name = ? WHERE notif_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the parameters and execute the update
    $stmt->bind_param("sssssssi", $lot_number, $status, $municipality, $barangay, $date_approved, $applicantFullName, $claimantFullName, $notif_id);
    if ($stmt->execute()) {
        // Successful update, redirect to another page (e.g., success page or list page)
        header("Location: user_submitted_land_list.php"); // Change this to your desired page
        exit(); // Ensure no further code is executed
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

$conn->close();
?>

