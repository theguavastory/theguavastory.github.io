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

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize SQL query
$sql = "SELECT notif_id, userLastName, time_sent, lot_number, status, date_approved, applicant_name, survey_claimant_name, barangay, municipality, notif_status FROM notifications WHERE 1=1";

// Check if status filter is applied
if (!empty($_GET['status'])) {
    $statuses = implode("','", $_GET['status']);
    $sql .= " AND status IN ('$statuses')";
}

// Check if municipality filter is applied
if (!empty($_GET['municipalities'])) {
    $municipalities = implode("','", $_GET['municipalities']);
    $sql .= " AND municipality IN ('$municipalities')";
}

// Check if barangay filter is applied
if (!empty($_GET['barangays'])) {
    $barangays = implode("','", $_GET['barangays']);
    $sql .= " AND barangay IN ('$barangays')";
}

// Execute the query
$sql .= " ORDER BY time_sent DESC";
$result = $conn->query($sql);
// Fetch notification count
$sql_count = "SELECT COUNT(*) AS count FROM notifications";
$result_count = $conn->query($sql_count);
$notification_count = 0;
if ($result_count->num_rows > 0) {
    $row_count = $result_count->fetch_assoc();
    $notification_count = $row_count['count'];
}

$userId = $_SESSION['userId'];

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR-CENRO: List of Verified Land Titles</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <?php include 'style.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="content">
<nav class="navbar navbar-custom">
    <span class="navbar-brand mb-0 h1 responsive-text">Record Management of Verified Land Records</span>
    <div class="user-info d-none d-md-flex">
        <span class="badge">Logged in as:</span>
        <span class="username"><?php echo htmlspecialchars($username); ?></span>
    </div>
</nav>
    <div class="search-bar">
            <form method="GET" action="search_results.php" class="form-inline">
                <div class="input-group w-100">
                    <input type="text" name="query" class="form-control" placeholder="Search by lot number, applicant, survey claimant, status, municipality, barangay...">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>
        </div>

       

        <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Approval Request</h3>
        <div class="d-flex">
            <button type="button" class="btn btn-info btn-sm p-2 me-6" data-toggle="modal" data-target="#advancedFilterModal" title="Advanced Filter">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </div>
</div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Employee Last Name</th>
                    <th>Date Submitted</th>
                    <th>Lot Number</th>
                    <th>Status</th>
                    <th>Date Approved</th>
                    <th>Applicant</th>
                    <th>Survey Claimant</th>
                    <th>Barangay</th>
                    <th>Municipality</th>
                    <th>Approval Status</th>
                </tr>
            </thead>
            <tbody id="notificationTableBody">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr id='row-" . $row["notif_id"] . "'>
                            <td>" . $row["userLastName"] . "</td>
                            <td>" . $row["time_sent"] . "</td>
                            <td>" . $row["lot_number"] . "</td>
                            <td>" . $row["status"] . "</td>
                            <td>" . $row["date_approved"] . "</td>
                            <td>" . $row["applicant_name"] . "</td>
                            <td>" . $row["survey_claimant_name"] . "</td>
                            <td>" . $row["barangay"] . "</td>
                            <td>" . $row["municipality"] . "</td>
                            <td>
                                <form method='POST' action='notification_submit.php'>
                                    <input type='hidden' name='notif_id' value='" . $row["notif_id"] . "'>
                                    <button class='btn btn-primary btn-sm' type='submit' name='approval_update' value='Approve'>
                                        <i class='fas fa-check'></i> Approve
                                    </button>
                                </form>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No records found</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

 <!-- Advanced Filter Modal -->
 <div class="modal fade" id="advancedFilterModal" tabindex="-1" role="dialog" aria-labelledby="advancedFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="advancedFilterModalLabel">Advanced Filter</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="GET" action="notif_filter.php">
                    <!-- Status Checkboxes -->
                    <div class="form-group">
                        <label for="status">Status</label>
                        <div id="status">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="No PLA (No Public Land Application)" id="statusNoPLA" name="status[]">
                                <label class="form-check-label" for="statusNoPLA">No PLA (No Public Land Application)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="SA (Survey Authority" id="statusSA" name="status[]">
                                <label class="form-check-label" for="statusSA">SA (Survey Authority)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="FPA (Free Patent Application)" id="statusFPA" name="status[]">
                                <label class="form-check-label" for="statusFPA">FPA (Free Patent Application)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="RSA (Request for Survey Authority)" id="statusRSA" name="status[]">
                                <label class="form-check-label" for="statusRSA">RSA (Request for Survey Authority)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="RFPA (Request for Free Patent Application)" id="statusRFPA" name="status[]">
                                <label class="form-check-label" for="statusRFPA">RFPA (Request for Free Patent Application)</label>
                            </div>
                        </div>
                    </div>

     <!-- Municipality and Barangay Checkboxes -->
<div class="form-group">
    <label for="municipalities">Municipalities</label>
    <div class="row">
        <div class="col-md-6">
            <div id="municipalitiesLeft">
                <!-- Left Column Municipalities -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Alaminos" id="municipalityAlaminos" name="municipalities[]">
                    <label class="form-check-label" for="municipalityAlaminos">Alaminos</label>
                </div>
                <div class="barangays" id="barangaysAlaminos" style="display:none; padding-left: 20px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Barangay I (Poblacion)" id="Barangay I (Poblacion)" name="barangays[]">
                        <label class="form-check-label" for="barangayI">Barangay I (Poblacion)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Barangay II (Poblacion)" id="Barangay II (Poblacion)" name="barangays[]">
                        <label class="form-check-label" for="barangayII">Barangay II (Poblacion)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Barangay III (Poblacion)" id="Barangay III (Poblacion)" name="barangays[]">
                        <label class="form-check-label" for="barangayIII">Barangay III (Poblacion)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Barangay IV (Poblacion)" id="Barangay IV (Poblacion)" name="barangays[]">
                        <label class="form-check-label" for="barangayIV">Barangay IV (Poblacion)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Del Carme" id="Del Carme" name="barangays[]">
                        <label class="form-check-label" for="delCarmen">Del Carmen</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Palma" id="Palma" name="barangays[]">
                        <label class="form-check-label" for="palma">Palma</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Agustin (Antipolo)" id="San Agustin (Antipolo)" name="barangays[]">
                        <label class="form-check-label" for="sanAgustin">San Agustin (Antipolo)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Andres" id="San Andres" name="barangays[]">
                        <label class="form-check-label" for="sanAndres">San Andres</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Benito (Palita)" id="San Benito (Palita)" name="barangays[]">
                        <label class="form-check-label" for="sanBenito">San Benito (Palita)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Gregorio" id="San Gregorio" name="barangays[]">
                        <label class="form-check-label" for="sanGregorio">San Gregorio</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Ildefonso" id="San Ildefonso" name="barangays[]">
                        <label class="form-check-label" for="sanIldefonso">San Ildefonso</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Juan" id="San Juan" name="barangays[]">
                        <label class="form-check-label" for="sanJuan">San Juan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Miguel" id="San Miguel" name="barangays[]">
                        <label class="form-check-label" for="sanMiguel">San Miguel</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Roque" id="San Roque" name="barangays[]">
                        <label class="form-check-label" for="sanRoque">San Roque</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Santa Rosa" id="Santa Rosa" name="barangays[]">
                        <label class="form-check-label" for="santaRosa">Santa Rosa</label>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Calauan" id="municipalityCalauan" name="municipalities[]">
                    <label class="form-check-label" for="municipalityCalauan">Calauan</label>
                </div>
                <div class="barangays" id="barangaysCalauan" style="display:none; padding-left: 20px;">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Balayhangin" id="Balayhangin" name="barangays[]">
                <label class="form-check-label" for="Balayhangin">Balayhangin</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Bangyas" id="Bangyas" name="barangays[]">
                <label class="form-check-label" for="Bangyas">Bangyas</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Dayap" id="Dayap" name="barangays[]">
                <label class="form-check-label" for="Dayap">Dayap</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Hanggan" id="Hanggan" name="barangays[]">
                <label class="form-check-label" for="Hanggan">Hanggan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Imok" id="Imok" name="barangays[]">
                <label class="form-check-label" for="Imok">Imok</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Lamot 1" id="Lamot1" name="barangays[]">
                <label class="form-check-label" for="Lamot1">Lamot 1</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Lamot 2" id="Lamot2" name="barangays[]">
                <label class="form-check-label" for="Lamot2">Lamot 2</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Limao" id="Limao" name="barangays[]">
                <label class="form-check-label" for="Limao">Limao</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Mabacan" id="Mabacan" name="barangays[]">
                <label class="form-check-label" for="Mabacan">Mabacan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Masiit" id="Masiit" name="barangays[]">
                <label class="form-check-label" for="Masiit">Masiit</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Paliparan" id="Paliparan" name="barangays[]">
                <label class="form-check-label" for="Paliparan">Paliparan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Pérez" id="Pérez" name="barangays[]">
                <label class="form-check-label" for="Pérez">Pérez</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Kanluran" id="Kanluran" name="barangays[]">
                <label class="form-check-label" for="Kanluran">Kanluran (Poblacion)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Silangan" id="Silangan" name="barangays[]">
                <label class="form-check-label" for="Silangan">Silangan (Poblacion)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Prinza" id="Prinza" name="barangays[]">
                <label class="form-check-label" for="Prinza">Prinza</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="San Isidro" id="SanIsidro" name="barangays[]">
                <label class="form-check-label" for="SanIsidro">San Isidro</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Santo Tomas" id="SantoTomas" name="barangays[]">
                <label class="form-check-label" for="SantoTomas">Santo Tomas</label>
            </div>
        </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Cavinti" id="municipalityCavinti" name="municipalities[]">
                    <label class="form-check-label" for="municipalityCavinti">Cavinti</label>
                </div>
                <div class="barangays" id="barangaysCavinti" style="display:none; padding-left: 20px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Anglas" id="Anglas" name="barangays[]">
                    <label class="form-check-label" for="Anglas">Anglas</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bangco" id="Bangco" name="barangays[]">
                    <label class="form-check-label" for="Bangco">Bangco</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bukal" id="Bukal" name="barangays[]">
                    <label class="form-check-label" for="Bukal">Bukal</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bulajo" id="Bulajo" name="barangays[]">
                    <label class="form-check-label" for="Bulajo">Bulajo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Cansuso" id="Cansuso" name="barangays[]">
                    <label class="form-check-label" for="Cansuso">Cansuso</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Duhat" id="Duhat" name="barangays[]">
                    <label class="form-check-label" for="Duhat">Duhat</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Inao-Awan" id="Inao-Awan" name="barangays[]">
                    <label class="form-check-label" for="InaoAwan">Inao-Awan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Kanluran Talaongan" id="Kanluran Talaongan" name="barangays[]">
                    <label class="form-check-label" for="KanluranTalaongan">Kanluran Talaongan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Labayo" id="Labayo" name="barangays[]">
                    <label class="form-check-label" for="Labayo">Labayo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Layasin" id="Layasin" name="barangays[]">
                    <label class="form-check-label" for="Layasin">Layasin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Layug" id="Layug" name="barangays[]">
                    <label class="form-check-label" for="Layug">Layug</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Lumot/Mahipon" id="Lumot/Mahipon" name="barangays[]">
                    <label class="form-check-label" for="LumotMahipon">Lumot/Mahipon</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Paowin" id="Paowin" name="barangays[]">
                    <label class="form-check-label" for="Paowin">Paowin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Poblacion" id="Poblacion" name="barangays[]">
                    <label class="form-check-label" for="Poblacion">Poblacion</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Sisilmin" id="Sisilmin" name="barangays[]">
                    <label class="form-check-label" for="Sisilmin">Sisilmin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Silangan Talaongan" id="Silangan Talaongan" name="barangays[]">
                    <label class="form-check-label" for="SilanganTalaongan">Silangan Talaongan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Sumucab" id="Sumucab" name="barangays[]">
                    <label class="form-check-label" for="Sumucab">Sumucab</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Tibatib" id="Tibatib" name="barangays[]">
                    <label class="form-check-label" for="Tibatib">Tibatib</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Udia" id="Udia" name="barangays[]">
                    <label class="form-check-label" for="Udia">Udia</label>
                </div>
            </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Famy" id="municipalityFamy" name="municipalities[]">
                    <label class="form-check-label" for="municipalityFamy">Famy</label>
                </div>
                <div class="barangays" id="barangaysFamy" style="display:none; padding-left: 20px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Asana (Poblacion)" id="Asana (Poblacion)" name="barangays[]">
                    <label class="form-check-label" for="AsanaPoblacion">Asana (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bacong-Sigsigan" id="Bacong-Sigsigan" name="barangays[]">
                    <label class="form-check-label" for="BacongSigsigan">Bacong-Sigsigan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bagong Pag-Asa (Poblacion)" id="Bagong Pag-Asa (Poblacion)" name="barangays[]">
                    <label class="form-check-label" for="BagongPagAsaPoblacion">Bagong Pag-Asa (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Balitoc" id="Balitoc" name="barangays[]">
                    <label class="form-check-label" for="Balitoc">Balitoc</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Banaba (Poblacion)" id="Banaba (Poblacion)" name="barangays[]">
                    <label class="form-check-label" for="BanabaPoblacion">Banaba (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Batuhan" id="Batuhan" name="barangays[]">
                    <label class="form-check-label" for="Batuhan">Batuhan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bulihan" id="Bulihan" name="barangays[]">
                    <label class="form-check-label" for="Bulihan">Bulihan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Caballero (Poblacion)" id="Caballero (Poblacion)" name="barangays[]">
                    <label class="form-check-label" for="CaballeroPoblacion">Caballero (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Calumpang (Poblacion)" id="Calumpang (Poblacion)" name="barangays[]">
                    <label class="form-check-label" for="CalumpangPoblacion">Calumpang (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Kapatalan" id="Kapatalan" name="barangays[]">
                    <label class="form-check-label" for="Kapatalan">Kapatalan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Cuebang Bato" id="Cuebang Bato" name="barangays[]">
                    <label class="form-check-label" for="CuebangBato">Cuebang Bato</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Damayan (Poblacion)" id="Damayan (Poblacion)" name="barangays[]">
                    <label class="form-check-label" for="DamayanPoblacion">Damayan (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Kataypuanan" id="Kataypuanan" name="barangays[]">
                    <label class="form-check-label" for="Kataypuanan">Kataypuanan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Liyang" id="Liyang" name="barangays[]">
                    <label class="form-check-label" for="Liyang">Liyang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Maate" id="Maate" name="barangays[]">
                    <label class="form-check-label" for="Maate">Maate</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Magdalo (Poblacion)" id="Magdalo (Poblacion)" name="barangays[]">
                    <label class="form-check-label" for="MagdaloPoblacion">Magdalo (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Mayatba" id="Mayatba" name="barangays[]">
                    <label class="form-check-label" for="Mayatba">Mayatba</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Minayutan" id="Minayutan" name="barangays[]">
                    <label class="form-check-label" for="Minayutan">Minayutan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Salangbato" id="Salangbato" name="barangays[]">
                    <label class="form-check-label" for="Salangbato">Salangbato</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Tunhac" id="Tunhac" name="barangays[]">
                    <label class="form-check-label" for="Tunhac">Tunhac</label>
                </div>
            </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Kalayaan" id="municipalityKalayaan" name="municipalities[]">
                    <label class="form-check-label" for="municipalityKalayaan">Kalayaan</label>
                </div>
                <div class="barangays" id="barangaysKalayaan" style="display:none; padding-left: 20px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Longos" id="Longos" name="barangays[]">
                    <label class="form-check-label" for="Longos">Longos</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="SanAntonio" id="San Antonio" name="barangays[]">
                    <label class="form-check-label" for="San Antonio">San Antonio</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="SanJuan (Poblacion)" id="SanJuan (Poblacion)" name="barangays[]">
                    <label class="form-check-label" for="SanJuanPoblacion">San Juan (Poblacion)</label>
                </div>
            </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Liliw" id="municipalityLiliw" name="municipalities[]">
                    <label class="form-check-label" for="municipalityLiliw">Liliw</label>
                </div>
                <div class="barangays" id="barangaysLiliw" style="display:none; padding-left: 20px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bagong Anyo (Poblacion)" id="bagongAnyoLiliw" name="barangays[]">
                    <label class="form-check-label" for="bagongAnyoLiliw">Bagong Anyo (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bayate" id="bayateLiliw" name="barangays[]">
                    <label class="form-check-label" for="bayateLiliw">Bayate</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bongkol" id="bongkolLiliw" name="barangays[]">
                    <label class="form-check-label" for="bongkolLiliw">Bongkol</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bubukal" id="bubukalLiliw" name="barangays[]">
                    <label class="form-check-label" for="bubukalLiliw">Bubukal</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Cabuyew" id="cabuyewLiliw" name="barangays[]">
                    <label class="form-check-label" for="cabuyewLiliw">Cabuyew</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Calumpang" id="calumpangLiliw" name="barangays[]">
                    <label class="form-check-label" for="calumpangLiliw">Calumpang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Isidro" id="sanIsidroLiliw" name="barangays[]">
                    <label class="form-check-label" for="sanIsidroLiliw">San Isidro</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Culoy" id="culoyLiliw" name="barangays[]">
                    <label class="form-check-label" for="culoyLiliw">Culoy</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Dagatan" id="dagatanLiliw" name="barangays[]">
                    <label class="form-check-label" for="dagatanLiliw">Dagatan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Daniw" id="daniwLiliw" name="barangays[]">
                    <label class="form-check-label" for="daniwLiliw">Daniw</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Dita" id="ditaLiliw" name="barangays[]">
                    <label class="form-check-label" for="ditaLiliw">Dita</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ibabang Palina" id="ibabangPalinaLiliw" name="barangays[]">
                    <label class="form-check-label" for="ibabangPalinaLiliw">Ibabang Palina</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ibabang San Roque" id="ibabangSanRoqueLiliw" name="barangays[]">
                    <label class="form-check-label" for="ibabangSanRoqueLiliw">Ibabang San Roque</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ibabang Sungi" id="ibabangSungiLiliw" name="barangays[]">
                    <label class="form-check-label" for="ibabangSungiLiliw">Ibabang Sungi</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ibabang Taykin" id="ibabangTaykinLiliw" name="barangays[]">
                    <label class="form-check-label" for="ibabangTaykinLiliw">Ibabang Taykin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ilayang Palina" id="ilayangPalinaLiliw" name="barangays[]">
                    <label class="form-check-label" for="ilayangPalinaLiliw">Ilayang Palina</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ilayang San Roque" id="ilayangSanRoqueLiliw" name="barangays[]">
                    <label class="form-check-label" for="ilayangSanRoqueLiliw">Ilayang San Roque</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ilayang Sungi" id="ilayangSungiLiliw" name="barangays[]">
                    <label class="form-check-label" for="ilayangSungiLiliw">Ilayang Sungi</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ilayang Taykin" id="ilayangTaykinLiliw" name="barangays[]">
                    <label class="form-check-label" for="ilayangTaykinLiliw">Ilayang Taykin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Kanlurang Bukal" id="kanlurangBukalLiliw" name="barangays[]">
                    <label class="form-check-label" for="kanlurangBukalLiliw">Kanlurang Bukal</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Laguan" id="laguanLiliw" name="barangays[]">
                    <label class="form-check-label" for="laguanLiliw">Laguan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Luquin" id="luquinLiliw" name="barangays[]">
                    <label class="form-check-label" for="luquinLiliw">Luquin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Malabo-Kalantukan" id="malaboKalantukanLiliw" name="barangays[]">
                    <label class="form-check-label" for="malaboKalantukanLiliw">Malabo-Kalantukan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Masikap (Poblacion)" id="masikapLiliw" name="barangays[]">
                    <label class="form-check-label" for="masikapLiliw">Masikap (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Maslun (Poblacion)" id="maslunLiliw" name="barangays[]">
                    <label class="form-check-label" for="maslunLiliw">Maslun (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Mojon" id="mojonLiliw" name="barangays[]">
                    <label class="form-check-label" for="mojonLiliw">Mojon</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Novaliches" id="novalichesLiliw" name="barangays[]">
                    <label class="form-check-label" for="novalichesLiliw">Novaliches</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Oples" id="oplesLiliw" name="barangays[]">
                    <label class="form-check-label" for="oplesLiliw">Oples</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Pag-asa (Poblacion)" id="pagasaLiliw" name="barangays[]">
                    <label class="form-check-label" for="pagasaLiliw">Pag-asa (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Palayan" id="palayanLiliw" name="barangays[]">
                    <label class="form-check-label" for="palayanLiliw">Palayan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Rizal (Poblacion)" id="rizalLiliw" name="barangays[]">
                    <label class="form-check-label" for="rizalLiliw">Rizal (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Isidro" id="sanIsidro2Liliw" name="barangays[]">
                    <label class="form-check-label" for="sanIsidro2Liliw">San Isidro</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Silangang Bukal" id="silangangBukalLiliw" name="barangays[]">
                    <label class="form-check-label" for="silangangBukalLiliw">Silangang Bukal</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Tuy-Baanan" id="tuyBaananLiliw" name="barangays[]">
                    <label class="form-check-label" for="tuyBaananLiliw">Tuy-Baanan</label>
                </div>
            </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Luisiana" id="municipalityLuisiana" name="municipalities[]">
                    <label class="form-check-label" for="municipalityLuisiana">Luisiana</label>
                </div>
                <div class="barangays" id="barangaysLuisiana" style="display:none; padding-left: 20px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="De La Paz" id="deLaPazLuisiana" name="barangays[]">
                    <label class="form-check-label" for="deLaPazLuisiana">De La Paz</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Barangay Zone I (Poblacion)" id="zone1Luisiana" name="barangays[]">
                    <label class="form-check-label" for="zone1Luisiana">Barangay Zone I (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Barangay Zone II (Poblacion)" id="zone2Luisiana" name="barangays[]">
                    <label class="form-check-label" for="zone2Luisiana">Barangay Zone II (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Barangay Zone III (Poblacion)" id="zone3Luisiana" name="barangays[]">
                    <label class="form-check-label" for="zone3Luisiana">Barangay Zone III (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Barangay Zone IV (Poblacion)" id="zone4Luisiana" name="barangays[]">
                    <label class="form-check-label" for="zone4Luisiana">Barangay Zone IV (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Barangay Zone V (Poblacion)" id="zone5Luisiana" name="barangays[]">
                    <label class="form-check-label" for="zone5Luisiana">Barangay Zone V (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Barangay Zone VI (Poblacion)" id="zone6Luisiana" name="barangays[]">
                    <label class="form-check-label" for="zone6Luisiana">Barangay Zone VI (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Barangay Zone VII (Poblacion)" id="zone7Luisiana" name="barangays[]">
                    <label class="form-check-label" for="zone7Luisiana">Barangay Zone VII (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Barangay Zone VIII (Poblacion)" id="zone8Luisiana" name="barangays[]">
                    <label class="form-check-label" for="zone8Luisiana">Barangay Zone VIII (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Antonio" id="sanAntonioLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanAntonioLuisiana">San Antonio</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Buenaventura" id="sanBuenaventuraLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanBuenaventuraLuisiana">San Buenaventura</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Diego" id="sanDiegoLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanDiegoLuisiana">San Diego</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Isidro" id="sanIsidroLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanIsidroLuisiana">San Isidro</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San José" id="sanJoseLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanJoseLuisiana">San José</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Juan" id="sanJuanLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanJuanLuisiana">San Juan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Luis" id="sanLuisLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanLuisLuisiana">San Luis</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Pablo" id="sanPabloLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanPabloLuisiana">San Pablo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Pedro" id="sanPedroLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanPedroLuisiana">San Pedro</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Rafaél" id="sanRafaelLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanRafaelLuisiana">San Rafaél</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Roque" id="sanRoqueLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanRoqueLuisiana">San Roque</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Salvador" id="sanSalvadorLuisiana" name="barangays[]">
                    <label class="form-check-label" for="sanSalvadorLuisiana">San Salvador</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Santo Domingo" id="santoDomingoLuisiana" name="barangays[]">
                    <label class="form-check-label" for="santoDomingoLuisiana">Santo Domingo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Santo Tomás" id="santoTomasLuisiana" name="barangays[]">
                    <label class="form-check-label" for="santoTomasLuisiana">Santo Tomás</label>
                </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Lumban" id="municipalityLumban" name="municipalities[]">
                    <label class="form-check-label" for="municipalityLumban">Lumban</label>
                </div>
                <div class="barangays" id="barangaysLumban" style="display:none; padding-left: 20px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bagong Silang" id="bagongSilangLumban" name="barangays[]">
                    <label class="form-check-label" for="bagongSilangLumban">Bagong Silang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Balimbingan (Poblacion)" id="balimbinganLumban" name="barangays[]">
                    <label class="form-check-label" for="balimbinganLumban">Balimbingan (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Balubad" id="balubadLumban" name="barangays[]">
                    <label class="form-check-label" for="balubadLumban">Balubad</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Caliraya" id="calirayaLumban" name="barangays[]">
                    <label class="form-check-label" for="calirayaLumban">Caliraya</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Concepcion" id="concepcionLumban" name="barangays[]">
                    <label class="form-check-label" for="concepcionLumban">Concepcion</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Lewin" id="lewinLumban" name="barangays[]">
                    <label class="form-check-label" for="lewinLumban">Lewin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Maracta (Poblacion)" id="maractaLumban" name="barangays[]">
                    <label class="form-check-label" for="maractaLumban">Maracta (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Maytalang I" id="maytalang1Lumban" name="barangays[]">
                    <label class="form-check-label" for="maytalang1Lumban">Maytalang I</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Maytalang II" id="maytalang2Lumban" name="barangays[]">
                    <label class="form-check-label" for="maytalang2Lumban">Maytalang II</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Primera Parang (Poblacion)" id="primeraParangLumban" name="barangays[]">
                    <label class="form-check-label" for="primeraParangLumban">Primera Parang (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Primera Pulo (Poblacion)" id="primeraPuloLumban" name="barangays[]">
                    <label class="form-check-label" for="primeraPuloLumban">Primera Pulo (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Salac (Poblacion)" id="salacLumban" name="barangays[]">
                    <label class="form-check-label" for="salacLumban">Salac (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Santo Niño (Poblacion)" id="santoNinoLumban" name="barangays[]">
                    <label class="form-check-label" for="santoNinoLumban">Santo Niño (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Segunda Parang (Poblacion)" id="segundaParangLumban" name="barangays[]">
                    <label class="form-check-label" for="segundaParangLumban">Segunda Parang (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Segunda Pulo (Poblacion)" id="segundaPuloLumban" name="barangays[]">
                    <label class="form-check-label" for="segundaPuloLumban">Segunda Pulo (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Wawa" id="wawaLumban" name="barangays[]">
                    <label class="form-check-label" for="wawaLumban">Wawa</label>
                </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Mabitac" id="municipalityMabitac" name="municipalities[]">
                    <label class="form-check-label" for="municipalityMabitac">Mabitac</label>
                </div>
                <div class="barangays" id="barangaysMabitac" style="display:none; padding-left: 20px;">
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Amuyong" id="amuyongMabitac" name="barangays[]">
                    <label class="form-check-label" for="amuyongMabitac">Amuyong</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Lambac (Poblacion)" id="lambacMabitac" name="barangays[]">
                    <label class="form-check-label" for="lambacMabitac">Lambac (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Lucong (Poblacion)" id="lucongMabitac" name="barangays[]">
                    <label class="form-check-label" for="lucongMabitac">Lucong (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Matalatala" id="matalatalaMabitac" name="barangays[]">
                    <label class="form-check-label" for="matalatalaMabitac">Matalatala</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Nanguma" id="nangumaMabitac" name="barangays[]">
                    <label class="form-check-label" for="nangumaMabitac">Nanguma</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Numero" id="numeroMabitac" name="barangays[]">
                    <label class="form-check-label" for="numeroMabitac">Numero</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Paagahan" id="paagahanMabitac" name="barangays[]">
                    <label class="form-check-label" for="paagahanMabitac">Paagahan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bayanihan (Poblacion)" id="bayanihanMabitac" name="barangays[]">
                    <label class="form-check-label" for="bayanihanMabitac">Bayanihan (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Libis ng Nayon (Poblacion)" id="libisNgNayonMabitac" name="barangays[]">
                    <label class="form-check-label" for="libisNgNayonMabitac">Libis ng Nayon (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Maligaya (Poblacion)" id="maligayaMabitac" name="barangays[]">
                    <label class="form-check-label" for="maligayaMabitac">Maligaya (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Masikap (Poblacion)" id="masikapMabitac" name="barangays[]">
                    <label class="form-check-label" for="masikapMabitac">Masikap (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Pag-Asa (Poblacion)" id="pagasaMabitac" name="barangays[]">
                    <label class="form-check-label" for="pagasaMabitac">Pag-Asa (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Sinagtala (Poblacion)" id="sinagtalaMabitac" name="barangays[]">
                    <label class="form-check-label" for="sinagtalaMabitac">Sinagtala (Poblacion)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Antonio" id="sanAntonioMabitac" name="barangays[]">
                    <label class="form-check-label" for="sanAntonioMabitac">San Antonio</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Miguel" id="sanMiguelMabitac" name="barangays[]">
                    <label class="form-check-label" for="sanMiguelMabitac">San Miguel</label>
                </div>
                    <!-- Add more barangays here -->
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Magdalena" id="municipalityMagdalena" name="municipalities[]">
                    <label class="form-check-label" for="municipalityMagdalena">Magdalena</label>
                </div>
                <div class="barangays" id="barangaysMagdalena" style="display:none; padding-left: 20px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Alipit" id="alipitMagdalena" name="barangays[]">
                    <label class="form-check-label" for="alipitMagdalena">Alipit</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Baanan" id="baananMagdalena" name="barangays[]">
                    <label class="form-check-label" for="baananMagdalena">Baanan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Balanac" id="balanacMagdalena" name="barangays[]">
                    <label class="form-check-label" for="balanacMagdalena">Balanac</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bucal" id="bucalMagdalena" name="barangays[]">
                    <label class="form-check-label" for="bucalMagdalena">Bucal</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Buenavista" id="buenavistaMagdalena" name="barangays[]">
                    <label class="form-check-label" for="buenavistaMagdalena">Buenavista</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bungkol" id="bungkolMagdalena" name="barangays[]">
                    <label class="form-check-label" for="bungkolMagdalena">Bungkol</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Buo" id="buoMagdalena" name="barangays[]">
                    <label class="form-check-label" for="buoMagdalena">Buo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Burlungan" id="burlunganMagdalena" name="barangays[]">
                    <label class="form-check-label" for="burlunganMagdalena">Burlungan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Cigaras" id="cigarasMagdalena" name="barangays[]">
                    <label class="form-check-label" for="cigarasMagdalena">Cigaras</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Halayhayin" id="halayhayinMagdalena" name="barangays[]">
                    <label class="form-check-label" for="halayhayinMagdalena">Halayhayin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ibabang Atingay" id="ibabangAtingayMagdalena" name="barangays[]">
                    <label class="form-check-label" for="ibabangAtingayMagdalena">Ibabang Atingay</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ibabang Butnong" id="ibabangButnongMagdalena" name="barangays[]">
                    <label class="form-check-label" for="ibabangButnongMagdalena">Ibabang Butnong</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ilayang Atingay" id="ilayangAtingayMagdalena" name="barangays[]">
                    <label class="form-check-label" for="ilayangAtingayMagdalena">Ilayang Atingay</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ilayang Butnong" id="ilayangButnongMagdalena" name="barangays[]">
                    <label class="form-check-label" for="ilayangButnongMagdalena">Ilayang Butnong</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Ilog" id="ilogMagdalena" name="barangays[]">
                    <label class="form-check-label" for="ilogMagdalena">Ilog</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Malaking Ambling" id="malakingAmblingMagdalena" name="barangays[]">
                    <label class="form-check-label" for="malakingAmblingMagdalena">Malaking Ambling</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Malinao" id="malinaoMagdalena" name="barangays[]">
                    <label class="form-check-label" for="malinaoMagdalena">Malinao</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Maravilla" id="maravillaMagdalena" name="barangays[]">
                    <label class="form-check-label" for="maravillaMagdalena">Maravilla</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Munting Ambling" id="muntingAmblingMagdalena" name="barangays[]">
                    <label class="form-check-label" for="muntingAmblingMagdalena">Munting Ambling</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Poblacion" id="poblacionMagdalena" name="barangays[]">
                    <label class="form-check-label" for="poblacionMagdalena">Poblacion</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Sabang" id="sabangMagdalena" name="barangays[]">
                    <label class="form-check-label" for="sabangMagdalena">Sabang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Salasad" id="salasadMagdalena" name="barangays[]">
                    <label class="form-check-label" for="salasadMagdalena">Salasad</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Tanawan" id="tanawanMagdalena" name="barangays[]">
                    <label class="form-check-label" for="tanawanMagdalena">Tanawan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Tipunan" id="tipunanMagdalena" name="barangays[]">
                    <label class="form-check-label" for="tipunanMagdalena">Tipunan</label>
                </div> 
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Majayjay" id="municipalityMajayjay" name="municipalities[]">
                    <label class="form-check-label" for="municipalityMajayjay">Majayjay</label>
                </div>
                <div class="barangays" id="barangaysMajayjay" style="display:none; padding-left: 20px;">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Amonoy" id="amonoyMajayjay" name="barangays[]">
                <label class="form-check-label" for="amonoyMajayjay">Amonoy</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Bakia" id="bakiaMajayjay" name="barangays[]">
                <label class="form-check-label" for="bakiaMajayjay">Bakia</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Balanac" id="balanacMajayjay" name="barangays[]">
                <label class="form-check-label" for="balanacMajayjay">Balanac</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Balayong" id="balayongMajayjay" name="barangays[]">
                <label class="form-check-label" for="balayongMajayjay">Balayong</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Banilad" id="baniladMajayjay" name="barangays[]">
                <label class="form-check-label" for="baniladMajayjay">Banilad</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Banti" id="bantiMajayjay" name="barangays[]">
                <label class="form-check-label" for="bantiMajayjay">Banti</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Bitaoy" id="bitaoyMajayjay" name="barangays[]">
                <label class="form-check-label" for="bitaoyMajayjay">Bitaoy</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Botocan" id="botocanMajayjay" name="barangays[]">
                <label class="form-check-label" for="botocanMajayjay">Botocan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Bukal" id="bukalMajayjay" name="barangays[]">
                <label class="form-check-label" for="bukalMajayjay">Bukal</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Burgos" id="burgosMajayjay" name="barangays[]">
                <label class="form-check-label" for="burgosMajayjay">Burgos</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Burol" id="burolMajayjay" name="barangays[]">
                <label class="form-check-label" for="burolMajayjay">Burol</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Coralao" id="coralaoMajayjay" name="barangays[]">
                <label class="form-check-label" for="coralaoMajayjay">Coralao</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Gagalot" id="gagalotMajayjay" name="barangays[]">
                <label class="form-check-label" for="gagalotMajayjay">Gagalot</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Ibabang Banga" id="ibabangBangaMajayjay" name="barangays[]">
                <label class="form-check-label" for="ibabangBangaMajayjay">Ibabang Banga</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Ibabang Bayucain" id="ibabangBayucainMajayjay" name="barangays[]">
                <label class="form-check-label" for="ibabangBayucainMajayjay">Ibabang Bayucain</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Ilayang Banga" id="ilayangBangaMajayjay" name="barangays[]">
                <label class="form-check-label" for="ilayangBangaMajayjay">Ilayang Banga</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Ilayang Bayucain" id="ilayangBayucainMajayjay" name="barangays[]">
                <label class="form-check-label" for="ilayangBayucainMajayjay">Ilayang Bayucain</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Isabang" id="isabangMajayjay" name="barangays[]">
                <label class="form-check-label" for="isabangMajayjay">Isabang</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Malinao" id="malinaoMajayjay" name="barangays[]">
                <label class="form-check-label" for="malinaoMajayjay">Malinao</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="May-It" id="mayitMajayjay" name="barangays[]">
                <label class="form-check-label" for="mayitMajayjay">May-It</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Munting Kawayan" id="muntingKawayanMajayjay" name="barangays[]">
                <label class="form-check-label" for="muntingKawayanMajayjay">Munting Kawayan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Olla" id="ollaMajayjay" name="barangays[]">
                <label class="form-check-label" for="ollaMajayjay">Olla</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Oobi" id="oobiMajayjay" name="barangays[]">
                <label class="form-check-label" for="oobiMajayjay">Oobi</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Origuel (Poblacion)" id="origuelMajayjay" name="barangays[]">
                <label class="form-check-label" for="origuelMajayjay">Origuel (Poblacion)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Panalaban" id="panalabanMajayjay" name="barangays[]">
                <label class="form-check-label" for="panalabanMajayjay">Panalaban</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Pangil" id="pangilMajayjay" name="barangays[]">
                <label class="form-check-label" for="pangilMajayjay">Pangil</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Panglan" id="panglanMajayjay" name="barangays[]">
                <label class="form-check-label" for="panglanMajayjay">Panglan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Piit" id="piitMajayjay" name="barangays[]">
                <label class="form-check-label" for="piitMajayjay">Piit</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Pook" id="pookMajayjay" name="barangays[]">
                <label class="form-check-label" for="pookMajayjay">Pook</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Rizal" id="rizalMajayjay" name="barangays[]">
                <label class="form-check-label" for="rizalMajayjay">Rizal</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="San Francisco (Poblacion)" id="sanFranciscoMajayjay" name="barangays[]">
                <label class="form-check-label" for="sanFranciscoMajayjay">San Francisco (Poblacion)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="San Miguel (Poblacion)" id="sanMiguelMajayjay" name="barangays[]">
                <label class="form-check-label" for="sanMiguelMajayjay">San Miguel (Poblacion)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="San Roque" id="sanRoqueMajayjay" name="barangays[]">
                <label class="form-check-label" for="sanRoqueMajayjay">San Roque</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Santa Catalina (Poblacion)" id="santaCatalinaMajayjay" name="barangays[]">
                <label class="form-check-label" for="santaCatalinaMajayjay">Santa Catalina (Poblacion)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Suba" id="subaMajayjay" name="barangays[]">
                <label class="form-check-label" for="subaMajayjay">Suba</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Talortor" id="talortorMajayjay" name="barangays[]">
                <label class="form-check-label" for="talortorMajayjay">Talortor</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Tanawan" id="tanawanMajayjay" name="barangays[]">
                <label class="form-check-label" for="tanawanMajayjay">Tanawan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Taytay" id="taytayMajayjay" name="barangays[]">
                <label class="form-check-label" for="taytayMajayjay">Taytay</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Villa Nogales" id="villaNogalesMajayjay" name="barangays[]">
                <label class="form-check-label" for="villaNogalesMajayjay">Villa Nogales</label>
            </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Nagcarlan" id="municipalityNagcarlan" name="municipalities[]">
                    <label class="form-check-label" for="municipalityNagcarlan">Nagcarlan</label>
                </div>
                <div class="barangays" id="barangaysNagcarlan" style="display:none; padding-left: 20px;">
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Abo" id="aboNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="aboNagcarlan">Abo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Alibungbungan" id="alibungbunganNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="alibungbunganNagcarlan">Alibungbungan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Alumbrado" id="alumbradoNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="alumbradoNagcarlan">Alumbrado</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Balayong" id="balayongNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="balayongNagcarlan">Balayong</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Balimbing" id="balimbingNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="balimbingNagcarlan">Balimbing</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Balinacon" id="balinaconNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="balinaconNagcarlan">Balinacon</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bambang" id="bambangNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="bambangNagcarlan">Bambang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Banago" id="banagoNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="banagoNagcarlan">Banago</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Banca-banca" id="bancaBancaNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="bancaBancaNagcarlan">Banca-banca</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bangcuro" id="bangcuroNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="bangcuroNagcarlan">Bangcuro</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Banilad" id="baniladNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="baniladNagcarlan">Banilad</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bayaquitos" id="bayaquitosNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="bayaquitosNagcarlan">Bayaquitos</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Buboy" id="buboyNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="buboyNagcarlan">Buboy</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Buenavista" id="buenavistaNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="buenavistaNagcarlan">Buenavista</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Buhanginan" id="buhanginanNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="buhanginanNagcarlan">Buhanginan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bukal" id="bukalNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="bukalNagcarlan">Bukal</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Bunga" id="bungaNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="bungaNagcarlan">Bunga</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Cabuyew" id="cabuyewNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="cabuyewNagcarlan">Cabuyew</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Calumpang" id="calumpangNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="calumpangNagcarlan">Calumpang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Kanluran Kabubuhayan" id="kanluranKabubuhayanNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="kanluranKabubuhayanNagcarlan">Kanluran Kabubuhayan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Silangan Kabubuhayan" id="silanganKabubuhayanNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="silanganKabubuhayanNagcarlan">Silangan Kabubuhayan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Labangan" id="labanganNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="labanganNagcarlan">Labangan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Lawaguin" id="lawaguinNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="lawaguinNagcarlan">Lawaguin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Kanluran Lazaan" id="kanluranLazaanNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="kanluranLazaanNagcarlan">Kanluran Lazaan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Silangan Lazaan" id="silanganLazaanNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="silanganLazaanNagcarlan">Silangan Lazaan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Lagulo" id="laguloNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="laguloNagcarlan">Lagulo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Maiit" id="maiitNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="maiitNagcarlan">Maiit</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Malaya" id="malayaNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="malayaNagcarlan">Malaya</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Malinao" id="malinaoNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="malinaoNagcarlan">Malinao</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Manaol" id="manaolNagcarlan" name="barangays[]">
                    <label class="form-check-label" for="manaolNagcarlan">Manaol</label>
                </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Paete" id="municipalityPaete" name="municipalities[]">
                    <label class="form-check-label" for="municipalityPaete">Paete</label>
                </div>
                <div class="barangays" id="barangaysPaete" style="display:none; padding-left: 20px;">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Barangay 1 - Ibaba del Sur" id="barangay1IbabaDelSurPaete" name="barangays[]">
                <label class="form-check-label" for="barangay1IbabaDelSurPaete">Barangay 1 - Ibaba del Sur</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Barangay 2 - Maytoong" id="barangay2MaytoongPaete" name="barangays[]">
                <label class="form-check-label" for="barangay2MaytoongPaete">Barangay 2 - Maytoong</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Barangay 3 - Ermita" id="barangay3ErmitaPaete" name="barangays[]">
                <label class="form-check-label" for="barangay3ErmitaPaete">Barangay 3 - Ermita</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Barangay 4 - Quinale" id="barangay4QuinalePaete" name="barangays[]">
                <label class="form-check-label" for="barangay4QuinalePaete">Barangay 4 - Quinale</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Barangay 5 - Ilaya del Sur" id="barangay5IlayaDelSurPaete" name="barangays[]">
                <label class="form-check-label" for="barangay5IlayaDelSurPaete">Barangay 5 - Ilaya del Sur</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Barangay 6 - Ilaya del Norte" id="barangay6IlayaDelNortePaete" name="barangays[]">
                <label class="form-check-label" for="barangay6IlayaDelNortePaete">Barangay 6 - Ilaya del Norte</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Barangay 7 - Bagumbayan" id="barangay7BagumbayanPaete" name="barangays[]">
                <label class="form-check-label" for="barangay7BagumbayanPaete">Barangay 7 - Bagumbayan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Barangay 8 - Bangkusay" id="barangay8BangkusayPaete" name="barangays[]">
                <label class="form-check-label" for="barangay8BangkusayPaete">Barangay 8 - Bangkusay</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Barangay 9 - Ibaba del Norte" id="barangay9IbabaDelNortePaete" name="barangays[]">
                <label class="form-check-label" for="barangay9IbabaDelNortePaete">Barangay 9 - Ibaba del Norte</label>
            </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div id="municipalitiesRight">
                <!-- Right Column Municipalities -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Pagsanjan" id="municipalityPagsanjan" name="municipalities[]">
                    <label class="form-check-label" for="municipalityPagsanjan">Pagsanjan</label>
                </div>
                <div class="barangays" id="barangaysPagsanjan" style="display:none; padding-left: 20px;">
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Anibong" id="anibongPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="anibongPagsanjan">Anibong</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Biñan" id="binanPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="binanPagsanjan">Biñan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Buboy" id="buboyPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="buboyPagsanjan">Buboy</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Cabanbanan" id="cabanbananPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="cabanbananPagsanjan">Cabanbanan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Calusiche" id="calusichePagsanjan" name="barangays[]">
                    <label class="form-check-label" for="calusichePagsanjan">Calusiche</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Dingin" id="dinginPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="dinginPagsanjan">Dingin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Lambac" id="lambacPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="lambacPagsanjan">Lambac</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Layugan" id="layuganPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="layuganPagsanjan">Layugan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Magdapio" id="magdapioPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="magdapioPagsanjan">Magdapio</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Maulawin" id="maulawinPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="maulawinPagsanjan">Maulawin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Pinagsanjan" id="pinagsanjanPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="pinagsanjanPagsanjan">Pinagsanjan</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Barangay I (Pob.)" id="barangayIPobPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="barangayIPobPagsanjan">Barangay I (Pob.)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Barangay II (Pob.)" id="barangayIIPobPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="barangayIIPobPagsanjan">Barangay II (Pob.)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Sabang" id="sabangPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="sabangPagsanjan">Sabang</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Sampaloc" id="sampalocPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="sampalocPagsanjan">Sampaloc</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="San Isidro" id="sanIsidroPagsanjan" name="barangays[]">
                    <label class="form-check-label" for="sanIsidroPagsanjan">San Isidro</label>
                </div>
                   
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Pakil" id="municipalityPakil" name="municipalities[]">
                    <label class="form-check-label" for="municipalityPakil">Pakil</label>
                </div>
                <div class="barangays" id="barangaysPakil" style="display:none; padding-left: 20px;">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Baño" id="banioPakil" name="barangays[]">
                <label class="form-check-label" for="banioPakil">Baño</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Banilan" id="banilanPakil" name="barangays[]">
                <label class="form-check-label" for="banilanPakil">Banilan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Burgos" id="burgosPakil" name="barangays[]">
                <label class="form-check-label" for="burgosPakil">Burgos</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Casa Real" id="casaRealPakil" name="barangays[]">
                <label class="form-check-label" for="casaRealPakil">Casa Real</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Casinsin" id="casinsinPakil" name="barangays[]">
                <label class="form-check-label" for="casinsinPakil">Casinsin</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Dorado" id="doradoPakil" name="barangays[]">
                <label class="form-check-label" for="doradoPakil">Dorado</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Gonzales" id="gonzalesPakil" name="barangays[]">
                <label class="form-check-label" for="gonzalesPakil">Gonzales</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Kabulusan" id="kabulusanPakil" name="barangays[]">
                <label class="form-check-label" for="kabulusanPakil">Kabulusan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Matikiw" id="matikiwPakil" name="barangays[]">
                <label class="form-check-label" for="matikiwPakil">Matikiw</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Rizal" id="rizalPakil" name="barangays[]">
                <label class="form-check-label" for="rizalPakil">Rizal</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Saray" id="sarayPakil" name="barangays[]">
                <label class="form-check-label" for="sarayPakil">Saray</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Taft" id="taftPakil" name="barangays[]">
                <label class="form-check-label" for="taftPakil">Taft</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Tavera" id="taveraPakil" name="barangays[]">
                <label class="form-check-label" for="taveraPakil">Tavera</label>
            </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Pangil" id="municipalityPangil" name="municipalities[]">
                    <label class="form-check-label" for="municipalityPangil">Pangil</label>
                </div>
                <div class="barangays" id="barangaysPangil" style="display:none; padding-left: 20px;">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Balian" id="balianPangil" name="barangays[]">
                <label class="form-check-label" for="balianPangil">Balian</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Isla (Poblacion)" id="islaPoblacionPangil" name="barangays[]">
                <label class="form-check-label" for="islaPoblacionPangil">Isla (Poblacion)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Natividad (Poblacion)" id="natividadPoblacionPangil" name="barangays[]">
                <label class="form-check-label" for="natividadPoblacionPangil">Natividad (Poblacion)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="San Jose (Poblacion)" id="sanJosePoblacionPangil" name="barangays[]">
                <label class="form-check-label" for="sanJosePoblacionPangil">San Jose (Poblacion)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Sulib (Poblacion)" id="sulibPoblacionPangil" name="barangays[]">
                <label class="form-check-label" for="sulibPoblacionPangil">Sulib (Poblacion)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Galalan" id="galalanPangil" name="barangays[]">
                <label class="form-check-label" for="galalanPangil">Galalan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Dambo" id="damboPangil" name="barangays[]">
                <label class="form-check-label" for="damboPangil">Dambo</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Mabato-Azufre" id="mabatoAzufrePangil" name="barangays[]">
                <label class="form-check-label" for="mabatoAzufrePangil">Mabato-Azufre</label>
            </div>
                  
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Pila" id="municipalityPila" name="municipalities[]">
                    <label class="form-check-label" for="municipalityPila">Pila</label>
                </div>
                <div class="barangays" id="barangaysPila" style="display:none; padding-left: 20px;">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Aplaya" id="aplayapila" name="barangays[]">
                <label class="form-check-label" for="aplayapila">Aplaya</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Bagong Pook" id="bagongPookPila" name="barangays[]">
                <label class="form-check-label" for="bagongPookPila">Bagong Pook</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Bukal" id="bukalPila" name="barangays[]">
                <label class="form-check-label" for="bukalPila">Bukal</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Bulilan Norte (Pob.)" id="bulilanNortePobPila" name="barangays[]">
                <label class="form-check-label" for="bulilanNortePobPila">Bulilan Norte (Pob.)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Bulilan Sur (Pob.)" id="bulilanSurPobPila" name="barangays[]">
                <label class="form-check-label" for="bulilanSurPobPila">Bulilan Sur (Pob.)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Concepcion" id="concepcionPila" name="barangays[]">
                <label class="form-check-label" for="concepcionPila">Concepcion</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Labuin" id="labuinPila" name="barangays[]">
                <label class="form-check-label" for="labuinPila">Labuin</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Linga" id="lingaPila" name="barangays[]">
                <label class="form-check-label" for="lingaPila">Linga</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Masico" id="masicoPila" name="barangays[]">
                <label class="form-check-label" for="masicoPila">Masico</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Mojon" id="mojonPila" name="barangays[]">
                <label class="form-check-label" for="mojonPila">Mojon</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Pansol" id="pansolPila" name="barangays[]">
                <label class="form-check-label" for="pansolPila">Pansol</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Pinagbayanan" id="pinagbayananPila" name="barangays[]">
                <label class="form-check-label" for="pinagbayananPila">Pinagbayanan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="San Antonio" id="sanAntonioPila" name="barangays[]">
                <label class="form-check-label" for="sanAntonioPila">San Antonio</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="San Miguel" id="sanMiguelPila" name="barangays[]">
                <label class="form-check-label" for="sanMiguelPila">San Miguel</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Santa Clara Norte (Pob.)" id="santaClaraNortePobPila" name="barangays[]">
                <label class="form-check-label" for="santaClaraNortePobPila">Santa Clara Norte (Pob.)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Santa Clara Sur (Pob.)" id="santaClaraSurPobPila" name="barangays[]">
                <label class="form-check-label" for="santaClaraSurPobPila">Santa Clara Sur (Pob.)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Tubuan" id="tubuanPila" name="barangays[]">
                <label class="form-check-label" for="tubuanPila">Tubuan</label>
            </div>

                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Rizal" id="municipalityRizal" name="municipalities[]">
                    <label class="form-check-label" for="municipalityRizal">Rizal</label>
                </div>
                <div class="barangays" id="barangaysRizal" style="display:none; padding-left: 20px;">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Antipolo" id="antipoloRizal" name="barangays[]">
                <label class="form-check-label" for="antipoloRizal">Antipolo</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Entablado" id="entabladoRizal" name="barangays[]">
                <label class="form-check-label" for="entabladoRizal">Entablado</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Laguan" id="laguanRizal" name="barangays[]">
                <label class="form-check-label" for="laguanRizal">Laguan</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Pauli 1" id="pauli1Rizal" name="barangays[]">
                <label class="form-check-label" for="pauli1Rizal">Pauli 1</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Pauli 2" id="pauli2Rizal" name="barangays[]">
                <label class="form-check-label" for="pauli2Rizal">Pauli 2</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="East Poblacion" id="eastPoblacionRizal" name="barangays[]">
                <label class="form-check-label" for="eastPoblacionRizal">East Poblacion</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="West Poblacion" id="westPoblacionRizal" name="barangays[]">
                <label class="form-check-label" for="westPoblacionRizal">West Poblacion</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Pook" id="pookRizal" name="barangays[]">
                <label class="form-check-label" for="pookRizal">Pook</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Tala" id="talaRizal" name="barangays[]">
                <label class="form-check-label" for="talaRizal">Tala</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Talaga" id="talagaRizal" name="barangays[]">
                <label class="form-check-label" for="talagaRizal">Talaga</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="Tuy" id="tuyRizal" name="barangays[]">
                <label class="form-check-label" for="tuyRizal">Tuy</label>
            </div>

                </div>

                <div class="form-check">
    <input class="form-check-input" type="checkbox" value='"San Pablo"' id="municipalitySanPablo" name="municipalities[]">
    <label class="form-check-label" for="municipalitySanPablo">San Pablo</label>
</div>
<div class="barangays" id="barangaysSanPablo" style="display:none; padding-left: 20px;">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="I-A (Sambat)" id="iaSanPablo" name="barangays[]">
        <label class="form-check-label" for="iaSanPablo">I-A (Sambat)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="I-B (City+Riverside)" id="ibSanPablo" name="barangays[]">
        <label class="form-check-label" for="ibSanPablo">I-B (City+Riverside)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="I-C (Bagong Bayan)" id="icSanPablo" name="barangays[]">
        <label class="form-check-label" for="icSanPablo">I-C (Bagong Bayan)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="II-A (Triangulo)" id="iiaSanPablo" name="barangays[]">
        <label class="form-check-label" for="iiaSanPablo">II-A (Triangulo)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="II-B (Guadalupe)" id="iibSanPablo" name="barangays[]">
        <label class="form-check-label" for="iibSanPablo">II-B (Guadalupe)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="II-C (Unson)" id="iicSanPablo" name="barangays[]">
        <label class="form-check-label" for="iicSanPablo">II-C (Unson)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="II-D (Bulante)" id="iidSanPablo" name="barangays[]">
        <label class="form-check-label" for="iidSanPablo">II-D (Bulante)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="II-E (San Anton)" id="iieSanPablo" name="barangays[]">
        <label class="form-check-label" for="iieSanPablo">II-E (San Anton)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="II-F (Villa Rey)" id="iifSanPablo" name="barangays[]">
        <label class="form-check-label" for="iifSanPablo">II-F (Villa Rey)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="III-A (Hermanos Belen)" id="iiiaSanPablo" name="barangays[]">
        <label class="form-check-label" for="iiiaSanPablo">III-A (Hermanos Belen)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="III-B" id="iiibSanPablo" name="barangays[]">
        <label class="form-check-label" for="iiibSanPablo">III-B</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="III-C (Labak/De Roma)" id="iiicSanPablo" name="barangays[]">
        <label class="form-check-label" for="iiicSanPablo">III-C (Labak/De Roma)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="III-D (Villongco)" id="iiidSanPablo" name="barangays[]">
        <label class="form-check-label" for="iiidSanPablo">III-D (Villongco)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="III-E" id="iiieSanPablo" name="barangays[]">
        <label class="form-check-label" for="iiieSanPablo">III-E</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="III-F (Balagtas)" id="iiifSanPablo" name="barangays[]">
        <label class="form-check-label" for="iiifSanPablo">III-F (Balagtas)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="IV-A" id="ivaSanPablo" name="barangays[]">
        <label class="form-check-label" for="ivaSanPablo">IV-A</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="IV-B" id="ivbSanPablo" name="barangays[]">
        <label class="form-check-label" for="ivbSanPablo">IV-B</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="IV-C" id="ivcSanPablo" name="barangays[]">
        <label class="form-check-label" for="ivcSanPablo">IV-C</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="V-A" id="vaSanPablo" name="barangays[]">
        <label class="form-check-label" for="vaSanPablo">V-A</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="V-B" id="vbSanPablo" name="barangays[]">
        <label class="form-check-label" for="vbSanPablo">V-B</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="V-C" id="vcSanPablo" name="barangays[]">
        <label class="form-check-label" for="vcSanPablo">V-C</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="V-D" id="vdSanPablo" name="barangays[]">
        <label class="form-check-label" for="vdSanPablo">V-D</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="VI-A (Mavenida)" id="viaSanPablo" name="barangays[]">
        <label class="form-check-label" for="viaSanPablo">VI-A (Mavenida)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="VI-B" id="vibSanPablo" name="barangays[]">
        <label class="form-check-label" for="vibSanPablo">VI-B</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="VI-C (Bagong Pook)" id="vicSanPablo" name="barangays[]">
        <label class="form-check-label" for="vicSanPablo">VI-C (Bagong Pook)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="VI-D (Lparkers)" id="vidSanPablo" name="barangays[]">
        <label class="form-check-label" for="vidSanPablo">VI-D (Lparkers)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="VI-E (YMCA)" id="vieSanPablo" name="barangays[]">
        <label class="form-check-label" for="vieSanPablo">VI-E (YMCA)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="VII-A (P.Alcantara)" id="viiaSanPablo" name="barangays[]">
        <label class="form-check-label" for="viiaSanPablo">VII-A (P.Alcantara)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="VII-B" id="viibSanPablo" name="barangays[]">
        <label class="form-check-label" for="viibSanPablo">VII-B</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="VII-C" id="viicSanPablo" name="barangays[]">
        <label class="form-check-label" for="viicSanPablo">VII-C</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="VII-D" id="viidSanPablo" name="barangays[]">
        <label class="form-check-label" for="viidSanPablo">VII-D</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="VII-E" id="viieSanPablo" name="barangays[]">
        <label class="form-check-label" for="viieSanPablo">VII-E</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Atisan" id="atisanSanPablo" name="barangays[]">
        <label class="form-check-label" for="atisanSanPablo">Atisan</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Bautista" id="bautistaSanPablo" name="barangays[]">
        <label class="form-check-label" for="bautistaSanPablo">Bautista</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Concepcion (Bunot)" id="concepcionBunotSanPablo" name="barangays[]">
        <label class="form-check-label" for="concepcionBunotSanPablo">Concepcion (Bunot)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Del Remedio (Wawa)" id="delRemedioWawaSanPablo" name="barangays[]">
        <label class="form-check-label" for="delRemedioWawaSanPablo">Del Remedio (Wawa)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Dolores" id="doloresSanPablo" name="barangays[]">
        <label class="form-check-label" for="doloresSanPablo">Dolores</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Antonio 1 (Balanga)" id="sanAntonio1BalangaSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanAntonio1BalangaSanPablo">San Antonio 1 (Balanga)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Antonio 2 (Sapa)" id="sanAntonio2SapaSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanAntonio2SapaSanPablo">San Antonio 2 (Sapa)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Bartolome (Matang-ag)" id="sanBartolomeMatangagSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanBartolomeMatangagSanPablo">San Bartolome (Matang-ag)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Buenaventura (Palakpakin)" id="sanBuenaventuraPalakpakinSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanBuenaventuraPalakpakinSanPablo">San Buenaventura (Palakpakin)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Crispin (Lumbangan)" id="sanCrispinLumbanganSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanCrispinLumbanganSanPablo">San Crispin (Lumbangan)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Cristobal" id="sanCristobalSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanCristobalSanPablo">San Cristobal</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Diego (Tiim)" id="sanDiegoTiimSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanDiegoTiimSanPablo">San Diego (Tiim)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Francisco (Calihan)" id="sanFranciscoCalihanSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanFranciscoCalihanSanPablo">San Francisco (Calihan)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Gabriel (Butucan)" id="sanGabrielButucanSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanGabrielButucanSanPablo">San Gabriel (Butucan)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Gregorio" id="sanGregorioSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanGregorioSanPablo">San Gregorio</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Ignacio" id="sanIgnacioSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanIgnacioSanPablo">San Ignacio</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Isidro (Balagbag)" id="sanIsidroBalagbagSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanIsidroBalagbagSanPablo">San Isidro (Balagbag)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Joaquin" id="sanJoaquinSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanJoaquinSanPablo">San Joaquin</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Jose (Malamig)" id="sanJoseMalamigSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanJoseMalamigSanPablo">San Jose (Malamig)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Juan" id="sanJuanSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanJuanSanPablo">San Juan</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Lorenzo (Saluyan)" id="sanLorenzoSaluyanSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanLorenzoSaluyanSanPablo">San Lorenzo (Saluyan)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Lucas 1 (Malinaw)" id="sanLucas1MalinawSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanLucas1MalinawSanPablo">San Lucas 1 (Malinaw)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Lucas 2" id="sanLucas2SanPablo" name="barangays[]">
        <label class="form-check-label" for="sanLucas2SanPablo">San Lucas 2</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Marcos (Tikew)" id="sanMarcosTikewSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanMarcosTikewSanPablo">San Marcos (Tikew)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Mateo" id="sanMateoSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanMateoSanPablo">San Mateo</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Miguel" id="sanMiguelSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanMiguelSanPablo">San Miguel</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Nicolas" id="sanNicolasSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanNicolasSanPablo">San Nicolas</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Pedro" id="sanPedroSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanPedroSanPablo">San Pedro</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Rafael (Magampon)" id="sanRafaelMagamponSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanRafaelMagamponSanPablo">San Rafael (Magampon)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Roque (Buluburan)" id="sanRoqueBuluburanSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanRoqueBuluburanSanPablo">San Roque (Buluburan)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="San Vicente" id="sanVicenteSanPablo" name="barangays[]">
        <label class="form-check-label" for="sanVicenteSanPablo">San Vicente</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santa Ana" id="santaAnaSanPablo" name="barangays[]">
        <label class="form-check-label" for="santaAnaSanPablo">Santa Ana</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santa Catalina (Sandig)" id="santaCatalinaSandigSanPablo" name="barangays[]">
        <label class="form-check-label" for="santaCatalinaSandigSanPablo">Santa Catalina (Sandig)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santa Cruz (Putol)" id="santaCruzPutolSanPablo" name="barangays[]">
        <label class="form-check-label" for="santaCruzPutolSanPablo">Santa Cruz (Putol)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santa Elena" id="santaElenaSanPablo" name="barangays[]">
        <label class="form-check-label" for="santaElenaSanPablo">Santa Elena</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santa Filomena (Banlagin)" id="santaFilomenaBanlaginSanPablo" name="barangays[]">
        <label class="form-check-label" for="santaFilomenaBanlaginSanPablo">Santa Filomena (Banlagin)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santa Isabel" id="santaIsabelSanPablo" name="barangays[]">
        <label class="form-check-label" for="santaIsabelSanPablo">Santa Isabel</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santa Maria" id="santaMariaSanPablo" name="barangays[]">
        <label class="form-check-label" for="santaMariaSanPablo">Santa Maria</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santa Maria Magdalena (Boe)" id="santaMariaMagdalenaBoeSanPablo" name="barangays[]">
        <label class="form-check-label" for="santaMariaMagdalenaBoeSanPablo">Santa Maria Magdalena (Boe)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santa Monica" id="santaMonicaSanPablo" name="barangays[]">
        <label class="form-check-label" for="santaMonicaSanPablo">Santa Monica</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santa Veronica (Bae)" id="santaVeronicaBaeSanPablo" name="barangays[]">
        <label class="form-check-label" for="santaVeronicaBaeSanPablo">Santa Veronica (Bae)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santiago I (Bulaho)" id="santiagoIBulahoSanPablo" name="barangays[]">
        <label class="form-check-label" for="santiagoIBulahoSanPablo">Santiago I (Bulaho)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santiago II" id="santiagoIISanPablo" name="barangays[]">
        <label class="form-check-label" for="santiagoIISanPablo">Santiago II</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santisimo Rosario" id="santisimoRosarioSanPablo" name="barangays[]">
        <label class="form-check-label" for="santisimoRosarioSanPablo">Santisimo Rosario</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santo Angel (Ilog)" id="santoAngelIlogSanPablo" name="barangays[]">
        <label class="form-check-label" for="santoAngelIlogSanPablo">Santo Angel (Ilog)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santo Cristo" id="santoCristoSanPablo" name="barangays[]">
        <label class="form-check-label" for="santoCristoSanPablo">Santo Cristo</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Santo Niño (Arsum)" id="santoNinoArsumSanPablo" name="barangays[]">
        <label class="form-check-label" for="santoNinoArsumSanPablo">Santo Niño (Arsum)</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" value="Soledad (Macopa)" id="soledadMacopaSanPablo" name="barangays[]">
        <label class="form-check-label" for="soledadMacopaSanPablo">Soledad (Macopa)</label>
    </div>
                
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Santa Cruz" id="municipalitySantaCruz" name="municipalities[]">
                    <label class="form-check-label" for="municipalitySantaCruz">Santa Cruz</label>
                </div>
                <div class="barangays" id="barangaysSantaCruz" style="display:none; padding-left: 20px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Alipit" id="barangayAlipit" name="barangays[]">
                        <label class="form-check-label" for="barangayAlipit">Alipit</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Bagumbayan" id="barangayBagumbayan" name="barangays[]">
                        <label class="form-check-label" for="barangayBagumbayan">Bagumbayan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Bubukal" id="barangayBubukal" name="barangays[]">
                        <label class="form-check-label" for="barangayBubukal">Bubukal</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Calios" id="barangayCalios" name="barangays[]">
                        <label class="form-check-label" for="barangayCalios">Calios</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Duhat" id="barangayDuhat" name="barangays[]">
                        <label class="form-check-label" for="barangayDuhat">Duhat</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Gatid" id="barangayGatid" name="barangays[]">
                        <label class="form-check-label" for="barangayGatid">Gatid</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Jasaan" id="barangayJasaan" name="barangays[]">
                        <label class="form-check-label" for="barangayJasaan">Jasaan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Labuin" id="barangayLabuin" name="barangays[]">
                        <label class="form-check-label" for="barangayLabuin">Labuin</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Malinao" id="barangayMalinao" name="barangays[]">
                        <label class="form-check-label" for="barangayMalinao">Malinao</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Oogong" id="barangayOogong" name="barangays[]">
                        <label class="form-check-label" for="barangayOogong">Oogong</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Pagsawitan" id="barangayPagsawitan" name="barangays[]">
                        <label class="form-check-label" for="barangayPagsawitan">Pagsawitan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Palasan" id="barangayPalasan" name="barangays[]">
                        <label class="form-check-label" for="barangayPalasan">Palasan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Patimbao" id="barangayPatimbao" name="barangays[]">
                        <label class="form-check-label" for="barangayPatimbao">Patimbao</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="PoblacionI" id="barangayPoblacionI" name="barangays[]">
                        <label class="form-check-label" for="barangayPoblacionI">Poblacion I</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="PoblacionII" id="barangayPoblacionII" name="barangays[]">
                        <label class="form-check-label" for="barangayPoblacionII">Poblacion II</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="PoblacionIII" id="barangayPoblacionIII" name="barangays[]">
                        <label class="form-check-label" for="barangayPoblacionIII">Poblacion III</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="PoblacionIV" id="barangayPoblacionIV" name="barangays[]">
                        <label class="form-check-label" for="barangayPoblacionIV">Poblacion IV</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="PoblacionV" id="barangayPoblacionV" name="barangays[]">
                        <label class="form-check-label" for="barangayPoblacionV">Poblacion V</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="SanJose" id="barangaySanJose" name="barangays[]">
                        <label class="form-check-label" for="barangaySanJose">San Jose</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="SanJuan" id="barangaySanJuan" name="barangays[]">
                        <label class="form-check-label" for="barangaySanJuan">San Juan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="SanPabloNorte" id="barangaySanPabloNorte" name="barangays[]">
                        <label class="form-check-label" for="barangaySanPabloNorte">San Pablo Norte</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="SanPabloSur" id="barangaySanPabloSur" name="barangays[]">
                        <label class="form-check-label" for="barangaySanPabloSur">San Pablo Sur</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="SantisimaCruz" id="barangaySantisimaCruz" name="barangays[]">
                        <label class="form-check-label" for="barangaySantisimaCruz">Santisima Cruz</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="SantoAngelCentral" id="barangaySantoAngelCentral" name="barangays[]">
                        <label class="form-check-label" for="barangaySantoAngelCentral">Santo Angel Central</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="SantoAngelNorte" id="barangaySantoAngelNorte" name="barangays[]">
                        <label class="form-check-label" for="barangaySantoAngelNorte">Santo Angel Norte</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="SantoAngelSur" id="barangaySantoAngelSur" name="barangays[]">
                        <label class="form-check-label" for="barangaySantoAngelSur">Santo Angel Sur</label>
                    </div>

                </div>

        
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="SantaMaria" id="municipalitySantaMaria" name="municipalities[]">
                    <label class="form-check-label" for="municipalitySantaMaria">Santa Maria</label>
                </div>
                <div class="barangays" id="barangaysSantaMaria" style="display:none; padding-left: 20px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Adia" id="barangayAdia" name="barangays[]">
                        <label class="form-check-label" for="barangayAdia">Adia</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Bagong Pook" id="barangayBagongPook" name="barangays[]">
                        <label class="form-check-label" for="barangayBagongPook">Bagong Pook</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Bagumbayan" id="barangayBagumbayan" name="barangays[]">
                        <label class="form-check-label" for="barangayBagumbayan">Bagumbayan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Bubucal" id="barangayBubucal" name="barangays[]">
                        <label class="form-check-label" for="barangayBubucal">Bubucal</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Cabooan" id="barangayCabooan" name="barangays[]">
                        <label class="form-check-label" for="barangayCabooan">Cabooan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Calangay" id="barangayCalangay" name="barangays[]">
                        <label class="form-check-label" for="barangayCalangay">Calangay</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Cambuja" id="barangayCambuja" name="barangays[]">
                        <label class="form-check-label" for="barangayCambuja">Cambuja</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Coralan" id="barangayCoralan" name="barangays[]">
                        <label class="form-check-label" for="barangayCoralan">Coralan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Cueva" id="barangayCueva" name="barangays[]">
                        <label class="form-check-label" for="barangayCueva">Cueva</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Inayapan" id="barangayInayapan" name="barangays[]">
                        <label class="form-check-label" for="barangayInayapan">Inayapan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Jose P. Laurel, Sr." id="barangayJosePLaurel" name="barangays[]">
                        <label class="form-check-label" for="barangayJosePLaurel">Jose P. Laurel, Sr.</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Jose P. Rizal" id="barangayJosePRizal" name="barangays[]">
                        <label class="form-check-label" for="barangayJosePRizal">Jose P. Rizal</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Juan Santiago" id="barangayJuanSantiago" name="barangays[]">
                        <label class="form-check-label" for="barangayJuanSantiago">Juan Santiago</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Kayhacat" id="barangayKayhacat" name="barangays[]">
                        <label class="form-check-label" for="barangayKayhacat">Kayhacat</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Macasipac" id="barangayMacasipac" name="barangays[]">
                        <label class="form-check-label" for="barangayMacasipac">Macasipac</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Masinao" id="barangayMasinao" name="barangays[]">
                        <label class="form-check-label" for="barangayMasinao">Masinao</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Matalinting" id="barangayMatalinting" name="barangays[]">
                        <label class="form-check-label" for="barangayMatalinting">Matalinting</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Pao-o" id="barangayPaoO" name="barangays[]">
                        <label class="form-check-label" for="barangayPaoO">Pao-o</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Parang ng Buho" id="barangayParangNgBuho" name="barangays[]">
                        <label class="form-check-label" for="barangayParangNgBuho">Parang ng Buho</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="PoblacionDos" id="barangayPoblacionDos" name="barangays[]">
                        <label class="form-check-label" for="barangayPoblacionDos">Poblacion Dos</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="PoblacionQuatro" id="barangayPoblacionQuatro" name="barangays[]">
                        <label class="form-check-label" for="barangayPoblacionQuatro">Poblacion Quatro</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="PoblacionTres" id="barangayPoblacionTres" name="barangays[]">
                        <label class="form-check-label" for="barangayPoblacionTres">Poblacion Tres</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="PoblacionUno" id="barangayPoblacionUno" name="barangays[]">
                        <label class="form-check-label" for="barangayPoblacionUno">Poblacion Uno</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Talangka" id="barangayTalangka" name="barangays[]">
                        <label class="form-check-label" for="barangayTalangka">Talangka</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Tungkod" id="barangayTungkod" name="barangays[]">
                        <label class="form-check-label" for="barangayTungkod">Tungkod</label>
                    </div>
                 
                </div>
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Siniloan" id="municipalitySiniloan" name="municipalities[]">
                    <label class="form-check-label" for="municipalitySiniloan">Siniloan</label>
                </div>
                <div class="barangays" id="barangaysSiniloan" style="display:none; padding-left: 20px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Acevida" id="barangayAcevida" name="barangays[]">
                        <label class="form-check-label" for="barangayAcevida">Acevida</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Bagong Pag-Asa (Poblacion)" id="barangayBagongPagAsa" name="barangays[]">
                        <label class="form-check-label" for="barangayBagongPagAsa">Bagong Pag-Asa (Poblacion)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Bagumbarangay (Poblacion)" id="barangayBagumbarangay" name="barangays[]">
                        <label class="form-check-label" for="barangayBagumbarangay">Bagumbarangay (Poblacion)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Buhay" id="barangayBuhay" name="barangays[]">
                        <label class="form-check-label" for="barangayBuhay">Buhay</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="G. Redor (Poblacion)" id="barangayGRedor" name="barangays[]">
                        <label class="form-check-label" for="barangayGRedor">G. Redor (Poblacion)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Gen. Luna" id="barangayGenLuna" name="barangays[]">
                        <label class="form-check-label" for="barangayGenLuna">Gen. Luna</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Halayahayin" id="barangayHalayahayin" name="barangays[]">
                        <label class="form-check-label" for="barangayHalayahayin">Halayahayin</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Kapatalan" id="barangayKapatalan" name="barangays[]">
                        <label class="form-check-label" for="barangayKapatalan">Kapatalan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Laguio" id="barangayLaguio" name="barangays[]">
                        <label class="form-check-label" for="barangayLaguio">Laguio</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Liyang" id="barangayLiyang" name="barangays[]">
                        <label class="form-check-label" for="barangayLiyang">Liyang</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Llavac" id="barangayLlavac" name="barangays[]">
                        <label class="form-check-label" for="barangayLlavac">Llavac</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Macatad" id="barangayMacatad" name="barangays[]">
                        <label class="form-check-label" for="barangayMacatad">Macatad</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Magsaysay" id="barangayMagsaysay" name="barangays[]">
                        <label class="form-check-label" for="barangayMagsaysay">Magsaysay</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Mayatba" id="barangayMayatba" name="barangays[]">
                        <label class="form-check-label" for="barangayMayatba">Mayatba</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Mendiola" id="barangayMendiola" name="barangays[]">
                        <label class="form-check-label" for="barangayMendiola">Mendiola</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="P. Burgos" id="barangayPBurgos" name="barangays[]">
                        <label class="form-check-label" for="barangayPBurgos">P. Burgos</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Pandeño" id="barangayPandeño" name="barangays[]">
                        <label class="form-check-label" for="barangayPandeño">Pandeño</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Salubungan" id="barangaySalubungan" name="barangays[]">
                        <label class="form-check-label" for="barangaySalubungan">Salubungan</label>
                    </div>
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Wawa" id="barangayWawa" name="barangays[]">
                    <label class="form-check-label" for="barangayWawa">Wawa</label>
                </div>
            </div>
          
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Victoria" id="municipalityVictoria" name="municipalities[]">
                    <label class="form-check-label" for="municipalityVictoria">Victoria</label>
                </div>
                <div class="barangays" id="barangaysVictoria" style="display:none; padding-left: 20px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Banca-banca" id="barangayBancaBanca" name="barangays[]">
                        <label class="form-check-label" for="barangayBancaBanca">Banca-banca</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Daniw" id="barangayDaniw" name="barangays[]">
                        <label class="form-check-label" for="barangayDaniw">Daniw</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Masapang" id="barangayMasapang" name="barangays[]">
                        <label class="form-check-label" for="barangayMasapang">Masapang</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Nanhaya (Poblacion)" id="barangayNanhaya" name="barangays[]">
                        <label class="form-check-label" for="barangayNanhaya">Nanhaya (Poblacion)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="Pagalangan" id="barangayPagalangan" name="barangays[]">
                        <label class="form-check-label" for="barangayPagalangan">Pagalangan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Benito" id="barangaySanBenito" name="barangays[]">
                        <label class="form-check-label" for="barangaySanBenito">San Benito</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Felix" id="barangaySanFelix" name="barangays[]">
                        <label class="form-check-label" for="barangaySanFelix">San Felix</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Francisco" id="barangaySanFrancisco" name="barangays[]">
                        <label class="form-check-label" for="barangaySanFrancisco">San Francisco</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="San Roque (Poblacion)" id="barangaySanRoque" name="barangays[]">
                        <label class="form-check-label" for="barangaySanRoque">San Roque (Poblacion)</label>
                    </div>
              
                </div>


                        </div>
                    </div>
                </div>
            </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    function printTable() {
        const headerHTML = `
            <div class="header">
                <img src="../images/logo.png" alt="DENR Logo">
                <h6>Republic of the Philippines</h6>
                <h3>Department of Natural Resources</h3>
                <h4>Community Environment and Natural Resources Office</h4>
                <p>Brgy. Duhat, Santa Cruz, Laguna</p>
            </div>
        `;
        const tableHTML = document.getElementById('printableTable').outerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.open();
        printWindow.document.write(`
            <html>
            <head>
                <title>Print Table</title>
                <style>
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: left; page-break-inside: auto; }
                    th { background-color: #f2f2f2; }
                    @media print {
                        .actions-column { display: none; }
                        .header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .header img {
                            width: 80px; /* Adjust as needed */
                            height: auto;
                        }
                        .header h6, .header h3, .header h4 {
                            margin: 0;
                        }
                            .header p {
                            margin: 0;
                            font-size: 10px; /* Reduced font size */
                        }
                        tr {
                            page-break-inside: avoid;
                        }
                    }
                </style>
            </head>
            <body>
                ${headerHTML}
                ${tableHTML}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.onload = function() {
            printWindow.print();
        };
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const municipalityCheckboxes = document.querySelectorAll('input[name="municipalities[]"]');

        municipalityCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                // Sanitize the value to match the ID
                const sanitizedValue = this.value.replace(/\s+/g, '').replace(/[^a-zA-Z0-9]/g, '');
                const barangayDiv = document.getElementById('barangays' + sanitizedValue);

                if (barangayDiv) {
                    barangayDiv.style.display = this.checked ? 'block' : 'none';
                }
            });
        });
    });
</script>
<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('show');
}
</script>
<script>
    function confirmLogout() {
        return confirm('Are you sure you want to logout?');
    }
</script>
</body>
</html>
