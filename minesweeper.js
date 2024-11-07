const grid = document.getElementById("grid");
const mineCountDisplay = document.getElementById("mine-count");
const timerDisplay = document.getElementById("timer");
const newGameBtn = document.getElementById("new-game-btn");

const GRID_SIZE = 10;
const MINE_COUNT = 10;

let cells = [];
let mineLocations = [];
let gameOver = false;
let timer = 0;
let timerInterval;
let flagCount = 0;
let firstClick = true;

function initializeGame() {
    grid.innerHTML = "";
    cells = [];
    mineLocations = [];
    gameOver = false;
    timer = 0;
    flagCount = 0;
    firstClick = true;
    clearInterval(timerInterval);
    timerDisplay.textContent = `Time: ${timer}`;
    updateMineCount();

    // Create cells
    for (let i = 0; i < GRID_SIZE * GRID_SIZE; i++) {
        const cell = document.createElement("div");
        cell.classList.add("cell");
        cell.dataset.index = i;
        cell.addEventListener("click", handleCellClick);
        cell.addEventListener("contextmenu", handleRightClick);
        grid.appendChild(cell);
        cells.push(cell);
    }
}

function placeMines(excludeIndex) {
    mineLocations = [];
    while (mineLocations.length < MINE_COUNT) {
        let index;
        do {
            index = Math.floor(Math.random() * (GRID_SIZE * GRID_SIZE));
        } while (mineLocations.includes(index) || index === excludeIndex);
        mineLocations.push(index);
    }
}

function startTimer() {
    timerInterval = setInterval(() => {
        timer++;
        timerDisplay.textContent = `Time: ${timer}`;
    }, 1000);
}

function handleCellClick(event) {
    if (gameOver) return;
    const index = parseInt(event.target.dataset.index);

    if (firstClick) {
        firstClick = false;
        placeMines(index);
        startTimer();
    }

    if (mineLocations.includes(index)) {
        gameOver = true;
        revealMines();
        clearInterval(timerInterval);
        alert("Game Over! You hit a mine!");
    } else {
        revealCell(index);
        checkWin();
    }
}

function handleRightClick(event) {
    event.preventDefault();
    if (gameOver || firstClick) return;
    const cell = event.target;
    if (!cell.classList.contains("revealed")) {
        cell.classList.toggle("flagged");
        flagCount += cell.classList.contains("flagged") ? 1 : -1;
        updateMineCount();
    }
}

function updateMineCount() {
    mineCountDisplay.textContent = `Mines: ${MINE_COUNT - flagCount}`;
}

function revealCell(index) {
    const cell = cells[index];
    if (
        cell.classList.contains("revealed") ||
        cell.classList.contains("flagged")
    )
        return;

    cell.classList.add("revealed");
    const mineCount = countAdjacentMines(index);

    if (mineCount === 0) {
        const adjacentCells = getAdjacentCells(index);
        adjacentCells.forEach((adjIndex) => revealCell(adjIndex));
    } else {
        cell.textContent = mineCount;
        cell.classList.add(`adjacent-${mineCount}`);
    }
}

function countAdjacentMines(index) {
    const adjacentCells = getAdjacentCells(index);
    return adjacentCells.filter((adjIndex) => mineLocations.includes(adjIndex))
        .length;
}

function getAdjacentCells(index) {
    const row = Math.floor(index / GRID_SIZE);
    const col = index % GRID_SIZE;
    const adjacentCells = [];

    for (let i = -1; i <= 1; i++) {
        for (let j = -1; j <= 1; j++) {
            const newRow = row + i;
            const newCol = col + j;
            if (
                newRow >= 0 &&
                newRow < GRID_SIZE &&
                newCol >= 0 &&
                newCol < GRID_SIZE
            ) {
                adjacentCells.push(newRow * GRID_SIZE + newCol);
            }
        }
    }

    return adjacentCells;
}

function revealMines() {
    mineLocations.forEach((index) => {
        cells[index].classList.add("revealed", "mine");
    });
}

function checkWin() {
    const revealedCount = cells.filter((cell) =>
        cell.classList.contains("revealed")
    ).length;
    if (revealedCount === GRID_SIZE * GRID_SIZE - MINE_COUNT) {
        gameOver = true;
        clearInterval(timerInterval);
        alert("Congratulations! You won!");
    }
}

newGameBtn.addEventListener("click", initializeGame);

initializeGame();
