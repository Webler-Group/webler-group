<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat Application</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
  <div class="app-container">
    <div class="sidebar">
      <div id="auth-section">
        <input type="text" id="client-name" placeholder="Enter your name" style="width: 100%; margin-bottom: 10px;">
        <button id="connect-btn" class="btn">
          <i class="fas fa-plug"></i> Connect
        </button>
      </div>

      <div id="error-panel"></div>

      <div id="room-section" style="display: none;">
        <div style="margin-top: 20px;">
          <input type="text" id="room-name" placeholder="Room name">
          <button id="join-room-btn" class="btn" style="margin-top: 10px; width: 100%;">
            <i class="fas fa-door-open"></i> Create or Join Room
          </button>
          <button id="exit-room-btn" class="btn" style="margin-top: 10px; width: 100%; display: none;">
          <i class="fas fa-sign-out-alt"></i> Exit Room
        </button>
        </div>


        <div style="margin-top: 20px;">
          <h3>Connected Users</h3>
          <div id="connected-users" class="user-list"></div>
        </div>
      </div>
    </div>

    <div class="main-chat">
      <div class="chat-header">
        <span id="current-room">Not Connected</span>
        <span id="connection-status">
          <i class="fas fa-times-circle" style="color: red;"></i>
        </span>
      </div>

      <div class="chat-body">
        <div id="messages" class="message-list"></div>
      </div>

      <div class="chat-footer">
        <div class="input-container">
          <textarea id="message-input" placeholder="Type your message..."></textarea>
          <div>
            <button id="broadcast-btn" class="btn">
              <i class="fas fa-bullhorn"></i>
            </button>
            <button id="whisper-btn" class="btn" style="margin-top: 10px;">
              <i class="fas fa-comment-dots"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div id="whisper-modal" class="modal" style="display: none;">
      <div class="modal-content">
        <h3>Select User to Whisper</h3>
        <div id="whisper-user-list"></div>
        <button id="cancel-whisper" class="btn" style="margin-top: 10px;">Cancel</button>
      </div>
    </div>
  </div>

  <script src="../../api/Network.js"></script>
  <script src="./assets/js/script.js"></script>
</body>
</html>