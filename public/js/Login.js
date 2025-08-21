document.addEventListener("DOMContentLoaded", function () {
  // -------------------------------
  // 🔹 Referencias a elementos del DOM
  // -------------------------------
  const loginForm = document.getElementById("loginForm"); // Formulario de login
  const emailInput = document.getElementById("email"); // Campo usuario/email
  const passwordInput = document.getElementById("password"); // Campo contraseña
  const loginBtn = document.getElementById("loginBtn"); // Botón de login
  const emailError = document.getElementById("emailError"); // Mensaje error usuario
  const passwordError = document.getElementById("passwordError"); // Mensaje error contraseña

  // -------------------------------
  // 🔹 Validaciones en inputs (usuario y contraseña)
  // -------------------------------
  emailInput.addEventListener("input", validateEmail);
  emailInput.addEventListener("blur", validateEmail);
  passwordInput.addEventListener("input", validatePassword);
  passwordInput.addEventListener("blur", validatePassword);

  // -------------------------------
  // 🔹 Evento submit del formulario
  // -------------------------------
  loginForm.addEventListener("submit", function (e) {
    e.preventDefault(); // Prevenir recarga

    if (loginBtn.disabled) return; // Evitar si el botón está deshabilitado

    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();

    if (isEmailValid && isPasswordValid) {
      handleLogin(); // Ejecuta login
    }
  });

  // -------------------------------
  // 🔹 Validación usuario
  // -------------------------------
  function validateEmail() {
    const email = emailInput.value.trim();
    const formGroup = emailInput.closest(".form-group");

    if (!email) {
      showError(emailError, "El usuario es requerido");
      setFormGroupState(formGroup, "error");
      return false;
    } else {
      hideError(emailError);
      setFormGroupState(formGroup, "success");
      return true;
    }
  }

  // -------------------------------
  // 🔹 Validación contraseña
  // -------------------------------
  function validatePassword() {
    const password = passwordInput.value;
    const formGroup = passwordInput.closest(".form-group");

    if (!password) {
      showError(passwordError, "La contraseña es requerida");
      setFormGroupState(formGroup, "error");
      return false;
    } else {
      hideError(passwordError);
      setFormGroupState(formGroup, "success");
      return true;
    }
  }

  // -------------------------------
  // 🔹 Mostrar/Ocultar errores
  // -------------------------------
  function showError(errorElement, message) {
    errorElement.textContent = message;
    errorElement.classList.add("show");
  }

  function hideError(errorElement) {
    errorElement.textContent = "";
    errorElement.classList.remove("show");
  }

  // -------------------------------
  // 🔹 Cambiar estado visual de form-group
  // -------------------------------
  function setFormGroupState(formGroup, state) {
    formGroup.classList.remove("success", "error");
    if (state) {
      formGroup.classList.add(state);
    }
  }

  // -------------------------------
  // 🔹 Lógica de login con fetch
  // -------------------------------
  function handleLogin() {
    // Limpiar rol antes de intentar login
    localStorage.removeItem("rolUsuario");
    const usuario = emailInput.value.trim();
    const contrasena = passwordInput.value;

    setLoadingState(true); // Poner botón en estado cargando
    removeExistingErrors(); // Limpiar errores previos

    // Construir datos para enviar
    const formData = new FormData();
    formData.append("action", "login");
    formData.append("usuario", usuario);
    formData.append("contrasena", contrasena);

    // Petición al backend
    fetch("controller/UsuarioController.php", {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then((response) => response.text())
      .then((text) => {
        console.log("🧾 Texto recibido:", text);

        try {
          const jsonText = text.trim().match(/^{.*}$/s)?.[0]; // Buscar JSON
          if (!jsonText) throw new Error("No se encontró JSON válido");

          const data = JSON.parse(jsonText);

          if (data.success) {
            // Guardar rol si existe
            if (data.rol) {
              localStorage.setItem("rolUsuario", data.rol);
            } else {
              localStorage.removeItem("rolUsuario");
            }

            // Guardar nombre si existe
            if (data.usuario) {
              localStorage.setItem("nombreUsuario", data.usuario);
            } else {
              localStorage.removeItem("nombreUsuario");
            }

            showSuccessMessage(); // Mostrar mensaje de éxito

            // Redirigir después de 1.5s
            setTimeout(() => {
              window.location.href =
                data.redirect || "/CRM_INT/CRM/index.php?view=dashboard";
            }, 1500);
          } else {
            showLoginError(data.message || "Credenciales incorrectas");
          }
        } catch (error) {
          console.error("❌ Error al parsear JSON:", error);
          showLoginError("Respuesta inválida del servidor:\n\n" + text);
        }
      })
      .catch((error) => {
        console.error("❌ Error de conexión:", error);
        showLoginError("Error de conexión. Por favor, intenta nuevamente.");
      })
      .finally(() => {
        setLoadingState(false); // Volver a estado normal
      });
  }

  // -------------------------------
  // 🔹 Estado de carga en botón login
  // -------------------------------
  function setLoadingState(isLoading) {
    loginBtn.disabled = isLoading;
    loginBtn.classList.toggle("loading", isLoading);

    const btnText = loginBtn.querySelector(".btn-text");
    if (btnText) {
      btnText.textContent = isLoading ? "Verificando..." : "Iniciar Sesión";
    }
  }

  // -------------------------------
  // 🔹 Mensaje éxito al loguear
  // -------------------------------
  function showSuccessMessage() {
    const btnText = loginBtn.querySelector(".btn-text");
    if (btnText) {
      const originalText = btnText.textContent;
      btnText.textContent = "¡Bienvenido!";
      loginBtn.style.background = "#28a745";
      loginBtn.style.color = "#ffffff";

      setTimeout(() => {
        btnText.textContent = originalText;
        loginBtn.style.background = "";
        loginBtn.style.color = "";
      }, 3000);
    }
  }

  // -------------------------------
  // 🔹 Mostrar error de login
  // -------------------------------
  function showLoginError(message) {
    removeExistingErrors();

    const errorDiv = document.createElement("div");
    errorDiv.className = "login-error";
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
      background: #f8d7da;
      color: #721c24;
      padding: 12px;
      border-radius: 4px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
      font-size: 14px;
      text-align: center;
      animation: fadeInUp 0.3s ease-out;
    `;

    loginForm.insertBefore(errorDiv, loginForm.firstChild);

    // Quitar mensaje después de 5s
    setTimeout(() => {
      if (errorDiv.parentNode) {
        errorDiv.style.animation = "fadeOut 0.3s ease-out";
        setTimeout(() => {
          if (errorDiv.parentNode) {
            errorDiv.remove();
          }
        }, 300);
      }
    }, 5000);
  }

  // -------------------------------
  // 🔹 Eliminar errores previos
  // -------------------------------
  function removeExistingErrors() {
    const existingErrors = document.querySelectorAll(".login-error");
    existingErrors.forEach((error) => error.remove());
  }

  // -------------------------------
  // 🔹 Limpiar formulario (reset)
  // -------------------------------
  function clearForm() {
    emailInput.value = "";
    passwordInput.value = "";
    hideError(emailError);
    hideError(passwordError);
    setFormGroupState(emailInput.closest(".form-group"), "");
    setFormGroupState(passwordInput.closest(".form-group"), "");
    removeExistingErrors();
  }

  // Exponer función para usar globalmente
  window.clearLoginForm = clearForm;

  // -------------------------------
  // 🔹 Atajos de teclado
  // -------------------------------
  document.addEventListener("keydown", function (e) {
    // Enter en email o password → enviar formulario
    if (
      e.key === "Enter" &&
      (e.target === emailInput || e.target === passwordInput)
    ) {
      e.preventDefault();
      loginForm.dispatchEvent(new Event("submit"));
    }

    // Escape → limpiar formulario
    if (e.key === "Escape") {
      clearForm();
    }
  });

  // -------------------------------
  // 🔹 Animaciones y estilos dinámicos
  // -------------------------------
  const style = document.createElement("style");
  style.textContent = `
    @keyframes fadeOut {
      from { opacity: 1; transform: translateY(0); }
      to { opacity: 0; transform: translateY(-10px); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .form-group.focused label {
      color: var(--amarillo);
      transform: translateY(-2px);
      transition: all 0.3s ease;
    }

    .btn.loading {
      opacity: 0.7;
      cursor: not-allowed;
    }

    .btn.loading:hover {
      transform: none;
    }

    .login-error {
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
  `;
  document.head.appendChild(style);

  const togglePassBtn = document.getElementById("togglePass");
  if (togglePassBtn) {
    togglePassBtn.addEventListener("click", () => {
      const isHidden = passwordInput.type === "password";
      passwordInput.type = isHidden ? "text" : "password";

      // Cambiar ícono
      const icon = togglePassBtn.querySelector("i");
      icon.className = isHidden ? "bi bi-eye-slash-fill" : "bi bi-eye-fill";

      togglePassBtn.setAttribute(
        "aria-label",
        isHidden ? "Ocultar contraseña" : "Mostrar contraseña"
      );
    });
  }
});
