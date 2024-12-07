<?php
session_start();

if(!isset($_SESSION['username']) || empty($_SESSION['username'])) {
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
    <title>DENR-CENRO: List of Verified Land Titles</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <?php include 'style.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
    
<?php include 'navbar.php'; ?>

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

        
        <div class="table-container">
            <h3>Archive List of Verified Land Titles</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lot Number</th>
                        <th>Land Category</th>
                        <th>Date Approved</th>
                        <th>Applicant</th>
                        <th>Survey Claimant</th>
                        <th>Barangay</th>
                        <th>Municipality</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="landTableBody">
                    <?php
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $database = "denr";

                    $conn = new mysqli($servername, $username, $password, $database);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $sql = "SELECT verified_landID, lot_number, status, date_approved, applicant_name, survey_claimant_name, barangay, municipality, applicantID, claimantID 
                            FROM verified_land
                            WHERE archive_status = '1'
                            ORDER BY date_approved DESC";

                    $result = $conn->query($sql);


                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr id='row-" . $row["verified_landID"] . "'>
                                <td>" . $row["verified_landID"] . "</td>
                                <td>" . $row["lot_number"] . "</td>
                                <td>" . $row["status"] . "</td>
                                <td>" . $row["date_approved"] . "</td>
                                <td>" . $row["applicant_name"] . "</td>
                                <td>" . $row["survey_claimant_name"] . "</td>
                                <td>" . $row["barangay"] . "</td>
                                <td>" . $row["municipality"] . "</td>
                                <td>
                                <form action='unarchive.php' method='POST'>
                                <input type='hidden' name='verified_landID' value='" . $row["verified_landID"] . "'>
                                <button type='submit' id='unarchiveButton' class='btn btn-warning'><i class='fas fa-archive'></i>Unarchive</button>
                                 </form>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>No records found</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>

   




    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    // JavaScript to show a confirmation dialog
    document.getElementById('unarchiveButton').addEventListener('click', function(event) {
        // Show confirmation dialog
        var confirmUpdate = confirm("Are you sure you want to unarchive the land title?");
        
        // If the user clicks "Cancel", prevent the form from submitting
        if (!confirmUpdate) {
            event.preventDefault();
        }
    });
</script>
    <script>

// Function to populate barangay options
function populateBarangayOptions(municipality, selectedBarangay = '') {
    const barangaySelect = $('#editBarangay');
    barangaySelect.empty();
    barangaySelect.append('<option value="">Select Barangay</option>');
    if (municipality) {
        const barangays = getBarangays(municipality);
        barangays.forEach(function(barangayOption) {
            const option = $('<option>', { value: barangayOption, text: barangayOption });
            if (barangayOption === selectedBarangay) {
                option.attr('selected', 'selected'); // Select the current barangay if provided
            }
            barangaySelect.append(option);
        });
    }
}

// Function to get barangays for the selected municipality
function getBarangays(municipality) {
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
    return barangays[municipality] || [];
}

</script>
<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('show');
}
</script>
<script>
function confirmEdit() {
    return confirm("Are you sure you want to edit?");
}
function confirmLogout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = 'logout.php';
    }
    return false; // Prevent default action
}
</script>

</body>
</html>

