document.addEventListener("DOMContentLoaded", function () {

  // ✅ Validación sencilla de contraseña (mínimo 6 caracteres, al menos una letra y un número)
 function esContrasenaValida(contrasena) {
  const regex = /^(?=.*[a-zA-Z])(?=.*\d).{6,}$/;
  return regex.test(contrasena);
}

  const form = document.getElementById("usuarioForm");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const usuario = document.getElementById("usuario").value.trim();
    const contrasena = document.getElementById("contrasena").value.trim();
    const rol = document.getElementById("rol").value.trim();
    const privilegios = document.getElementById("privilegios").value.trim();

    if (!usuario || !contrasena || !rol) {
      alert("⚠️ Todos los campos obligatorios deben completarse.");
      return;
    }

    if (!esContrasenaValida(contrasena)) {
      alert("⚠️ La contraseña debe tener al menos 6 caracteres, una letra y un número.");
      return;
    }

    const datos = new FormData();
    datos.append("usuario", usuario);
    datos.append("contrasena", contrasena);
    datos.append("rol", rol);
    datos.append("privilegios", privilegios);

    fetch("/CRM_INT/CRM/controller/guardarUsuario.php", {
      method: "POST",
      body: datos,
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("✅ Usuario guardado correctamente.");
          form.reset();
          cargarUsuarios();
        } else {
          alert("❌ No se pudo guardar: " + data.message);
        }
      })
      .catch(err => {
        console.error("Error al guardar usuario:", err);
        alert("❌ Error al guardar usuario.");
      });
  });

  // ✅ Cargar usuarios en la tabla
  window.cargarUsuarios = function () {
    const contenedor = document.getElementById("usuarioLista");
    contenedor.innerHTML = `<div class="text-center p-3">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2">Cargando usuarios...</p>
    </div>`;

    fetch("/CRM_INT/CRM/controller/listarUsuarios.php")
      .then(res => res.text())
      .then(html => {
        contenedor.innerHTML = html;
      })
      .catch(err => {
        console.error("Error al cargar usuarios:", err);
        contenedor.innerHTML = "<p class='text-danger'>Error al cargar usuarios.</p>";
      });
  };

  // ✅ Eliminar usuario al hacer clic
  document.addEventListener("click", function (e) {
    if (e.target.classList.contains("eliminar-usuario") || e.target.closest(".eliminar-usuario")) {
      const btn = e.target.closest(".eliminar-usuario");
      const id = btn.getAttribute("data-id");

      if (confirm("¿Estás seguro de eliminar este usuario?")) {
        const datos = new FormData();
        datos.append("id", id);

        fetch("/CRM_INT/CRM/controller/eliminarUsuario.php", {
          method: "POST",
          body: datos,
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              alert("✅ Usuario eliminado.");
              cargarUsuarios();
            } else {
              alert("❌ No se pudo eliminar: " + data.message);
            }
          })
          .catch(err => {
            console.error("Error:", err);
            alert("❌ Error al eliminar usuario.");
          });
      }
    }
  });

  // ✅ Cargar la lista al abrir la vista
  cargarUsuarios();
});
