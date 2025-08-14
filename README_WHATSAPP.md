# 📱 CRM con Integración WhatsApp

## 🚀 Setup Inicial
### Rutas para que funcionen los comandos 
- **Windows:** `C:\xampp\htdocs\CRM_INT\CRM\bridge-node`

### 1. Instalar Dependencias Node.js
```bash
# Desde la raíz del proyecto CRM
cd bridge-node
npm install
```

## 🔧 Instalación PM2 como Servicio de Windows (Recomendado)

### Paso 1: Instalar PM2 y el wrapper de servicio
**Requiere PowerShell/CMD como Administrador**
```bash
npm i -g pm2 pm2-windows-service
```

### Paso 2: Definir la carpeta de estado de PM2
```bash
setx PM2_HOME "C:\pm2"
```
**⚠️ Importante:** Cierra y vuelve a abrir la consola Admin para que tome la variable.

### Paso 3: Instalar el servicio de PM2
```bash
pm2-service-install -n PM2
```

**Respuestas a las preguntas interactivas:**
- `? Perform environment setup (recommended)? (Y/n)` → **Y**
- `? Set PM2_HOME? (Y/n)` → **Y** 
- `? PM2_HOME value (...): (C:\pm2)` → **[Enter]** (acepta el valor por defecto)
- `? Set PM2_SERVICE_SCRIPTS (...)? (Y/n)` → **Y**
- `? Set the list of startup scripts (...) ()` → **[Enter]** (deja vacío)
- `? Set PM2_SERVICE_PM2_DIR (...)? [recommended] (Y/n)` → **Y**
- `PM2_SERVICE_PM2_DIR (...): (C:\USERS\...\pm2\index.js)` → **[Enter]** (acepta el valor por defecto)

Si te pregunta usuario/clave y no quieres usar una cuenta, deja **LocalSystem**.

### Paso 4: Arrancar la aplicación con PM2
```bash
cd C:\xampp\htdocs\CRM_INT\CRM\bridge-node
pm2 start server.js --name whatsapp-bridge
pm2 save
```

### Paso 5: Reiniciar el servicio PM2 (prueba)
```bash
pm2-service-restart
```

### Paso 6: Verificar que todo funciona
```bash
pm2 status
sc query PM2
```

## 🔄 Solución de Problemas

### Si aparece error EPERM //./pipe/rpc.sock:
1. **Reiniciar servicio PM2:**
   ```bash
   pm2-service-restart
   ```

2. **O usar comandos de Windows:**
   ```bash
   net stop PM2
   net start PM2
   ```

3. **Si persiste, limpiar PM2 y reiniciar:**
   ```bash
   pm2 kill
   pm2 start server.js --name whatsapp-bridge
   pm2 save
   ```

## 📋 Comandos de Gestión Diaria

### Ver estado del bridge
```bash
pm2 status
```

### Ver logs en tiempo real
```bash
pm2 logs whatsapp-bridge
```

### Reiniciar el bridge
```bash
pm2 restart whatsapp-bridge
```

### Parar/Iniciar el bridge
```bash
pm2 stop whatsapp-bridge
pm2 start whatsapp-bridge
```

### Verificar que WhatsApp está conectado
```bash
curl http://localhost:3001/status-json
```
**Respuesta esperada:** `{"ready":true,"qr":null}`

## 🎯 Beneficios de PM2 como Servicio

✅ **El proceso sobrevive al cerrar la consola**  
✅ **Reinicio automático en caso de crash**  
✅ **Inicio automático con Windows**  
✅ **Gestión centralizada de logs**  
✅ **Monitoreo de memoria y CPU**

## 🔗 URLs Importantes

- **Estado visual:** http://localhost:3001/status
- **Estado JSON:** http://localhost:3001/status-json  
- **Debug info:** http://localhost:3001/debug
- **QR Raw:** http://localhost:3001/raw-qr

## ⚡ Uso en el CRM

El CRM ya está configurado para usar el bridge automáticamente:
- **Controlador:** `controller/CumpleController.php`
- **Servicio:** `LIB/phpmailer/WhatsAppService.php`
- **Action:** `enviarWhatsCumple`


