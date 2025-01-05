let flippedCards = [];
let matchedPairs = 0;
let totalPairs = 0;

// Khởi tạo trò chơi
document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll(".card-item");
    totalPairs = cards.length / 2;

    // Xáo trộn các thẻ
    shuffleCards(cards);

    // Thêm sự kiện click vào từng thẻ
    cards.forEach(card => {
        card.addEventListener("click", () => flipCard(card));
    });
});

// Hàm lật thẻ
function flipCard(card) {
    // Ngăn không cho lật lại thẻ đã lật hoặc khi có 2 thẻ đang được kiểm tra
    if (card.classList.contains("flipped") || flippedCards.length === 2) return;

    // Lật thẻ
    card.classList.add("flipped");
    flippedCards.push(card);

    // Nếu đã chọn 2 thẻ, kiểm tra xem chúng có khớp không
    if (flippedCards.length === 2) {
        // Đảm bảo cả hai thẻ đều lật lên trước khi kiểm tra
        setTimeout(() => {
            checkMatch();
        }, 1000); // 800ms để thẻ kịp lật lên
    }
}

// Hàm kiểm tra khớp
function checkMatch() {
    const [card1, card2] = flippedCards;

    // So sánh flashcard_id của hai thẻ
    const isMatch = card1.dataset.flashcardId === card2.dataset.flashcardId;

    if (isMatch) {
        // Nếu khớp, ẩn các thẻ và tính điểm
        card1.classList.add("matched");
        card2.classList.add("matched");

        // Xóa sự kiện click để không thể lật lại
        card1.removeEventListener("click", () => flipCard(card1));
        card2.removeEventListener("click", () => flipCard(card2));

        matchedPairs++;

        // Kiểm tra nếu tất cả các cặp đã khớp
        if (matchedPairs === totalPairs) {
            setTimeout(() => {
                alert("Chúc mừng! Bạn đã hoàn thành trò chơi!");
            }, 500);
        }
    } else {
        // Nếu không khớp, úp lại thẻ 
        setTimeout(() => {
            card1.classList.remove("flipped");
            card2.classList.remove("flipped");
        }, 500);
    }

    // Đặt lại danh sách thẻ đã lật
    flippedCards = [];
}

// Hàm xáo trộn các thẻ
function shuffleCards(cards) {
    const board = document.querySelector(".game-board");
    const shuffled = Array.from(cards).sort(() => Math.random() - 0.5);
    shuffled.forEach(card => board.appendChild(card));
}

// Hàm chơi lại
function restartGame() {
    location.reload(); // Tải lại trang
}


