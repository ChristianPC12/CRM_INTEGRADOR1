import 'dotenv/config';
import express from 'express';
import cors from 'cors';
import qrcode from 'qrcode';
import qrcodeTerminal from 'qrcode-terminal';
import { makeWASocket, DisconnectReason, useMultiFileAuthState } from '@whiskeysockets/baileys';

const app = express();
app.use(cors());
app.use(express.json());

let sock = null;
let ready = false;
let qrDataUrl = null;
let lastRawQR = null;
const eventLog = [];

function logEvent(type, data) {
  const entry = { ts: new Date().toISOString(), type, data };
  eventLog.push(entry);
  if (eventLog.length > 100) eventLog.shift();
  console.log(`[${entry.ts}] ${type}`, data || '');
}

function renderStatusHTML() {
  if (ready) {
    return '<h1>WhatsApp está listo!</h1>';
  } else if (qrDataUrl) {
    return `<h1>Escanea este QR con WhatsApp</h1><img src="${qrDataUrl}" style="width:300px; height:300px;">`;
  } else {
    return '<h1>⏳ Generando QR...</h1><script>setTimeout(() => location.reload(), 2000);</script>';
  }
}

async function connectToWhatsApp() {
  const { state, saveCreds } = await useMultiFileAuthState('./session');
  
  sock = makeWASocket({
    auth: state,
    printQRInTerminal: false
  });

  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;
    
    if (qr) {
      ready = false;
      lastRawQR = qr;
      qrDataUrl = await qrcode.toDataURL(qr);
      qrcodeTerminal.generate(qr, { small: true });
      logEvent('qr', 'Nuevo QR generado');
    }
    
    if (connection === 'close') {
      const shouldReconnect = (lastDisconnect?.error)?.output?.statusCode !== DisconnectReason.loggedOut;
      logEvent('disconnected', `Connection closed. Reconnecting: ${shouldReconnect}`);
      
      if (shouldReconnect) {
        setTimeout(() => connectToWhatsApp(), 3000);
      }
      ready = false;
    } else if (connection === 'open') {
      ready = true;
      qrDataUrl = null;
      logEvent('ready', 'WhatsApp conectado exitosamente');
    }
  });

  sock.ev.on('creds.update', saveCreds);
  
  sock.ev.on('messages.upsert', (m) => {
    logEvent('message_received', `${m.messages.length} mensajes nuevos`);
  });
}

// Inicializar conexión
connectToWhatsApp();

// Rutas API
app.get('/', (req, res) => {
  res.send(renderStatusHTML());
});

app.get('/status', (req, res) => {
  res.send(renderStatusHTML());
});

app.get('/status-json', (req, res) => {
  res.json({ ready, qr: ready ? null : qrDataUrl });
});

app.get('/debug', (req, res) => {
  res.json({ ready, hasQR: !!qrDataUrl, events: eventLog.slice(-30) });
});

app.get('/raw-qr', (req, res) => {
  if (lastRawQR && !ready) return res.type('text/plain').send(lastRawQR);
  res.status(404).send('No QR');
});

app.post('/restart', async (req, res) => {
  try {
    if (sock) {
      sock.end();
    }
    ready = false;
    qrDataUrl = null;
    lastRawQR = null;
    await connectToWhatsApp();
    res.json({ ok: true, restarted: true });
  } catch (e) {
    res.status(500).json({ ok: false, error: e.message });
  }
});

app.post('/send', async (req, res) => {
  try {
    const { to, message } = req.body || {};
    if (!to || !message) return res.status(400).json({ error: 'to y message son requeridos' });
    if (!ready) return res.status(503).json({ error: 'No listo, escanea el QR en /status' });

    const jid = String(to).includes('@s.whatsapp.net') ? String(to) : `${to}@s.whatsapp.net`;
    const result = await sock.sendMessage(jid, { text: String(message) });
    res.json({ ok: true, id: result?.key?.id || null });
  } catch (e) {
    res.status(500).json({ error: e.message });
  }
});

const PORT = process.env.PORT || 3002;
app.listen(PORT, () => console.log(`API WhatsApp Baileys: http://localhost:${PORT}`));

process.on('unhandledRejection', (r) => console.error('UnhandledRejection', r));
process.on('uncaughtException', (e) => {
  console.error('UncaughtException', e);
});
