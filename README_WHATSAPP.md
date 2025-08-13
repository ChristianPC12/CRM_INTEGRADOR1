# 📱 CRM con Integración WhatsApp

## 🚀 Setup Inicial

### 1. Instalar Dependencias Node.js
```bash
# Desde la raíz del proyecto CRM
cd bridge-node
npm install
```

### 2. Configurar PM2
```bash
# Instalar PM2 globalmente (desde cualquier ubicación)
npm install -g pm2

# Desde el directorio bridge-node
cd bridge-node
pm2 start server.js --name "whatsapp-bridge"
pm2 save
```

### 3. Configurar WhatsApp (SOLO UNA VEZ)
1. Abrir: http://localhost:3001
2. Escanear código QR con WhatsApp
3. ¡Listo! La sesión queda guardada

## 📋 Comandos PM2 Útiles
```bash
# Estos comandos funcionan desde cualquier ubicación
pm2 status                    # Ver estado
pm2 logs whatsapp-bridge      # Ver logs
pm2 restart whatsapp-bridge   # Reiniciar
pm2 stop whatsapp-bridge      # Parar
```

## 🔒 Archivos de Sesión
- Los archivos de `bridge-node/session/` contienen tu sesión personal de WhatsApp
- **NO se suben a Git** (están en .gitignore)
- Cada desarrollador debe configurar su propia sesión

## 🎯 Uso del Sistema
1. PM2 debe estar corriendo (`pm2 status`)
2. En el CRM: Módulo Cumpleaños → Botón "Enviar WhatsApp"
3. El mensaje se envía automáticamente

## ⚡ Reinicio de Windows
```bash
# Desde cualquier ubicación
pm2 resurrect  # Restaurar PM2 después de reinicio
```

## 📁 Estructura del Proyecto
```
CRM/
├── bridge-node/          ← Aquí están los comandos de Node.js
│   ├── server.js
│   ├── package.json
│   └── session/          ← Tu sesión de WhatsApp (no se sube a Git)
├── controller/
├── view/
└── ...
```

## 🔧 Troubleshooting

### Si los comandos no funcionan:
```bash
# 1. Verificar que estés en la ruta correcta
pwd                           # Ver ruta actual
cd /ruta/a/tu/CRM            # Ir a la raíz del CRM
cd bridge-node               # Entrar a bridge-node

# 2. Si PM2 no se encuentra
npm list -g pm2              # Verificar si PM2 está instalado
npm install -g pm2           # Reinstalar si es necesario

# 3. Si el bridge no inicia
cd bridge-node               # Asegurar estar en bridge-node
npm install                  # Reinstalar dependencias
pm2 start server.js --name "whatsapp-bridge"
```

### Rutas de ejemplo:
- **Windows:** `C:\xampp\htdocs\CRM_INT\CRM\bridge-node`
- **Linux/Mac:** `/var/www/html/CRM/bridge-node`

**¡Todo configurado para trabajar en equipo!** 👥✨
