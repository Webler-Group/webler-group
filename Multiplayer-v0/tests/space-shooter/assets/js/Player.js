class Bullet {
    constructor(x, y, angle, color) {
        this.x = x;
        this.y = y;
        this.speed = 500;
        this.angle = angle;
        this.color = color;
        this.radius = 5;
    }

    update(dt) {
        this.x += Math.cos(this.angle) * this.speed * dt;
        this.y += Math.sin(this.angle) * this.speed * dt;
    }

    draw(ctx) {
        ctx.beginPath();
        ctx.fillStyle = this.color;
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
        ctx.fill();
    }
}

class Player {
    constructor(x, y, width, height, canvasWidth, canvasHeight) {
        this.x = x;
        this.y = y;
        this.width = width;
        this.height = height;
        this.speed = 200;
        this.canvasWidth = canvasWidth;
        this.canvasHeight = canvasHeight;
        this.color = `hsl(${Math.random() * 360}, 70%, 60%)`;
        this.username = '';
        this.health = 100;
        this.bullets = [];
        this.lastShot = 0;
    }

    draw(ctx) {
        ctx.fillStyle = this.color;
        ctx.fillRect(this.x, this.y, this.width, this.height);
        
        // Draw username
        ctx.fillStyle = 'white';
        ctx.font = '12px Arial';
        ctx.fillText(this.username + "  " + this.health + "%", this.x, this.y - 10);

        // Draw bullets
        this.bullets.forEach(bullet => bullet.draw(ctx));
    }

    move(dt, keys) {
        if (keys['ArrowLeft'] || keys['a']) this.x -= this.speed * dt;
        if (keys['ArrowRight'] || keys['d']) this.x += this.speed * dt;
        if (keys['ArrowUp'] || keys['w']) this.y -= this.speed * dt;
        if (keys['ArrowDown'] || keys['s']) this.y += this.speed * dt;

        // Constrain to canvas
        this.x = Math.max(0, Math.min(this.x, this.canvasWidth - this.width));
        this.y = Math.max(0, Math.min(this.y, this.canvasHeight - this.height));
    }

    shoot(mouseX, mouseY) {
        const now = Date.now();
        if (now - this.lastShot > 200) {  // 5 shots per second
            const angle = Math.atan2(mouseY - this.y, mouseX - this.x);
            this.bullets.push(new Bullet(
                this.x + this.width/2, 
                this.y + this.height/2, 
                angle, 
                this.color
            ));
            this.lastShot = now;
            return true;
        }
        return false;
    }

    updateBullets(dt, players) {
        this.bullets = this.bullets.filter(bullet => {
            bullet.update(dt);
            
            // Check collision with other players
            for (let peerId in players) {
                const otherPlayer = players[peerId];
                if (otherPlayer !== this && 
                    bullet.x > otherPlayer.x && 
                    bullet.x < otherPlayer.x + otherPlayer.width &&
                    bullet.y > otherPlayer.y && 
                    bullet.y < otherPlayer.y + otherPlayer.height) {
                    otherPlayer.health -= 10;
                    return false;  // Remove bullet on hit
                }
            }

            // Remove bullets out of canvas
            return bullet.x > 0 && 
                   bullet.x < this.canvasWidth && 
                   bullet.y > 0 && 
                   bullet.y < this.canvasHeight;
        });
    }
}
