<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Acceso - Sistema de Gestión</title>
    <link rel="stylesheet" href="public/css/Login.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-container">
                <img src="public/img/Principal_Amarillo.png" alt="Logo Bastos" class="logo-bastos">
            </div>
            
            <div class="header">
                <h2>CRM BASTOS</h2>
                <div class="subtitle">Inicio de sesión</div>
            </div>

            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="email">Nombre de Usuario</label>
                    <input type="text" id="email" name="usuario" placeholder="Ej. CarlosBastos" required>
                    <span class="error-message" id="emailError"></span>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="contrasena" placeholder="Contraseña" required>
                    <span class="error-message" id="passwordError"></span>
                </div>

                <button type="submit" class="btn-primary" id="loginBtn">
                    <span class="btn-text">Iniciar Sesión</span>
                    <div class="spinner" id="spinner"></div>
                </button>
            </form>
        </div>
    </div>

    <script src="public/js/Login.js"></script>
</body>

</html>