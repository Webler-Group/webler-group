<?php
require_once __DIR__ . '/classes/Controller.php';

// Initialize controller
$controller = new MultiplayerController();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationName = $_POST['application_name'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Attempt to create application
        $createdAppName = $controller->createApplication($applicationName, $password);
        $successMessage = "Application '$createdAppName' created successfully!";
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webler Multiplayer Apps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #121212;
            margin: 0;
            padding: 20px;
            color: #e0e0e0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #1e1e1e;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        h1, h2 { 
            color: #4da6ff; 
            text-align: center; 
        }
        .error {
            background-color: #2c0f0f;
            color: #ff6b6b;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ff6b6b;
        }
        .success {
            background-color: #0f2c0f;
            color: #7dff7d;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #7dff7d;
        }
        form {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }
        input {
            width: 100%;
            padding: 10px;
            background-color: #2c2c2c;
            color: #e0e0e0;
            border: 1px solid #444;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4da6ff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .section {
            background-color: #2a2a2a;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #3a3a3a;
        }
        .code-example {
            margin: 15px 0;
        }
        .warning {
            color: #ff6b6b;
            font-style: italic;
            background-color: #2c0f0f;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ff6b6b;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #4da6ff;
            text-decoration: none;
            margin: 0 10px;
        }
        .links a:hover {
            text-decoration: underline;
        }
        pre code {
            border-radius: 5px;
            background-color: #2c2c2c !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Webler Multiplayer Apps</h1>

        <?php if (isset($errorMessage)): ?>
            <div class="error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <?php if (isset($successMessage)): ?>
            <div class="success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="application_name" placeholder="Application Name" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Create Application">
        </form>

        <div class="section">
            <h2>What are Webler Multiplayer Apps?</h2>
            <p>Webler Multiplayer Apps are designed to simplify real-time, peer-to-peer communication for web applications inside Webler Ecosystem. It provides tools for creating interactive, multiplayer experiences with minimal setup.</p>
        </div>

        <div class="section">
            <h2>Key Features</h2>
            <ul>
                <li><strong>Network Class:</strong> WebSocket-based real-time communication</li>
                <li><strong>Server Class:</strong> Peer-to-peer communication with WebRTC signaling</li>
                <li>Easy integration for games, chat applications, and collaborative tools</li>
            </ul>
        </div>

        <div class="code-example">
            <h2>Network Class Example (WebSockets)</h2>
            <pre><code class="language-html">
&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;title&gt;NetworkJs Sample&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
  &lt;script src="../../api/Network.js"&gt;&lt;/script&gt;
  
  &lt;script&gt;

    let init = async () =&gt; {

        let network = new Network("chat-app", "password");

        await network.connect();

        await network.join("game");

        await network.broadcast("Hi, I'm Paul");

        network.onMessage = (message, from) =&gt; {
            console.log(`${from} : ${message}`);
        };

    };

    init();

  &lt;/script&gt;

&lt;/body&gt;
&lt;/html&gt;
</code></pre>

        </div>

        <div class="code-example">
            <h2>Server Class Example (WebRTC)</h2>
            <pre><code class="language-html">
            <pre><code class="language-html">
&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;Multiplayer Game&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;

    &lt;canvas id="gameCanvas"&gt;&lt;/canvas&gt;

    &lt;script src="../../api/Network.js"&gt;&lt;/script&gt;
    &lt;script src="../../api/Server.js"&gt;&lt;/script&gt;

    &lt;script&gt;
        let canvas = document.getElementById("gameCanvas");
        let ctx = canvas.getContext("2d");
        canvas.width = 500;
        canvas.height = 500;

        // My player
        let client = {
            x: 250,
            y: 250
        };

        // Other players
        let players = {};
        let server;

        // For controlling player with AWSD
        let keys = {};

        let init = async () =&gt; {
            try {
                server = new Server("demo-canvas", "password");

                // When a new user joins the game
                server.onJoin = (userId) =&gt; { 
                    // Connect Peer-to-Peer with newly joined user
                    server.connect(userId);

                    // Create instance for other player at the center of the screen   
                    players[userId] = {
                        x: 250, 
                        y: 250
                    };       
                };

                // When user exits the game
                server.onLeave = (userId) =&gt; {
                    // Delete the user instance 
                    delete players[userId];
                    server.disconnect(userId);
                };

                // Message received from other users
                server.onMessage = (data, from) =&gt; {
                    let { type, x, y } = JSON.parse(data);

                    switch (type) {
                        // Update the location of userId 'from' in client's device
                        case 'position-update':
                            if (players[from]) {
                                players[from].x = x;
                                players[from].y = y;
                            } else { // User already present in server before client joined
                                players[from] = { x, y }; 
                            }
                            break;
                    }
                };

            } catch (err) {
                // Error handling is important!
                console.log(err);
            }
        };

        init();

        // Keyboard event listeners
        window.addEventListener('keydown', (e) =&gt; keys[e.key] = true);
        window.addEventListener('keyup', (e) =&gt; keys[e.key] = false);
        
        // Simple game loop
        function loop() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw other players
            Object.values(players).forEach(player =&gt; {
                ctx.fillStyle = "blue";
                ctx.fillRect(player.x, player.y, 30, 30);
            });

            // Draw our player (client player)
            ctx.fillStyle = "red";
            ctx.fillText("Client(you)", client.x, client.y - 10);
            ctx.fillRect(client.x, client.y, 30, 30);

            // Control player with keyboard event listeners
            if (keys['a']) client.x -= 3;
            if (keys['d']) client.x += 3;
            if (keys['w']) client.y -= 3;
            if (keys['s']) client.y += 3;

            // Update client's location to everyone else
            server.broadcast(JSON.stringify({
                type: 'position-update',
                x: client.x, 
                y: client.y
            }));
            
            requestAnimationFrame(loop);
        }

        loop();
    &lt;/script&gt;
    
&lt;/body&gt;
&lt;/html&gt;
</code></pre>

            </code></pre>
        </div>

        <div class="warning">
            <p>⚠️ Project Status: Under Active Development</p>
            <p>Please note that Webler Multiplayer Apps is currently in development. You may encounter bugs or unexpected behavior.</p>
        </div>

        <div class="links">
            <h2>Demo</h2>
            <p>
                <a href="http://webler.com/Multiplayer-v0/tests/chat-app/index.php" target="_blank">Chat app</a> | 
                <a href="http://webler.com/Multiplayer-v0/tests/space-shooter/index.php" target="_blank">Simplified-space-shooter</a> 
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            hljs.highlightAll();
        });
    </script>
</body>
</html>