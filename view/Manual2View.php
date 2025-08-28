<div class="container guia-container mt-4">
  <h2 class="guia-titulo text-center mb-4">
    <i class="bi bi-info-circle"></i> Manual técnico para el sistema
  </h2>

  <!-- Botón fijo arriba-izquierda: Reparar MySQL -->
  <button type="button" id="btnRepararMySQL" class="repair-download-btn" title="Descargar RepararMySqlV2.bat">
    <i class="bi bi-tools"></i>
    <span class="repair-download-text">Reparar MySQL</span>
  </button>

  <!-- Botón fijo dentro del contenedor -->
  <button type="button" id="btnDescargarManual" class="manual-download-btn" title="Descargar Manual (PDF)">
    <i class="bi bi-file-earmark-arrow-down"></i>
    <span class="manual-download-text">Manual (PDF)</span>
  </button>

  <!-- Acordeones del manual -->
  <div class="guia-accordion">

    <!-- 1) Requerimientos (cerrado por defecto) -->
    <details class="acc-item">
      <summary class="acc-summary">
        <span><i class="bi bi-gear"></i> Requerimientos para el levantamiento y uso del sistema</span>
        <i class="bi bi-chevron-down acc-arrow"></i>
      </summary>

      <div class="acc-content">
        <ul class="mb-3">
          <li>Tener <strong>XAMPP</strong> instalado con los servicios <strong>Apache</strong> y <strong>MySQL</strong>
            activos.</li>
          <li>Contar con un cliente/administrador de base de datos: <em>HeidiSQL</em>, <em>phpMyAdmin</em>,
            <em>SQLyog</em> o <em>MySQL Workbench</em>.
          </li>
          <li>Tener la base de datos del sistema debidamente importada en el motor de MySQL.</li>
          <li>Una computadora con navegador web actualizado (Windows/macOS/Linux) para acceder al sistema.</li>
        </ul>
      </div>
    </details>

    <!-- 2) Requerimientos de hardware (nuevo, cerrado por defecto) -->
    <details class="acc-item">
      <summary class="acc-summary">
        <span><i class="bi bi-cpu"></i> Requerimientos de hardware</span>
        <i class="bi bi-chevron-down acc-arrow"></i>
      </summary>

      <div class="acc-content">
        <ul class="mb-3">
          <li><strong>Lector de código de barras:</strong> Recomendaciones: <em>Eyoyo EY-007</em> (aprox. ₡25,000) o
            <em>Inateck BCST-70</em> (aprox. ₡35,000).
          </li>
          <li><strong>Impresora de código de barras:</strong> Se recomienda <em>Xprinter XP-350B</em> por su relación
            calidad/precio (aprox. ₡65,000 – ₡80,000).<br><strong>Consumible:</strong> Rollo de etiquetas <em>40 × 30
              mm</em> (aprox. ₡6,000 – ₡8,000).</li>
          <li><strong>Laptop (requerimientos mínimos):</strong>
            <ul>
              <li><strong>Procesador:</strong> Intel® Core™ i5-1145G7</li>
              <li><strong>Frecuencia base / turbo:</strong> 2.60 GHz / hasta ~4.40 GHz</li>
              <li><strong>Núcleos / hilos:</strong> 4 / 8</li>
              <li><strong>Gráficos integrados:</strong> Intel Iris Xe Graphics</li>
              <li><strong>Memoria RAM:</strong> 16 GB (recomendado 24 GB)</li>
              <li><strong>Almacenamiento:</strong> 256 GB SSD</li>
            </ul>
          </li>
        </ul>
        <p class="nota">Precios referenciales en colones costarricenses (₡). Ajustar según proveedor/disponibilidad.</p>
      </div>
    </details>

    <!-- 3) Puesta en funcionamiento (cerrado por defecto) -->
    <details class="acc-item">
      <summary class="acc-summary">
        <span><i class="bi bi-list-check"></i> Puesta en funcionamiento del sistema</span>
        <i class="bi bi-chevron-down acc-arrow"></i>
      </summary>

      <div class="acc-content">
        <ol class="pasos-list">
          <li>Verificar conexión a internet.</li>
          <li>Abrir el navegador de preferencia.</li>
          <li>Descargar <strong>XAMPP</strong>: https://www.apachefriends.org/download.html</li>
          <li>Click en la primera versión <strong>Esperar a que se descargue</strong></li>
          <li>Buscar en descargas</li>
          <li>Instalar XAMPP y activar <strong>Apache</strong> y <strong>MySQL</strong></li>
          <li>Descargar <strong>HeidiSQL</strong> en: https://www.heidisql.com/download.php</li>
          <li><strong>Guiarse con el video para la descarga de HeidiSQL</strong></li>
          <li>Instalar XAMPP y activar <strong>Apache</strong> y <strong>MySQL</strong></li>
          <li>Descargar la base de datos (.sql): <strong>Ver video de cómo importar la bd</strong></li>
          <li>Importar la base de datos en <em>HeidiSQL</em> o <em>phpMyAdmin</em>.</li>
          <li>Copiar el proyecto a <code>htdocs</code> (carpeta de XAMPP).</li>
          <li>Abrir la ruta del proyecto: <code>http://localhost/CRM_INT/CRM/</code></li>
          <li>Configurar credenciales en <code>config/Database.php</code> para establecer una contraseña.</li>
          <li>Probar inicio de sesión y navegación básica.</li>
        </ol>

        <!-- Videos (ocultos por defecto) -->
        <!-- Dentro de tus detalles de Videos -->
        <details class="acc-item nested">
          <summary class="acc-summary">
            <span><i class="bi bi-play-circle"></i> Videos (instalación y primeros pasos)</span>
            <i class="bi bi-chevron-down acc-arrow"></i>
          </summary>
          <div class="acc-content">

            <!-- Marcadores de videos (sin iframes todavía) -->
            <div class="guia-video mt-2 video-lazy"
              data-src="https://www.youtube.com/embed/M9FpsTmXZkw?si=vw5qkb7OiLeT-eb"></div>
            <div class="guia-video mt-2 video-lazy"
              data-src="https://www.youtube.com/embed/kmNUa1KKs88?si=sTxatI_NB4sYiQc4"></div>
            <div class="guia-video mt-2 video-lazy"
              data-src="https://www.youtube.com/embed/O3msEOcHP2E?si=LllUSLtkQkMtT1-9"></div>
            <div class="guia-video mt-2 video-lazy"
              data-src="https://www.youtube.com/embed/p1uZlYBueX0?si=mmCWc_Ee2C-JMJWs"></div>
            <div class="guia-video mt-2 video-lazy"
              data-src="https://www.youtube.com/embed/jHxqhRftB1U?si=2egVp5ywi8iJb_V8"></div>
            <div class="guia-video mt-2 video-lazy"
              data-src="https://www.youtube.com/embed/v64q8mzD0cA?si=tUNVfxZnkeOO-bU2"></div>
            <div class="guia-video mt-2 video-lazy"
              data-src="https://www.youtube.com/embed/eCUP5z4fyM4?si=nl8yzhK2GUOtBn3C"></div>

          </div>
        </details>

      </div>
    </details>
  </div>
</div>

<style>
  .guia-container {
    position: relative;
  }

  /* Botón Reparar MySQL */
  .repair-download-btn {
    position: absolute;
    top: .5rem;
    left: .5rem;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .7rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    background: #212529;
    color: #fff;
    cursor: pointer;
    z-index: 5;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
  }

  .repair-download-btn:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
  }

  .repair-download-btn i {
    font-size: 1.1rem;
  }

  /* Botón Manual PDF */
  .manual-download-btn {
    position: absolute;
    top: .5rem;
    right: .5rem;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .7rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    background: #ffc107;
    color: #000;
    cursor: pointer;
    z-index: 5;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
  }

  .manual-download-btn:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
  }

  .manual-download-btn i {
    font-size: 1.1rem;
  }

  /* Resto de estilos que ya tenías (videos, etc.) */
  .video-placeholder-generic {
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 8px;
    overflow: hidden;
  }

  .video-preview {
    display: flex;
    align-items: center;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid #dee2e6;
    border-radius: 8px;
    min-height: 80px;
    position: relative;
    transition: all 0.3s ease;
  }

  .video-placeholder-generic:hover .video-preview {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-color: #2196f3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
  }

  .video-icon {
    font-size: 2rem;
    color: #6c757d;
    margin-right: 15px;
    transition: color 0.3s ease;
  }

  .video-placeholder-generic:hover .video-icon {
    color: #2196f3;
  }

  .video-info {
    flex: 1;
  }

  .video-info h6 {
    margin: 0 0 5px 0;
    color: #495057;
    font-weight: 600;
  }

  .video-info p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
  }

  .play-button-generic {
    font-size: 2.5rem;
    color: #ff0000;
    transition: all 0.3s ease;
  }

  .video-placeholder-generic:hover .play-button-generic {
    transform: scale(1.1);
    color: #d32f2f;
  }

  .guia-video iframe {
    border-radius: 8px;
  }

  .video-message {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    color: #856404;
  }

  .video-message i {
    font-size: 3rem;
    margin-bottom: 10px;
    display: block;
  }
</style>

<script>
  const PDF_URL = "/CRM_INT/CRM/public/docs/MANUAL_USUARIO_BASTOS_CRM.pdf";
  const PDF_NOMBRE = "Manual_Usuario_CRM_Bastos.pdf";

  const BAT_URL = "/CRM_INT/CRM/RepararMySqlV2.bat";
  const BAT_NAME = "RepararMySqlV2.bat";

  document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("btnDescargarManual")?.addEventListener("click", () => {
      const a = document.createElement("a");
      a.href = PDF_URL;
      a.download = PDF_NOMBRE;
      document.body.appendChild(a);
      a.click();
      a.remove();
    });

    document.getElementById("btnRepararMySQL")?.addEventListener("click", () => {
      const a = document.createElement("a");
      a.href = BAT_URL;
      a.download = BAT_NAME;
      document.body.appendChild(a);
      a.click();
      a.remove();
    });
  });

  document.addEventListener("DOMContentLoaded", () => {
    // Botón manual
    document.getElementById("btnDescargarManual")?.addEventListener("click", () => {
      const a = document.createElement("a");
      a.href = PDF_URL;
      a.download = PDF_NOMBRE;
      document.body.appendChild(a);
      a.click();
      a.remove();
    });

    // Botón reparar MySQL
    document.getElementById("btnRepararMySQL")?.addEventListener("click", () => {
      const a = document.createElement("a");
      a.href = BAT_URL;
      a.download = BAT_NAME;
      document.body.appendChild(a);
      a.click();
      a.remove();
    });

    // Lazy load de videos
    const videosSection = document.querySelector('details.acc-item.nested');
    if (videosSection) {
      videosSection.addEventListener("toggle", () => {
        if (videosSection.open) {
          document.querySelectorAll(".video-lazy").forEach(div => {
            if (!div.querySelector("iframe")) {
              const iframe = document.createElement("iframe");
              iframe.width = "100%";
              iframe.height = "200";
              iframe.src = div.dataset.src;
              iframe.title = "Video tutorial";
              iframe.frameBorder = "0";
              iframe.allowFullscreen = true;
              div.appendChild(iframe);
            }
          });
        }
      }, { once: true }); // se ejecuta solo la primera vez que lo abre
    }
  });

</script>