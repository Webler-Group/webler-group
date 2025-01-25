let network = null;
let currentWhisperTarget = null;

const userColors = {};
function getColorForUser(username) {
  if (!userColors[username]) {
    let hash = 0;
    for (let i = 0; i < username.length; i++) {
      hash = username.charCodeAt(i) + ((hash << 5) - hash);
    }
    const hue = hash % 360;
    userColors[username] = `hsl(${hue}, 70%, 60%)`;
  }
  return userColors[username];
}

function showError(message) {
  const errorPanel = document.getElementById('error-panel');
  const errorDiv = document.createElement('div');
  errorDiv.classList.add('error-message');
  
  errorDiv.innerHTML = `
    <span>${message}</span>
    <span class="close-btn">&times;</span>
  `;

  errorDiv.querySelector('.close-btn').addEventListener('click', () => {
    errorPanel.removeChild(errorDiv);
  });

  errorPanel.appendChild(errorDiv);

  setTimeout(() => {
    if (errorPanel.contains(errorDiv)) {
      errorPanel.removeChild(errorDiv);
    }
  }, 5000);
}


function showWhisperModal() {
  const modal = document.getElementById('whisper-modal');
  const userList = document.getElementById('whisper-user-list');
  const connectedUsers = Array.from(network.connectedUsers);

  userList.innerHTML = '';

  connectedUsers.forEach(user => {
    const userButton = document.createElement('button');
    userButton.textContent = user;
    userButton.classList.add('btn');
    userButton.style.width = '100%';
    userButton.style.marginBottom = '10px';
    userButton.addEventListener('click', () => {
      currentWhisperTarget = user;
      modal.style.display = 'none';
      sendWhisper();
    });
    userList.appendChild(userButton);
  });

  modal.style.display = 'flex';
}

function sendWhisper() {
  const message = document.getElementById('message-input').value;
  if (!message) {
    showError('Message cannot be empty');
    return;
  }

  if (!currentWhisperTarget) {
    showWhisperModal();
    return;
  }

  network.whisper(currentWhisperTarget, message);
  appendMessage(`whispered to ${currentWhisperTarget}: ${message}`, 'sent', "You");
  document.getElementById('message-input').value = '';
  currentWhisperTarget = null;
}
function appendMessage(message, type = 'system', from) {
  const messagesContainer = document.getElementById('messages');
  const messageElement = document.createElement('div');
  messageElement.classList.add('message', type);


    const userNameSpan = document.createElement('span');
    userNameSpan.classList.add('user-name');
    userNameSpan.textContent = `${from}: `;
    userNameSpan.style.color = getColorForUser(from);

    const messageTextSpan = document.createElement('span');
    messageTextSpan.textContent = message;

    messageElement.appendChild(userNameSpan);
    messageElement.appendChild(messageTextSpan);
  
  messagesContainer.appendChild(messageElement);
  messagesContainer.scrollTop = messagesContainer.scrollHeight;
}
document.getElementById('connect-btn').addEventListener('click', () => {
  const clientName = document.getElementById('client-name').value;
  if (!clientName) {
    showError('Please enter a name');
    return;
  }

  network = new Network('zebra', 'password', clientName);
  network.connect()
  .then(() => {
    init();
  })
  .catch((err) => {
    showError(err);
  });

  document.getElementById('auth-section').style.display = 'none';
  document.getElementById('room-section').style.display = 'block';
  document.getElementById('connection-status').innerHTML = '<i class="fas fa-check-circle" style="color: green;"></i>';
});

document.getElementById('join-room-btn').addEventListener('click', () => {
  const roomName = document.getElementById('room-name').value;
  if (!roomName) {
    showError('Please enter a room name');
    return;
  }
  network.join(roomName).catch((err) => {
    showError(err);
  });
  document.getElementById('current-room').textContent = "Room : " + roomName;
  
  document.getElementById('exit-room-btn').style.display = 'block';
});

document.getElementById('exit-room-btn').addEventListener('click', () => {
  if (!network) {
    showError('No active connection');
    return;
  }

  network.exit();
  
  document.getElementById('current-room').textContent = 'Join in a room to get started';
  document.getElementById('connected-users').innerHTML = '<p>No users connected</p>';
  document.getElementById('messages').innerHTML = '';
  document.getElementById('exit-room-btn').style.display = 'none';

  showError('You have left the room');
});

document.getElementById('broadcast-btn').addEventListener('click', () => {
  const message = document.getElementById('message-input').value;
  if (!message) return;
  network.broadcast(message).then(() => {
    appendMessage(`${message}`, 'sent', "You");
    document.getElementById('message-input').value = '';
  }).catch((err) => {
    showError(err);
  });
});

document.getElementById('whisper-btn').addEventListener('click', () => {
  const connectedUsers = Array.from(network.connectedUsers);
  if (connectedUsers.length === 0) {
    alert('No users to whisper to');
    return;
  }
  showWhisperModal();
});

document.getElementById('cancel-whisper').addEventListener('click', () => {
  document.getElementById('whisper-modal').style.display = 'none';
});


let init = () => {
  network.onMessage = (msg, from) => {
    appendMessage(`${msg}`, 'received', from);
  };

  network.onJoinRoom = (userId) => {
    appendMessage(`${userId} joined the room.`, "system", "->");
  };

  network.onExitRoom = (userId) => {
    appendMessage(`${userId} left the room.`, "system", "->");
  };

  network.onWhisper = (from, msg) => {
    appendMessage(`Whisper: ${msg}`, 'received', from);
  };

  network.onUsers = (connectedUsers) => {
    console.log("Giggidy")
    const usersDiv = document.getElementById('connected-users');
    usersDiv.innerHTML = connectedUsers.length > 0 
      ? connectedUsers.map((user) => `
          <div class="user-item">
            ${user}
            <button class="btn" onclick="currentWhisperTarget='${user}'; sendWhisper();">
              <i class="fas fa-comment"></i>
            </button>
          </div>
        `).join('') 
      : '<p>No users connected</p>';
  };
}
