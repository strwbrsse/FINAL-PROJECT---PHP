const decorText = ["WELCOME", "BONJOUR", "CIAO", "こんにちは", "안녕하세요", "สวัสดี"];
const h1 = document.getElementById("text");
let currentChar = 0;
let currentTextIndex = 0;
let isDeleting = false;
let delay = 100;
let pauseDuration = 500;
let typeTime;
const nonBreakingSpace = "\u00A0";

function startType() {
    currentChar = 0;
    isDeleting = false;

    typeTime = setInterval(() => {
        const currentText = decorText[currentTextIndex];

        if (!isDeleting) {
            if (currentChar < currentText.length) {
                h1.textContent = currentText.substring(0, currentChar + 1) + nonBreakingSpace;
                currentChar++;
            } else {
                isDeleting = true;
                clearInterval(typeTime);
                setTimeout(() => {
                    typeTime = setInterval(deleteText, delay);
                }, pauseDuration);
            }
        }
    }, delay);
}

function deleteText() {
    const currentText = decorText[currentTextIndex];

    if (currentChar > 0) {
        h1.textContent = currentText.substring(0, currentChar - 1) + nonBreakingSpace;
        currentChar--;
    } else {
        isDeleting = false;
        currentChar = 0;
        currentTextIndex = (currentTextIndex + 1) % decorText.length;
        clearInterval(typeTime);
        setTimeout(() => {
            startType();
        }, pauseDuration);
    }
}

startType();