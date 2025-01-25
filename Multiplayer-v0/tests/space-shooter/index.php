<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Space Shooter Multiplayer</title>

    <link href="./assets/css/style.css" rel="stylesheet">

    <script src="../../tools/LatencyCalculator.js"></script>
    <script src="../../api/Network.js"></script>
    <script src="../../api/Server.js"></script>

    <script src="./assets/js/Player.js"></script>
</head>
<body>
    <div id="stats">FPS: <span id="fpsDisplay">0</span> | Ping: <span id="pingDisplay">0</span> | Health: <span id="healthDisplay">100</span></div>
    <canvas id="canvas"></canvas>

    <script>

    let inGameName = prompt("Enter display name: ")
    if(inGameName === "") {
        inGameName = Date.now();
    }

    const canvas = document.getElementById('canvas');
    canvas.width = window.innerWidth - 10;
    canvas.height = window.innerHeight - 10;
    const ctx = canvas.getContext('2d');

    const player = new Player(200, 200, 50, 50, canvas.width, canvas.height);
    let players = {};
    let server;
    let keys = {};
    let mouseX = 0, mouseY = 0;

    const fpsDisplay = document.getElementById('fpsDisplay');
    const pingDisplay = document.getElementById('pingDisplay');
    const healthDisplay = document.getElementById('healthDisplay');

    // Handle key presses
    window.addEventListener('keydown', (e) => keys[e.key] = true);
    window.addEventListener('keyup', (e) => keys[e.key] = false);
    window.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });
    canvas.addEventListener('click', (e) => {
        if (player.shoot(e.clientX, e.clientY)) {
            server.broadcast(JSON.stringify({
                type: 'shoot',
                x: player.x + player.width/2,
                y: player.y + player.height/2,
                angle: Math.atan2(e.clientY - player.y, e.clientX - player.x)
            }));
        }
    });

    let dt, prev = performance.now();

    async function initialization() {
        try {

            // Here server can be treated as the current browser itself.
            // Initialize the server with Webler Multiplayer App(app name, app password)
            // from: http://webler.com/Multiplayer-v0/index.php
            server = new Server("zebra", "password", inGameName);
            // Each browser have unique id / uid ------^
            player.username = server.uid;

            // Update fps and ping every 1sec
            setInterval(() => {
                server.latency.calculateAverageLatency();
                const ping = Math.round(server.latency.ping * 10000) / 10000;
                pingDisplay.textContent = ping;
                fpsDisplay.textContent = Math.round(1 / dt);
            }, 1000);

            // New user joined the game
            // Add the new user to playres hashmap
            server.onJoin = async (peerId) => {
                await server.connect(peerId);
                players[peerId] = new Player(-200, -200, 50, 50, canvas.width, canvas.height);
                players[peerId].username = peerId;

                server.broadcast(JSON.stringify({
                    type: 'previous-user', 
                    x: client.x, 
                    y: client.y, 
                    health: client.health
                }))
                
            }

            // User left the game
            // remove the user from players 
            server.onLeave = async (peerId) => {
                delete players[peerId];
                await server.disconnect(peerId);
            }


            // Received message from userId 'from'
            // These messages can transfer other player information like
            // their position, stats, etc..
            server.onMessage = (data, from) => {
                let { type, x, y, angle, health } = JSON.parse(data);
                switch(type) {
                    case 'position-update':
                        if(players[from]) {
                            players[from].x = x;
                            players[from].y = y;
                        }else{
                            // Player that already present before we joining
                            players[from] = new Player(x, y, 50, 50, canvas.width, canvas.height);
                            players[from].username = from;
                        }
                        break;
                    case 'shoot':
                        if(players[from]) {
                            players[from].bullets.push(new Bullet(x, y, angle, players[from].color));
                        }
                        break;
                    case 'previous-user':
                        players[from] = new Player(x, y, 50, 50, canvas.width, canvas.height);
                        players[from].username = from;
                        players[from].health = health;
                        break;
                }
            }
        } catch(err) {
            console.log(err);
        }
    }

    initialization();

    function loop() {
        let now = performance.now();
        dt = (now - prev) / 1000;

        dt = Math.min(0.016, Math.max(dt, 0.02))

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Update player movement and send position
        player.move(dt, keys);

        // BROADCAST the position and health to all other users
        server.broadcast(JSON.stringify({
            type: 'position-update',
            x: player.x,
            y: player.y,
        }));

        // Update and draw bullets
        player.updateBullets(dt, players);
        Object.values(players).forEach(otherPlayer => {
            otherPlayer.updateBullets(dt, Object.assign({[player.username]: player}, players));
        });

        // Update health display
        healthDisplay.textContent = player.health;

        // Draw players
        player.draw(ctx);
        Object.values(players).forEach(player => player.draw(ctx));

        prev = now;
        requestAnimationFrame(loop);
    }

    loop();
    </script>
</body>
</html>