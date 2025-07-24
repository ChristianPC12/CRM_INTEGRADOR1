document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("loginForm");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const loginBtn = document.getElementById("loginBtn");
  const emailError = document.getElementById("emailError");
  const passwordError = document.getElementById("passwordError");

  emailInput.addEventListener("input", validateEmail);
  emailInput.addEventListener("blur", validateEmail);
  passwordInput.addEventListener("input", validatePassword);
  passwordInput.addEventListener("blur", validatePassword);

  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

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
    // Limpiar el rol antes de intentar login
    localStorage.removeItem("rolUsuario");
    const usuario = emailInput.value.trim();
    const contrasena = passwordInput.value;

    setLoadingState(true);
    removeExistingErrors();

    const formData = new FormData();
    formData.append("action", "login");
    formData.append("usuario", usuario);
    formData.append("contrasena", contrasena);

    fetch("controller/UsuarioController.php", {

      method: "POST",
      body: formData,
      credentials: "same-origin",
    })
      .then((response) => response.text())
      .then((text) => {
        console.log("🧾 Texto recibido:", text);

        try {
          const jsonText = text.trim().match(/^{.*}$/s)?.[0];
          if (!jsonText) throw new Error("No se encontró JSON válido");

          const data = JSON.parse(jsonText);

          if (data.success) {
            if (data.rol) {
              localStorage.setItem("rolUsuario", data.rol);
            } else {
              localStorage.removeItem("rolUsuario");
            }

            showSuccessMessage();

            setTimeout(() => {
              window.location.href = data.redirect || "/CRM_INT/CRM/index.php?view=dashboard";
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
        setLoadingState(false);
      });
  }

  function setLoadingState(isLoading) {
    loginBtn.disabled = isLoading;
    loginBtn.classList.toggle("loading", isLoading);

    const btnText = loginBtn.querySelector(".btn-text");
    if (btnText) {
      btnText.textContent = isLoading ? "Verificando..." : "Iniciar Sesión";
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

  function clearForm() {
    emailInput.value = "";
    passwordInput.value = "";
    hideError(emailError);
    hideError(passwordError);
    setFormGroupState(emailInput.closest(".form-group"), "");
    setFormGroupState(passwordInput.closest(".form-group"), "");
    removeExistingErrors();
  }

  window.clearLoginForm = clearForm;

  document.addEventListener("keydown", function (e) {
    if (
      e.key === "Enter" &&
      (e.target === emailInput || e.target === passwordInput)
    ) {
      e.preventDefault();
      loginForm.dispatchEvent(new Event("submit"));
    }

    if (e.key === "Escape") {
      clearForm();
    }
  });

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
