document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("loginForm");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const loginBtn = document.getElementById("loginBtn");
  const emailError = document.getElementById("emailError");
  const passwordError = document.getElementById("passwordError");

  // Validaciones en tiempo real
  emailInput.addEventListener("input", validateEmail);
  emailInput.addEventListener("blur", validateEmail);
  passwordInput.addEventListener("input", validatePassword);
  passwordInput.addEventListener("blur", validatePassword);

  // Evento de envío del formulario
  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

    // Prevenir múltiples envíos
    if (loginBtn.disabled) return;

    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();

    if (isEmailValid && isPasswordValid) {
      handleLogin();
    }
  });

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

  function showError(errorElement, message) {
    errorElement.textContent = message;
    errorElement.classList.add("show");
  }

  function hideError(errorElement) {
    errorElement.textContent = "";
    errorElement.classList.remove("show");
  }

  function setFormGroupState(formGroup, state) {
    formGroup.classList.remove("success", "error");
    if (state) {
      formGroup.classList.add(state);
    }
  }

  function handleLogin() {
    const usuario = emailInput.value.trim();
    const contrasena = passwordInput.value;

    setLoadingState(true);

    // Remover errores anteriores
    removeExistingErrors();

    // Crear FormData para enviar al servidor
    const formData = new FormData();
    formData.append("action", "login");
    formData.append("usuario", usuario);
    formData.append("contrasena", contrasena);

    // Realizar petición AJAX al controlador PHP
    fetch("/CRM_INT/CRM/controller/LoginController.php", {
      method: "POST",
      body: formData,
      credentials: "same-origin", // Importante para mantener la sesión
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        console.log("Respuesta del servidor:", data); // Para debugging

        if (data.success) {
          showSuccessMessage();

          // Usar la URL de redirección proporcionada por el servidor
          const redirectUrl = data.redirect || "index.php?view=dashboard";

          setTimeout(() => {
            window.location.href = redirectUrl;
          }, 1500);
        } else {
          showLoginError(data.message || "Credenciales incorrectas");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showLoginError("Error de conexión. Por favor, intenta nuevamente.");
      })
      .finally(() => {
        setLoadingState(false);
      });
  }

  function setLoadingState(isLoading) {
    loginBtn.disabled = isLoading;
    loginBtn.classList.toggle("loading", isLoading);

    const btnText = loginBtn.querySelector(".btn-text");
    if (btnText) {
      if (isLoading) {
        btnText.textContent = "Verificando...";
      } else {
        btnText.textContent = "Iniciar Sesión";
      }
    }
  }

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

  function showLoginError(message) {
    // Remover errores anteriores
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

    // Auto-remover después de 5 segundos
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

  function removeExistingErrors() {
    const existingErrors = document.querySelectorAll(".login-error");
    existingErrors.forEach((error) => error.remove());
  }

  // Efectos de focus en inputs
  const inputs = [emailInput, passwordInput];
  inputs.forEach((input) => {
    input.addEventListener("focus", function () {
      this.parentElement.classList.add("focused");
    });

    input.addEventListener("blur", function () {
      this.parentElement.classList.remove("focused");
    });
  });

  // Prevenir múltiples envíos
  let isSubmitting = false;
  loginForm.addEventListener("submit", function (e) {
    if (isSubmitting) {
      e.preventDefault();
      return false;
    }
    isSubmitting = true;
    setTimeout(() => {
      isSubmitting = false;
    }, 3000);
  });

  // Función para limpiar el formulario
  function clearForm() {
    emailInput.value = "";
    passwordInput.value = "";
    hideError(emailError);
    hideError(passwordError);
    setFormGroupState(emailInput.closest(".form-group"), "");
    setFormGroupState(passwordInput.closest(".form-group"), "");
    removeExistingErrors();
  }

  // Exponer función globalmente para uso externo
  window.clearLoginForm = clearForm;

  // Eventos de teclado
  document.addEventListener("keydown", function (e) {
    // Enter en inputs para enviar formulario
    if (
      e.key === "Enter" &&
      (e.target === emailInput || e.target === passwordInput)
    ) {
      e.preventDefault();
      loginForm.dispatchEvent(new Event("submit"));
    }

    // Escape para limpiar formulario
    if (e.key === "Escape") {
      clearForm();
    }
  });

  // Estilos adicionales
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
});
