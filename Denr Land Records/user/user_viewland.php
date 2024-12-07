<?php
session_start();

if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location: login_page.php");
    exit;
}

$user = $_SESSION['username'];
$userId = $_SESSION['userId'];

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

    // Fetch status options from the verified_land table
    $statusOptionsStmt = $conn->query("SELECT DISTINCT status FROM verified_land");
    $statusOptions = $statusOptionsStmt->fetch_all(MYSQLI_ASSOC);

    // Fetch applicant civil status and spouse name
    $applicantCivilStatus = $applicantRow['applicantCivilStatus'];
    $applicantSpouseName = $applicantRow['applicantSpouseName'];

    // Fetch claimant civil status and spouse name
    $claimantCivilStatus = $claimantRow['claimantCivilStatus'];
    $claimantSpouseName = $claimantRow['claimantSpouseName'];


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
    <title>DENR-CENRO: Verified Land Title</title>
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

  <form method="POST" action="update_land_title.php">
        <div class="form-container">   <div class="row">
        <div class="col-md-6"> <!-- Adjusted column for medium-sized screens -->
        <div class="card">
            <div class="card-header">
                <h4>Verified Land Title Information</h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="lot_number">Lot Number</label>
                    <input type="text" class="form-control" id="lot_number" name="lot_number" placeholder="Enter Lot Number" value="<?php echo isset($row['lot_number']) ? $row['lot_number'] : ''; ?>" readonly>
                </div>
                        <div>
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status" disabled>
                <?php
                foreach ($statusOptions as $statusRow) {
                    $option = $statusRow['status'];
                    $selected = ($row['status'] == $option) ? 'selected' : '';
                    echo "<option value='$option' $selected>$option</option>";
                }
                ?>
            </select>
        </div>
                <div class="form-group">
                    <label for="municipality">Municipality</label>
                    <select class="form-control" id="municipality" name="municipality" disabled>
                        <option value="">Select Municipality</option>
                        <?php
                        $municipalities = ["Alaminos", "Calauan", "Cavinti", "Famy", "Kalayaan", "Liliw", "Luisiana", "Lumban", "Mabitac", "Magdalena", "Majayjay", "Nagcarlan", "Paete", "Pagsanjan", "Pakil", "Pangil", "Pila", "Rizal", "San Pablo", "Santa Cruz", "Santa Maria", "Siniloan", "Victoria"];
                        foreach ($municipalities as $municipality) {
                            $selected = isset($row['municipality']) && $row['municipality'] == $municipality ? 'selected' : '';
                            echo "<option value='$municipality' $selected>$municipality</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <select class="form-control" id="barangay" name="barangay" disabled>
                        <option value="">Select Barangay</option>
                        <!-- Populate barangay options dynamically based on selected municipality -->
                        <?php
        $municipality = isset($row['municipality']) ? $row['municipality'] : ''; // Assuming $row['municipality'] contains the selected municipality
        $barangays = [];
        switch ($municipality) {
            case 'Alaminos':
                $barangays = ["Barangay I (Poblacion)",
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
                            "Santa Rosa"];
                break;
            case 'Calauan':
                $barangays = ["Balayhangin",
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
                            "Santo Tomas"];
                break;
                case 'Cavinti':
                    $barangays = ["Anglas",
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
                                "Udia"];
                    break;
                    case 'Famy':
                        $barangays = ["Asana (Poblacion)",
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
                                    "Tunhac"];
                        break;
                    case 'Kalayaan':
                        $barangays = ["Longos",
                                    "San Antonio",
                                    "San Juan (Poblacion)"];
                        break;
                    case 'Liliw':
                        $barangays = ["Bagong Anyo (Poblacion)",
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
                                    "Tuy-Baanan"];
                        break;
                    case 'Luisiana':
                        $barangays = ["De La Paz",
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
                                    "Santo Tomás"];
                        break;
                        case 'Lumban':
                            $barangays = ["Bagong Silang",
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
                                        "Wawa"];
                            break;
                        case 'Mabitac':
                            $barangays = ["Amuyong",
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
                                        "San Miguel"];
                            break;
                        case 'Magdalena':
                            $barangays = ["Alipit",
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
                                        "Tipunan"];
                            break;
                        case 'Majayjay':
                            $barangays = ["Amonoy",
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
                                        "Villa Nogales"];
                            break;
                            case 'Nagcarlan':
                                $barangays = ["Abo",
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
                                            "Yukos"];
                                break;
                            case 'Paete':
                                $barangays = ["Barangay 1 - Ibaba del Sur",
                                            "Barangay 2 - Maytoong",
                                            "Barangay 3 - Ermita",
                                            "Barangay 4 - Quinale",
                                            "Barangay 5 - Ilaya del Sur",
                                            "Barangay 6 - Ilaya del Norte",
                                            "Barangay 7 - Bagumbayan",
                                            "Barangay 8 - Bangkusay",
                                            "Barangay 9 - Ibaba del Norte"];
                                break;
                            case 'Pagsanjan':
                                $barangays = ["Anibong",
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
                                            "San Isidro"];
                                break;
                            case 'Pakil':
                                $barangays = ["Baño",
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
                                            "Tavera"];
                                break;
                            case 'Pangil':
                                $barangays = ["Balian",
                                            "Isla (Poblacion)",
                                            "Natividad (Poblacion)",
                                            "San Jose (Poblacion)",
                                            "Sulib (Poblacion)",
                                            "Galalan",
                                            "Dambo",
                                            "Mabato-Azufre"];
                                break;
                                case 'Pila':
                                    $barangays = ["Aplaya",
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
                                                "Tubuan"];
                                    break;
                                case 'Rizal':
                                    $barangays = ["Antipolo",
                                                "Entablado",
                                                "Laguan",
                                                "Pauli 1",
                                                "Pauli 2",
                                                "East Poblacion",
                                                "West Poblacion",
                                                "Pook",
                                                "Tala",
                                                "Talaga",
                                                "Tuy"];
                                    break;
                                case 'San Pablo':
                                    $barangays = ["I-A (Sambat)",
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
                                                "Soledad (Macopa)"];
                                    break;
                                    case 'Santa Cruz':
                                        $barangays = ["Alipit",
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
                                                    "Santo Angel Sur"];
                                        break;
                                    case 'Santa Maria':
                                        $barangays = ["Adia",
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
                                                    "Tungkod"];
                                        break;
                                    case 'Siniloan':
                                        $barangays = ["Acevida",
                                                    "Bagong Pag-Asa (Poblacion)",
                                                    "Bagumbarangay (Poblacion)",
                                                    "Buhay",
                                                    "G. Redor (Poblacion)",
                                                    "Gen. Luna",
                                                    "Halayahayin",
                                                    "Kapatalan",
                                                    "Liyang",
                                                    "Llavac",
                                                    "Macatad",
                                                    "Magsaysay",
                                                    "Mayatba",
                                                    "Mendiola",
                                                    "P. Burgos",
                                                    "Pandeño",
                                                    "Salubungan",
                                                    "Wawa"];
                                        break;
                                    case 'Victoria':
                                        $barangays = ["Banca-banca",
                                                    "Daniw",
                                                    "Masapang",
                                                    "Nanhaya (Poblacion)",
                                                    "Pagalangan",
                                                    "San Benito",
                                                    "San Felix",
                                                    "San Francisco",
                                                    "San Roque (Poblacion)"];
                                        break;
            // Add cases for other municipalities with their respective barangays
            // Repeat this pattern for each municipality
            default:
                // No municipality selected, do nothing or display a message
                break;
        }
        
        // Output barangay options based on selected municipality
        foreach ($barangays as $barangay) {
            $selected = isset($row['barangay']) && $row['barangay'] == $barangay ? 'selected' : '';
            echo "<option value='$barangay' $selected>$barangay</option>";
        }
        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_approved">Date Approved</label>
                    <input type="date" name="date_approved" required value="<?php echo isset($row['date_approved']) ? $row['date_approved'] : ''; ?>" disabled>
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
                    <label for="coordinates" class="mr-2">Enter Coordinates</label>
                    <input type="text" id="coordinates" name="coordinates" class="form-control mr-2" placeholder="Enter Latitude, Longitude" readonly value="<?php echo isset($row['latitude']) ? $row['latitude'] : ''; ?><?php echo isset($row['longitude']) ? $row['longitude'] : ''; ?>">
                    <!-- Search Button with Icon -->
                    <button type="button" class="btn btn-primary" id="searchButton" hidden>
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
            <input type="text" class="form-control" id="applicantLastName" name="applicantLastName" placeholder="Enter Last Name" value="<?php echo isset($applicantRow['applicantLastName']) ? $applicantRow['applicantLastName'] : ''; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="applicantFirstName">First Name</label>
            <input type="text" name="applicantFirstName" class="form-control" id="applicantFirstName" placeholder="Enter First Name" value="<?php echo isset($applicantRow['applicantFirstName']) ? $applicantRow['applicantFirstName'] : ''; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="applicantMiddleName">Middle Name</label>
            <input type="text" class="form-control" name="applicantMiddleName" id="applicantMiddleName" placeholder="Enter Middle Name" value="<?php echo isset($applicantRow['applicantMiddleName']) ? $applicantRow['applicantMiddleName'] : ''; ?>" readonly>
        </div>
                <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="extensionCheck" <?php echo isset($applicantRow['applicantExtension']) && $applicantRow['applicantExtension'] ? 'checked' : ''; ?> disabled>
            <label class="form-check-label" for="extensionCheck">Extensions</label>
            <input type="text" class="form-control mt-2" name="applicantExtension" id="applicantExtension" placeholder="Enter Extensions" style="<?php echo isset($applicantRow['applicantExtension']) && $applicantRow['applicantExtension'] ? '' : 'display: none;'; ?>" value="<?php echo isset($applicantRow['applicantExtension']) ? $applicantRow['applicantExtension'] : ''; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="applicantSex">Sex</label>
            <select class="form-control" id="applicantSex" name="applicantSex" disabled>
                <option <?php echo isset($applicantRow['applicantSex']) && $applicantRow['applicantSex'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                <option <?php echo isset($applicantRow['applicantSex']) && $applicantRow['applicantSex'] == 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>
        <div class="form-group">
            <label for="applicantBirthday">Birthday</label>
            <input type="date" class="form-control" id="applicantBirthday" name="applicantBirthday" placeholder="Select Birthday" required value="<?php echo isset($applicantRow['applicantBirthday']) ? $applicantRow['applicantBirthday'] : ''; ?>" disabled>
        </div>
        <div class="form-group">
            <label for="applicantAge">Age</label>
            <input type="text" class="form-control" id="applicantAge" name="applicantAge" placeholder="Enter Age" readonly value="<?php echo isset($applicantRow['applicantAge']) ? $applicantRow['applicantAge'] : ''; ?>" >
        </div>
        <div class="form-group">
            <label for="applicantContactNumber">Contact Number</label>
            <input type="text" class="form-control" id="applicantContactNumber" name="applicantContactNumber" placeholder="Enter Contact Number" maxlength="11" value="<?php echo isset($applicantRow['applicantContactNumber']) ? $applicantRow['applicantContactNumber'] : ''; ?>" readonly>
        </div>
        <!-- Civil Status Dropdown for Applicant -->
        <div class="form-group">
            <label for="applicantCivilStatus">Civil Status</label>
            <select class="form-control" id="applicantCivilStatus" name="applicantCivilStatus" onchange="toggleSpouseField()" required disabled>
                <option value="Single" <?php if ($applicantCivilStatus == 'Single') echo 'selected'; ?>>Single</option>
                <option value="Married" <?php if ($applicantCivilStatus == 'Married') echo 'selected'; ?>>Married</option>
                <option value="Separated" <?php if ($applicantCivilStatus == 'Separated') echo 'selected'; ?>>Separated</option>
                <option value="Widow" <?php if ($applicantCivilStatus == 'Widow') echo 'selected'; ?>>Widow</option>
            </select>
        </div>

        <!-- Name of Spouse/Partner (Hidden initially) -->
        <div class="form-group" id="spouseField" style="display: none;">
            <label for="applicantSpouseName">Name of Spouse/Partner</label>
            <input type="text" class="form-control" id="applicantSpouseName" name="applicantSpouseName" placeholder="Enter Spouse/Partner Name" value="<?php echo isset($applicantRow['applicantSpouseName']) ? $applicantRow['applicantSpouseName'] : ''; ?>" readonly>
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
            <input type="text" class="form-control" id="claimantLastName" name="claimantLastName" placeholder="Enter Last Name" value="<?php echo isset($claimantRow['claimantLastName']) ? $claimantRow['claimantLastName'] : ''; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="claimantFirstName">First Name</label>
            <input type="text" class="form-control" id="claimantFirstName" name="claimantFirstName" placeholder="Enter First Name" value="<?php echo isset($claimantRow['claimantFirstName']) ? $claimantRow['claimantFirstName'] : ''; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="claimantMiddleName">Middle Name</label>
            <input type="text" class="form-control" id="claimantMiddleName" name="claimantMiddleName" placeholder="Enter Middle Name" value="<?php echo isset($claimantRow['claimantMiddleName']) ? $claimantRow['claimantMiddleName'] : ''; ?>" readonly>
        </div>
                <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="claimantExtensionCheck" <?php echo isset($claimantRow['claimantExtension']) && $claimantRow['claimantExtension'] ? 'checked' : ''; ?> disabled>
            <label class="form-check-label" for="claimantExtensionCheck">Extensions</label>
            <input type="text" class="form-control mt-2" name="claimantExtension" id="claimantExtension" placeholder="Enter Extensions" style="<?php echo isset($claimantRow['claimantExtension']) && $claimantRow['claimantExtension'] ? '' : 'display: none;'; ?>" value="<?php echo isset($claimantRow['claimantExtension']) ? $claimantRow['claimantExtension'] : ''; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="claimantSex">Sex</label>
            <select class="form-control" id="claimantSex" name="claimantSex" disabled>
                <option <?php echo isset($claimantRow['claimantSex']) && $claimantRow['claimantSex'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                <option <?php echo isset($claimantRow['claimantSex']) && $claimantRow['claimantSex'] == 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>
        <div class="form-group">
            <label for="claimantBirthday">Birthday</label>
            <input type="date" class="form-control datepicker" id="claimantBirthday" name="claimantBirthday" placeholder="Select Birthday" required value="<?php echo isset($claimantRow['claimantBirthday']) ? $claimantRow['claimantBirthday'] : ''; ?>" disabled>
        </div>
        <div class="form-group">
            <label for="claimantAge">Age</label>
            <input type="number" class="form-control" id="claimantAge" name="claimantAge" placeholder="Enter Age" readonly value="<?php echo isset($claimantRow['claimantAge']) ? $claimantRow['claimantAge'] : ''; ?>">
        </div>
        <div class="form-group">
            <label for="claimantContactNumber">Contact Number</label>
            <input type="text" class="form-control" id="claimantContactNumber" name="claimantContactNumber" placeholder="Enter Contact Number" maxlength="11" value="<?php echo isset($claimantRow['claimantContactNumber']) ? $claimantRow['claimantContactNumber'] : ''; ?>" readonly>
        </div>
                        <!-- Civil Status Dropdown for Claimant -->
            <div class="form-group">
                <label for="claimantCivilStatus">Civil Status</label>
                <select class="form-control" id="claimantCivilStatus" name="claimantCivilStatus" onchange="toggleClaimantSpouseField()" disabled>
                <option value="Single" <?php if ($claimantCivilStatus == 'Single') echo 'selected'; ?>>Single</option>
                <option value="Married" <?php if ($claimantCivilStatus == 'Married') echo 'selected'; ?>>Married</option>
                <option value="Separated" <?php if ($claimantCivilStatus == 'Separated') echo 'selected'; ?>>Separated</option>
                <option value="Widow" <?php if ($claimantCivilStatus == 'Widow') echo 'selected'; ?>>Widow</option>
                </select>
            </div>

            <!-- Name of Spouse/Partner for Claimant (Hidden initially) -->
            <div class="form-group" id="claimantSpouseField" style="display: none;">
                <label for="claimantSpouseName">Name of Spouse/Partner</label>
                <input type="text" class="form-control" id="claimantSpouseName" name="claimantSpouseName" placeholder="Enter Spouse/Partner Name" value="<?php echo isset($claimantRow['claimantSpouseName']) ? $claimantRow['claimantSpouseName'] : ''; ?>" readonly>
            </div>
        </div>
    </div>
        <br>
        <input type="hidden" name="verified_landID" value="<?php echo isset($verified_landID) ? $verified_landID : ''; ?>">
                <button type="button" class="btn btn-success" onclick="printLandTitle()">Print</button>
            </form>
        </div>
    </div>
 

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script>
// Initialize the Leaflet map with default coordinates
var map = L.map('map').setView([14.3123, 121.564], 10); // Default center (Latitude, Longitude)

// Add OpenStreetMap tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Variable to store the current marker
var currentMarker = null;
var coordinatesLocked = false; // Flag to prevent changes after marker is placed

// Function to update the input field with the coordinates
function updateCoordinatesInput(lat, lon) {
    var coordinatesInput = document.getElementById('coordinates');
    coordinatesInput.value = lat.toFixed(8) + ', ' + lon.toFixed(8); // Format coordinates with 8 decimal places
}

// Function to search and center the map based on coordinates with zoom
function searchCoordinates() {
    if (coordinatesLocked) return; // Do nothing if coordinates are locked

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

            // If there is no existing marker, add a new marker at the coordinates and store it in currentMarker
            if (!currentMarker) {
                currentMarker = L.marker([lat, lon]).addTo(map);
                coordinatesLocked = true; // Lock coordinates after the marker is placed
                document.getElementById('coordinates').setAttribute('readonly', true); // Lock the input field
            }

            // Optionally, update the input field with the exact coordinates
            document.getElementById('coordinates').value = lat + ', ' + lon;
        } else {
            alert("Invalid coordinates entered.");
        }
    } else {
        alert("Please enter valid coordinates (Latitude, Longitude).");
    }
}

// Check if there are coordinates from PHP when the page loads
var initialLatitude = <?php echo isset($row['latitude']) ? $row['latitude'] : 'null'; ?>;
var initialLongitude = <?php echo isset($row['longitude']) ? $row['longitude'] : 'null'; ?>;

if (initialLatitude && initialLongitude) {
    // Set the map's view to the coordinates fetched from PHP
    map.setView([initialLatitude, initialLongitude], 15);

    // Add a marker at the fetched coordinates, but do not replace it
    if (!currentMarker) {
        currentMarker = L.marker([initialLatitude, initialLongitude]).addTo(map);
        coordinatesLocked = true; // Lock coordinates after the marker is placed
        document.getElementById('coordinates').setAttribute('readonly', true); // Lock the input field
    }

    // Update the input field with the coordinates
    updateCoordinatesInput(initialLatitude, initialLongitude);
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
    if (coordinatesLocked) return; // Do nothing if coordinates are locked

    var lat = e.latlng.lat;
    var lon = e.latlng.lng;

    // Update input field with the clicked coordinates
    updateCoordinatesInput(lat, lon);

    // Only add a new marker if there isn't one already
    if (!currentMarker) {
        currentMarker = L.marker([lat, lon]).addTo(map);
        coordinatesLocked = true; // Lock coordinates after the marker is placed
        document.getElementById('coordinates').setAttribute('readonly', true); // Lock the input field
    }
});

// Handle form submission to lock the input field as readonly after saving
document.getElementById('coordinatesForm').addEventListener('submit', function(event) {
    // Lock the input field as readonly when form is submitted
    document.getElementById('coordinates').setAttribute('readonly', true);
});
</script>
    <script>
    // JavaScript to show a confirmation dialog
    document.getElementById('resubmitButton').addEventListener('click', function(event) {
        // Show confirmation dialog
        var confirmUpdate = confirm("Are you sure you want to resubmit the information?");
        
        // If the user clicks "Cancel", prevent the form from submitting
        if (!confirmUpdate) {
            event.preventDefault();
            return false; // Optional, but makes it clearer
        }
        // If confirmed, allow form submission
    });
</script>
    <script>
        // Function to toggle spouse name fields based on civil status
        function toggleSpouseField(fieldId, selectId) {
            var status = document.getElementById(selectId).value;
            var spouseField = document.getElementById(fieldId);

            if (status === 'Married' || status === 'Widow' || status === 'Separated') {
                spouseField.style.display = 'block';
            } else {
                spouseField.style.display = 'none';
            }
        }

        // Ensure the spouse fields are displayed correctly when the page loads
        window.onload = function() {
            toggleSpouseField('applicantSpouseField', 'applicantCivilStatus');
            toggleSpouseField('claimantSpouseField', 'claimantCivilStatus');
        }
    </script>
    <script>
    // Function to toggle spouse field visibility for applicant
    function toggleSpouseField() {
        var civilStatus = document.getElementById('applicantCivilStatus').value;
        var spouseField = document.getElementById('spouseField');
        if (civilStatus === 'Married' || civilStatus === 'Widow' || civilStatus === 'Separated') {
            spouseField.style.display = 'block';
        } else {
            spouseField.style.display = 'none';
        }
    }

    // Function to toggle spouse field visibility for claimant
    function toggleClaimantSpouseField() {
        var civilStatus = document.getElementById('claimantCivilStatus').value;
        var claimantSpouseField = document.getElementById('claimantSpouseField');
        if (civilStatus === 'Married' || civilStatus === 'Widow' || civilStatus === 'Separated') {
            claimantSpouseField.style.display = 'block';
        } else {
            claimantSpouseField.style.display = 'none';
        }
    }

    // On page load, check civil status and display/hide spouse field accordingly
    document.addEventListener('DOMContentLoaded', function() {
        toggleSpouseField();
        toggleClaimantSpouseField();
    });
</script>
   <script>
function printLandTitle() {
    var landID = "<?php echo isset($verified_landID) ? $verified_landID : ''; ?>";
    if (landID) {
        window.open('print_verified_title.php?id=' + landID, '_blank');
    } else {
        alert('Land ID is not set. Cannot print.');
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
                $('#applicantExtension').toggle(this.checked);
            });

            $('#claimantExtensionCheck').on('change', function() {
                $('#claimantExtension').toggle(this.checked);
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

         // Populate barangays on page load if municipality is already selected
         document.addEventListener('DOMContentLoaded', function() {
                if (document.getElementById('municipality').value !== '') {
                    populateBarangays();
                }
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

</body>
</html>
