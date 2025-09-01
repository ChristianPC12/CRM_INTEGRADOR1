document.addEventListener("DOMContentLoaded", () => {
  // Aplica estilos a todas las miniaturas
  document.querySelectorAll(".video-thumb").forEach((thumb) => {
    const imgUrl = thumb.dataset.thumb;
    if (imgUrl) {
      Object.assign(thumb.style, {
        background: `url(${imgUrl}) center/cover`,
        height: "200px",
        position: "relative",
        cursor: "pointer",
        borderRadius: "8px",
      });

      const play = thumb.querySelector(".play-button");
      if (play) {
        Object.assign(play.style, {
          position: "absolute",
          top: "50%",
          left: "50%",
          transform: "translate(-50%, -50%)",
          fontSize: "3rem",
          color: "rgba(255,255,255,.9)",
          textShadow: "0 0 10px rgba(0,0,0,.7)",
          zIndex: "5",
          transition: "all .3s ease",
        });
      }
    }
  });

  // Manejo de placeholders
  document.querySelectorAll(".video-placeholder").forEach((ph) => {
    const videoId = ph.dataset.videoId;
    const thumb = ph.querySelector(".video-thumb");
    if (!videoId || !thumb) return;

    let activo = false;

    const crearVideo = () => {
      if (activo) return;
      activo = true;

      const cont = document.createElement("div");
      cont.className = "video-container-activo";
      Object.assign(cont.style, {
        position: "absolute",
        inset: 0,
        width: "100%",
        height: "100%",
        zIndex: "1000",
        background: "#000",
        borderRadius: "8px",
        overflow: "hidden",
        opacity: "0",
        transition: "opacity .3s ease",
      });

      const iframe = document.createElement("iframe");
      Object.assign(iframe, {
        src: `https://www.youtube-nocookie.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1`,
        allow: "autoplay; encrypted-media; fullscreen",
      });
      Object.assign(iframe.style, { width: "100%", height: "100%", border: "none", borderRadius: "8px" });

      const closeBtn = document.createElement("button");
      closeBtn.textContent = "×";
      Object.assign(closeBtn.style, {
        position: "absolute",
        top: "8px",
        right: "8px",
        width: "35px",
        height: "35px",
        border: "none",
        borderRadius: "50%",
        background: "rgba(0,0,0,.8)",
        color: "#fff",
        fontSize: "20px",
        fontWeight: "bold",
        cursor: "pointer",
        zIndex: "1001",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        transition: "all .2s ease",
        boxShadow: "0 2px 10px rgba(0,0,0,.3)",
      });

      const cerrar = () => {
        cont.style.opacity = "0";
        setTimeout(() => cont.remove(), 300);
        activo = false;
      };

      closeBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        cerrar();
      });
      document.addEventListener("keydown", (e) => e.key === "Escape" && activo && cerrar(), { once: true });

      cont.append(iframe, closeBtn);
      ph.appendChild(cont);
      setTimeout(() => (cont.style.opacity = "1"), 50);
    };

    ph.addEventListener("click", crearVideo);

    // Hover effect en el botón play
    thumb.addEventListener("mouseenter", () => {
      const play = thumb.querySelector(".play-button");
      if (play) {
        play.style.transform = "translate(-50%, -50%) scale(1.1)";
        play.style.color = "#fff";
      }
    });
    thumb.addEventListener("mouseleave", () => {
      const play = thumb.querySelector(".play-button");
      if (play) {
        play.style.transform = "translate(-50%, -50%) scale(1)";
        play.style.color = "rgba(255,255,255,.9)";
      }
    });
  });

  // Cierra todos los videos desde fuera
  window.cerrarTodosLosVideos = () => 
    document.querySelectorAll(".video-container-activo button").forEach((btn) => btn.click());

  console.log("Sistema de videos optimizado ✅");
});
