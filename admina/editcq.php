<?php
// MySQL kapcsolódás
$servername = "192.168.8.179:6033";
$username = "root";
$password = "passwd";
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
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
    }
    return false;
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

    if ($type === 'card') {
        // Az adatok frissítése kártya típus esetén
        $sql = "UPDATE card SET name=?, manufacturing_time=?, material=?, description=? WHERE code=?";
        $stmt = $conn->prepare($sql);
        $formatted_description = str_replace(['<p>'], '', $data['description']);
        $another_formatted_description = str_replace(['</p>'], '<br>', $formatted_description);
        $stmt->bind_param("sssss", $data['name'], $data['time'], $data['madeof'], $another_formatted_description, $code);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo "Az adatok sikeresen frissültek.";
            header("Refresh:0");
        } else {
            echo "Hiba történt az adatok frissítése során.";
        }
    } else {
        // Ha a típus kvíz, akkor nem frissítünk semmit, és csak egy üzenetet jelenítünk meg
        echo '<div class="alert alert-danger" role="alert">
                Sajnos ezt a típusú kódot nem lehet módosítani.
              </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adatok módosítása</title>
    <script src="//cdn.ckeditor.com/4.6.2/standard/ckeditor.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <style>.button {
        background-color: #155DE9;
        color: white;
        border: none;
        padding: 10px 20px;
        text-align: center;
        font-family: 'Arial';
      }</style>
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
        <h2>
        <form action="" method="post">
            <input type="hidden" name="code" value="<?= $code ?>">
            <input type="hidden" name="type" value="<?= $codeType ?>">
            <?php if ($codeType === 'card'): ?>
                <!-- Kártya adatok szerkesztése -->
                <div class="mb-3">
                    <label for="name" class="form-label">Név</label>
                    <input type="text" class="form-control" id="name" name="card[name]" value="<?= isset($data['name']) ? $data['name'] : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="time" class="form-label">Gyártási idő</label>
                    <input type="text" class="form-control" id="time" name="card[time]" value="<?= isset($data['manufacturing_time']) ? $data['manufacturing_time'] : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="madeof" class="form-label">Anyag</label>
                    <input type="text" class="form-control" id="madeof" name="card[madeof]" value="<?= isset($data['material']) ? $data['material'] : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Leírás</label>
                    <textarea class="form-control" id="description" name="card[description]" required><?= isset($data['description']) ? $data['description'] : '' ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" name="update_data">Adatok mentése</button>
                
<script type="text/javascript">
      window.onload = () => {
        CKEDITOR.replace("description");
      };

      function sendText() {
        window.parent.postMessage(CKEDITOR.instances.CK1.getData(), "*");
      }
    </script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
            <?php endif; ?>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
