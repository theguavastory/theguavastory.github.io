<?php
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

$user = $_SESSION['username'];

// Check if the ID is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $verified_landID = $_GET['id'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "denr";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve data from verified_land table
    $stmt = $conn->prepare("SELECT * FROM verified_land WHERE verified_landID = ?");
    $stmt->bind_param("i", $verified_landID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Retrieve applicant data
    $applicantID = $row['applicantID'];
    $applicantQuery = $conn->prepare("SELECT * FROM applicant WHERE applicantID = ?");
    $applicantQuery->bind_param("i", $applicantID);
    $applicantQuery->execute();
    $applicantResult = $applicantQuery->get_result();
    $applicantRow = $applicantResult->fetch_assoc();

    // Retrieve survey claimant data
    $claimantID = $row['claimantID'];
    $claimantQuery = $conn->prepare("SELECT * FROM surveyclaimant WHERE claimantID = ?");
    $claimantQuery->bind_param("i", $claimantID);
    $claimantQuery->execute();
    $claimantResult = $claimantQuery->get_result();
    $claimantRow = $claimantResult->fetch_assoc();

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Title Information</title>
    <style>
         body {
            font-family: Arial, sans-serif;
            margin: 20px;
            position: relative;
        }

        /* Watermark styling */
        body::before {
            content: "";
            position: absolute;
            top: 70%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-image: url('../images/logo.png'); /* Path to the watermark image */
            background-size: contain;
            background-repeat: no-repeat;
            width: 80%; /* Adjust watermark size */
            height: 80%; /* Adjust watermark size */
            opacity: 0.1; /* Adjust opacity */
            z-index: -1; /* Place it behind content */
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }
        .header img {
            position: absolute;
            left: 0;
            top: 0;
            width: 80px;
            height: auto;
        }
        .header h6, .header h3, .header h4, .header p {
            margin: 0;
            font-size: 14px;
        }
        h2 {
            text-align: center;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h4 {
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        label {
            font-weight: bold;
        }
        .form-control {
            margin-left: 10px;
        }
        /* Styles for two-column layout */
        .columns {
            display: flex;
            justify-content: space-between;
        }
        .column {
            width: 48%;
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>
<body>

<div class="header">
    <img src="../images/logo.png" alt="DENR Logo">
    <h6>Republic of the Philippines</h6>
    <h3>Department of Natural Resources</h3>
    <h4>Community Environment and Natural Resources Office</h4>
    <p>Brgy. Duhat, Santa Cruz, Laguna</p>
</div>

<h2>Land Title Information</h2>

<div class="section">
    <h4>Land Information</h4>
    <div class="form-group">
        <label>Lot Number:</label>
        <span class="form-control"><?php echo htmlspecialchars($row['lot_number']); ?></span>
    </div>
    <div class="form-group">
        <label>Status:</label>
        <span class="form-control"><?php echo htmlspecialchars($row['status']); ?></span>
    </div>
    <div class="form-group">
        <label>Municipality:</label>
        <span class="form-control"><?php echo htmlspecialchars($row['municipality']); ?></span>
    </div>
    <div class="form-group">
        <label>Barangay:</label>
        <span class="form-control"><?php echo htmlspecialchars($row['barangay']); ?></span>
    </div>
    <div class="form-group">
        <label>Date Approved:</label>
        <span class="form-control"><?php echo htmlspecialchars($row['date_approved']); ?></span>
    </div>
</div>

<!-- Two-column layout for Applicant and Survey Claimant Information -->
<div class="columns">
    <div class="column">
        <div class="section">
            <h4>Applicant Information</h4>
            <div class="form-group">
                <label>Last Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($applicantRow['applicantLastName']); ?></span>
            </div>
            <div class="form-group">
                <label>First Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($applicantRow['applicantFirstName']); ?></span>
            </div>
            <div class="form-group">
                <label>Middle Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($applicantRow['applicantMiddleName']); ?></span>
            </div>
            <div class="form-group">
                <label>Sex:</label>
                <span class="form-control"><?php echo htmlspecialchars($applicantRow['applicantSex']); ?></span>
            </div>
            <div class="form-group">
                <label>Birthday:</label>
                <span class="form-control"><?php echo htmlspecialchars($applicantRow['applicantBirthday']); ?></span>
            </div>
            <div class="form-group">
                <label>Age:</label>
                <span class="form-control"><?php echo htmlspecialchars($applicantRow['applicantAge']); ?></span>
            </div>
            <div class="form-group">
                <label>Civil Status:</label>
                <span class="form-control"><?php echo htmlspecialchars($applicantRow['applicantCivilStatus']); ?></span>
            </div>
            <div class="form-group">
                <label>Name of Spouse:</label>
                <span class="form-control"><?php echo htmlspecialchars($applicantRow['applicantSpouseName']); ?></span>
            </div>
            <div class="form-group">
                <label>Contact Number:</label>
                <span class="form-control"><?php echo htmlspecialchars($applicantRow['applicantContactNumber']); ?></span>
            </div>
        </div>
    </div>

    <div class="column">
        <div class="section">
            <h4>Survey Claimant Information</h4>
            <div class="form-group">
                <label>Last Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($claimantRow['claimantLastName']); ?></span>
            </div>
            <div class="form-group">
                <label>First Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($claimantRow['claimantFirstName']); ?></span>
            </div>
            <div class="form-group">
                <label>Middle Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($claimantRow['claimantMiddleName']); ?></span>
            </div>
            <div class="form-group">
                <label>Sex:</label>
                <span class="form-control"><?php echo htmlspecialchars($claimantRow['claimantSex']); ?></span>
            </div>
            <div class="form-group">
                <label>Birthday:</label>
                <span class="form-control"><?php echo htmlspecialchars($claimantRow['claimantBirthday']); ?></span>
            </div>
            <div class="form-group">
                <label>Age:</label>
                <span class="form-control"><?php echo htmlspecialchars($claimantRow['claimantAge']); ?></span>
            </div>
            <div class="form-group">
                <label>Civil Status:</label>
                <span class="form-control"><?php echo htmlspecialchars($claimantRow['claimantCivilStatus']); ?></span>
            </div>
            <div class="form-group">
                <label>Name of Spouse:</label>
                <span class="form-control"><?php echo htmlspecialchars($claimantRow['claimantSpouseName']); ?></span>
            </div>
            <div class="form-group">
                <label>Contact Number:</label>
                <span class="form-control"><?php echo htmlspecialchars($claimantRow['claimantContactNumber']); ?></span>
            </div>
        </div>
    </div>
</div>

</body>
</html>

