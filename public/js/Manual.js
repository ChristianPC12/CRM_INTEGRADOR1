document.addEventListener("DOMContentLoaded", function () {
    // Efecto de escala al pasar el mouse
    document.querySelectorAll(".guia-card").forEach((card) => {
      card.addEventListener("mouseenter", () => {
        card.style.transform = "scale(1.01)";
      });
      card.addEventListener("mouseleave", () => {
        card.style.transform = "scale(1)";
      });
    });
  
    // Redirección desde los títulos
    document.querySelectorAll(".guia-link-seccion").forEach((enlace) => {
      enlace.style.cursor = "pointer";
      enlace.addEventListener("click", function () {
        const destino = this.getAttribute("data-ir-a");
  
        if (destino) {
          if (destino.startsWith("#")) {
            const target = document.querySelector(destino);
            if (target) {
              target.scrollIntoView({ behavior: "smooth" });
            }
          } else {
            window.location.href = destino;
          }
        }
      });
    });

    // Lazy loading para videos de YouTube
    document.querySelectorAll(".video-placeholder").forEach((placeholder) => {
      placeholder.addEventListener("click", function() {
        const videoId = this.getAttribute("data-video-id");
        const iframe = document.createElement("iframe");
        
        iframe.setAttribute("width", "100%");
        iframe.setAttribute("height", "200");
        iframe.setAttribute("src", `https://www.youtube.com/embed/${videoId}?autoplay=1`);
        iframe.setAttribute("frameborder", "0");
        iframe.setAttribute("allowfullscreen", "");
        iframe.setAttribute("allow", "autoplay");
        iframe.style.borderRadius = "8px";
        
        // Reemplazar el placeholder con el iframe
        this.parentNode.replaceChild(iframe, this);
      });
    });
  });