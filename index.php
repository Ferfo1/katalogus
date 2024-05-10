<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 'Off');
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Méhészeti Katalógus</title>
    <link rel="icon" type="image/x-icon" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .bg-light {
            background-color: #545a60 !important;
        }
        .navbar-light .navbar-nav .nav-link.active{
            color: aliceblue;
        }
        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 10px 0;
        }
        .card-container {
            flex-grow: 1;
            margin-bottom: 20px;
        }
        .d-flex {
            min-height: 100vh;
            flex-direction: column;
        }
        .carousel-item img {
            max-height: auto;
            width: auto;
        }
        .list-group-item input[type="radio"] {
            transform: scale(1.5);
        }
        .list-group-item label {
            font-weight: bold;
            font-size: 18px;
        }
        p {
            font-size: large;
        }
        .card {
            width: auto;
            max-width: 100%;
            margin: auto;
        }

        .card img {
            width: 100%;
            height: auto;
            max-height: 100%;
        }
    </style>
</head>
<body class="d-flex">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="white.png" alt="" width="100" height="100">
            </a>
            <a class="navbar-brand" href="#"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Új kód megadása</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container card-container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8 col-lg-6">
            <?php
            $servername = "192.168.8.179:6033";
            $username = "root";
            $password = "oC.eC]9]oqWo3dY5";
            $dbname = "katalogus";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Sajnos hiba történt. A hibakód: 1" . $conn->connect_error);
            }

            if(isset($_GET['code'])) {
                $code = $_GET['code'];
                $sql = "SELECT * FROM `type` WHERE `code` = $code";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $type = $row['type'];
                    
                    if ($type == 'card') {
                        $sql = "SELECT * FROM `card` WHERE `code` = $code";
                        $result = $conn->query($sql);
                    
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $photos = json_decode($row['photos']);
                            if ($photos !== null) {
                                $carouselInner = '';
                                foreach ($photos as $index => $photo) {
                                    $active = $index === 0 ? 'active' : '';
                                    $carouselInner .= '
                                        <div class="carousel-item ' . $active . '">
                                            <img src="' . $photo . '" class="d-block w-100" alt="...">
                                        </div>';
                                }
                                echo '<div class="card" style="width: 95%;">
                                        <div id="carouselExampleControls" class="carousel slide" data-bs="carousel" data-bs-ride="false">
                                            <div class="carousel-inner">' . $carouselInner . '</div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        </div>
                                    ';
                            } else {
                                echo "Sajnos hiba történt. A hibakód: 2";
                            }
                            
                            echo '
                                <div class="card-body">
                                    <h5 class="card-title">' . $row['name'] . '</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">Készítés Ideje: ' . $row['manufacturing_time'] . '</li>
                                        <li class="list-group-item">Anyag: ' . $row['material'] . '</li>
                                        <li class="list-group-item">Leírás: ' . $row['description'] . '</li>
                                    </ul>
                                </div></div>';
                        } else {
                            echo "Nincs találat vagy hibás szám! Hibakód: 3";
                        }
                    }
                    
                    elseif ($type == 'quiz') {
                        $sql = "SELECT * FROM `quiz` WHERE `code` = $code ORDER BY `question_number` ASC";
                        $result = $conn->query($sql);
                    
                        if ($result->num_rows > 0) {
                            $total_questions = $result->num_rows;
                            $correct_answers = 0;
                    
                            echo '<form method="post">';
                    
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">' . $row['question'] . '</h5>';
                                $quiz_id = $row['id'];
                                $sql_answers = "SELECT * FROM `answer` WHERE `quiz_id` = $quiz_id";
                                $result_answers = $conn->query($sql_answers);
                                if ($result_answers->num_rows > 0) {
                                    while ($row_answer = $result_answers->fetch_assoc()) {
                                        echo '<div class="form-check">';
                                        $input_id = 'answer_' . $row['id'] . '_' . $row_answer['id'];
                                        echo '<input class="form-check-input" type="radio" name="answer[' . $row['id'] . ']" id="' . $input_id . '" value="' . $row_answer['id'] . '">';
                                        echo '<label class="form-check-label" for="' . $input_id . '">' . $row_answer['text'] . '</label>';
                                        echo '</div>';
                                    }
                                }
                                echo '</div></div>';
                            }
                    
                            echo '<button type="submit" class="btn btn-primary mt-3" name="checkAnswersBtn" id="checkAnswersBtn">Ellenőrzés</button>';
                    
                            echo '</form>';
                    
                            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkAnswersBtn'])) {
                                $correct_answers = 0;
                            
                                foreach ($_POST['answer'] as $quiz_id => $selected_answer_id) {
                                    $sql = "SELECT `correct_id` FROM `quiz` WHERE `id` = $quiz_id";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $correct_answer_id = $row['correct_id'];
                            
                                        if ($selected_answer_id == $correct_answer_id) {
                                            $correct_answers++;
                                            echo '<script>document.getElementById("answer_' . $quiz_id . '_' . $selected_answer_id . '").nextElementSibling.style.color = "green";</script>';
                                        } else {
                                            echo '<script>document.getElementById("answer_' . $quiz_id . '_' . $selected_answer_id . '").nextElementSibling.style.color = "red";</script>';
                                            echo '<script>document.getElementById("answer_' . $quiz_id . '_' . $correct_answer_id . '").nextElementSibling.style.color = "green";</script>';
                                        }
                                    }
                                }
                            
                                echo '<p>Helyes válaszok száma: ' . $correct_answers . '</p>';
                            }

                        } else {
                            echo "Nincs találat vagy hibás szám! Hibakód: 3";
                        }
                    }
                     else {
                        echo "Nincs találat vagy hibás szám! Hibakód: 4";
                    }
                } else {
                    echo "Nincs találat vagy hibás szám! Hibakód: 3";
                }
            } else {
                echo '<form action="index.php" method="get">
                          <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Írd be a kódot" aria-label="Írd be a kódot" aria-describedby="button-addon2" name="code">
                            <button class="btn btn-primary" type="submit" id="button-addon2">Küldés</button>
                          </div>
                      </form>';
            }

            $conn->close();
            ?>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Méhész Múzeum Szarvas</p>
        </div>
    </footer>
    <script>
        const answerRows = document.querySelectorAll('.list-group-item');
        answerRows.forEach(row => {
            row.addEventListener('click', (event) => {
                const clickedElement = event.target;
                if (clickedElement.tagName !== 'INPUT') {
                    const radioInput = row.querySelector('input[type="radio"]');
                    radioInput.checked = true;
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
