document.addEventListener("DOMContentLoaded", function () {
  // Cargar miniaturas con el botón de play
  document.querySelectorAll(".video-thumb").forEach((thumb) => {
    const imgUrl = thumb.getAttribute("data-thumb");
    if (imgUrl) {
      thumb.style.backgroundImage = `url(${imgUrl})`;
      thumb.style.backgroundSize = "cover";
      thumb.style.backgroundPosition = "center";
      thumb.style.height = "200px";
      thumb.style.position = "relative";
      thumb.style.cursor = "pointer";
      thumb.style.borderRadius = "8px";
      
      // Asegurar que el botón play esté visible
      const playButton = thumb.querySelector('.play-button');
      if (playButton) {
        playButton.style.position = "absolute";
        playButton.style.top = "50%";
        playButton.style.left = "50%";
        playButton.style.transform = "translate(-50%, -50%)";
        playButton.style.fontSize = "3rem";
        playButton.style.color = "rgba(255, 255, 255, 0.9)";
        playButton.style.textShadow = "0 0 10px rgba(0,0,0,0.7)";
        playButton.style.transition = "all 0.3s ease";
        playButton.style.zIndex = "5";
      }
    }
  });

  // Manejar clics en los video placeholders
  document.querySelectorAll(".video-placeholder").forEach((placeholder) => {
    const videoId = placeholder.getAttribute("data-video-id");
    const thumb = placeholder.querySelector(".video-thumb");
    
    if (!videoId || !thumb) return;

    placeholder.style.position = "relative";
    let videoActivo = false;

    placeholder.addEventListener("click", function (e) {
      e.preventDefault();
      
      if (videoActivo) return; // Prevenir múltiples clics
      videoActivo = true;

      // Crear contenedor del video
      const videoContainer = document.createElement("div");
      videoContainer.className = "video-container-activo";
      videoContainer.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        background: #000;
        border-radius: 8px;
        overflow: hidden;
      `;

      // Crear iframe del video
      const iframe = document.createElement("iframe");
      iframe.style.cssText = `
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 8px;
      `;
      iframe.src = `https://www.youtube-nocookie.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1`;
      iframe.allow = "autoplay; encrypted-media; fullscreen";
      iframe.allowFullscreen = true;

      // Crear botón de cerrar
      const closeBtn = document.createElement("button");
      closeBtn.innerHTML = "×";
      closeBtn.title = "Cerrar video";
      closeBtn.style.cssText = `
        position: absolute;
        top: 8px;
        right: 8px;
        width: 35px;
        height: 35px;
        border: none;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        z-index: 1001;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
      `;

      // Efectos del botón
      closeBtn.addEventListener("mouseenter", () => {
        closeBtn.style.background = "rgba(220, 53, 69, 0.9)";
        closeBtn.style.transform = "scale(1.1)";
      });

      closeBtn.addEventListener("mouseleave", () => {
        closeBtn.style.background = "rgba(0, 0, 0, 0.8)";
        closeBtn.style.transform = "scale(1)";
      });

      // Función para cerrar el video
      const cerrarVideo = () => {
        videoContainer.style.transition = "opacity 0.3s ease";
        videoContainer.style.opacity = "0";
        
        setTimeout(() => {
          if (videoContainer.parentNode) {
            videoContainer.remove();
          }
          videoActivo = false;
        }, 300);
      };

      // Event listener para cerrar
      closeBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        cerrarVideo();
      });

      // Cerrar con Escape
      const handleEscape = (e) => {
        if (e.key === "Escape" && videoActivo) {
          cerrarVideo();
          document.removeEventListener("keydown", handleEscape);
        }
      };
      document.addEventListener("keydown", handleEscape);

      // Ensamblar y mostrar
      videoContainer.appendChild(iframe);
      videoContainer.appendChild(closeBtn);
      placeholder.appendChild(videoContainer);

      // Animación de entrada
      videoContainer.style.opacity = "0";
      setTimeout(() => {
        videoContainer.style.transition = "opacity 0.3s ease";
        videoContainer.style.opacity = "1";
      }, 50);
    });

    // Efecto hover en el thumbnail
    thumb.addEventListener("mouseenter", () => {
      const playButton = thumb.querySelector('.play-button');
      if (playButton) {
        playButton.style.transform = "translate(-50%, -50%) scale(1.1)";
        playButton.style.color = "#fff";
      }
    });

    thumb.addEventListener("mouseleave", () => {
      const playButton = thumb.querySelector('.play-button');
      if (playButton) {
        playButton.style.transform = "translate(-50%, -50%) scale(1)";
        playButton.style.color = "rgba(255, 255, 255, 0.9)";
      }
    });
  });

  // Función global para cerrar todos los videos
  window.cerrarTodosLosVideos = function() {
    document.querySelectorAll(".video-container-activo").forEach(container => {
      const closeBtn = container.querySelector("button");
      if (closeBtn) closeBtn.click();
    });
  };

  console.log("Sistema de videos cargado correctamente ✅");
});