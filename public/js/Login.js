document.addEventListener("DOMContentLoaded", function () {
  // Elementos del DOM
  const loginForm = document.getElementById("loginForm");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const loginBtn = document.getElementById("loginBtn");
  const emailError = document.getElementById("emailError");
  const passwordError = document.getElementById("passwordError");

  // Validación en tiempo real
  emailInput.addEventListener("input", function () {
    validateEmail();
  });

  emailInput.addEventListener("blur", function () {
    validateEmail();
  });

  passwordInput.addEventListener("input", function () {
    validatePassword();
  });

  passwordInput.addEventListener("blur", function () {
    validatePassword();
  });

  // Manejo del envío del formulario
  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();

    if (isEmailValid && isPasswordValid) {
      handleLogin();
    }
  });

  // Función de validación de email
  function validateEmail() {
    const email = emailInput.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const formGroup = emailInput.closest(".form-group");

    if (!email) {
      showError(emailError, "El correo electrónico es requerido");
      setFormGroupState(formGroup, "error");
      return false;
    } else if (!emailRegex.test(email)) {
      showError(emailError, "Ingresa un correo electrónico válido");
      setFormGroupState(formGroup, "error");
      return false;
    } else {
      hideError(emailError);
      setFormGroupState(formGroup, "success");
      return true;
    }
  }

  // Función de validación de contraseña
  function validatePassword() {
    const password = passwordInput.value;
    const formGroup = passwordInput.closest(".form-group");

    if (!password) {
      showError(passwordError, "La contraseña es requerida");
      setFormGroupState(formGroup, "error");
      return false;
    } else if (password.length < 6) {
      showError(
        passwordError,
        "La contraseña debe tener al menos 6 caracteres"
      );
      setFormGroupState(formGroup, "error");
      return false;
    } else {
      hideError(passwordError);
      setFormGroupState(formGroup, "success");
      return true;
    }
  }

  // Función para mostrar errores
  function showError(errorElement, message) {
    errorElement.textContent = message;
    errorElement.classList.add("show");
  }

  // Función para ocultar errores
  function hideError(errorElement) {
    errorElement.textContent = "";
    errorElement.classList.remove("show");
  }

  // Función para establecer el estado visual del grupo de formulario
  function setFormGroupState(formGroup, state) {
    formGroup.classList.remove("success", "error");
    if (state) {
      formGroup.classList.add(state);
    }
  }

  // Función para manejar el login
  function handleLogin() {
    const email = emailInput.value.trim();
    const password = passwordInput.value;

    // Mostrar estado de carga
    setLoadingState(true);

    // Simular llamada a la API (reemplazar con tu lógica de autenticación)
    simulateLogin(email, password)
      .then((response) => {
        if (response.success) {
          // Login exitoso
          showSuccessMessage();
          // Redirigir después de un breve delay
          setTimeout(() => {
            window.location.href = "view/DashboardView.php"; // Ruta corregida para tu estructura
          }, 1500);
        } else {
          // Login fallido
          showLoginError(response.message);
        }
      })
      .catch((error) => {
        console.error("Error en el login:", error);
        showLoginError("Error de conexión. Por favor, intenta nuevamente.");
      })
      .finally(() => {
        setLoadingState(false);
      });
  }

  // Función para simular el login (reemplazar con tu API real)
  function simulateLogin(email, password) {
    return new Promise((resolve) => {
      // Simular delay de red
      setTimeout(() => {
        // Aquí deberías hacer tu llamada real a la API
        // Por ahora, simulamos diferentes respuestas
        if (email === "admin@empresa.com" && password === "123456") {
          resolve({ success: true, message: "Login exitoso" });
        } else {
          resolve({ success: false, message: "Credenciales incorrectas" });
        }
      }, 2000);
    });
  }

  // Función para establecer el estado de carga
  function setLoadingState(isLoading) {
    if (isLoading) {
      loginBtn.disabled = true;
      loginBtn.classList.add("loading");
    } else {
      loginBtn.disabled = false;
      loginBtn.classList.remove("loading");
    }
  }

  // Función para mostrar mensaje de éxito
  function showSuccessMessage() {
    const btnText = loginBtn.querySelector(".btn-text");
    const originalText = btnText.textContent;

    btnText.textContent = "¡Bienvenido!";
    loginBtn.style.background = "#28a745";
    loginBtn.style.color = "#ffffff";

    // Restaurar después de un tiempo
    setTimeout(() => {
      btnText.textContent = originalText;
      loginBtn.style.background = "";
      loginBtn.style.color = "";
    }, 3000);
  }

  // Función para mostrar errores de login
  function showLoginError(message) {
    // Crear elemento de error temporal
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

    // Insertar antes del formulario
    loginForm.insertBefore(errorDiv, loginForm.firstChild);

    // Remover después de 5 segundos
    setTimeout(() => {
      errorDiv.style.animation = "fadeOut 0.3s ease-out";
      setTimeout(() => {
        if (errorDiv.parentNode) {
          errorDiv.parentNode.removeChild(errorDiv);
        }
      }, 300);
    }, 5000);
  }

  // Efecto de focus mejorado para los inputs
  const inputs = [emailInput, passwordInput];
  inputs.forEach((input) => {
    input.addEventListener("focus", function () {
      this.parentElement.classList.add("focused");
    });

    input.addEventListener("blur", function () {
      this.parentElement.classList.remove("focused");
    });
  });

  // Prevenir envío múltiple del formulario
  let isSubmitting = false;
  loginForm.addEventListener("submit", function (e) {
    if (isSubmitting) {
      e.preventDefault();
      return false;
    }
    isSubmitting = true;

    // Resetear después de un tiempo
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
  }

  // Función pública para limpiar el formulario (si se necesita desde fuera)
  window.clearLoginForm = clearForm;

  // Manejo de teclas especiales
  document.addEventListener("keydown", function (e) {
    // Enter en cualquier input envía el formulario
    if (
      e.key === "Enter" &&
      (e.target === emailInput || e.target === passwordInput)
    ) {
      e.preventDefault();
      loginForm.dispatchEvent(new Event("submit"));
    }

    // Escape limpia el formulario
    if (e.key === "Escape") {
      clearForm();
    }
  });

  // Añadir estilos adicionales para animaciones
  const style = document.createElement("style");
  style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
        
        .form-group.focused label {
            color: var(--amarillo);
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
    `;
  document.head.appendChild(style);
});
