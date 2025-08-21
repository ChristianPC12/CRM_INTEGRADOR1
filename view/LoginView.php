<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Acceso - Sistema de Gestión</title>
    <!-- Estilos del login -->
    <link rel="stylesheet" href="public/css/Login.css">
</head>

<body>
    <!-- Contenedor principal del login -->
    <div class="login-container">
        <!-- Tarjeta del formulario -->
        <div class="login-card">
            <!-- Logo superior -->
            <div class="logo-container">
                <img src="public/img/Principal_Amarillo.png" alt="Logo Bastos" class="logo-bastos">
            </div>
            
            <!-- Encabezado: título y subtítulo -->
            <div class="header">
                <h2>CRM BASTOS</h2>
                <div class="subtitle">Inicio de sesión</div>
            </div>

            <!-- Formulario de acceso (el envío/validación lo maneja Login.js) -->
            <form id="loginForm" class="login-form">
                <!-- Usuario -->
                <div class="form-group">
                    <label for="email">Nombre de Usuario</label>
                    <input type="text" id="email" name="usuario" placeholder="Ej. CarlosBastos" required>
                    <!-- Mensaje de error para el usuario -->
                    <span class="error-message" id="emailError"></span>
                </div>

                <!-- Contraseña -->
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="contrasena" placeholder="Contraseña" required>
                    <!-- Mensaje de error para la contraseña -->
                    <span class="error-message" id="passwordError"></span>
                </div>

                <!-- Botón de enviar con spinner de carga -->
                <button type="submit" class="btn-primary" id="loginBtn">
                    <span class="btn-text">Iniciar Sesión</span>
                    <div class="spinner" id="spinner"></div>
                </button>
            </form>
        </div>
    </div>

    <!-- Lógica de login: validación y envío -->
    <script src="public/js/Login.js"></script>
</body>

</html>
