var Service = require('node-windows').Service;

var svc = new Service({
  name: 'WhatsApp Bridge CRM',
  script: 'C:\\xampp\\htdocs\\CRM_INT\\CRM\\bridge-node\\server.js'
});

svc.on('uninstall', function(){
  console.log('✅ Servicio WhatsApp Bridge desinstalado completamente');
});

console.log('🗑️ Desinstalando servicio WhatsApp Bridge...');
svc.uninstall();
