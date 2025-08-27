# CRM con Integración WhatsApp

## Setup Inicial
### Rutas para que funcionen los comandos 
- **Windows: (CMD normal)** `C:\xampp\htdocs\CRM_INT\CRM\bridge-node`

### 1. Instalar Dependencias Node.js (CMD normal)
```bash
# Desde la raíz del proyecto CRM
cd bridge-node
npm install

# Desde la raíz del proyecto CRM aca es donde les va a mandar el link para escanear el qr 
```

## Instalación como Servicio de Windows (Método Definitivo)

### Paso 1: Instalar node-windows
**Requiere PowerShell/CMD como Administrador**
```bash
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

npm install -g node-windows
```

### Paso 2: Navegar al directorio del bridge (PowerShell admin)
```bash
cd C:\xampp\htdocs\CRM_INT\CRM\bridge-node
```

### Paso 3: Instalar node-windows localmente (PowerShell admin)
```bash
npm install node-windows
```

### Paso 4: Ejecutar el instalador del servicio (PowerShell admin)
```bash
node install-service.cjs
```

**✅ El script automáticamente:**
- Instala el servicio "WhatsApp Bridge CRM"
- Lo inicia automáticamente
- Lo configura para arrancar con Windows
- ¡Ya no necesitas mantener CMD abierto!

### Paso 5: Pegar en el navegador la siguiente url -> http://localhost:3001 

## Paso 6: Escanear el código qr en WhatsApp

### Paso 7: Después de escanear, verifica la respuesta
```bash
curl http://localhost:3001/status-json
```

**Respuesta esperada: content:** `{"ready":false,"qr":null}` (luego hacer login de WhatsApp)

## Ventajas del Servicio de Windows

✅ **Funciona independientemente de CMD/PowerShell**  
✅ **Inicia automáticamente con Windows**  
✅ **No se cierra al cerrar la consola**  
✅ **Reinicio automático en caso de crash**  
✅ **Gestión desde Services de Windows**  
✅ **Logs automáticos del sistema**

## 🔄 Gestión del Servicio

### Verificar estado del servicio
```bash
sc query "WhatsApp Bridge CRM"
```

### Parar el servicio
```bash
sc stop "WhatsApp Bridge CRM"
```

### Iniciar el servicio
```bash
sc start "WhatsApp Bridge CRM"
```

### Desinstalar el servicio (si es necesario)
**Crear archivo uninstall-service.cjs:**
```javascript
var Service = require('node-windows').Service;

var svc = new Service({
  name: 'WhatsApp Bridge CRM',
  script: 'C:\\xampp\\htdocs\\CRM_INT\\CRM\\bridge-node\\server.js'
});

svc.on('uninstall', function(){
  console.log('✅ Servicio WhatsApp Bridge desinstalado');
});

svc.uninstall();
```

**Ejecutar:** `node uninstall-service.cjs`

## 🔄 Solución de Problemas

### Si el servicio no inicia:
1. **Verificar permisos de administrador**
2. **Revisar logs del servicio en Event Viewer**
3. **Reinstalar el servicio:**
   ```bash
   node uninstall-service.cjs
   node install-service.cjs
   ```



