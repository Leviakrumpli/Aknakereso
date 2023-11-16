let board = [];
let rows = 8;
let columns = 8;
let minesdb = 0;
let maxakna = (rows*columns)/2;

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

    if(tilesClicked === 0){
        do{
            minesdb = prompt(`Mennyi akna legyen a pályán? (${rows}-${maxakna})`);
        }while(minesdb < rows || minesdb > maxakna)
        minesCount = parseInt(minesdb);
    }
    else if(tilesClicked > 0){
        alert("Csak a játék vége után lehet megváltoztatni az aknák számát!");
    }
}

document.getElementById('myButton').addEventListener('click' ,function() {
    aknavalt();
    startGame();
})


function aknahelyezes() {
    minesLocation = [];
    let minesLeft = minesCount;
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
    if (gameOver || this.classList.contains("tile-clicked")) {
        return;
    }

    let aknachange = 0;
    let tile = this;
    if (flagEnabled) {
        if (tile.innerText == "") {
            tile.innerText = "🚩";
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

ikon.onclick = function() {
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

    if (text.style.display === "none") {
        text.style.display = "block";
        document.getElementById("board").style.marginRight = "20%";
        document.getElementById("flag-button").style.marginLeft = "29%";
        document.getElementById("focim").style.marginLeft = "29%";
    } 
    else {
        text.style.display = "none";
        document.getElementById("board").style.marginRight = "";
        document.getElementById("flag-button").style.marginLeft = "";
        document.getElementById("focim").style.marginLeft = "";
    }
  }