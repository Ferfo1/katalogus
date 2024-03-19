<?php
error_reporting(E_ERROR | E_PARSE); // Csak az E_ERROR és E_PARSE hibák jelenjenek meg
ini_set('display_errors', 'Off'); // A hibák ne jelenjenek meg a kimeneten
// JSON fájl elérési útvonala
$json_file = 'codes.json';

$question6 = ''; // Inicializálás
$answer6_1 = '';
$answer6_2 = '';
$answer6_3 = '';
$answer6_4 = '';
$correct_answer6 = '';
$i = 1;
$j = 1;
// Kódok betöltése JSON fájlból
function loadCodes() {
    global $json_file;
    $json = file_get_contents($json_file);
    return json_decode($json, true);
}

// Kódok mentése JSON fájlba
function saveCodes($data) {
    global $json_file;
    $json = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($json_file, $json);
}

// Új kód hozzáadása
if (isset($_POST['add_code'])) {
    if (isset($_POST['type']) && ($_POST['type'] === 'card' || $_POST['type'] === 'quiz')) {
        $type = $_POST['type'];
        $codes = loadCodes();
        $code = $_POST['code'];

        if ($type === 'card') {
            // Kártya hozzáadása
            $name = $_POST['name'];
            $time = $_POST['time'];
            $madeof = $_POST['madeof'];
            $description = $_POST['description'];

            // Feltöltött fájlok feldolgozása
            $photos = array();
            $uploadDir = 'uploads/'; // Állítsa be a megfelelő könyvtár elérési útvonalát
            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['photos']['name'][$key];
                $file_tmp = $_FILES['photos']['tmp_name'][$key];
                $file_type = $_FILES['photos']['type'][$key];
                $file_size = $_FILES['photos']['size'][$key];
                $file_error = $_FILES['photos']['error'][$key];

                // Ellenőrizzük, hogy a fájl valóban képfájl-e
                $allowed_extensions = array("image/jpeg", "image/png", "image/gif");
                if (in_array($file_type, $allowed_extensions)) {
                    // Biztonságosabb fájlnév létrehozása
                    $file_destination = $uploadDir . uniqid('', true) . '_' . $file_name;
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $photos[] = $file_destination;
                    } else {
                        echo "Hiba a kép feltöltésekor: " . $_FILES['photos']['error'][$key];
                    }
                } else {
                    echo "Nem megfelelő fájltípus: " . $file_type;
                }
            }

            // Az adatok összeállítása
            $data = array(
                "name" => $name,
                "time" => $time,
                "madeof" => $madeof,
                "description" => $description,
                "photos" => $photos
            );
        } elseif ($type === 'quiz') {
            // Quiz hozzáadása
            $questions = [];
            for ($i = 1; $i <= 6; $i++) {
                $question = $_POST["question$i"];
                $answers = [];
                for ($j = 1; $j <= 4; $j++) {
                    $answers[] = $_POST["answer{$i}_{$j}"];
                }
                $correct_answer = $_POST["correct_answer$i"];
                $questions[] = [
                    "question" => $question,
                    "answers" => $answers,
                    "correct_answer" => $correct_answer
                ];
            }
            $data = [
                "questions" => $questions
            ];
        }

        // Az új adatok hozzáadása a kódokhoz és mentése
        $codes[$code] = $data;
        saveCodes($codes);
    }
    header('Location: admina.php');
    exit;
}

// Kód törlése
if (isset($_GET['delete'])) {
    $codes = loadCodes();
    $code = $_GET['delete'];
    unset($codes[$code]);
    saveCodes($codes);
    header('Location: admina.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admina Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h1>admina Panel</h1>
        <hr>
        <h2>Új kód hozzáadása</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="type" class="form-label">Elem típusa</label>
                <select class="form-control" id="type" name="type" required>
                    <option value="card">Kártya</option>
                    <option value="quiz">Quiz</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="code" class="form-label">Kód</label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            <div class="mb-3 card-section">
                <label for="name" class="form-label">Név</label>
                <input type="text" class="form-control" id="name" name="name">
            </div>
            <div class="mb-3 card-section">
                <label for="time" class="form-label">Idő</label>
                <input type="text" class="form-control" id="time" name="time">
            </div>
            <div class="mb-3 card-section">
                <label for="madeof" class="form-label">Alapanyag</label>
                <input type="text" class="form-control" id="madeof" name="madeof">
            </div>
            <div class="mb-3 card-section">
                <label for="description" class="form-label">Leírás</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3 card-section">
                <label for="photos" class="form-label">Fotók feltöltése</label>
                <input type="file" class="form-control" id="photos" name="photos[]" multiple>
            </div>
            <div class="mb-3 quiz-section" style="display: none;">
                <?php for ($i = 1; $i <= 6; $i++) : ?>
                    <div class="mb-3">
                        <label for="question<?= $i ?>" class="form-label">Kérdés <?= $i ?></label>
                        <input type="text" class="form-control" id="question<?= $i ?>" name="question<?= $i ?>">
                        <?php for ($j = 1; $j <= 4; $j++) : ?>
                            <label for="answer<?= $i ?>_<?= $j ?>" class="form-label">Válasz <?= $j ?></label>
                            <input type="text" class="form-control" id="answer<?= $i ?>_<?= $j ?>" name="answer<?= $i ?>_<?= $j ?>">
                        <?php endfor; ?>
                        <label for="correct_answer<?= $i ?>" class="form-label">Helyes válasz</label>
                        <input type="text" class="form-control" id="correct_answer<?= $i ?>" name="correct_answer<?= $i ?>">
                    </div>
                <?php endfor; ?>
            </div>
            <button type="submit" class="btn btn-primary" name="add_code">Hozzáadás</button>
        </form>
        <hr>
        <h2>Kódok megtekintése vagy törlése</h2>
        <ul class="list-group">
            <?php foreach (loadCodes() as $code => $code_data) : ?>
                <li class="list-group-item">
                    <strong><?= $code ?></strong>
                    <div><pre><?php print_r($code_data); ?></pre></div>
                    <a href="?delete=<?= $code ?>" class="btn btn-danger btn-sm float-end">Törlés</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <script>
        document.getElementById('type').addEventListener('change', function () {
            var type = this.value;
            if (type === 'card') {
                document.querySelectorAll('.card-section').forEach(function (element) {
                    element.style.display = 'block';
                });
                document.querySelector('.quiz-section').style.display = 'none';
            } else if (type === 'quiz') {
                document.querySelectorAll('.card-section').forEach(function (element) {
                    element.style.display = 'none';
                });
                document.querySelector('.quiz-section').style.display = 'block';
            }
        });
    </script>
</body>

</html>
