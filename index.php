<?php
// error_reporting(E_ERROR | E_PARSE); // Csak az E_ERROR és E_PARSE hibák jelenjenek meg
// ini_set('display_errors', 'Off'); // A hibák ne jelenjenek meg a kimeneten
?><!DOCTYPE html>
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
        width: 100%; /* A kártya teljes szélességű legyen */
        max-width: 500px; /* Maximális szélesség beállítása */
        margin: auto; /* Középre igazítás */
        }
        .card img {
            max-width: 100%; /* A kép maximális szélessége a kártya szélességéhez igazodik */
            height: 40; /* Automatikus magasság beállítása a képarány megtartása érdekében */
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
if(isset($_GET['code'])) {
    $json = file_get_contents('codes.json');
    $data = json_decode($json, true);

    if(array_key_exists($_GET['code'], $data)) {
        $item = $data[$_GET['code']];

        // Ha a kód egy kártyás játék
        if(isset($item['photos'])) {
            $maxWidth = 0;
            $maxHeight = 0;

            if(is_array($item['photos']) && !empty($item['photos'])) {
                function isMobile() {
                    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
                }
                echo '<div class="card" style="';
                if (isMobile()) {
                    echo 'width: 95%;">';
                } else {
                    echo 'width: 95%;">';
                }
                echo '<div id="carouselExampleControls" class="carousel slide" data-bs="carousel" data-bs-ride="false">
                              <div class="carousel-inner">';
                foreach ($item['photos'] as $index => $photo) {
                    $active = $index === 0 ? 'active' : ''; // Az első kép lesz aktív
                    echo '<div class="carousel-item ' . $active . '">
                              <img src="' . $photo . '" class="d-block w-100" alt="...">
                          </div>';
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
                  </div>
                  <div class="card-body">
                      <h5 class="card-title">' . $item['name'] . '</h5>
                      <ul class="list-group list-group-flush">
                                <li class="list-group-item">Készítés Ideje: ' . $item['time'] . '</li>
                                <li class="list-group-item">Anyag: ' . $item['madeof'] . '</li>
                                <li class="list-group-item">Leírás: '. $item['description'] . '</li>
                        </ul>
                  </div>
              </div>';
            } else {
                echo '<div class="card" style="width: 18rem;">
                          <img src="' . $item['photo'] . '" class="card-img-top" alt="...">
                          <div class="card-body">
                              <h5 class="card-title">' . $item['name'] . '</h5>
                              <ul class="list-group list-group-flush">
                                <li class="list-group-item">Készítés Ideje: ' . $item['time'] . '</li>
                                <li class="list-group-item">Anyag: ' . $item['madeof'] . '</li>
                                <li class="list-group-item">Leírás: '. $item['description'] . '</li>
                              </ul>
                          </div>
                      </div>';
            }
            
        }
        // Ha a kód egy quiz
        elseif(isset($item['questions'])) {
            echo '<div class="card">
                      <div class="card-body">
                        <h5 class="card-title">' . $item['name'] . '</h5>
                        <form action="" method="post">';
            foreach($item['questions'] as $index => $question) {
                echo '<p class="card-text">' . ($index+1) . '. ' . $question['question'] . '</p>';
                echo '<ul class="list-group list-group-flush">';
                foreach($question['answers'] as $answer) {
                    $class = '';
                    if(isset($_POST['submit'])) {
                        if($_POST['answer_' . $index] == $answer) {
                            if($_POST['answer_' . $index] == $question['correct_answer']) {
                                $class = 'text-success'; // Ha a válasz helyes, zöld színű lesz
                            } else {
                                $class = 'text-danger'; // Ha a válasz helytelen, piros színű lesz
                            }
                        } elseif ($answer == $question['correct_answer']) {
                            $class = 'text-success'; // Ha a válasz helyes, de nem lett kiválasztva, zöld színű lesz
                        }
                    }
                    echo '<li class="list-group-item">
                            <input type="radio" name="answer_' . $index . '" value="' . $answer . '"> 
                            <label class="' . $class . '">' . $answer . '</label>
                          </li>';
                }
                echo '</ul>';
            }
            echo '<button type="submit" class="btn btn-primary mt-3" name="submit">Ellenőrzés</button>';
            echo '</form>';
            // Ellenőrizzük a válaszokat
            if(isset($_POST['submit'])) {
                $correct_answers = 0;
                foreach($item['questions'] as $index => $question) {
                    $selected_answer = $_POST['answer_' . $index];
                    if($selected_answer == $question['correct_answer']) {
                        $correct_answers++;
                    }
                }
                echo '<p class="mt-3">Helyes válaszok száma: ' . $correct_answers . '/' . count($item['questions']) . '</p>';
            }
            echo '</div>
                  </div>';
        } else {
            echo "A kódhoz nem tartozik információ.";
        }
    } else {
        echo "Nincs ilyen kód!";
    }
} else {
    echo '<form action="index.php" method="get">
              <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Írd be a kódot" aria-label="Írd be a kódot" aria-describedby="button-addon2" name="code">
                <button class="btn btn-primary" type="submit" id="button-addon2">Küldés</button>
              </div>
          </form>';
}
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
<script>
                        document.querySelector(".card").style.height = "' . $maxHeight . 'px";
                      </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
