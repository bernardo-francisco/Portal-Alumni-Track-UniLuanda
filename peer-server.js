import { PeerServer } from 'peer';
import os from 'os';

const PORT = parseInt(process.env.PEER_PORT || '9000', 10);
const PATH = process.env.PEER_PATH || '/myapp';

function getLocalIP() {
    const nets = os.networkInterfaces();
    for (const name of Object.keys(nets)) {
        for (const net of nets[name]) {
            if (net.family === 'IPv4' && !net.internal) return net.address;
        }
    }
    return 'localhost';
}

PeerServer({
    port: PORT,
    path: PATH,
    allow_discovery: true,
    proxied: true,
    debug: 2,
});

console.log('==============================================');
console.log('      UNILUANDA — PEERSERVER (LOCAL)');
console.log('==============================================');
console.log(`Porta: ${PORT}`);
console.log(`Path:  ${PATH}`);
console.log(`Local: http://127.0.0.1:${PORT}${PATH}`);
console.log(`Rede:  http://${getLocalIP()}:${PORT}${PATH}`);
console.log('==============================================');