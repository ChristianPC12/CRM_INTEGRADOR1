document.addEventListener("DOMContentLoaded", function () {
  const btns = document.querySelectorAll(".compra-btn-opcion");
  btns.forEach((btn) => {
    btn.addEventListener("click", function () {
      btns.forEach((b) => b.classList.remove("selected")); // Quitar selección previa
      this.classList.add("selected"); // Agregar al actual
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  document
    .getElementById("compraBtnBuscar")
    .addEventListener("click", function () {
      if (
        document
          .getElementById("compraOpcionCompra")
          .classList.contains("selected")
      ) {
        document.getElementById("compraCardForm").style.display = "block";
        document.getElementById("compraIndicacion").style.display = "none";
      }
    });
});
