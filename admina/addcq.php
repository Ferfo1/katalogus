<?php
error_reporting(E_ERROR | E_PARSE); 
ini_set('display_errors', 'Off'); 
?>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "katalogus";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Nem sikerült kapcsolódni az adatbázishoz: " . $conn->connect_error);
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
            echo "A típus sikeresen hozzá lett adva az adatbázishoz.";
        } else {
            echo "Hiba történt a típus hozzáadása során.";
        }

        $stmt->close();

        if ($type === 'card') {
            $name = $_POST['name'];
            $time = $_POST['time'];
            $madeof = $_POST['madeof'];
            $description = $_POST['description'];
            $photos = array();
            $uploadDir = 'uploads/';

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['photos']['name'][$key];
                $file_tmp = $_FILES['photos']['tmp_name'][$key];
                $file_type = $_FILES['photos']['type'][$key];

                $allowed_extensions = array("image/jpeg", "image/png", "image/gif");
                if (in_array($file_type, $allowed_extensions)) {
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

            $sql = "INSERT INTO card (code, name, manufacturing_time, material, description, photos) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $code, $name, $time, $madeof, $description, json_encode($photos));
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "A kártya sikeresen hozzá lett adva az adatbázishoz.";
            } else {
                echo "Hiba történt a kártya hozzáadása során.";
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
                        echo "An error occurred while adding the quiz.";
                    }
                }
            }
        } else {
            echo "An error occurred while adding the type.";
        }

        $stmt->close();
    }}


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
                <button type="button" onclick="applyStyle('underline')">Alhúzás</button>
<button type="button" onclick="applyStyle('italic')">Dőlt betű</button>
<button type="button" onclick="applyStyle('bold')">Vastag betű</button>
<button type="button" onclick="applyStyle('strikethrough')">Áthúzott betű</button>
<button type="button" onclick="applyStyle('brakeLine')">Új sor</button>
            </div>


<script>
    function applyStyle(style) {
        var description = document.getElementById('description');
        var start = description.selectionStart;
        var end = description.selectionEnd;

        var beforeText = description.value.substring(0, start);
        var selectedText = description.value.substring(start, end);
        var afterText = description.value.substring(end, description.value.length);

        var styledText = '';
        switch (style) {
            case 'underline':
                styledText = '<u>' + selectedText + '</u>';
                break;
            case 'italic':
                styledText = '<i>' + selectedText + '</i>';
                break;
            case 'bold':
                styledText = '<b>' + selectedText + '</b>';
                break;
            case 'strikethrough':
                styledText = '<strike>' + selectedText + '</strike>';
                break;
            case 'brakeLine':
                styledText = selectedText + '<br>';
                break;
        }

        description.value = beforeText + styledText + afterText;
    }
</script>

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
</body>

</html>
