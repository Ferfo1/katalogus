<?php
// MySQL kapcsolódás
$servername = "localhost";
$username = "root";
$password = "";
$database = "katalogus";

$conn = new mysqli($servername, $username, $password, $database);

// Ellenőrzés, hogy sikeres volt-e a kapcsolódás
if ($conn->connect_error) {
    die("Nem sikerült kapcsolódni az adatbázishoz: " . $conn->connect_error);
}

// Funkció a kód típusának lekérdezésére a megadott kód alapján
function getCodeType($code) {
    global $conn;
    $sql = "SELECT type FROM type WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['type'];
    }
    return false;
}

// Funkció az adatok lekérdezésére a megadott kód alapján
function getData($code, $type) {
    global $conn;
    if ($type === 'card') {
        $sql = "SELECT name, manufacturing_time, material, description FROM card WHERE code=?";
    } elseif ($type === 'quiz') {
        $sql = "SELECT question, answer, options FROM quiz WHERE code=?";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row;
    }
    return false;
}

// Funkció az adatok frissítésére a megadott kód és típus alapján
function updateData($code, $type, $data) {
    global $conn;
    if ($type === 'card') {
        $sql = "UPDATE card SET name=?, manufacturing_time=?, material=?, description=? WHERE code=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $data['name'], $data['time'], $data['madeof'], $data['description'], $code);
    } elseif ($type === 'quiz') {
        $sql = "UPDATE quiz SET question=?, answer=?, options=? WHERE code=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $data['question'], $data['answer'], json_encode($data['options']), $code);
    }
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

// Kód típusának lekérése, ha a felhasználó már beküldte a formot
$codeType = null;
$data = null;
if (isset($_POST['code'])) {
    $code = $_POST['code'];
    $codeType = getCodeType($code);
    $data = getData($code, $codeType);
}

// Adatok frissítése, ha a felhasználó már beküldte a módosított adatokat
if (isset($_POST['update_data'])) {
    $code = $_POST['code'];
    $type = $_POST['type'];
    $data = $_POST[$type];

    if (updateData($code, $type, $data)) {
        echo "Az adatok sikeresen frissültek.";
    } else {
        echo "Hiba történt az adatok frissítése során.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adatok módosítása</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Adatok módosítása</h1>
        <hr>
        <form action="" method="post">
            <div class="mb-3">
                <label for="code" class="form-label">Kód</label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            <button type="submit" class="btn btn-primary" name="get_code_type">Kód ellenőrzése</button>
        </form>

        <?php if ($codeType): ?>
        <h2><?= ucfirst($codeType) ?> adatok módosítása</h2>
        <form action="" method="post">
            <input type="hidden" name="code" value="<?= $code ?>">
            <input type="hidden" name="type" value="<?= $codeType ?>">
            <?php if ($codeType === 'card'): ?>
                <!-- Kártya adatok szerkesztése -->
                <div class="mb-3">
                    <label for="name" class="form-label">Név</label>
                    <input type="text" class="form-control" id="name" name="card[name]" required>
                </div>
                <div class="mb-3">
                    <label for="time" class="form-label">Gyártási idő</label>
                    <input type="text" class="form-control" id="time" name="card[time]" required>
                </div>
                <div class="mb-3">
                    <label for="madeof" class="form-label">Anyag</label>
                    <input type="text" class="form-control" id="madeof" name="card[madeof]" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Leírás</label>
                    <textarea class="form-control" id="description" name="card[description]" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="photos" class="form-label">Fényképek</label>
                    <input type="file" class="form-control" id="photos" name="card[photos][]" multiple required>
                </div>
            <?php elseif ($codeType === 'quiz'): ?>
                <!-- Kvíz adatok szerkesztése -->
                <div class="mb-3">
                    <label for="question" class="form-label">Kérdés</label>
                    <input type="text" class="form-control" id="question" name="quiz[question]" required>
                </div>
                <div class="mb-3">
                    <label for="answer" class="form-label">Válasz</label>
                    <input type="text" class="form-control" id="answer" name="quiz[answer]" required>
                </div>
                <div class="mb-3">
                    <label for="options" class="form-label">Opciók</label>
                    <input type="text" class="form-control" id="options" name="quiz[options]" required>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary" name="update_data">Adatok mentése</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
