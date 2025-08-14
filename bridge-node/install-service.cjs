var Service = require('node-windows').Service;

// Crear un nuevo objeto de servicio
var svc = new Service({
  name: 'WhatsApp Bridge CRM',
  description: 'Servicio de WhatsApp para el CRM de Bastos',
  script: 'C:\\xampp\\htdocs\\CRM_INT\\CRM\\bridge-node\\server.js',
  nodeOptions: [
    '--harmony',
    '--max_old_space_size=4096'
  ],
  env: {
    name: "NODE_ENV",
    value: "production"
  }
});

// Escuchar el evento 'install' que se dispara cuando el servicio se instala
svc.on('install', function(){
  console.log('✅ Servicio WhatsApp Bridge instalado correctamente');
  console.log('Iniciando servicio...');
  svc.start();
});

// Escuchar el evento 'start' que se dispara cuando el servicio inicia
svc.on('start', function(){
  console.log('✅ Servicio WhatsApp Bridge iniciado');
  console.log('El servicio ahora correrá automáticamente al iniciar Windows');
  console.log('URL: http://localhost:3001');
});

// Instalar el servicio
console.log('Instalando servicio WhatsApp Bridge...');
svc.install();
