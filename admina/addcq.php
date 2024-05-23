<?php


$servername = "192.168.8.179:6033";
$username = "root";
$password = "passwd";
$database = "katalogus";
$ifcard = false;
$ifcard1 = false;
$ifcard2 = false;
$ifcard3 = false;
$ifcard4 = false;
$ifcard5 = false;

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Sajnos hiba történt. A hibakód: 10" . $conn->connect_error);
}

if (isset($_POST['add_code'])) {
    if (isset($_POST['type']) && ($_POST['type'] === 'card' || $_POST['type'] === 'quiz')) {
        $type = $_POST['type'];
        $code = $_POST['code'];

        $sql = "INSERT INTO type (code, type) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $code, $type);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $ifcard = true;
        } else {
            echo "Sajnos hiba történt. A hibakód: 11";
        }

        $stmt->close();

        if ($type === 'card') {
            $name = $_POST['name'];
            $time = $_POST['time'];
            $madeof = $_POST['madeof'];
            $description = $_POST['description'];
            $formatted_description = str_replace(['<p>'], '', $description);
            $another_formatted_description = str_replace(['</p>'], '<br>', $formatted_description);
            $photos = array();
            $uploadDir = '../uploads/';

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['photos']['name'][$key];
                $file_tmp = $_FILES['photos']['tmp_name'][$key];
                $file_type = $_FILES['photos']['type'][$key];

                $allowed_extensions = array("image/jpeg", "image/png", "image/gif");
                if (in_array($file_type, $allowed_extensions)) {
                    $file_destination = $uploadDir . uniqid('', true) . '_' . $file_name;
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $photos[] = $file_destination;
                        $ifcard1 = true;
                    } else {
                        echo "Hiba a kép feltöltésekor: " . $_FILES['photos']['error'][$key].  "Hibakód: 12 ";
                    }
                } else {
                    echo "Nem megfelelő fájltípus: " . $file_type . "Hibakód: 13 ";
                }
            }

            $sql = "INSERT INTO card (code, name, manufacturing_time, material, description, photos) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $code, $name, $time, $madeof, $another_formatted_description, json_encode($photos));
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $ifcard2 = true;
                if ($ifcard && $ifcard1 && $ifcard2) {
                    echo '<div class="alert alert-success" role="alert">
                    A művelet sikeres: Kártya hozzáadása
                  </div>';
                    echo '<script>window.location.href = "https://katalogus.meheszmuzeum.hu/admina/addcq.php";</script>';

                }
                else {
                    echo "Sajnos hiba történt. A hibakód: 15";
                }
            } else {
                echo "Adatbázis hiba! Hibakód: 14";
            }

            $stmt->close();

        } elseif ($type === 'quiz') {
            for ($i = 1; $i <= 6; $i++) {
                $question = $_POST["question$i"];
                if (!empty($question)) {
                    $sql = "INSERT INTO quiz (question, question_number, code) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sis", $question, $i, $code);
                    $stmt->execute();

                    if ($stmt->affected_rows > 0) {
                        echo '<div class="alert alert-success" role="alert">
                        A művelet sikeres: Kérdés hozzáadása
                      </div>';
                        $question_id = $stmt->insert_id;

                        for ($j = 1; $j <= 4; $j++) {
                            $answer = $_POST["answer{$i}_{$j}"];
                            if (!empty($answer)) {
                                $sql = "INSERT INTO answer (text, quiz_id) VALUES (?, ?)";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("si", $answer, $question_id);
                                $stmt->execute();

                                if ($j == $_POST["correct_answer{$i}"]) {
                                    $correct_id = $conn->insert_id;

                                    $sql = "UPDATE quiz SET correct_id = ? WHERE id = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("ii", $correct_id, $question_id);
                                    $stmt->execute();
                                }
                            }
                        }
                    } else {
                        echo "Adatbázishiba! Hibakód: 14";
                    }
                }
            }
        } else {
            echo "Adatbázishiba! Hibakód: 14";
        }

        $stmt->close();
    }}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="//cdn.ckeditor.com/4.6.2/standard/ckeditor.js"></script>
    <title>admina Panel</title>
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
                <textarea class="form-control" id="CK1" name="description" rows="3"></textarea>
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
                        <label class="form-label">Helyes válasz</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_answer<?= $i ?>" id="correct_answer<?= $i ?>_1" value="1">
                            <label class="form-check-label" for="correct_answer<?= $i ?>_1">1</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_answer<?= $i ?>" id="correct_answer<?= $i ?>_2" value="2">
                            <label class="form-check-label" for="correct_answer<?= $i ?>_2">2</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_answer<?= $i ?>" id="correct_answer<?= $i ?>_3" value="3">
                            <label class="form-check-label" for="correct_answer<?= $i ?>_3">3</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_answer<?= $i ?>" id="correct_answer<?= $i ?>_4" value="4">
                            <label class="form-check-label" for="correct_answer<?= $i ?>_4">4</label>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
            <button type="submit" class="btn btn-primary" name="add_code">Hozzáadás</button>
        </form>
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
    <script type="text/javascript">
      window.onload = () => {
        CKEDITOR.replace("description");
      };

      function sendText() {
        window.parent.postMessage(CKEDITOR.instances.CK1.getData(), "*");
      }
    </script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</body>

</html>
