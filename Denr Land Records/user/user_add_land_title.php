<?php
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

$username = $_SESSION['username'];
$userId = $_SESSION['userId'];

// Database connection
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

// Fetch notification count
$sql_count = "SELECT COUNT(*) AS count FROM notifications";
$result_count = $conn->query($sql_count);
$notification_count = 0;
if ($result_count->num_rows > 0) {
    $row_count = $result_count->fetch_assoc();
    $notification_count = $row_count['count'];
}

// Fetch unread message count
$sql_unread_message_count = "SELECT COUNT(*) AS count FROM messages WHERE (receiverId = ? AND isRead = FALSE)";
$stmt_unread_message_count = $conn->prepare($sql_unread_message_count);
$stmt_unread_message_count->bind_param("i", $userId);
$stmt_unread_message_count->execute();
$result_unread_message_count = $stmt_unread_message_count->get_result();

$unread_message_count = 0;
if ($result_unread_message_count->num_rows > 0) {
    $row_unread_message_count = $result_unread_message_count->fetch_assoc();
    $unread_message_count = $row_unread_message_count['count'];
}

$stmt_unread_message_count->close(); // Close the statement after fetching the count



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENR-CENRO: Add Land Title</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php include 'user_style.php'; ?>
</head>
<body>
<?php include 'user_sidebar.php'; ?>

<?php include 'user_navbar.php'; ?>

        <div class="search-bar">
            <form method="GET" action="user_search_results.php" class="form-inline">
                <div class="input-group w-100">
                    <input type="text" name="query" class="form-control" placeholder="Search by lot number, applicant, survey claimant, status, municipality, barangay...">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>
        </div>


        <form method="POST" action="user_submit_land_title.php">

         <div class="form-container">
            <form>
            <div class="row">
        <div class="col-md-6"> <!-- Adjusted column for medium-sized screens -->
        <div class="card">
            <div class="card-header">
                <h4>Submit Land Title</h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="lot_number">Lot Number</label>
                    <input type="text" class="form-control" id="lot_number" name="lot_number" placeholder="Enter Lot Number">
                </div>
                <div class="form-group">
                    <label for="status">Land Category</label>
                    <select class="form-control" id="status" name="status">
                        <option>No PLA (No Public Land Application)</option>
                        <option>SA (Survey Authority)</option>
                        <option>FPA (Free Patent Application)</option>
                        <option>RSA (Request for Survey Authority)</option>
                        <option>RFPA (Request for Free Patent Application)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="municipality">Municipality</label>
                    <select class="form-control" id="municipality" name="municipality">
                        <option value="">Select Municipality</option>
                        <option value="Alaminos">Alaminos</option>
                        <option value="Calauan">Calauan</option>
                        <option value="Cavinti">Cavinti</option>
                        <option value="Famy">Famy</option>
                        <option value="Kalayaan">Kalayaan</option>
                        <option value="Liliw">Liliw</option>
                        <option value="Luisiana">Luisiana</option>
                        <option value="Lumban">Lumban</option>
                        <option value="Mabitac">Mabitac</option>
                        <option value="Magdalena">Magdalena</option>
                        <option value="Majayjay">Majayjay</option>
                        <option value="Nagcarlan">Nagcarlan</option>
                        <option value="Paete">Paete</option>
                        <option value="Pagsanjan">Pagsanjan</option>
                        <option value="Pakil">Pakil</option>
                        <option value="Pangil">Pangil</option>
                        <option value="Pila">Pila</option>
                        <option value="Rizal">Rizal</option>
                        <option value="San Pablo">San Pablo</option>
                        <option value="Santa Cruz">Santa Cruz</option>
                        <option value="Santa Maria">Santa Maria</option>
                        <option value="Siniloan">Siniloan</option>
                        <option value="Victoria">Victoria</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <select class="form-control" id="barangay" name="barangay">
                        <option value="">Select Barangay</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_approved">Date Approved</label>
                    <input type="date" name="date_approved" class="form-control" required>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <h4>Location</h4>
        </div>
        <div class="card-body">
            <!-- Map Container -->
            <div class="map-container" style="position: relative;">
                <div id="map" style="height: 250px; width: 100%;"></div>
                
                <!-- Input field for coordinates -->
                 <br>
                    <div class="form-group d-flex">
                        <label for="coordinates" class="mr-2"> Enter Coordinates</label>
                        <input type="text" id="coordinates" name="coordinates" class="form-control mr-2" placeholder="Enter Latitude, Longitude">
                        <!-- Search Button with Icon -->
                        <button type="button" class="btn btn-primary" id="searchButton">
                            <i class="fas fa-search"></i> <!-- Search Icon -->
                        </button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


     <div class="card">
            <div class="card-header">
                <h4>Applicant</h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="applicantLastName">Last Name</label>
                    <input type="text" class="form-control" id="applicantLastName" name="applicantLastName" placeholder="Enter Last Name">

                </div>
                <div class="form-group">
                    <label for="applicantFirstName">First Name</label>
                    <input type="text" name="applicantFirstName" class="form-control" id="applicantFirstName" placeholder="Enter First Name">
                </div>
                <div class="form-group">
                    <label for="applicantMiddleName">Middle Name</label>
                    <input type="text" class="form-control" name="applicantMiddleName" id="applicantMiddleName" placeholder="Enter Middle Name">
                </div>
                <div class="form-group form-check">
                <input type="checkbox" class="form-check-input"  id="extensionCheck">
                <label class="form-check-label" for="extensionCheck">Extensions</label>
                <input type="text" class="form-control mt-2" id="extensionText" placeholder="Enter Extensions" style="display: none;">
            </div>
                <div class="form-group">
                    <label for="applicantSex">Sex</label>
                    <select class="form-control" id="applicantSex" name="applicantSex">
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="applicantBirthday">Birthday</label>
                    <input type="date" class="form-control" id="applicantBirthday" name="applicantBirthday" placeholder="Select Birthday" required>
                </div>
                <div class="form-group">
                    <label for="applicantAge">Age</label>
                    <input type="text" class="form-control" id="applicantAge" name="applicantAge" placeholder="Enter Age" readonly>
                </div>
                <div class="form-group">
                    <label for="applicantContactNumber">Contact Number</label>
                    <input type="text" class="form-control" id="applicantContactNumber" name="applicantContactNumber" placeholder="Enter Contact Number" maxlength="11" minlength="11"  oninput="validateNumberInput(this)">
                </div>
                <!-- Civil Status Dropdown -->
                <div class="form-group">
                        <label for="applicantCivilStatus">Civil Status</label>
                        <select class="form-control" id="applicantCivilStatus" name="applicantCivilStatus" onchange="toggleSpouseField()">
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Separated">Separated</option>
                            <option value="Widow">Widow</option>
                        </select>
                    </div>

                    <!-- Name of Spouse/Partner (Hidden initially) -->
                    <div class="form-group" id="spouseField" style="display: none;">
                        <label for="spouseName">Name of Spouse/Partner</label>
                        <input type="text" class="form-control" id="applicantSpouseName" name="applicantSpouseName" placeholder="Enter Spouse/Partner Name">
                    </div>
                </div>
            </div>
            </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Survey Claimant</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="claimantLastName">Last Name</label>
                            <input type="text" class="form-control" id="claimantLastName" name="claimantLastName" placeholder="Enter Last Name">
                        </div>
                        <div class="form-group">
                            <label for="claimantFirstName">First Name</label>
                            <input type="text" class="form-control" id="claimantFirstName" name="claimantFirstName" placeholder="Enter First Name">
                        </div>
                        <div class="form-group">
                            <label for="claimantMiddleName">Middle Name</label>
                            <input type="text" class="form-control" id="claimantMiddleName" name="claimantMiddleName" placeholder="Enter Middle Name">
                        </div>
                        <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="claimantExtensionCheck">
                        <label class="form-check-label" for="claimantExtensionCheck">Extensions</label>
                        <input type="text" class="form-control mt-2" id="claimantExtensionText" placeholder="Enter Extensions" style="display: none;">
                    </div>
                        <div class="form-group">
                            <label for="claimantSex">Sex</label>
                            <select class="form-control" id="claimantSex" name="claimantSex">
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>
                    <div class="form-group">
                <label for="claimantBirthday">Birthday</label>
                <input type="date" class="form-control datepicker" id="claimantBirthday" name="claimantBirthday" placeholder="Select Birthday" required>
                        </div>
                        <div class="form-group">
                            <label for="claimantAge">Age</label>
                            <input type="number" class="form-control" id="claimantAge" name="claimantAge" placeholder="Enter Age" readonly>
                        </div>
                                    <div class="form-group">
                                        <label for="claimantContactNumber">Contact Number</label>
                                        <input type="text" class="form-control" id="claimantContactNumber" name="claimantContactNumber" placeholder="Enter Contact Number" maxlength="11" minlength="11"  oninput="validateNumberInput(this)">
                                    </div>
                                <!-- Civil Status Dropdown -->
                        <div class="form-group">
                            <label for="claimantCivilStatus">Civil Status</label>
                            <select class="form-control" id="claimantCivilStatus" name="claimantCivilStatus" onchange="toggleClaimantSpouseField()">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Separated">Separated</option>
                                <option value="Widow">Widow</option>
                            </select>
                        </div>

                        <!-- Name of Spouse/Partner (Hidden initially) -->
                        <div class="form-group" id="claimantSpouseField" style="display: none;">
                            <label for="claimantSpouseName">Name of Spouse/Partner</label>
                            <input type="text" class="form-control" id="claimantSpouseName" name="claimantSpouseName" placeholder="Enter Spouse/Partner Name">
                            </div>
                        </div>
                    </div>
                    <br>
                <button type="submit" id="submitButton" class="btn btn-primary" name="submit">Submit</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script>
// Initialize the Leaflet map
var map = L.map('map').setView([14.3123, 121.564], 10); // Default center (Latitude, Longitude)

// Add OpenStreetMap tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Variable to store the current marker
var currentMarker = null;

// Function to update the input field with the coordinates
function updateCoordinatesInput(lat, lon) {
    var coordinatesInput = document.getElementById('coordinates');
    coordinatesInput.value = lat.toFixed(8) + ', ' + lon.toFixed(8); // Format coordinates with 8 decimal places
}

// Function to search and center the map based on coordinates with zoom
function searchCoordinates() {
    var coordinates = document.getElementById('coordinates').value;

    // Split the coordinates by comma
    var coordsArray = coordinates.split(',');

    if (coordsArray.length === 2) {
        var lat = parseFloat(coordsArray[0].trim());
        var lon = parseFloat(coordsArray[1].trim());

        // Check if the coordinates are valid
        if (!isNaN(lat) && !isNaN(lon)) {
            // Set the map's view to the given coordinates with a zoom level (e.g., 15 for zoomed-in view)
            map.setView([lat, lon], 15); // Adjust the zoom level as needed (e.g., 15 for more zoomed-in)

            // If there is an existing marker, remove it
            if (currentMarker) {
                map.removeLayer(currentMarker);
            }

            // Add a new marker at the coordinates and store it in currentMarker
            currentMarker = L.marker([lat, lon]).addTo(map);

            // Optionally, update the input field with the exact coordinates
            document.getElementById('coordinates').value = lat + ', ' + lon;
        } else {
            alert("Invalid coordinates entered.");
        }
    } else {
        alert("Please enter valid coordinates (Latitude, Longitude).");
    }
}

// Search when the button is clicked
document.getElementById('searchButton').addEventListener('click', function() {
    searchCoordinates();
});

// Search when the Enter key is pressed in the input field
document.getElementById('coordinates').addEventListener('keydown', function(event) {
    if (event.key === "Enter") {
        searchCoordinates();
    }
});

// Add event listener for map click to update coordinates input
map.on('click', function(e) {
    var lat = e.latlng.lat;
    var lon = e.latlng.lng;

    // Update input field with the clicked coordinates
    updateCoordinatesInput(lat, lon);

    // If there is an existing marker, remove it
    if (currentMarker) {
        map.removeLayer(currentMarker);
    }

    // Add a new marker at the clicked location and store it in currentMarker
    currentMarker = L.marker([lat, lon]).addTo(map);
});

// Handle form submission to lock the input field as readonly after saving
document.getElementById('coordinatesForm').addEventListener('submit', function(event) {
    // Lock the input field as readonly when form is submitted
    document.getElementById('coordinates').setAttribute('readonly', true);
});
</script>
    <script>
                // Toggle Spouse/Partner Field based on Civil Status
        function toggleSpouseField() {
            const civilStatus = document.getElementById('applicantCivilStatus').value;
            const spouseField = document.getElementById('spouseField');
            
            if (civilStatus === 'Married' || civilStatus === 'Separated' || civilStatus === 'Widow') {
                spouseField.style.display = 'block';
            } else {
                spouseField.style.display = 'none';
            }
        }
                    function toggleClaimantSpouseField() {
                const civilStatus = document.getElementById('claimantCivilStatus').value;
                const spouseField = document.getElementById('claimantSpouseField');
                
                if (civilStatus === 'Married' || civilStatus === 'Separated' || civilStatus === 'Widow') {
                    spouseField.style.display = 'block';
                } else {
                    spouseField.style.display = 'none';
                }
            }
    </script>
    <script>
        $(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });

            $('#extensionCheck').on('change', function() {
                $('#extensionText').toggle(this.checked);
            });

            $('#claimantExtensionCheck').on('change', function() {
                $('#claimantExtensionText').toggle(this.checked);
            });

            const barangays = {
                Alaminos: ["Barangay I (Poblacion)",
                            "Barangay II (Poblacion)",
                            "Barangay III (Poblacion)",
                            "Barangay IV (Poblacion)",
                            "Del Carmen",
                            "Palma",
                            "San Agustin (Antipolo)",
                            "San Andres",
                            "San Benito (Palita)",
                            "San Gregorio",
                            "San Ildefonso",
                            "San Juan",
                            "San Miguel",
                            "San Roque",
                            "Santa Rosa"],
                Calauan: ["Balayhangin",
                            "Bangyas",
                            "Dayap",
                            "Hanggan",
                            "Imok",
                            "Lamot 1",
                            "Lamot 2",
                            "Limao",
                            "Mabacan",
                            "Masiit",
                            "Paliparan",
                            "Pérez",
                            "Kanluran (Poblacion)",
                            "Silangan (Poblacion)",
                            "Prinza",
                            "San Isidro",
                            "Santo Tomas"],
                Cavinti: ["Anglas",
                        "Bangco",
                        "Bukal",
                        "Bulajo",
                        "Cansuso",
                        "Duhat",
                        "Inao-Awan",
                        "Kanluran Talaongan",
                        "Labayo",
                        "Layasin",
                        "Layug",
                        "Lumot/Mahipon",
                        "Paowin",
                        "Poblacion",
                        "Sisilmin",
                        "Silangan Talaongan",
                        "Sumucab",
                        "Tibatib",
                        "Udia"],
                Famy: ["Asana (Poblacion)",
                        "Bacong-Sigsigan",
                        "Bagong Pag-Asa (Poblacion)",
                        "Balitoc",
                        "Banaba (Poblacion)",
                        "Batuhan",
                        "Bulihan",
                        "Caballero (Poblacion)",
                        "Calumpang (Poblacion)",
                        "Kapatalan",
                        "Cuebang Bato",
                        "Damayan (Poblacion)",
                        "Kataypuanan",
                        "Liyang",
                        "Maate",
                        "Magdalo (Poblacion)",
                        "Mayatba",
                        "Minayutan",
                        "Salangbato",
                        "Tunhac"],
                Kalayaan: ["Longos",
                            "San Antonio",
                            "San Juan (Poblacion)"],
                Liliw: ["Bagong Anyo (Poblacion)",
                        "Bayate",
                        "Bongkol",
                        "Bubukal",
                        "Cabuyew",
                        "Calumpang",
                        "San Isidro",
                        "Culoy",
                        "Dagatan",
                        "Daniw",
                        "Dita",
                        "Ibabang Palina",
                        "Ibabang San Roque",
                        "Ibabang Sungi",
                        "Ibabang Taykin",
                        "Ilayang Palina",
                        "Ilayang San Roque",
                        "Ilayang Sungi",
                        "Ilayang Taykin",
                        "Kanlurang Bukal",
                        "Laguan",
                        "Luquin",
                        "Malabo-Kalantukan",
                        "Masikap (Poblacion)",
                        "Maslun (Poblacion)",
                        "Mojon",
                        "Novaliches",
                        "Oples",
                        "Pag-asa (Poblacion)",
                        "Palayan",
                        "Rizal (Poblacion)",
                        "San Isidro",
                        "Silangang Bukal",
                        "Tuy-Baanan"],
                Luisiana: ["De La Paz",
                        "Barangay Zone I (Poblacion)",
                        "Barangay Zone II (Poblacion)",
                        "Barangay Zone III (Poblacion)",
                        "Barangay Zone IV (Poblacion)",
                        "Barangay Zone V (Poblacion)",
                        "Barangay Zone VI (Poblacion)",
                        "Barangay Zone VII (Poblacion)",
                        "Barangay Zone VIII (Poblacion)",
                        "San Antonio",
                        "San Buenaventura",
                        "San Diego",
                        "San Isidro",
                        "San José",
                        "San Juan",
                        "San Luis",
                        "San Pablo",
                        "San Pedro",
                        "San Rafaél",
                        "San Roque",
                        "San Salvador",
                        "Santo Domingo",
                        "Santo Tomás"],
                Lumban: ["Bagong Silang",
                        "Balimbingan (Poblacion)",
                        "Balubad",
                        "Caliraya",
                        "Concepcion",
                        "Lewin",
                        "Maracta (Poblacion)",
                        "Maytalang I",
                        "Maytalang II",
                        "Primera Parang (Poblacion)",
                        "Primera Pulo (Poblacion)",
                        "Salac (Poblacion)",
                        "Santo Niño (Poblacion)",
                        "Segunda Parang (Poblacion)",
                        "Segunda Pulo (Poblacion)",
                        "Wawa"],
                Mabitac: ["Amuyong",
                        "Lambac (Poblacion)",
                        "Lucong (Poblacion)",
                        "Matalatala",
                        "Nanguma",
                        "Numero",
                        "Paagahan",
                        "Bayanihan (Poblacion)",
                        "Libis ng Nayon (Poblacion)",
                        "Maligaya (Poblacion)",
                        "Masikap (Poblacion)",
                        "Pag-Asa (Poblacion)",
                        "Sinagtala (Poblacion)",
                        "San Antonio",
                        "San Miguel"],
                Magdalena: ["Alipit",
                            "Baanan",
                            "Balanac",
                            "Bucal",
                            "Buenavista",
                            "Bungkol",
                            "Buo",
                            "Burlungan",
                            "Cigaras",
                            "Halayhayin",
                            "Ibabang Atingay",
                            "Ibabang Butnong",
                            "Ilayang Atingay",
                            "Ilayang Butnong",
                            "Ilog",
                            "Malaking Ambling",
                            "Malinao",
                            "Maravilla",
                            "Munting Ambling",
                            "Poblacion",
                            "Sabang",
                            "Salasad",
                            "Tanawan",
                            "Tipunan"],
                Majayjay: ["Amonoy",
                            "Bakia",
                            "Balanac",
                            "Balayong",
                            "Banilad",
                            "Banti",
                            "Bitaoy",
                            "Botocan",
                            "Bukal",
                            "Burgos",
                            "Burol",
                            "Coralao",
                            "Gagalot",
                            "Ibabang Banga",
                            "Ibabang Bayucain",
                            "Ilayang Banga",
                            "Ilayang Bayucain",
                            "Isabang",
                            "Malinao",
                            "May-It",
                            "Munting Kawayan",
                            "Olla",
                            "Oobi",
                            "Origuel (Poblacion)",
                            "Panalaban",
                            "Pangil",
                            "Panglan",
                            "Piit",
                            "Pook",
                            "Rizal",
                            "San Francisco (Poblacion)",
                            "San Miguel (Poblacion)",
                            "San Roque",
                            "Santa Catalina (Poblacion)",
                            "Suba",
                            "Talortor",
                            "Tanawan",
                            "Taytay",
                            "Villa Nogales"],
                Nagcarlan: ["Abo",
                            "Alibungbungan",
                            "Alumbrado",
                            "Balayong",
                            "Balimbing",
                            "Balinacon",
                            "Bambang",
                            "Banago",
                            "Banca-banca",
                            "Bangcuro",
                            "Banilad",
                            "Bayaquitos",
                            "Buboy",
                            "Buenavista",
                            "Buhanginan",
                            "Bukal",
                            "Bunga",
                            "Cabuyew",
                            "Calumpang",
                            "Kanluran Kabubuhayan",
                            "Silangan Kabubuhayan",
                            "Labangan",
                            "Lawaguin",
                            "Kanluran Lazaan",
                            "Silangan Lazaan",
                            "Lagulo",
                            "Maiit",
                            "Malaya",
                            "Malinao",
                            "Manaol",
                            "Maravilla",
                            "Nagcalbang",
                            "Poblacion I (Poblacion)",
                            "Poblacion II (Poblacion)",
                            "Poblacion III (Poblacion)",
                            "Oples",
                            "Palayan",
                            "Palina",
                            "Sabang",
                            "San Francisco",
                            "Sibulan",
                            "Silangan Napapatid",
                            "Silangan Ilaya",
                            "Sinipian",
                            "Santa Lucia",
                            "Sulsuguin",
                            "Talahib",
                            "Talangan",
                            "Taytay",
                            "Tipacan",
                            "Wakat",
                            "Yukos"],
                Paete: ["Barangay 1 - Ibaba del Sur",
                        "Barangay 2 - Maytoong",
                        "Barangay 3 - Ermita",
                        "Barangay 4 - Quinale",
                        "Barangay 5 - Ilaya del Sur",
                        "Barangay 6 - Ilaya del Norte",
                        "Barangay 7 - Bagumbayan",
                        "Barangay 8 - Bangkusay",
                        "Barangay 9 - Ibaba del Norte"],
                Pagsanjan: ["Anibong",
                            "Biñan",
                            "Buboy",
                            "Cabanbanan",
                            "Calusiche",
                            "Dingin",
                            "Lambac",
                            "Layugan",
                            "Magdapio",
                            "Maulawin",
                            "Pinagsanjan",
                            "Barangay I (Pob.)",
                            "Barangay II (Pob.)",
                            "Sabang",
                            "Sampaloc",
                            "San Isidro"],
                Pakil: ["Baño",
                        "Banilan",
                        "Burgos",
                        "Casa Real",
                        "Casinsin",
                        "Dorado",
                        "Gonzales",
                        "Kabulusan",
                        "Matikiw",
                        "Rizal",
                        "Saray",
                        "Taft",
                        "Tavera"],
                Pangil: ["Balian",
                        "Isla (Poblacion)",
                        "Natividad (Poblacion)",
                        "San Jose (Poblacion)",
                        "Sulib (Poblacion)",
                        "Galalan",
                        "Dambo",
                        "Mabato-Azufre"],
                Pila: ["Aplaya",
                        "Bagong Pook",
                        "Bukal",
                        "Bulilan Norte (Pob.)",
                        "Bulilan Sur (Pob.)",
                        "Concepcion",
                        "Labuin",
                        "Linga",
                        "Masico",
                        "Mojon",
                        "Pansol",
                        "Pinagbayanan",
                        "San Antonio",
                        "San Miguel",
                        "Santa Clara Norte (Pob.)",
                        "Santa Clara Sur (Pob.)",
                        "Tubuan"],
                Rizal: ["Antipolo",
                        "Entablado",
                        "Laguan",
                        "Pauli 1",
                        "Pauli 2",
                        "East Poblacion",
                        "West Poblacion",
                        "Pook",
                        "Tala",
                        "Talaga",
                        "Tuy"],
                "San Pablo": ["I-A (Sambat)",
                            "I-B (City+Riverside)",
                            "I-C (Bagong Bayan)",
                            "II-A (Triangulo)",
                            "II-B (Guadalupe)",
                            "II-C (Unson)",
                            "II-D (Bulante)",
                            "II-E (San Anton)",
                            "II-F (Villa Rey)",
                            "III-A (Hermanos Belen)",
                            "III-B",
                            "III-C (Labak/De Roma)",
                            "III-D (Villongco)",
                            "III-E",
                            "III-F (Balagtas)",
                            "IV-A",
                            "IV-B",
                            "IV-C",
                            "V-A",
                            "V-B",
                            "V-C",
                            "V-D",
                            "VI-A (Mavenida)",
                            "VI-B",
                            "VI-C (Bagong Pook)",
                            "VI-D (Lparkers)",
                            "VI-E (YMCA)",
                            "VII-A (P.Alcantara)",
                            "VII-B",
                            "VII-C",
                            "VII-D",
                            "VII-E",
                            "Atisan",
                            "Bautista",
                            "Concepcion (Bunot)",
                            "Del Remedio (Wawa)",
                            "Dolores",
                            "San Antonio 1 (Balanga)",
                            "San Antonio 2 (Sapa)",
                            "San Bartolome (Matang-ag)",
                            "San Buenaventura (Palakpakin)",
                            "San Crispin (Lumbangan)",
                            "San Cristobal",
                            "San Diego (Tiim)",
                            "San Francisco (Calihan)",
                            "San Gabriel (Butucan)",
                            "San Gregorio",
                            "San Ignacio",
                            "San Isidro (Balagbag)",
                            "San Joaquin",
                            "San Jose (Malamig)",
                            "San Juan",
                            "San Lorenzo (Saluyan)",
                            "San Lucas 1 (Malinaw)",
                            "San Lucas 2",
                            "San Marcos (Tikew)",
                            "San Mateo",
                            "San Miguel",
                            "San Nicolas",
                            "San Pedro",
                            "San Rafael (Magampon)",
                            "San Roque (Buluburan)",
                            "San Vicente",
                            "Santa Ana",
                            "Santa Catalina (Sandig)",
                            "Santa Cruz (Putol)",
                            "Santa Elena",
                            "Santa Filomena (Banlagin)",
                            "Santa Isabel",
                            "Santa Maria",
                            "Santa Maria Magdalena (Boe)",
                            "Santa Monica",
                            "Santa Veronica (Bae)",
                            "Santiago I (Bulaho)",
                            "Santiago II",
                            "Santisimo Rosario",
                            "Santo Angel (Ilog)",
                            "Santo Cristo",
                            "Santo Niño (Arsum)",
                            "Soledad (Macopa)"
                        ],
                "Santa Cruz": ["Alipit",
                                "Bagumbayan",
                                "Bubukal",
                                "Calios",
                                "Duhat",
                                "Gatid",
                                "Jasaan",
                                "Labuin",
                                "Malinao",
                                "Oogong",
                                "Pagsawitan",
                                "Palasan",
                                "Patimbao",
                                "Poblacion I",
                                "Poblacion II",
                                "Poblacion III",
                                "Poblacion IV",
                                "Poblacion V",
                                "San Jose",
                                "San Juan",
                                "San Pablo Norte",
                                "San Pablo Sur",
                                "Santisima Cruz",
                                "Santo Angel Central",
                                "Santo Angel Norte",
                                "Santo Angel Sur"],
                "Santa Maria": ["Adia",
                                "Bagong Pook",
                                "Bagumbayan",
                                "Bubucal",
                                "Cabooan",
                                "Calangay",
                                "Cambuja",
                                "Coralan",
                                "Cueva",
                                "Inayapan",
                                "Jose P. Laurel, Sr.",
                                "Jose P. Rizal",
                                "Juan Santiago",
                                "Kayhacat",
                                "Macasipac",
                                "Masinao",
                                "Matalinting",
                                "Pao-o",
                                "Parang ng Buho",
                                "Poblacion Dos",
                                "Poblacion Quatro",
                                "Poblacion Tres",
                                "Poblacion Uno",
                                "Talangka",
                                "Tungkod"],
                Siniloan: ["Acevida",
                            "Bagong Pag-Asa (Poblacion)",
                            "Bagumbarangay (Poblacion)",
                            "Buhay",
                            "G. Redor (Poblacion)",
                            "Gen. Luna",
                            "Halayahayin",
                            "Kapatalan",
                            "Laguio",
                            "Liyang",
                            "Llavac",
                            "Macatad",
                            "Magsaysay",
                            "Mayatba",
                            "Mendiola",
                            "P. Burgos",
                            "Pandeño",
                            "Salubungan",
                            "Wawa"],
                Victoria: ["Banca-banca",
                            "Daniw",
                            "Masapang",
                            "Nanhaya (Poblacion)",
                            "Pagalangan",
                            "San Benito",
                            "San Felix",
                            "San Francisco",
                            "San Roque (Poblacion)"]

            };

            $('#municipality').on('change', function() {
                const selectedMunicipality = $(this).val();
                const barangaySelect = $('#barangay');
                barangaySelect.empty();
                barangaySelect.append('<option value="">Select Barangay</option>');
                
                if (selectedMunicipality && barangays[selectedMunicipality]) {
                    barangays[selectedMunicipality].forEach(function(barangay) {
                        barangaySelect.append(new Option(barangay, barangay));
                    });
                }
            });
        });
    </script>
<script>
    // Function to calculate age based on birthday
    function calculateAge(birthday) {
        var today = new Date();
        var birthDate = new Date(birthday);
        var age = today.getFullYear() - birthDate.getFullYear();
        var m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    // Attach change event listener to the birthday input field
    $('#claimantBirthday').change(function() {
        var birthday = $(this).val();
        var age = calculateAge(birthday);
        $('#claimantAge').val(age);
    });
</script>

<script>
    // Function to calculate age based on birthday
    function calculateAge(birthday) {
        var today = new Date();
        var birthDate = new Date(birthday);
        var age = today.getFullYear() - birthDate.getFullYear();
        var m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    // Attach change event listener to the birthday input field
    $('#applicantBirthday').change(function() {
        var birthday = $(this).val();
        var age = calculateAge(birthday);
        $('#applicantAge').val(age);
    });
</script>
<script>
    document.getElementById('landTitleForm').addEventListener('submit', function(event) {
            event.preventDefault();
            alert('Form submitted successfully!');
            // You can reset the form if needed
            this.reset();
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
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = 'logout.php';
    }
    return false; // Prevent default action
}
</script>
<script>
function validateNumberInput(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}
</script>

</body>
</html>
