<div class="container guia-container mt-4">
  <h2 class="guia-titulo text-center mb-4">
    <i class="bi bi-info-circle"></i> Manual de usario para el sistema
  </h2>

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
          <li>Tener <strong>XAMPP</strong> instalado con los servicios <strong>Apache</strong> y <strong>MySQL</strong> activos.</li>
          <li>Contar con un cliente/administrador de base de datos: <em>HeidiSQL</em>, <em>phpMyAdmin</em>, <em>SQLyog</em> o <em>MySQL Workbench</em>.</li>
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
          <li>
            <strong>Lector de código de barras:</strong>
            Recomendaciones: <em>Eyoyo EY-007</em> (aprox. ₡25,000) o <em>Inateck BCST-70</em> (aprox. ₡35,000).
          </li>
          <li>
            <strong>Impresora de código de barras:</strong>
            Se recomienda <em>Xprinter XP-350B</em> por su relación calidad/precio (aprox. ₡65,000 – ₡80,000).<br>
            <strong>Consumible:</strong> Rollo de etiquetas <em>40 × 30 mm</em> (aprox. ₡6,000 – ₡8,000).
          </li>
          <li>
            <strong>Laptop (requerimientos mínimos):</strong>
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
        <details class="acc-item nested">
          <summary class="acc-summary">
            <span><i class="bi bi-play-circle"></i> Videos (instalación y primeros pasos)</span>
            <i class="bi bi-chevron-down acc-arrow"></i>
          </summary>
          <div class="acc-content">

            <!-- 1) -->
            <div class="guia-video mt-2">
              <iframe width="100%" height="200"
                src="https://www.youtube.com"
                title="Cómo descargar e instalar XAMPP" frameborder="0" allowfullscreen></iframe>
            </div>

            <!-- 2) -->
            <div class="guia-video mt-2">
              <iframe width="100%" height="200"
                src="https://www.youtube.com"
                title="Cómo activar servicios Apache/MySQL —" frameborder="0" allowfullscreen></iframe>
            </div>

            <!-- 3) -->
            <div class="guia-video mt-2">
              <iframe width="100%" height="200"
                src="https://www.youtube.com"
                title="Cómo Poner credenciales a la base de datos" frameborder="0" allowfullscreen></iframe>
            </div>

            <!-- 4) -->
            <div class="guia-video mt-2">
              <iframe width="100%" height="200"
                src="https://www.youtube.com"
                title="Cómo importar la base de datos" frameborder="0" allowfullscreen></iframe>
            </div>

            <!-- 5) -->
            <div class="guia-video mt-2">
              <iframe width="100%" height="200"
                src="https://www.youtube.com"
                title="Cómo Entrar a la ruta del proyecto" frameborder="0" allowfullscreen></iframe>
            </div>

            <!-- 6) -->
            <div class="guia-video mt-2">
              <iframe width="100%" height="200"
                src="https://www.youtube.com"
                title="Cómo hacer respaldos de la base de datos" frameborder="0" allowfullscreen></iframe>
            </div>

            <!-- 7) -->
            <div class="guia-video mt-2">
              <iframe width="100%" height="200"
                src="https://www.youtube.com"
                title="Cómo configurar el acceso para entrar con teléfono al sistema" frameborder="0" allowfullscreen></iframe>
            </div>

            <!-- 8) -->
            <div class="guia-video mt-2">
              <iframe width="100%" height="200"
                src="https://www.youtube.com"
                title="Cómo entrar al sistema con el telefono" frameborder="0" allowfullscreen></iframe>
            </div>

          </div>
        </details>
      </div>
    </details>

  </div>
</div>
