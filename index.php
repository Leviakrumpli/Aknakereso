<?php

session_start();

if(isset($_SESSION["user_id"]))
{
    $mysqli = require __DIR__ . "/database.php";

    $sql = "SELECT * FROM user WHERE id = {$_SESSION["user_id"]}";

    $result = $mysqli->query($sql);

    $user = $result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index_css.css">
    <link rel="stylesheet" href="aknastyle.css">
    <link rel="stylesheet" href="aknamedia.css">
    <title>Index</title>
</head>
<body>
    <?php if(isset($user)): ?>
        <h1 id="udv">Bejelentkezve, mint <?= htmlspecialchars($user["name"]) ?></h1>

        <h1 id="focim">Aknakereső</h1>
        <h1 id="h1cim" class="h1cim">Aknák: <span id="mines-count">0</span></h1>
        <div class="zaszlok">
            <h1 id="flagcount"></h1>
        </div>

        <button id="rules" onclick="displayText()">Szabályok, tudnivalók</button>
        <div id="textField" style="display: none;">
            Az aknakereső egy általában egyszemélyes játék,<br> melynek célja a játékmezőn lévő összes akna megtalálása anélkül,<br> hogy felfedné őket a játékos.<br><br>
            A táblára kattintva a játékos találhat:<br>
            -aknát, ami a játék végét jelenti,<br>
            -egy üres szürke mezőt, ami azt jelenti,<br> hogy az adott négyzet körül nincs egy akna sem,<br>
            -illetve egy számot, ami azt mondja meg, hogy az adott<br> négyzet körül mennyi akna található (1-8).<br><br>
            A felhasználó számára elérhető egy zászló gomb segítségként,<br> amit ha használ a játékos akkor könnyebben olvasható lesz a játék,<br> mivel tudni fogja, hogy az alatt szinte biztosan egy akna található.<br>
            Ezt a zászlót bármelyik mezőre le lehet helyezni,<br> szóval nem biztos, hogy mindig jó helyre fog kerülni.
        </div>
        

        <br>
        <div id="board"></div>
        <br>
        <button id="flag-button">🚩</button>
        <button id="myButton">Aknák számának változtatása</button>
        <button id="ujjatek">Új játék</button>
        <a href="https://github.com/Leviakrumpli/Aknakereso"><button id="gitbutton">GitHub</button></a>
        <img src="nap.png" id="ikon" onclick="stilus()">

        <div class="dropdown">
            <button class="dropbtn"><img src="nyil.png" width="30"></button>
            <div class="dropdown-content">
              <a href="#" id="ujjatekkicsi">Új játék</a>
              <a href="#" id="myButtonkicsi">Aknák számának változtatása</a>
              <a href="#" onclick="displayText()">Szabályok, tudnivalók</a>
              <a href="https://github.com/Leviakrumpli/Aknakereso">GitHub</a>
              <a href="#" onclick="stilus()">Stílusváltás</a>
            </div>
          </div>

        <p><a href="logout.php" id="klink">Kijelentkezés</a></p>
    <?php else: ?>
        <h1 id="bvagyr"><a href="login.php" id="link">Bejelentkezés</a> vagy <a href="signup.html" id="link">Regisztráció</a></h1>
    <?php endif; ?>

<style>
    #udv{
        position: absolute;
        left: 1%;
        bottom: 7%;
        font-size: medium;
        text-align: left;
    }

    #klink{
        text-decoration: none;
        color: rgb(248, 197, 197);
        position: absolute;
        left: 1%;
        bottom: 4%;
        font-size: medium;
        color: red;
    }

    #bvagyr{
        position: absolute;
        text-align: center;
        left: 30%;
        right: 30%;
        top: 35%;
        border: 3px solid black;
        border-radius: 10px;
        padding: 20px;
    }

    #textField{
        justify-content: center;
        align-items: center;
        display: flex;
        position: absolute;
        text-align: left;
        font-size: 80%;
        font-weight: normal;
        top: 26.5%;
        color: var(--alapszin);
        border: 2px solid red;
        border-radius: 5px;
        padding: 5px 10px;
        max-width: 35%;
        min-height: 45%;
    }
</style>

<script>
let board = [];
let rows = 8;
let columns = 8;
let minesdb = 0;
let maxakna = Math.round(((rows*columns)/4)+rows);

let minesLocation = []; 

let tilesClicked = 0;
let flagEnabled = false;

let gameOver = false;

let minesCount = 10;

let flagCount = 0;

var ikon = document.getElementById("ikon");

var rulesbutton = document.getElementById("rules");

window.onload = function() {
    startGame();
}

function aknavalt() {
    if (tilesClicked === 0) 
    {
        do 
        {
            minesdb = prompt(`Mennyi akna legyen a pályán? (${rows}-${maxakna})`);
        } while (!minesdb.match(/^\d+$/) || minesdb < rows || minesdb > maxakna);
        minesCount = parseInt(minesdb);
    } 
    else if (tilesClicked > 0) 
    {
        alert("Csak a játék vége után lehet megváltoztatni az aknák számát!");
    }
}

document.getElementById('myButton').addEventListener('click' ,function() {
    aknavalt();
    startGame();
})

document.getElementById('myButtonkicsi').addEventListener('click' ,function() {
    aknavalt();
    startGame();
})


function aknahelyezes() {
    minesLocation = [];
    let minesLeft = minesCount;
    let tile = this;
    while (minesLeft > 0) { 
        let r = Math.floor(Math.random() * rows);
        let c = Math.floor(Math.random() * columns);
        let id = r.toString() + "-" + c.toString();

        if (!minesLocation.includes(id)) {
            minesLocation.push(id);
            minesLeft -= 1;
        }
    }
}

function startGame() {
    document.getElementById("mines-count").innerText = minesCount;
    document.getElementById("flag-button").addEventListener("click", zaszlo);
    aknahelyezes();

    for (let r = 0; r < rows; r++) {
        let row = [];
        for (let c = 0; c < columns; c++) {
            let tile = document.createElement("div");
            tile.id = r.toString() + "-" + c.toString();
            tile.addEventListener("click", helyclick);
            document.getElementById("board").append(tile);
            row.push(tile);
        }
        board.push(row);
    }

    console.log(board);
}

function zaszlo() {
    if (flagEnabled) {
        flagEnabled = false;
        document.getElementById("flag-button").style.backgroundColor = "lightgray";
        document.getElementById("flag-button").style.border = "lightgray";
    }
    else {
        flagEnabled = true;
        document.getElementById("flag-button").style.backgroundColor = "darkgray";
        document.getElementById("flag-button").style.border = "2px red solid";
    }
}

function helyclick() {
    if (gameOver || this.classList.contains("tile-clicked") || (!flagEnabled && this.classList.contains("zaszlos"))) {
        return;
    }

    let aknachange = 0;
    let tile = this;
    if (flagEnabled) {
        if (tile.innerText == "") {
            tile.innerText = "🚩";
            tile.classList.add("zaszlos");
            flagCount++;
            aknachange = minesCount - flagCount;
            if(aknachange > 0){
                document.getElementById("h1cim").textContent = `Aknák: ${aknachange}`;
                document.getElementById("flagcount").textContent = "";
            }
            else if(!gameOver && aknachange === 0){
                document.getElementById("h1cim").textContent = `Aknák: 0`;
                document.getElementById("flagcount").textContent = "Zászlók = Aknák";
            }
            else if(aknachange < 0 && !gameOver){
                document.getElementById("h1cim").textContent = `Aknák: 0`;
                document.getElementById("flagcount").textContent = "Valami biztos nem jó :(";
            }
        }
        else if (tile.innerText == "🚩") {
            tile.innerText = "";
            tile.classList.remove("zaszlos");
            flagCount--;
            aknachange = minesCount - flagCount;
            if(aknachange === 0 && !gameOver){
                document.getElementById("h1cim").textContent = `Aknák: 0`;
                document.getElementById("flagcount").textContent = "Zászlók = Aknák";
            }
            else if(aknachange < 0 && !gameOver){
                document.getElementById("h1cim").textContent = `Aknák: 0`;
                document.getElementById("flagcount").textContent = "Valami biztos nem jó :(";
            }
            else{
                document.getElementById("h1cim").textContent = `Aknák: ${aknachange}`;
                document.getElementById("flagcount").textContent = "";
            }
        }
        return;
    }

    if (minesLocation.includes(tile.id)) {
        gameOver = true;
        aknamutat();
        return;
    }


    let coords = tile.id.split("-"); 
    let r = parseInt(coords[0]);
    let c = parseInt(coords[1]);
    aknae(r, c);

}

function aknamutat() {
    for (let r= 0; r < rows; r++) {
        for (let c = 0; c < columns; c++) {
            let tile = board[r][c];
            if (minesLocation.includes(tile.id)) {
                tile.innerText = "💣";
                tile.style.backgroundColor = "red";                
            }
        }
    }
}

function aknae(r, c) {
    if (r < 0 || r >= rows || c < 0 || c >= columns) {
        return;
    }
    if (board[r][c].classList.contains("tile-clicked")) {
        return;
    }

    board[r][c].classList.add("tile-clicked");
    tilesClicked += 1;

    let minesFound = 0;

    minesFound += aknacheck(r-1, c-1);
    minesFound += aknacheck(r-1, c);
    minesFound += aknacheck(r-1, c+1);

    minesFound += aknacheck(r, c-1);
    minesFound += aknacheck(r, c+1);

    minesFound += aknacheck(r+1, c-1);
    minesFound += aknacheck(r+1, c);
    minesFound += aknacheck(r+1, c+1);

    if (minesFound > 0) {
        board[r][c].innerText = minesFound;
        board[r][c].classList.add("x" + minesFound.toString());
    }
    else {
        board[r][c].innerText = "";
        
        aknae(r-1, c-1);
        aknae(r-1, c);
        aknae(r-1, c+1);

        aknae(r, c-1);
        aknae(r, c+1);

        aknae(r+1, c-1);
        aknae(r+1, c);
        aknae(r+1, c+1);
    }

    if (tilesClicked == rows * columns - minesCount) {
        gameOver = true;
        document.getElementById("flagcount").textContent = "Nagyon szép! :)";
        document.getElementById("h1cim").textContent = `Aknák: 0`;
        
    }
}

function aknacheck(r, c) {
    if (r < 0 || r >= rows || c < 0 || c >= columns) {
        return 0;
    }
    if (minesLocation.includes(r.toString() + "-" + c.toString())) {
        return 1;
    }
    return 0;
}

function ujjatek() {
    for (let r = 0; r < rows; r++) {
        for (let c = 0; c < columns; c++) {
            let tile = board[r][c];
            tile.innerText = "";
            tile.classList.remove("tile-clicked", "x1", "x2", "x3", "x4", "x5", "x6", "x7", "x8");
            tile.style.backgroundColor = "";
            tile.classList.remove("zaszlos");
        }
    }

    aknahelyezes();

    gameOver = false;
    tilesClicked = 0;
    document.getElementById("h1cim").textContent = `Aknák: ${minesCount}`;

    flagEnabled = false;
    document.getElementById("flag-button").style.backgroundColor = "lightgray";
    document.getElementById("flag-button").style.border = "lightgray";

    tilesClicked = 0;

    flagCount = 0;
    document.getElementById("flagcount").textContent = "";
}

document.getElementById('ujjatek').addEventListener('click', function(){
    ujjatek();
})

document.getElementById('ujjatekkicsi').addEventListener('click', function(){
    ujjatek();
})

function stilus() {
    document.body.classList.toggle("vilagos-tema");
    if(document.body.classList.contains("vilagos-tema")){
        ikon.src = "hold.png";
        ikon.style.width = "40px";
    }
    else{
        ikon.src = "nap.png";
        ikon.style.width = "50px";
    }
}

function displayText() {
    var text = document.getElementById("textField");
    var board = document.getElementById("board");
    var flagButton = document.getElementById("flag-button");
    var focim = document.getElementById("focim");

    function handleWindowSize() {
        if (text.style.display === "none") 
        {
            text.style.display = "block";
        } 
        else 
        {
            text.style.display = "none";
        }
    }

    window.addEventListener('resize', handleWindowSize);

    handleWindowSize();
}
</script>

</body>
</html>