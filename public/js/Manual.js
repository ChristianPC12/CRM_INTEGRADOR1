document.addEventListener("DOMContentLoaded", function () {
  // Efecto de escala optimizado con requestAnimationFrame
  document.querySelectorAll(".guia-card").forEach((card) => {
    let isHovered = false;

    card.addEventListener("mouseenter", () => {
      if (!isHovered) {
        isHovered = true;
        requestAnimationFrame(() => {
          card.style.transform = "scale(1.01)";
        });
      }
    });

    card.addEventListener("mouseleave", () => {
      if (isHovered) {
        isHovered = false;
        requestAnimationFrame(() => {
          card.style.transform = "scale(1)";
        });
      }
    });
  });

  // Redirección optimizada desde los títulos
  document.querySelectorAll(".guia-link-seccion").forEach((enlace) => {
    enlace.style.cursor = "pointer";
    enlace.addEventListener("click", function (e) {
      // Solo prevenimos si hay data-ir-a; si no, dejamos que corra el onclick del span
      const destino = this.getAttribute("data-ir-a");
      if (!destino) return;

      e.preventDefault();
      if (destino.startsWith("#")) {
        const target = document.querySelector(destino);
        if (target) target.scrollIntoView({ behavior: "smooth" });
      } else {
        window.location.href = destino;
      }
    });
  });

  // Intersection Observer para lazy loading de imágenes (thumbnails)
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const thumb = entry.target;
        const imgUrl = thumb.getAttribute("data-thumb");

        if (imgUrl) {
          const img = new Image();
          img.onload = () => {
            thumb.style.backgroundImage = `url(${imgUrl})`;
            thumb.style.backgroundSize = "cover";
            thumb.style.backgroundPosition = "center";
            thumb.style.height = "200px";
            thumb.classList.add("loaded");
          };
          img.src = imgUrl;
        }
        observer.unobserve(thumb);
      }
    });
  });

  // Observar todos los thumbnails
  document.querySelectorAll(".video-thumb").forEach((thumb) => {
    imageObserver.observe(thumb);
  });

  // Reproductor con opción de cerrar (sin cambiar HTML)
  document.querySelectorAll(".video-placeholder").forEach((placeholder) => {
    const videoId = placeholder.getAttribute("data-video-id");
    const thumb = placeholder.querySelector(".video-thumb");

    // Asegura posición relativa para el botón cerrar
    placeholder.style.position = "relative";

    // Al hacer click: mostrar iframe y botón cerrar (si no existe ya)
    placeholder.addEventListener("click", function (e) {
      // Evitar que el click sobre el botón cerrar vuelva a abrir
      if (e.target.closest(".video-close")) return;

      // Si ya hay un iframe, no duplicar
      if (placeholder.querySelector("iframe")) return;

      if (!videoId) return;

      // Crear iframe
      const iframe = document.createElement("iframe");
      iframe.setAttribute("width", "100%");
      iframe.setAttribute("height", "200");
      iframe.setAttribute(
        "src",
        `https://www.youtube-nocookie.com/embed/${videoId}?autoplay=1&rel=0`
      );
      iframe.setAttribute("frameborder", "0");
      iframe.setAttribute("allowfullscreen", "");
      iframe.setAttribute("allow", "autoplay; encrypted-media");
      iframe.setAttribute("loading", "lazy");
      iframe.style.borderRadius = "8px";
      iframe.style.display = "block";

      // Botón cerrar
      const closeBtn = document.createElement("button");
      closeBtn.className = "video-close";
      closeBtn.setAttribute("type", "button");
      closeBtn.setAttribute("aria-label", "Cerrar video");
      // Usa Bootstrap Icons si están cargados; si no, usa "×"
      closeBtn.innerHTML =
        '<i class="bi bi-x-lg" aria-hidden="true"></i>' || "×";

      // Estilos inline para no tocar CSS
      Object.assign(closeBtn.style, {
        position: "absolute",
        top: "6px",
        left: "6px", // esquina superior izquierda
        zIndex: "5",
        border: "none",
        borderRadius: "9999px",
        width: "22px", // tamaño pequeño
        height: "22px", // tamaño pequeño
        display: "grid",
        placeItems: "center",
        background: "rgba(0,0,0,.55)",
        color: "#fff",
        cursor: "pointer",
        lineHeight: "1",
        fontSize: "12px", // ícono pequeño
        padding: "0",
      });

      // Ocultar thumbnail y montar iframe + botón
      if (thumb) thumb.style.display = "none";
      placeholder.appendChild(iframe);
      placeholder.appendChild(closeBtn);

      // Cerrar video y restaurar thumbnail
      closeBtn.addEventListener("click", function (ev) {
        ev.stopPropagation(); // no dispare el open
        // Detener reproducción y limpiar
        try {
          iframe.src = "";
        } catch {}
        iframe.remove();
        closeBtn.remove();
        if (thumb) thumb.style.display = "";
      });
    });
  });

  // Preconectar a dominios de YouTube para mejorar rendimiento
  const preconnectLink1 = document.createElement("link");
  preconnectLink1.rel = "preconnect";
  preconnectLink1.href = "https://www.youtube-nocookie.com";
  document.head.appendChild(preconnectLink1);

  const preconnectLink2 = document.createElement("link");
  preconnectLink2.rel = "preconnect";
  preconnectLink2.href = "https://img.youtube.com";
  document.head.appendChild(preconnectLink2);
});
