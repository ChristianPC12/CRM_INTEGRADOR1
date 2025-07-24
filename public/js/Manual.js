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
  });
  