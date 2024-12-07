<?php
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

$username = $_SESSION['username'];

$servername = "localhost";
$db_username = "root"; // Changed variable name to avoid confusion
$password = "";
$database = "denr";

// Create connection
$conn = new mysqli($servername, $db_username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize row variable
$row = [];

// Check if notif_id is set in the URL
if (isset($_GET['id'])) {
    $notif_id = intval($_GET['id']); // Get notif_id and cast to integer

    // Prepare statement to fetch the row from the database
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE notif_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $notif_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a record was found
        if ($result->num_rows > 0) {
            // Fetch data into row variable
            $row = $result->fetch_assoc();
        } else {
            echo "No record found for this ID.";
            exit;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Failed to prepare statement: " . $conn->error;
        exit;
    }
} else {
    echo "No notif_id provided.";
    exit;
}

// Close the connection
$conn->close();
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
                <span class="form-control"><?php echo htmlspecialchars($row['applicantLastName']); ?></span>
            </div>
            <div class="form-group">
                <label>First Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['applicantFirstName']); ?></span>
            </div>
            <div class="form-group">
                <label>Middle Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['applicantMiddleName']); ?></span>
            </div>
            <div class="form-group">
                <label>Sex:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['applicantSex']); ?></span>
            </div>
            <div class="form-group">
                <label>Birthday:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['applicantBirthday']); ?></span>
            </div>
            <div class="form-group">
                <label>Age:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['applicantAge']); ?></span>
            </div>
            <div class="form-group">
                <label>Civil Status:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['applicantCivilStatus']); ?></span>
            </div>
            <div class="form-group">
                <label>Name of Spouse:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['applicantSpouseName']); ?></span>
            </div>
            <div class="form-group">
                <label>Contact Number:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['applicantContactNumber']); ?></span>
            </div>
        </div>
    </div>

    <div class="column">
        <div class="section">
            <h4>Survey Claimant Information</h4>
            <div class="form-group">
                <label>Last Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['claimantLastName']); ?></span>
            </div>
            <div class="form-group">
                <label>First Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['claimantFirstName']); ?></span>
            </div>
            <div class="form-group">
                <label>Middle Name:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['claimantMiddleName']); ?></span>
            </div>
            <div class="form-group">
                <label>Sex:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['claimantSex']); ?></span>
            </div>
            <div class="form-group">
                <label>Birthday:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['claimantBirthday']); ?></span>
            </div>
            <div class="form-group">
                <label>Age:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['claimantAge']); ?></span>
            </div>
            <div class="form-group">
                <label>Civil Status:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['claimantCivilStatus']); ?></span>
            </div>
            <div class="form-group">
                <label>Name of Spouse:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['claimantSpouseName']); ?></span>
            </div>
            <div class="form-group">
                <label>Contact Number:</label>
                <span class="form-control"><?php echo htmlspecialchars($row['claimantContactNumber']); ?></span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
