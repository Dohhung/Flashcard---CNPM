const gridSize = 10; // 10x10 lưới
const grid = document.getElementById('grid');
const gridArray = Array(gridSize).fill(null).map(() => Array(gridSize).fill(''));

let isSelecting = false;
let selectedCells = [];
let wordsFound = 0;

// Đặt từ lên lưới
const placeWords = () => {
    words.forEach(word => {
        let placed = false;
        while (!placed) {
            const direction = Math.random() > 0.5 ? 'horizontal' : 'vertical';
            const startRow = Math.floor(Math.random() * gridSize);
            const startCol = Math.floor(Math.random() * gridSize);
            if (canPlaceWord(word, startRow, startCol, direction)) {
                placeWord(word, startRow, startCol, direction);
                placed = true;
            }
        }
    });
};

const canPlaceWord = (word, row, col, direction) => {
    if (direction === 'horizontal') {
        if (col + word.length > gridSize) return false;
        for (let i = 0; i < word.length; i++) {
            if (gridArray[row][col + i] !== '' && gridArray[row][col + i] !== word[i]) {
                return false;
            }
        }
    } else {
        if (row + word.length > gridSize) return false;
        for (let i = 0; i < word.length; i++) {
            if (gridArray[row + i][col] !== '' && gridArray[row + i][col] !== word[i]) {
                return false;
            }
        }
    }
    return true;
};

const placeWord = (word, row, col, direction) => {
    for (let i = 0; i < word.length; i++) {
        if (direction === 'horizontal') {
            gridArray[row][col + i] = word[i];
        } else {
            gridArray[row + i][col] = word[i];
        }
    }
};

const fillGrid = () => {
    for (let row = 0; row < gridSize; row++) {
        for (let col = 0; col < gridSize; col++) {
            if (gridArray[row][col] === '') {
                gridArray[row][col] = String.fromCharCode(65 + Math.floor(Math.random() * 26));
            }
        }
    }
};

const renderGrid = () => {
    grid.innerHTML = '';
    gridArray.forEach((row, rowIndex) => {
        row.forEach((letter, colIndex) => {
            const cell = document.createElement('div');
            cell.classList.add('cell');
            cell.textContent = letter;
            cell.dataset.row = rowIndex;
            cell.dataset.col = colIndex;
            cell.addEventListener('mousedown', startSelection);
            cell.addEventListener('mousemove', selectCell);
            cell.addEventListener('mouseup', endSelection);
            grid.appendChild(cell);
        });
    });
};

const startSelection = (e) => {
    isSelecting = true;
    selectedCells = [];
    e.target.classList.add('selected');
    selectedCells.push(e.target);
};

const selectCell = (e) => {
    if (!isSelecting) return;
    const cell = e.target;
    if (!cell.classList.contains('selected')) {
        cell.classList.add('selected');
        selectedCells.push(cell);
    }
};

const endGame = () => {
    alert('Chúc mừng! Bạn đã tìm thấy tất cả các từ. Trò chơi kết thúc!');
};

const endSelection = () => {
    isSelecting = false;
    const selectedWord = selectedCells.map(cell => cell.textContent).join('');
    if (words.includes(selectedWord)) {
        selectedCells.forEach(cell => cell.classList.add('correct'));
        const wordElement = document.getElementById(`word-${selectedWord}`);
        if (!wordElement.classList.contains('found')) {
            wordElement.classList.add('found');
            wordsFound++;
        }
    }
    selectedCells.forEach(cell => cell.classList.remove('selected'));
    selectedCells = [];

    // Kiểm tra nếu tất cả các từ đã được tìm thấy
    if (wordsFound === words.length) {
        endGame();
    }
};

document.addEventListener('mouseup', () => {
    isSelecting = false;
});

document.getElementById('reset').addEventListener('click', () => {
    location.reload();
});

placeWords();
fillGrid();
renderGrid();
