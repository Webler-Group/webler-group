const canvas = document.getElementById('avatarCanvas');
const ctx = canvas.getContext('2d');

drawAvatar(canvas.dataset.avatarUrl);

// Preview the avatar image on canvas
function previewAvatar(event) {
    const file = event.target.files[0];

    // Ensure there is a valid image file
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();

        reader.onload = function (e) {
            drawAvatar(e.target.result);
        };

        reader.readAsDataURL(file);
    }
}

function drawAvatar(url) {
    const img = new Image();
    img.onload = function () {
        const canvasSize = 128;

        canvas.width = canvasSize;
        canvas.height = canvasSize;

        // Draw circular mask on canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.beginPath();
        ctx.arc(canvasSize / 2, canvasSize / 2, canvasSize / 2, 0, Math.PI * 2);
        ctx.clip();

        // Draw image with scaling
        ctx.drawImage(img, 0, 0, canvasSize, canvasSize);
    };
    img.src = url;
}