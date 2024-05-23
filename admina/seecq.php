 <!-- List of codes -->
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
}?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admina Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
</head>
 <h2>admina Panel: Hozzáadott kódok megtekintése</h2>
<table class="table">
    <thead>
        <tr>
            <th scope="col">-</th>
            <th scope="col">kód</th>
            <th scope="col">típus</th>
            <th scope="col">név</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Fetch all codes from the type table
        $sql = "SELECT * FROM type";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<th scope='row'></th>";
                echo "<td>" . $row["code"] . "</td>";
                echo "<td>" . $row["type"] . "</td>";
                if ($row["type"] == "quiz") {
                    echo "<td>quiz</td>";
                } elseif ($row["type"] == "card") {
                    // Fetch the name from the card table
                    $cardSql = "SELECT name FROM card WHERE code = '" . $row["code"] . "'";
                    $cardResult = $conn->query($cardSql);
                    if ($cardResult->num_rows > 0) {
                        $cardRow = $cardResult->fetch_assoc();
                        echo "<td>" . $cardRow["name"] . "</td>";
                    } else {
                        echo "<td>Nem található kód'</td>";
                    }
                } else {
                    echo "<td>" . $row["name"] . "</td>";
                }
                
            }
        } else {
            echo "<tr><td colspan='4'>Nem található kód</td></tr>";
        }
        ?>
    </tbody>
</table>


