<?php
error_reporting(E_ERROR | E_PARSE); // Csak az E_ERROR és E_PARSE hibák jelenjenek meg
ini_set('display_errors', 'Off'); // A hibák ne jelenjenek meg a kimeneten
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Méhészeti Katalógus</title>
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
            flex-grow: 1; /* Az oszlop teljes magasságú legyen */
            margin-bottom: 20px; /* Üres hely a kártya és a footer között */
        }
        .d-flex {
            min-height: 100vh; /* A konténer teljes magasságú legyen */
            flex-direction: column; /* Az elemek egy oszlopban legyenek */
        }
        .carousel-item img {
            max-height: auto; /* Maximális magasság beállítása */
            width: auto; /* Automatikus szélesség */
        }
        .list-group-item input[type="radio"] {
            transform: scale(1.5); /* Make radio buttons larger */
        }
        .list-group-item label {
            font-weight: bold; /* Make answer labels bold */
            font-size: 18px; /* Increase font size of answer labels */
        }
        p {
            font-size: large;
        }
        .card {
    width: auto; /* Kártya szélessége automatikus */
    max-width: 100%; /* Maximális szélesség beállítása */
    margin: auto; /* Középre igazítás */

}

.card img {
    width: 100%; /* A kép szélessége a kártya szélességéhez igazodik */
    height: auto; /* Automatikus magasság beállítása a képarány megtartása érdekében */
    max-height: 100%; /* Maximális magasság beállítása, ha a kép magasabb lenne, mint a kártya */
}

    </style>
</head>
<body class="d-flex">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="" width="100" height="100">
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
            // Kapcsolódás az adatbázishoz
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "katalogus";

            $conn = new mysqli($servername, $username, $password, $dbname);

            // Ellenőrizzük a kapcsolatot
            if ($conn->connect_error) {
                die("Sikertelen kapcsolódás az adatbázishoz: " . $conn->connect_error);
            }

            if(isset($_GET['code'])) {
                $code = $_GET['code'];
                // Ellenőrizzük a kódot a type táblában
                $sql = "SELECT * FROM `type` WHERE `code` = $code";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $type = $row['type'];
                    
                    // Betöltjük az adatokat a megfelelő táblából a kód típusa alapján
                    if ($type == 'card') {
                        // Betöltjük a kártya adatait a card táblából
                        $sql = "SELECT * FROM `card` WHERE `code` = $code";
                        $result = $conn->query($sql);
                    
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            // Képek feldolgozása a Carousel-hoz
                            $photos = json_decode($row['photos']); // JSON string átalakítása PHP tömbbé
                            if ($photos !== null) { // Ellenőrzés, hogy a JSON dekódolása sikeres volt-e
                                // Képek megjelenítése a Carousel-ben
                                echo '<div class="card" style="width: 95%;"><div id="carouselExampleControls" class="carousel slide" data-bs="carousel" data-bs-ride="false">
                                        <div class="carousel-inner">';
                                foreach ($photos as $index => $photo) {
                                    $active = $index === 0 ? 'active' : ''; // Az első kép lesz aktív
                                    
                                }
                                echo '</div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>';
                            } else {
                                echo "Hiba történt a képek feldolgozásakor";
                            }
                            // További kártyaadatok megjelenítése
                            echo '
                            <div class="carousel-item ' . $active . '">
                                            <img src="' . $photo . '" class="d-block w-100" alt="...">
                                          </div>
                                    <div class="card-body">
                                    
                                        <h5 class="card-title">' . $row['name'] . '</h5>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">Készítés Ideje: ' . $row['manufacturing_time'] . '</li>
                                            <li class="list-group-item">Anyag: ' . $row['material'] . '</li>
                                            <li class="list-group-item">Leírás: ' . $row['description'] . '</li>
                                        </ul>
                                    </div>
                                </div>';
                        } else {
                            echo "Nincs ilyen kód a card táblában";
                        }
                    }
                    
                    elseif ($type == 'quiz') {
                        // Betöltjük a quiz adatait a quiz táblából
                        $sql = "SELECT * FROM `quiz` WHERE `code` = $code ORDER BY `question_number` ASC";
                        $result = $conn->query($sql);
                    
                        if ($result->num_rows > 0) {
                            $total_questions = $result->num_rows;
                            $correct_answers = 0;
                    
                            echo '<form method="post">'; // Forma megnyitása az összes kérdés és válasz megjelenítése előtt
                    
                            while ($row = $result->fetch_assoc()) {
                                // Quiz adatok feldolgozása és megjelenítése
                                echo '<div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">' . $row['question'] . '</h5>';
                                // Betöltjük a válaszokat a válasz táblából
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
                    
                            echo '</form>'; // Forma lezárása az összes kérdés és válasz megjelenítése után
                    
                            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkAnswersBtn'])) {
                                $correct_answers = 0; // Helyes válaszok számlálója
                            
                                // Ellenőrizzük az összes kérdésre adott választ
                                foreach ($_POST['answer'] as $quiz_id => $selected_answer_id) {
                                    $sql = "SELECT `correct_id` FROM `quiz` WHERE `id` = $quiz_id";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        $row = $result->fetch_assoc();
                                        $correct_answer_id = $row['correct_id'];
                            
                                        // Ha a válasz helyes, növeljük a helyes válaszok számát és beállítjuk a színét zöldre
                                        if ($selected_answer_id == $correct_answer_id) {
                                            $correct_answers++;
                                            echo '<script>document.getElementById("answer_' . $quiz_id . '_' . $selected_answer_id . '").nextElementSibling.style.color = "green";</script>';
                                        } else {
                                            // Ha a válasz helytelen, a válasz színét pirosra állítjuk
                                            echo '<script>document.getElementById("answer_' . $quiz_id . '_' . $selected_answer_id . '").nextElementSibling.style.color = "red";</script>';
                                            // A helyes választ is zöldre állítjuk
                                            echo '<script>document.getElementById("answer_' . $quiz_id . '_' . $correct_answer_id . '").nextElementSibling.style.color = "green";</script>';
                                        }
                                    }
                                }
                            
                                // Helyes válaszok számának megjelenítése
                                echo '<p>Helyes válaszok száma: ' . $correct_answers . '</p>';
                            }

                        } else {
                            echo "Nincs ilyen kód a quiz táblában";
                        }
                    }
                     else {
                        echo "Ismeretlen kód típus: $type";
                    }
                } else {
                    echo "Nincs ilyen kód a type táblában";
                }
            } else {
                // A felhasználó még nem adott meg kódot
                echo '<form action="index.php" method="get">
                          <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Írd be a kódot" aria-label="Írd be a kódot" aria-describedby="button-addon2" name="code">
                            <button class="btn btn-primary" type="submit" id="button-addon2">Küldés</button>
                          </div>
                      </form>';
            }

            // Kapcsolat lezárása
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
        // Válaszok soraira kattintva választási művelet végrehajtása
        const answerRows = document.querySelectorAll('.list-group-item');
        answerRows.forEach(row => {
            row.addEventListener('click', (event) => {
                const clickedElement = event.target;
                // Csak akkor válasszuk ki az inputot, ha az a körvonala volt a kattintásnak
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
