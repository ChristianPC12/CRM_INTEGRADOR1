import 'dotenv/config';
import express from 'express';
import cors from 'cors';
import qrcode from 'qrcode';
import fs from 'fs';
import path from 'path';
import pkg from 'whatsapp-web.js';
const { Client, LocalAuth } = pkg;

const app = express();
app.use(cors());
app.use(express.json());

let ready = false;
let qrDataUrl = null;
let lastRawQR = null; // texto QR, por si se requiere exportar
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

const SESSION_PATH = process.env.WHATS_SESSION_PATH || './session';
const client = new Client({
  authStrategy: new LocalAuth({ dataPath: SESSION_PATH }),
  puppeteer: { headless: true, args: ['--no-sandbox','--disable-setuid-sandbox'] }
});

client.on('qr', async (qr) => {
  ready = false;
  lastRawQR = qr;
  qrDataUrl = await qrcode.toDataURL(qr);
  logEvent('qr', 'Nuevo QR generado');
});

client.on('loading_screen', (percent, msg) => logEvent('loading_screen', `${percent}% ${msg}`));
client.on('authenticated', () => logEvent('authenticated'));
client.on('auth_failure', (m) => logEvent('auth_failure', m));
client.on('ready', () => { ready = true; qrDataUrl = null; logEvent('ready'); });
client.on('change_state', (s) => logEvent('change_state', s));
client.on('disconnected', (r) => { 
  ready = false; 
  logEvent('disconnected', r); 
  // Auto re-init tras breve espera
  setTimeout(() => { 
    logEvent('reinit_attempt');
    client.initialize();
  }, 5000);
});

client.initialize();

// Rutas API
// Raíz muestra mismo contenido que /status
app.get('/', (req, res) => {
  res.send(renderStatusHTML());
});

app.get('/status', (req, res) => {
  res.send(renderStatusHTML());
});

app.get('/status-json', (req, res) => {
  res.json({ ready, qr: ready ? null : qrDataUrl });
});

// Últimos eventos para depuración
app.get('/debug', (req, res) => {
  res.json({ ready, hasQR: !!qrDataUrl, events: eventLog.slice(-30) });
});

// Devuelve texto crudo del QR (opcional para generar con otra app)
app.get('/raw-qr', (req, res) => {
  if (lastRawQR && !ready) return res.type('text/plain').send(lastRawQR);
  res.status(404).send('No QR');
});

// Reinicia el cliente sin borrar sesión
app.post('/restart', async (req, res) => {
  try {
    await client.destroy();
    ready = false; qrDataUrl = null; lastRawQR = null;
    client.initialize();
    res.json({ ok: true, restarted: true });
  } catch (e) { res.status(500).json({ ok: false, error: e.message }); }
});

// Borra sesión y reinicia (para cuando QR no vincula)
app.post('/reset-session', async (req, res) => {
  try {
    await client.destroy();
    ready = false; qrDataUrl = null; lastRawQR = null;

    // Resolver ruta de sesión según configuración
    const resolvedSessionPath = path.isAbsolute(SESSION_PATH)
      ? SESSION_PATH
      : path.join(process.cwd(), SESSION_PATH);

    // Borrar carpeta de sesión completa
    if (fs.existsSync(resolvedSessionPath)) {
      fs.rmSync(resolvedSessionPath, { recursive: true, force: true });
      logEvent('session_deleted', resolvedSessionPath);
    }

    // LocalAuth guarda credenciales en subcarpeta "wwebjs_auth" dentro de dataPath
    const authDir = path.join(resolvedSessionPath, 'wwebjs_auth');
    if (fs.existsSync(authDir)) {
      fs.rmSync(authDir, { recursive: true, force: true });
      logEvent('auth_deleted', authDir);
    }

    client.initialize();
    res.json({ ok: true, reset: true });
  } catch (e) { res.status(500).json({ ok: false, error: e.message }); }
});

app.post('/send', async (req, res) => {
  try {
    const { to, message } = req.body || {};
    if (!to || !message) return res.status(400).json({ error: 'to y message son requeridos' });
    if (!ready) return res.status(503).json({ error: 'No listo, escanea el QR en /status' });

    const jid = String(to).includes('@c.us') ? String(to) : `${to}@c.us`;
    const r = await client.sendMessage(jid, String(message));
    res.json({ ok: true, id: r?.id?.id || null });
  } catch (e) {
    res.status(500).json({ error: e.message });
  }
});

const PORT = process.env.PORT || 3002;
app.listen(PORT, () => console.log(`API WhatsApp local: http://localhost:${PORT} (session dir: ${SESSION_PATH})`));

// Manejo global de errores no capturados
process.on('unhandledRejection', (r) => console.error('UnhandledRejection', r));
process.on('uncaughtException', (e) => {
  console.error('UncaughtException', e);
});
