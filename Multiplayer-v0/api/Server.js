class Server {
  constructor(appName, password, clientId, socketUrl) {

      try {
          this.client = new Network(appName, password, clientId);
          this.uid = this.client.uid;
          this.connections = new Map();

          this.latency = new RTCLatency(this);


          this.init(socketUrl).catch(this.handleGlobalError);
      } catch (error) {
          this.handleGlobalError(error, 'Failed to initialize Server');
      }

  }


  handleGlobalError = (error, context = '') => {
      console.error(`[Global Error ${context ? `in ${context}` : ''}]`, error);
  }

  async init(socketUrl) {
      try {
          await this.client.connect(socketUrl);

          await this.client.join("signalling_server_0001");

          this.client.onError = (err) => {
            this.onError(err);
          }

          this.client.onJoinRoom = (userId) => {
            this.onJoin(userId);
          }

          this.client.onExitRoom = (userId) => {
            this.onLeave(userId);
          }

          this.client.onUsers = (users) => {
            this.onUsers(users);
          }

          this.client.onWhisper = (from, message) => {
              try {
                  this.handleMessages(from, message);
              } catch (messageHandlerError) {
                  console.error('Error in message handling:', messageHandlerError);
              }
          }
      } catch (err) {
          this.handleGlobalError(err, 'Initialization');
          throw err;
      }
  }

  onMessage(message, from) {};
  onJoin(peerId){}
  onLeave(userId){}
  onError(err){};
  onUsers(users){};


  onDataChannelOpen(peerId){}
  onDataChannelClose(peerId){}

  __onMessageDev(message, from){};

  send(peerId, message) {

    let { dataChannel } = this.connections.get(peerId);
    

    return new Promise(async (res, rej) => {

      if(dataChannel.readyState == 'open') {
        try{
            await dataChannel.send(message);
            res();
        }catch(err) {
          this.handleGlobalError(err, `Error while sending ping-req to peer: ${peerId}`);
          rej(err);
        }
      }

    });

  }


  async setupDataChannel(dataChannel, peerId) {
      dataChannel.onopen = () => {
          console.log("Data channel is open!")
          this.onDataChannelOpen(peerId);
      }

      dataChannel.onmessage = (event) => {
          this.onMessage(event.data, peerId);
          this.__onMessageDev(event.data, peerId);
      }

      dataChannel.onclose = () => {
          console.log(peerId, " data channel closed");
          this.onDataChannelClose(peerId);
      }

  }


  getConnectedUsers() {
    let users = [];

    Array.from(this.connections).forEach(ele => {
      users.push(ele[0])
    })

    return users;
  }


  async __setupPeerConnection(peerConnection, dataChannel, peerId) {
      try {
          peerConnection.onicecandidate = ({
              candidate
          }) => {
              if (candidate) {
                  this.client.whisper(peerId,
                      JSON.stringify({
                          type: 'candidate',
                          candidate,
                          to: peerId,
                          from: this.uid,
                      })
                  )
              }
          }

          peerConnection.oniceconnectionstatechange = async () => {
              if (peerConnection.iceConnectionState === 'connected') {
                  console.log("Peer connected : ", peerId);


              } else if (peerConnection.iceConnectionState === 'disconnected') {
                  console.log("Peer disconnected : ", peerId);
              }
          }

          await this.setupDataChannel(dataChannel, peerId)

          peerConnection.ondatachannel = async ({
              channel
          }) => {
              await this.setupDataChannel(channel, peerId)
          }
      } catch (err) {
          this.handleGlobalError(err, "Setting up peer connection / setting up ice candidates sharing with signalling.");
      }
  }

  connect(peerId) {

    return new Promise(async (res, rej) => {
        try {
            const peerConnection = new RTCPeerConnection();
            const dataChannel = peerConnection.createDataChannel("dataChannel");
            this.connections.set(peerId, {
                peerConnection,
                dataChannel
            });

            await this.__setupPeerConnection(peerConnection, dataChannel, peerId)

            const offer = await peerConnection.createOffer()
            await peerConnection.setLocalDescription(offer)

            await this.client.whisper(peerId,
                JSON.stringify({
                    type: 'offer',
                    offer,
                    to: peerId,
                    from: this.id,
                })
            )

            res(dataChannel)

        } catch (err) {
            console.log("Error: failed to connect to ", peerId, " : ", err);
            rej(err);
        }

    });

  }

  async connectAll() {
      try {
          const connectPromises = Array.from(this.client.connectedUsers).map(
              (peerId) => this.connect(peerId)
          )
          await Promise.all(connectPromises)
      } catch (err) {
          console.log("error connecting to all peers: ", err)
      }
  }


  async broadcast(message) {
      const sendPromises = Array.from(this.connections.values()).map(
          ({
              dataChannel
          }) => {
              if (dataChannel.readyState === 'open') {
                  dataChannel.send(message)
              }
          }
      )
      await Promise.all(sendPromises)
  }

  async handleOfferRequest(peerId, offer) {
      try {
          const peerConnection = new RTCPeerConnection()
          const dataChannel = peerConnection.createDataChannel('dataChannel')

          this.connections.set(peerId, {
              peerConnection,
              dataChannel
          })

          await this.__setupPeerConnection(peerConnection, dataChannel, peerId)
          await peerConnection.setRemoteDescription(
              new RTCSessionDescription(offer)
          )

          const answer = await peerConnection.createAnswer()
          await peerConnection.setLocalDescription(answer)

          this.client.whisper(peerId,
              JSON.stringify({
                  type: 'answer',
                  answer,
                  to: peerId,
                  from: this.id,
              })
          )
      } catch (error) {
          console.log(`Failed to handle connection request from: ${peerId}`, error)
      }
  }


  async handleAnswer(peerId, answer) {
      try {
          const {
              peerConnection
          } = this.connections.get(peerId)
          await peerConnection.setRemoteDescription(
              new RTCSessionDescription(answer)
          )
      } catch (error) {
          console.log(`Failed to handle answer from: ${peerId}`, error)
      }
  }

  async handleCandidate(peerId, candidate) {
      try {
          const {
              peerConnection
          } = this.connections.get(peerId)
          await peerConnection.addIceCandidate(new RTCIceCandidate(candidate))
      } catch (error) {
          console.log(`Failed to handle ICE candidate from: ${peerId}`, error)
      }
  }


  handleMessages(from, message) {
      const data = JSON.parse(message);
      let {
          type,
          to,
          offer,
          answer,
          candidate
      } = data;

      switch (type) {
          case 'offer':
              this.handleOfferRequest(from, offer);
              break;
          case 'answer':
              this.handleAnswer(from, answer);
              break;
          case 'candidate':
              this.handleCandidate(from, candidate)
              break;

      }

  }

  async disconnect(peerId) {

    return new Promise((res, rej) => {
        try {
            const connection = this.connections.get(peerId)
            if (connection) {
                const {
                    peerConnection,
                    dataChannel
                } = connection
                if (dataChannel) {
                    dataChannel.close()
                }
                if (peerConnection) {
                    peerConnection.close()
                }
                this.connections.delete(peerId)

                res(peerId);

                console.log("Disconnected with peer: ", peerId)
            }

            res(-1);
            
        } catch (error) {
            console.log(`Failed to disconnect from peer: ${peerId}`, error)
            rej(error);
        }
    })
  }

  async disconnectAll() {
      const disconnectPromises = Array.from(this.connections.keys()).map(
          (peerId) => this.disconnect(peerId)
      )
      await Promise.all(disconnectPromises)
      if (this.socket.readyState === WebSocket.OPEN) {
          this.socket.close()
      }
  }

}