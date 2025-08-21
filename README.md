# CRM Bastos

---------------------------------------------------------------------------

## Equipo de Desarrollo

Proyecto desarrollado por estudiantes de la **Universidad Técnica Nacional (UTN), sede Guanacaste**, de la carrera de **Tecnologías de Información**:

- **Christian Paniagua Castro** — Project Manager, Backend, Database Manager, Version Control Manager & Frontend
- **Steven Baltodano Ugarte** — Backend, UX Designer, Frontend     
- **Reyman Barquero Ramírez** —Backend, UX Designer, Frontend  
- **Brayan Vega Ordóñez** —Backend, UX Designer, Frontend   
- **Felipe Sandoval Chaverri** —Backend, Frontend & QA Tester  

### Roles principales
- **Project Manager:** Christian Paniagua Castro  
- **QA (Aseguramiento de calidad):** Felipe Sandoval Chaverri  
- **UX / Experiencia de usuario:** Brayan Vega Ordóñez  
- **Backend:** Christian Paniagua, Steven Baltodano, Reyman Barquero  
- **Frontend:** Steven Baltodano, Brayan Vega  
- **Database Manager (DBA):** Christian Paniagua Castro  
- **Version Control (Git Kraken & Repositorio):** Christian Paniagua Castro  

---------------------------------------------------------------------------

## Descripción del Proyecto

**CRM Bastos** es un sistema de gestión de clientes VIP para restaurantes. Su propósito es mejorar la fidelización de clientes, optimizar la comunicación y brindar herramientas de análisis que apoyen la toma de decisiones del negocio.  

Este sistema permite automatizar recordatorios, gestionar beneficios personalizados, llevar control de compras y códigos de barras, organizar tareas del personal y mantener un historial de acciones dentro del restaurante. Está diseñado para ser utilizado por **administradores, propietarios y personal de salón**, con permisos y vistas diferenciadas para cada rol.  

-------------------------------------------------------------------------

## Arquitectura y Buenas Prácticas
- **Patrón MVC (Modelo–Vista–Controlador)** para separación de responsabilidades.  
- Uso de **DAO (Data Access Object)**, **DTO (Data Transfer Object)** y **Mapper** para acceso, transporte y mapeo de datos.  
- **Procedimientos almacenados en MySQL** para mayor seguridad y eficiencia.  
- Código modular, limpio y escalable.  

-------------------------------------------------------------------------

## Módulos Principales
- **Clientes VIP:** gestión, validación y búsqueda de clientes.  
- **Cumpleaños:** listado semanal, envío de correos, historial de felicitaciones y cambio automático de estado.  
- **Compras y Códigos de Barra:** registro de transacciones, emisión y lectura de códigos para promociones.  
- **Tareas (Dashboard):** tarjetas tipo libreta, estados pendientes/completadas, orden cronológico y buscador.  
- **Bitácora:** registro de eventos y operaciones para trazabilidad.  
- **Análisis:** panel con métricas visuales sobre clientes y actividad.  
- **Usuarios y Sesiones:** roles diferenciados (Administrador / Salonero).  

---------------------------------------------------------------------------

## Tecnologías Utilizadas
- **Backend:** PHP 8+, MySQL  
- **Frontend:** HTML5, CSS3, JavaScript (jQuery), Bootstrap  
- **Correo:** PHPMailer  
- **IDE recomendado:** Visual Studio Code  
- **Control de versiones:** GitKraken + GitHub  
- **Integración con WhatsApp:** Node.js (bridge-node con `node-windows`)  

---------------------------------------------------------------------------

