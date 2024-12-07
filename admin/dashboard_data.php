<?php
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

// Query to get the count of each status
$sql = "SELECT status, COUNT(*) as count FROM verified_land GROUP BY status";
$result = $conn->query($sql);

$statusCounts = [
    'SA (Survey Authority)' => 0,
    'RSA (Request for Survey Authority)' => 0,
    'No PLA (No Public Land Application)' => 0,
    'FPA(Free Patent Application)' => 0,
    'RFPA (Request for Free Patent Application)' => 0
];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = $row['status'];
        if (isset($statusCounts[$status])) {
            $statusCounts[$status] = $row['count'];
        }
    }
}

$conn->close();
?>
