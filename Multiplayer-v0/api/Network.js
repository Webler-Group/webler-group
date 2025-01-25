class Network {
    constructor(appName, password, uid = Date.now()) {
        this.appName = appName;
        this.password = password;
        this.socket = null;
        this.uid = uid;
        this.currentRoom = -1;
        this.connectedUsers = new Set();
        this.connectionPromise = null;
    }

    connect(url = "ws://localhost:8080") {
        if (this.connectionPromise) {
            return this.connectionPromise;
        }

        this.connectionPromise = new Promise((resolve, reject) => {
            try {
                this.socket = new WebSocket(`${url}/?token=${this.appName}&password=${this.password}`);

                this.socket.onopen = () => {
                    console.log("Connected to WebSocket.");
                    resolve(this.socket);
                };

                this.socket.onmessage = (e) => {
                    try {
                        const data = JSON.parse(e.data);
                        this.__handleMessage(data);
                    } catch (parseError) {
                        console.error("Error parsing message:", parseError);
                    }
                };

                this.socket.onclose = (e) => {
                    const msg = e.wasClean ? "closed cleanly" : "connection died";
                    console.log(`Connection ${msg}`);
                    
                    if (this.currentRoom !== -1) {
                        this.exit();
                    }
                    
                    this.connectionPromise = null;
                    reject(new Error(`WebSocket closed: ${msg}`));
                };

                this.socket.onerror = (err) => {
                    console.log(err);
                    console.error("WebSocket Error:", err);
                    
                    if (this.currentRoom !== -1) {
                        this.exit();
                    }
                    
                    this.connectionPromise = null;
                    reject(err);
                };
            } catch (setupError) {
                console.error("WebSocket setup error:", setupError);
                this.connectionPromise = null;
                reject(setupError);
            }
        });

        window.addEventListener('beforeunload', () => {
            if (this.currentRoom !== -1) {
                this.exit();
            }
        });

        return this.connectionPromise;
    }

    _send(json) {
        return new Promise((resolve, reject) => {
            const sendMessage = () => {
                if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                    try {
                        this.socket.send(JSON.stringify(json));
                        resolve();
                    } catch (sendError) {
                        console.error("Message send error:", sendError);
                        reject(sendError);
                    }
                } else {
                    this.connect()
                        .then(sendMessage)
                        .catch(reject);
                }
            };

            // If not connected, first connect
            if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
                this.connect()
                    .then(sendMessage)
                    .catch(reject);
            } else {
                sendMessage();
            }
        });
    }

    join(roomId) {
        return new Promise((resolve, reject) => {
            if (this.currentRoom !== -1) {
                return reject(new Error("Already in a room"));
            }

            this.currentRoom = roomId;

            // Send join and room request messages
            Promise.all([
                this._send({
                    type: EVENT_TYPES.JOIN_ROOM,
                    uid: this.uid,
                    roomId
                }),
                this._send({
                    type: EVENT_TYPES.REQ_ROOM, 
                    from: this.uid,
                    to: this.currentRoom
                })
            ])
            .then(() => resolve(roomId))
            .catch(reject);
        });
    }

    exit() {
        return new Promise((resolve, reject) => {
            if (this.currentRoom === -1) {
                return reject(new Error("Not in any room"));
            }

            const roomToExit = this.currentRoom;
            this.currentRoom = -1;
            this.connectedUsers.clear();
            this.onUsers(Array.from(this.connectedUsers));

            this._send({
                type: EVENT_TYPES.EXIT_ROOM,
                uid: this.uid,
                roomId: roomToExit
            })
            .then(() => resolve(roomToExit))
            .catch(reject);
        });
    }

    broadcast(message) {
        return new Promise((resolve, reject) => {
            if (this.currentRoom === -1) {
                return reject(new Error("Not in any room"));
            }

            this._send({
                type: EVENT_TYPES.MESSAGE,
                message: message,
                to: this.currentRoom, 
                from: this.uid
            })
            .then(resolve)
            .catch(reject);
        });
    }

    whisper(uid, message) {
        return new Promise((resolve, reject) => {
            this._send({
                type: EVENT_TYPES.WHISPER,
                message: message,
                to: uid, 
                from: this.uid
            })
            .then(resolve)
            .catch(reject);
        });
    }

    onMessage(message, from){};
    onJoinRoom(userId){};
    onExitRoom(userId){}
    onWhisper(from, message){};
    onError(errorMessage){};
    onUsers(users){}

    __handleMessage(data) {
        const { type, uid, roomId, to, from, message } = data;

        try {
            switch(type) {
                case EVENT_TYPES.JOIN_ROOM:
                    this.__joinUserToRoom(uid, roomId);
                    break;
                case EVENT_TYPES.EXIT_ROOM:
                    this.__exitUserFromRoom(uid, roomId);
                    break; 
                case EVENT_TYPES.MESSAGE:
                    if (to === this.currentRoom && from !== this.uid) {
                        // console.log(`${from}: ${message}`);
                        this.onMessage(message, from);
                    }
                    break;
                case EVENT_TYPES.WHISPER:
                    if (to === this.uid) {
                        // console.log(`Private message: ${from}: ${message}`);
                        this.onWhisper(from, message);
                    }
                    break;
                case EVENT_TYPES.REQ_ROOM:
                    if (to === this.currentRoom && this.currentRoom !== -1 && from !== this.uid) {
                        this._send({
                            type: EVENT_TYPES.ACK_ROOM,
                            from: this.uid,
                            to
                        });
                    }
                    break;
                case EVENT_TYPES.ACK_ROOM:
                    if (from !== this.uid && to === this.currentRoom) {
                        this.connectedUsers.add(from);
                        this.onUsers(Array.from(this.connectedUsers));
                    }
                    break;
                case "error":
                    console.error(message);
                    this.onError(message);
                    break;
                default:
                    console.warn(`Unhandled event type: ${type}`);
            }
        } catch (error) {
            console.error("Error handling message:", error);
        }
    }

    __joinUserToRoom(uid, roomId) {
        if (roomId === this.currentRoom && uid !== this.uid) {
            console.log(`User ${uid} joined the room ${roomId}`);
            this.connectedUsers.add(uid);
            this.onJoinRoom(uid);
            this.onUsers(Array.from(this.connectedUsers));
        }
    }

    __exitUserFromRoom(uid, roomId) {
        if (roomId === this.currentRoom && uid !== this.uid) {
            console.log(`User ${uid} left the room ${roomId}`);
            this.connectedUsers.delete(uid);
            this.onExitRoom(uid);
            this.onUsers(Array.from(this.connectedUsers));

        }
    }
}

const EVENT_TYPES = {
    JOIN_ROOM: 1,
    EXIT_ROOM: 2,
    MESSAGE: 3, 
    WHISPER: 4,
    REQ_ROOM: 5,
    ACK_ROOM: 6
};

