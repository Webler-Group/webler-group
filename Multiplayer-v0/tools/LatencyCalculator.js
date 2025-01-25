class RTCLatency {
    constructor(server) {

        this.latencies = [];
        this.server = server;
        this.avgLatencyCallback = () => {};
        this.avgLatencyLimit = 0;
        this.iterations = 5;
        this.ping = -1;
        this.init();
    }

    async init() {
        this.server.__onMessageDev = async (message, fromPeerId) => {
            let { type, from, to, timestamp, delay } = JSON.parse(message);
            if(type === 'ping-req' && to === this.server.uid) {
                await this.server.send(fromPeerId, JSON.stringify({
                    type: 'ping-ack',
                    from: this.server.uid,
                    to: fromPeerId,
                    delay: performance.now() - timestamp,
                    timestamp: performance.now()
                }));
            }

            if(type === 'ping-ack' && to === this.server.uid) {
                let roundTripTime = delay + (performance.now() - timestamp);
                this.latencies.push(roundTripTime);
                if(this.latencies.length >= this.avgLatencyLimit && this.latencies.length > 0) {
                    let averageLatency = 0;
                    let temp = 0;
                    let min = this.latencies[0];
                    let max = this.latencies[0];

                    for(let i = 0; i < this.latencies.length; i++) {
                        temp += this.latencies[i];
                        min = Math.min(min, this.latencies[i]);
                        max = Math.max(max, this.latencies[i]);
                    }

                    this.avgLatencyCallback({ averageLatency: (temp / this.latencies.length), maxLatency: max, minLatency: min });
                    this.ping = (temp / this.latencies.length);
                }
                // console.log(roundTripTime, "ms")
            }
        }
    }

    async calculateLatency(peerA) {
        
        await this.server.send(peerA, JSON.stringify({
            type: 'ping-req',
            from: this.server.uid, 
            to: peerA,
            timestamp: performance.now()
        }));

    }

    async calculateAverageLatency(callback = () => {}) {
        let allPeers = Array.from(this.server.connections)
        this.avgLatencyLimit = allPeers.length * this.iterations;
        this.latencies = [];

        for(let i = 0; i < (allPeers.length * this.iterations); i++) {
            let index = i % allPeers.length;
            let otherPeer = allPeers[index][0]; // [peerId, { peerConnection, dataChannel }]

            await this.calculateLatency(otherPeer);
        }
        this.avgLatencyCallback = callback;
    }

}