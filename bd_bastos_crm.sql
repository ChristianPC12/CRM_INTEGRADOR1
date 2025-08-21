-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 14, 2025 at 05:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bd_bastos_crm`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `BitacoraCreate` (IN `p_IdUsuario` INT, IN `p_HoraEntrada` TIME, IN `p_HoraSalida` TIME, IN `p_Fecha` DATE)   BEGIN
    INSERT INTO bitacora (IdUsuario, HoraEntrada, HoraSalida, Fecha)
    VALUES (p_IdUsuario, p_HoraEntrada, p_HoraSalida, p_Fecha);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `BitacoraRead` (IN `p_Id` INT)   BEGIN
    SELECT * FROM bitacora WHERE Id = p_Id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `BitacoraReadAll` ()   BEGIN
    SELECT * FROM bitacora;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `BitacoraSelectByUsuario` (IN `p_IdUsuario` INT)   BEGIN
    SELECT * FROM bitacora WHERE IdUsuario = p_IdUsuario;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `BitacoraUpdate` (IN `p_Id` INT, IN `p_IdUsuario` INT, IN `p_HoraEntrada` TIME, IN `p_HoraSalida` TIME, IN `p_Fecha` DATE)   BEGIN
    UPDATE bitacora
    SET IdUsuario = p_IdUsuario,
        HoraEntrada = p_HoraEntrada,
        HoraSalida = p_HoraSalida,
        Fecha = p_Fecha
    WHERE Id = p_Id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ClienteCreate` (IN `pCedula` VARCHAR(20), IN `pNombre` VARCHAR(120), IN `pCorreo` VARCHAR(120), IN `pTelefono` VARCHAR(30), IN `pLugarResidencia` VARCHAR(200), IN `pFechaCumpleanos` DATE, IN `pAlergias` TEXT, IN `pGustosEspeciales` TEXT)   BEGIN
    INSERT INTO cliente (
        Cedula, Nombre, Correo, Telefono, LugarResidencia, FechaCumpleanos,
        Alergias, gustos_especiales
    ) VALUES (
        pCedula, pNombre, pCorreo, pTelefono, pLugarResidencia, pFechaCumpleanos,
        pAlergias, pGustosEspeciales
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ClienteCumpleSemana` ()   BEGIN
    DECLARE hoy DATE;
    DECLARE domingo DATE;

    SET hoy = DATE_SUB(CURDATE(), INTERVAL (DAYOFWEEK(CURDATE()) + 5) % 7 DAY);
    SET domingo = DATE_ADD(hoy, INTERVAL 6 DAY);

    SET @hoy := hoy;
    SET @domingo := domingo;

    SELECT 
        c.Id,
        c.Cedula,
        c.Nombre,
        c.Correo,
        c.Telefono,
        c.FechaCumpleanos
    FROM cliente c
    WHERE 
        DATE_FORMAT(c.FechaCumpleanos, '%m-%d') BETWEEN DATE_FORMAT(@hoy, '%m-%d') AND DATE_FORMAT(@domingo, '%m-%d')
        AND c.Id NOT IN (
            SELECT cu.IdCliente
            FROM cumple cu
            WHERE cu.FechaLlamada BETWEEN @hoy AND @domingo
        );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ClienteCumpleSemanaOffset` (IN `p_offset` INT)   BEGIN
    DECLARE lunes   DATE;
    DECLARE domingo DATE;

    -- Lunes de la semana actual + (7 * offset) días
    SET lunes   = DATE_ADD(
                    DATE_SUB(CURDATE(), INTERVAL (DAYOFWEEK(CURDATE()) + 5) % 7 DAY),
                    INTERVAL 7 * p_offset DAY
                 );
    SET domingo = DATE_ADD(lunes, INTERVAL 6 DAY);

    SELECT 
        c.Id,
        c.Cedula,
        c.Nombre,
        c.Correo,
        c.Telefono,
        c.FechaCumpleanos
    FROM cliente c
    WHERE DATE_FORMAT(c.FechaCumpleanos, '%m-%d') IN (
        DATE_FORMAT(lunes, '%m-%d'),
        DATE_FORMAT(DATE_ADD(lunes, INTERVAL 1 DAY), '%m-%d'),
        DATE_FORMAT(DATE_ADD(lunes, INTERVAL 2 DAY), '%m-%d'),
        DATE_FORMAT(DATE_ADD(lunes, INTERVAL 3 DAY), '%m-%d'),
        DATE_FORMAT(DATE_ADD(lunes, INTERVAL 4 DAY), '%m-%d'),
        DATE_FORMAT(DATE_ADD(lunes, INTERVAL 5 DAY), '%m-%d'),
        DATE_FORMAT(DATE_ADD(lunes, INTERVAL 6 DAY), '%m-%d')
    )
    AND c.Id NOT IN (
        SELECT cu.IdCliente
        FROM cumple cu
        WHERE cu.FechaLlamada BETWEEN lunes AND domingo
    )
    ORDER BY MONTH(c.FechaCumpleanos), DAY(c.FechaCumpleanos), c.Nombre;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ClienteDelete` (IN `pId` INT)   BEGIN
  DELETE FROM cliente WHERE Id = pId;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ClienteExisteCedula` (IN `p_Cedula` VARCHAR(20), IN `p_Id` INT)   BEGIN
    SELECT Id 
    FROM cliente 
    WHERE Cedula = p_Cedula AND (p_Id IS NULL OR Id != p_Id)
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ClienteRead` (IN `pId` INT)   BEGIN
    SELECT
        Id,
        Cedula,
        Nombre,
        Correo,
        Telefono,
        LugarResidencia,
        FechaCumpleanos,
        Acumulado,
        FechaRegistro,
        Alergias AS Alergias,
        gustos_especiales AS GustosEspeciales,
        TotalHistorico  -- deja lo que ya tenías
    FROM cliente
    WHERE Id = pId;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ClienteReadAll` ()   BEGIN
  SELECT 
    Id,
    Cedula,
    Nombre,
    Correo,
    Telefono,
    LugarResidencia,
    FechaCumpleanos,
    Acumulado,
    FechaRegistro,
    Alergias AS Alergias,
    gustos_especiales AS GustosEspeciales,
    TotalHistorico
  FROM cliente
  ORDER BY Id DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ClienteUpdate` (IN `pId` INT, IN `pCedula` VARCHAR(20), IN `pNombre` VARCHAR(120), IN `pCorreo` VARCHAR(120), IN `pTelefono` VARCHAR(30), IN `pLugarResidencia` VARCHAR(200), IN `pFechaCumpleanos` DATE, IN `pAcumulado` DECIMAL(10,2), IN `pAlergias` TEXT, IN `pGustosEspeciales` TEXT)   BEGIN
    UPDATE cliente
    SET Cedula = pCedula,
        Nombre = pNombre,
        Correo = pCorreo,
        Telefono = pTelefono,
        LugarResidencia = pLugarResidencia,
        FechaCumpleanos = pFechaCumpleanos,
        Acumulado = pAcumulado,
        Alergias = pAlergias,
        gustos_especiales = pGustosEspeciales
    WHERE Id = pId;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ClienteVerificarDuplicados` (IN `p_Telefono` VARCHAR(20), IN `p_Correo` VARCHAR(100), IN `p_Id` INT)   BEGIN
    SELECT Id FROM cliente
    WHERE 
        (Telefono = p_Telefono OR 
        (p_Correo IS NOT NULL AND p_Correo != '' AND Correo = p_Correo))
      AND (Id != p_Id OR p_Id IS NULL);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CodigoCreate` (IN `p_IdCliente` INT, IN `p_CodigoBarra` VARCHAR(64), IN `p_CantImpresiones` INT)   BEGIN
    INSERT INTO codigo (IdCliente, CodigoBarra, CantImpresiones)
    VALUES (p_IdCliente, p_CodigoBarra, p_CantImpresiones);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CodigoDelete` (IN `p_Id` INT)   BEGIN
    DELETE FROM codigo WHERE Id = p_Id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CodigoRead` (IN `p_Id` INT)   BEGIN
    SELECT * FROM codigo WHERE Id = p_Id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CodigoReadAll` ()   BEGIN
    SELECT * FROM codigo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CodigoReassign` (IN `p_IdCliente` INT, IN `p_Motivo` VARCHAR(100))   BEGIN
    DECLARE codActual VARCHAR(64);
    DECLARE codActualExiste INT;
    DECLARE codNuevo VARCHAR(64);
    DECLARE intento INT DEFAULT 0;
    -- Obtener código actual activo del cliente
    SELECT CodigoBarra INTO codActual
    FROM codigo
    WHERE IdCliente = p_IdCliente AND Estado = 'Activo'
    ORDER BY Id DESC
    LIMIT 1;
    -- Verificar que existe
    IF codActual IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El cliente no tiene una tarjeta activa para reasignar.';
    END IF;
    -- Generar nuevo código único
    generar_codigo: LOOP
        SET codNuevo = CONCAT('VIP', LPAD(FLOOR(RAND() * 99999), 5, '0'));
        IF NOT EXISTS (SELECT 1 FROM codigo WHERE CodigoBarra = codNuevo) THEN
            LEAVE generar_codigo;
        END IF;
        SET intento = intento + 1;
        IF intento > 10 THEN
            SIGNAL SQLSTATE '45001'
            SET MESSAGE_TEXT = 'No se pudo generar un código único.';
        END IF;
    END LOOP;
    -- Desactivar código anterior con motivo y fecha
    UPDATE codigo
    SET Estado = 'Inactivo',
        MotivoCambio = p_Motivo,
        FechaCambio = NOW()
    WHERE CodigoBarra = codActual AND IdCliente = p_IdCliente;
    -- Crear nuevo código usando procedimiento existente
    CALL CodigoCreate(p_IdCliente, codNuevo, 1);
    -- Retornar nuevo código (opcional)
    SELECT codNuevo AS NuevoCodigoGenerado;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CodigoSelectByCliente` (IN `p_IdCliente` INT)   BEGIN
    SELECT * FROM codigo WHERE IdCliente = p_IdCliente;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CodigoUpdate` (IN `p_Id` INT, IN `p_CodigoBarra` VARCHAR(64), IN `p_CantImpresiones` INT)   BEGIN
    UPDATE codigo
    SET CodigoBarra = p_CodigoBarra,
        CantImpresiones = p_CantImpresiones
    WHERE Id = p_Id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CompraCreate` (IN `p_FechaCompra` DATE, IN `p_Total` INT, IN `p_IdCliente` INT)   BEGIN
    INSERT INTO compra (FechaCompra, Total, IdCliente)
    VALUES (p_FechaCompra, p_Total, p_IdCliente);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CompraDelete` (IN `p_IdCompra` INT)   BEGIN
    DELETE FROM compra WHERE IdCompra = p_IdCompra;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CompraRead` (IN `p_IdCompra` INT)   BEGIN
    SELECT * FROM compra WHERE IdCompra = p_IdCompra;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CompraReadAll` ()   BEGIN
    SELECT * FROM compra;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CompraReadByCliente` (IN `p_IdCliente` INT)   BEGIN
    SELECT * FROM compra WHERE IdCliente = p_IdCliente;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CompraUpdate` (IN `p_IdCompra` INT, IN `p_FechaCompra` DATE, IN `p_Total` INT, IN `p_IdCliente` INT)   BEGIN
    UPDATE compra
    SET FechaCompra = p_FechaCompra,
        Total = p_Total,
        IdCliente = p_IdCliente
    WHERE IdCompra = p_IdCompra;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `PurgarHistorialCumple30Dias` ()   BEGIN
    DELETE FROM cumple 
    WHERE FechaLlamada < (NOW() - INTERVAL 30 DAY);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SumarCompra` (IN `p_IdCliente` INT, IN `p_Monto` INT)   BEGIN
    /* Manejador de errores: si algo falla ⇒ ROLLBACK */
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
    START TRANSACTION;
        /* 1) Registrar la compra */
        INSERT INTO compra (FechaCompra, Total, IdCliente)
        VALUES (NOW(), p_Monto, p_IdCliente);
        /* 2) Sumar al saldo y al histórico */
        UPDATE cliente
        SET  Acumulado      = IFNULL(Acumulado, 0)      + p_Monto,
             TotalHistorico = IFNULL(TotalHistorico, 0) + p_Monto
        WHERE Id = p_IdCliente;
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TareaCreate` (IN `p_descripcion` VARCHAR(220), IN `p_estado` ENUM('pendiente','completado'))   BEGIN
    INSERT INTO tarea (descripcion, estado)
    VALUES (p_descripcion, p_estado);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TareaDelete` (IN `p_Id` INT)   BEGIN
    DELETE FROM tarea WHERE id = p_Id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TareaRead` (IN `p_Id` INT)   BEGIN
    SELECT * FROM tarea WHERE id = p_Id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TareaReadAll` ()   BEGIN
    SELECT * FROM tarea ORDER BY fecha_creacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TareaUpdate` (IN `p_Id` INT, IN `p_Estado` ENUM('pendiente','completada'))   BEGIN
    UPDATE Tarea
    SET estado = p_Estado
    WHERE id = p_Id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UsuarioCreate` (IN `p_Usuario` VARCHAR(25), IN `p_Contrasena` VARCHAR(255), IN `p_Rol` VARCHAR(20))   BEGIN
    INSERT INTO usuario (Usuario, Contrasena, Rol)
    VALUES (p_Usuario, p_Contrasena, p_Rol);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UsuarioDelete` (IN `p_Id` INT)   BEGIN
    DELETE FROM usuario WHERE Id = p_Id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UsuarioRead` (IN `p_Id` INT)   BEGIN
    SELECT * FROM usuario WHERE Id = p_Id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UsuarioReadAll` ()   BEGIN
    SELECT * FROM usuario;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UsuarioUpdate` (IN `p_Id` INT, IN `p_Usuario` VARCHAR(25), IN `p_Contrasena` VARCHAR(255), IN `p_Rol` VARCHAR(20))   BEGIN
    UPDATE usuario
    SET Usuario = p_Usuario,
        Contrasena = p_Contrasena,
        Rol = p_Rol
    WHERE Id = p_Id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `bitacora`
--

CREATE TABLE `bitacora` (
  `IdUsuario` int(11) NOT NULL,
  `HoraEntrada` time NOT NULL,
  `HoraSalida` time DEFAULT NULL,
  `Fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bitacora`
--

INSERT INTO `bitacora` (`IdUsuario`, `HoraEntrada`, `HoraSalida`, `Fecha`) VALUES
(1005, '20:04:38', '21:09:03', '2025-07-15'),
(1005, '21:10:32', '20:58:25', '2025-07-15'),
(1005, '21:46:58', '20:46:32', '2025-07-15'),
(1005, '21:48:00', '16:45:58', '2025-07-15'),
(1005, '16:35:20', '16:44:31', '2025-07-16'),
(1005, '21:03:45', '16:41:24', '2025-07-16'),
(1005, '21:51:09', '16:13:33', '2025-07-16'),
(1005, '21:52:55', '15:59:40', '2025-07-16'),
(1015, '15:59:55', '16:00:05', '2025-07-28'),
(1016, '16:00:14', '16:00:45', '2025-07-28'),
(1017, '16:01:00', '16:12:46', '2025-07-28'),
(1017, '16:41:34', '16:41:52', '2025-07-28'),
(1015, '16:42:04', '16:42:28', '2025-07-28'),
(1018, '16:44:43', '16:44:56', '2025-07-28'),
(1018, '16:46:15', '16:47:20', '2025-07-28'),
(1015, '21:50:22', '22:05:54', '2025-07-28'),
(1005, '21:09:08', '21:10:07', '2025-08-06'),
(1005, '21:10:12', '21:13:06', '2025-08-06'),
(1005, '21:13:11', '21:14:51', '2025-08-06'),
(1005, '21:14:56', '21:32:33', '2025-08-06'),
(1005, '21:32:39', '22:08:34', '2025-08-06'),
(1005, '22:08:38', '23:36:05', '2025-08-06'),
(1005, '23:36:16', '21:05:42', '2025-08-06'),
(1005, '21:23:04', '22:50:03', '2025-08-13'),
(1005, '08:39:07', '00:00:00', '2025-08-14');

-- --------------------------------------------------------

--
-- Table structure for table `cliente`
--

CREATE TABLE `cliente` (
  `Id` int(4) NOT NULL,
  `Cedula` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Nombre` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Correo` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Telefono` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `LugarResidencia` varchar(22) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `FechaCumpleanos` date NOT NULL,
  `Acumulado` int(6) DEFAULT NULL,
  `FechaRegistro` date NOT NULL,
  `TotalHistorico` int(9) DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `gustos_especiales` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cliente`
--

INSERT INTO `cliente` (`Id`, `Cedula`, `Nombre`, `Correo`, `Telefono`, `LugarResidencia`, `FechaCumpleanos`, `Acumulado`, `FechaRegistro`, `TotalHistorico`, `alergias`, `gustos_especiales`) VALUES
(1, '', 'Vivana Guillen Wong', NULL, '88370518', '', '2024-06-25', NULL, '2025-08-13', NULL, NULL, NULL),
(2, '502400549', 'Shirley Wong Contreras', NULL, '87671094', '', '1966-10-04', NULL, '2025-08-13', NULL, NULL, NULL),
(3, '', 'Rody Bogarín Morales', NULL, '83380881', '', '1977-05-17', NULL, '2025-08-13', NULL, NULL, NULL),
(4, '503930066', 'Diego Wong Salazar', NULL, '85624662', '', '2024-05-24', NULL, '2025-08-13', NULL, NULL, NULL),
(5, '', 'Guiselle Salazar', NULL, '26690259', '', '2024-05-31', NULL, '2025-08-13', NULL, NULL, NULL),
(6, '205770879', 'Oscar Vargas Solano', NULL, '88457010', '', '2024-11-30', NULL, '2025-08-13', NULL, NULL, NULL),
(7, '', 'Ana Mora Gómez', NULL, '83088412', '', '2024-11-28', NULL, '2025-08-13', NULL, NULL, NULL),
(8, '110260959', 'Evelyn Fallas López', NULL, '88448265', '', '2024-12-27', NULL, '2025-08-13', NULL, NULL, NULL),
(9, '', 'Ester Solano', NULL, '89237526', '', '2024-07-30', NULL, '2025-08-13', NULL, NULL, NULL),
(10, '', 'Ana María Solano', NULL, '88191888', '', '0000-00-00', NULL, '2025-08-13', NULL, NULL, NULL),
(11, '', 'Rebeca Porras', NULL, '84098684', '', '2024-02-01', NULL, '2025-08-13', NULL, NULL, NULL),
(12, '', 'Marlyn Zeledón', NULL, '', '', '2024-06-27', NULL, '2025-08-13', NULL, NULL, NULL),
(13, '107560243', 'Carlos Alvarado Ruiz', NULL, '88343096', '', '2024-09-23', NULL, '2025-08-13', NULL, NULL, NULL),
(14, '', 'Ander Romero', NULL, '88219158', '', '0000-00-00', NULL, '2025-08-13', NULL, NULL, NULL),
(15, '501640421', 'Ivette Wong Díjeres', NULL, '83832041', '', '2024-10-24', NULL, '2025-08-13', NULL, NULL, NULL),
(16, '503320397', 'Gabriel Alvarez Wong', NULL, '83342718', '', '2024-12-18', NULL, '2025-08-13', NULL, NULL, NULL),
(17, '604210909', 'Jessica Palma Calvo', NULL, '72087806', '', '2024-02-08', NULL, '2025-08-13', NULL, NULL, NULL),
(18, '', 'Carlos Gonzales ', NULL, '25517084', '', '2024-01-13', NULL, '2025-08-13', NULL, NULL, NULL),
(19, '', 'Jose Gonzales', NULL, '11972096', '', '2024-08-26', NULL, '2025-08-13', NULL, NULL, NULL),
(20, '', 'Juan Pablo Mendez Rodríguez', NULL, '87995045', '', '2024-01-10', NULL, '2025-08-13', NULL, NULL, NULL),
(21, '', 'Paola Campos', NULL, '83434686', '', '2024-01-28', NULL, '2025-08-13', NULL, NULL, NULL),
(22, '', 'Celia Apuy', NULL, '83120405', '', '2024-10-05', NULL, '2025-08-13', NULL, NULL, NULL),
(23, '602920117', 'Roque Rojas Pérez', NULL, '89659627', '', '2024-07-07', NULL, '2025-08-13', NULL, NULL, NULL),
(24, '206590043', 'Eduardo Vega Cortes', NULL, '84976306', '', '2024-03-10', NULL, '2025-08-13', NULL, NULL, NULL),
(25, '', 'Lilliana Mora Fanseca', NULL, '83029213', '', '2024-04-17', NULL, '2025-08-13', NULL, NULL, NULL),
(26, '', 'Gabriela Ruiz Salas', NULL, '84422024', '', '2024-08-18', NULL, '2025-08-13', NULL, NULL, NULL),
(27, '', 'Cristhian Gutierrez ', NULL, '88281852', '', '2024-10-19', NULL, '2025-08-13', NULL, NULL, NULL),
(28, '', 'María Fernanda Melendez O.', NULL, '84238294', '', '2024-06-29', NULL, '2025-08-13', NULL, NULL, NULL),
(29, '', 'Luis Andrey Jimenez Alnis', NULL, '89255543', '', '2024-03-02', NULL, '2025-08-13', NULL, NULL, NULL),
(30, '', 'Yecferson Rodríguez Garay', NULL, '88929116', '', '2024-12-22', NULL, '2025-08-13', NULL, NULL, NULL),
(31, '', 'Jessica Vega', NULL, '88458781', '', '2024-05-14', NULL, '2025-08-13', NULL, NULL, NULL),
(32, '', 'Marianela Aguirre', NULL, '89242220', '', '2024-08-09', NULL, '2025-08-13', NULL, NULL, NULL),
(33, '', 'Georgio Jerez Lonza', NULL, '88441313', '', '2024-07-23', NULL, '2025-08-13', NULL, NULL, NULL),
(34, '', 'Darinka ', NULL, '88373584', '', '2024-01-06', NULL, '2025-08-13', NULL, NULL, NULL),
(35, '', 'José Alfredo Tencio', NULL, '83242690', '', '2024-02-15', NULL, '2025-08-13', NULL, NULL, NULL),
(36, '112330284', 'Raquel Murillo Zúñiga', NULL, '87070415', '', '2024-02-02', NULL, '2025-08-13', NULL, NULL, NULL),
(37, '', 'Elizabeth Elizondo Ramírez', NULL, '', '', '2024-02-07', NULL, '2025-08-13', NULL, NULL, NULL),
(38, '501320086', 'Juan Acón Chen', NULL, '88848607', '', '2024-02-01', NULL, '2025-08-13', NULL, NULL, NULL),
(39, '', 'José Rojas Alvarez', NULL, '83211089', '', '2024-02-15', NULL, '2025-08-13', NULL, NULL, NULL),
(40, '', 'KriSttel Fernández', NULL, '83766421', '', '2024-08-03', NULL, '2025-08-13', NULL, NULL, NULL),
(41, '', 'Odalis Fernández', NULL, '83798400', '', '2024-08-05', NULL, '2025-08-13', NULL, NULL, NULL),
(42, '', 'Luis Lacayo', NULL, '72065198', '', '2024-07-19', NULL, '2025-08-13', NULL, NULL, NULL),
(43, '', 'Felipe Avendaño', NULL, '84354618', '', '2024-06-30', NULL, '2025-08-13', NULL, NULL, NULL),
(44, '', 'Jalil Tabash Fonseca', NULL, '88601109', '', '2024-04-24', NULL, '2025-08-13', NULL, NULL, NULL),
(45, '', 'José Pablo Arguedas', NULL, '85099065', '', '2024-03-19', NULL, '2025-08-13', NULL, NULL, NULL),
(46, '110860262', 'Muriel Grijalba Murillo', NULL, '89934520', '', '2024-11-06', NULL, '2025-08-13', NULL, NULL, NULL),
(47, '', 'Rafa Mejías', NULL, '88154907', '', '2024-11-04', NULL, '2025-08-13', NULL, NULL, NULL),
(48, '', 'Euyenit Hernandez', NULL, '88210380', '', '2024-09-08', NULL, '2025-08-13', NULL, NULL, NULL),
(49, '', 'Dayana Rojas', NULL, '88482979', '', '2024-09-17', NULL, '2025-08-13', NULL, NULL, NULL),
(50, '', 'Wanda Calderon', NULL, '83131495', '', '2024-09-09', NULL, '2025-08-13', NULL, NULL, NULL),
(51, '503420930', 'RicardoGutierrez Ramirez', NULL, '88352902', '', '2024-12-14', NULL, '2025-08-13', NULL, NULL, NULL),
(52, '503700040', 'Johana Bermudez Carvajal', NULL, '85037642', '', '2024-05-08', NULL, '2025-08-13', NULL, NULL, NULL),
(53, '', 'Italia Valverde', NULL, '83956497', '', '2024-04-25', NULL, '2025-08-13', NULL, NULL, NULL),
(54, '', 'Andrea Martínez', NULL, '89621393', '', '2024-05-24', NULL, '2025-08-13', NULL, NULL, NULL),
(55, '', 'Gabriel Guzman', NULL, '86045470', '', '2024-03-22', NULL, '2025-08-13', NULL, NULL, NULL),
(56, '', 'Francisca Linarez', NULL, '85351796', '', '2024-10-01', NULL, '2025-08-13', NULL, NULL, NULL),
(57, '', 'Mauricio Castro', NULL, '87017214', '', '2024-09-02', NULL, '2025-08-13', NULL, NULL, NULL),
(58, '', 'Erick Bastos', NULL, '88357772', '', '2024-02-18', NULL, '2025-08-13', NULL, NULL, NULL),
(59, '502420006', 'Daniel Bastos Luna', NULL, '', '', '2024-06-27', NULL, '2025-08-13', NULL, NULL, NULL),
(60, '111010528', 'Mariana Rovira Apuy', NULL, '89924694', '', '2024-04-22', NULL, '2025-08-13', NULL, NULL, NULL),
(61, '401690075', 'Andrey Rojas Méndez', NULL, '88129729', '', '2024-12-29', NULL, '2025-08-13', NULL, NULL, NULL),
(62, '', 'Scarlet Vargas Picado', NULL, '64376961', '', '2024-04-01', NULL, '2025-08-13', NULL, NULL, NULL),
(63, '', 'Raquel Gamboa Garcia', NULL, '88409227', '', '2024-07-05', NULL, '2025-08-13', NULL, NULL, NULL),
(64, '', 'Ivannia Campos Araya', NULL, '84854528', '', '2024-08-12', NULL, '2025-08-13', NULL, NULL, NULL),
(65, '', 'Senia Barrios', NULL, '89290404', '', '2024-10-24', NULL, '2025-08-13', NULL, NULL, NULL),
(66, '124300985', 'Geiner Jose Murillo Castro', NULL, '89787612', '', '0000-00-00', NULL, '2025-08-13', NULL, NULL, NULL),
(67, '', 'Eduardo Espinal', NULL, '86579608', '', '2024-05-05', NULL, '2025-08-13', NULL, NULL, NULL);

--
-- Triggers `cliente`
--
DELIMITER $$
CREATE TRIGGER `InsertCodigoAfterCliente` AFTER INSERT ON `cliente` FOR EACH ROW BEGIN
    INSERT INTO codigo (IdCliente, CodigoBarra, CantImpresiones)
    VALUES (NEW.Id, CONCAT('CODE-', NEW.Id), 0);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `codigo`
--

CREATE TABLE `codigo` (
  `Id` int(4) NOT NULL,
  `IdCliente` int(4) NOT NULL,
  `CodigoBarra` varchar(64) DEFAULT NULL,
  `CantImpresiones` int(2) DEFAULT 0,
  `Estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `MotivoCambio` varchar(50) DEFAULT NULL,
  `FechaCambio` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `codigo`
--

INSERT INTO `codigo` (`Id`, `IdCliente`, `CodigoBarra`, `CantImpresiones`, `Estado`, `MotivoCambio`, `FechaCambio`) VALUES
(2499, 1, 'CODE-1', 0, 'Activo', NULL, NULL),
(2500, 2, 'CODE-2', 0, 'Activo', NULL, NULL),
(2501, 3, 'CODE-3', 0, 'Activo', NULL, NULL),
(2502, 4, 'CODE-4', 0, 'Activo', NULL, NULL),
(2503, 5, 'CODE-5', 0, 'Activo', NULL, NULL),
(2504, 6, 'CODE-6', 0, 'Activo', NULL, NULL),
(2505, 7, 'CODE-7', 0, 'Activo', NULL, NULL),
(2506, 8, 'CODE-8', 0, 'Activo', NULL, NULL),
(2507, 9, 'CODE-9', 0, 'Activo', NULL, NULL),
(2508, 10, 'CODE-10', 0, 'Activo', NULL, NULL),
(2509, 11, 'CODE-11', 0, 'Activo', NULL, NULL),
(2510, 12, 'CODE-12', 0, 'Activo', NULL, NULL),
(2511, 13, 'CODE-13', 0, 'Activo', NULL, NULL),
(2512, 14, 'CODE-14', 0, 'Activo', NULL, NULL),
(2513, 15, 'CODE-15', 0, 'Activo', NULL, NULL),
(2514, 16, 'CODE-16', 0, 'Activo', NULL, NULL),
(2515, 17, 'CODE-17', 0, 'Activo', NULL, NULL),
(2516, 18, 'CODE-18', 0, 'Activo', NULL, NULL),
(2517, 19, 'CODE-19', 0, 'Activo', NULL, NULL),
(2518, 20, 'CODE-20', 0, 'Activo', NULL, NULL),
(2519, 21, 'CODE-21', 0, 'Activo', NULL, NULL),
(2520, 22, 'CODE-22', 0, 'Activo', NULL, NULL),
(2521, 23, 'CODE-23', 0, 'Activo', NULL, NULL),
(2522, 24, 'CODE-24', 0, 'Activo', NULL, NULL),
(2523, 25, 'CODE-25', 0, 'Activo', NULL, NULL),
(2524, 26, 'CODE-26', 0, 'Activo', NULL, NULL),
(2525, 27, 'CODE-27', 0, 'Activo', NULL, NULL),
(2526, 28, 'CODE-28', 0, 'Activo', NULL, NULL),
(2527, 29, 'CODE-29', 0, 'Activo', NULL, NULL),
(2528, 30, 'CODE-30', 0, 'Activo', NULL, NULL),
(2529, 31, 'CODE-31', 0, 'Activo', NULL, NULL),
(2530, 32, 'CODE-32', 0, 'Activo', NULL, NULL),
(2531, 33, 'CODE-33', 0, 'Activo', NULL, NULL),
(2532, 34, 'CODE-34', 0, 'Activo', NULL, NULL),
(2533, 35, 'CODE-35', 0, 'Activo', NULL, NULL),
(2534, 36, 'CODE-36', 0, 'Activo', NULL, NULL),
(2535, 37, 'CODE-37', 0, 'Activo', NULL, NULL),
(2536, 38, 'CODE-38', 0, 'Activo', NULL, NULL),
(2537, 39, 'CODE-39', 0, 'Activo', NULL, NULL),
(2538, 40, 'CODE-40', 0, 'Activo', NULL, NULL),
(2539, 41, 'CODE-41', 0, 'Activo', NULL, NULL),
(2540, 42, 'CODE-42', 0, 'Activo', NULL, NULL),
(2541, 43, 'CODE-43', 0, 'Activo', NULL, NULL),
(2542, 44, 'CODE-44', 0, 'Activo', NULL, NULL),
(2543, 45, 'CODE-45', 0, 'Activo', NULL, NULL),
(2544, 46, 'CODE-46', 0, 'Activo', NULL, NULL),
(2545, 47, 'CODE-47', 0, 'Activo', NULL, NULL),
(2546, 48, 'CODE-48', 0, 'Activo', NULL, NULL),
(2547, 49, 'CODE-49', 0, 'Activo', NULL, NULL),
(2548, 50, 'CODE-50', 0, 'Activo', NULL, NULL),
(2549, 51, 'CODE-51', 0, 'Activo', NULL, NULL),
(2550, 52, 'CODE-52', 0, 'Activo', NULL, NULL),
(2551, 53, 'CODE-53', 0, 'Activo', NULL, NULL),
(2552, 54, 'CODE-54', 0, 'Activo', NULL, NULL),
(2553, 55, 'CODE-55', 0, 'Activo', NULL, NULL),
(2554, 56, 'CODE-56', 0, 'Activo', NULL, NULL),
(2555, 57, 'CODE-57', 0, 'Activo', NULL, NULL),
(2556, 58, 'CODE-58', 0, 'Activo', NULL, NULL),
(2557, 59, 'CODE-59', 0, 'Activo', NULL, NULL),
(2558, 60, 'CODE-60', 0, 'Activo', NULL, NULL),
(2559, 61, 'CODE-61', 0, 'Activo', NULL, NULL),
(2560, 62, 'CODE-62', 0, 'Activo', NULL, NULL),
(2561, 63, 'CODE-63', 0, 'Activo', NULL, NULL),
(2562, 64, 'CODE-64', 0, 'Activo', NULL, NULL),
(2563, 65, 'CODE-65', 0, 'Activo', NULL, NULL),
(2564, 66, 'CODE-66', 0, 'Activo', NULL, NULL),
(2565, 67, 'CODE-67', 0, 'Activo', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `compra`
--

CREATE TABLE `compra` (
  `IdCompra` int(6) NOT NULL,
  `FechaCompra` date NOT NULL,
  `Total` int(6) NOT NULL,
  `IdCliente` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cumple`
--

CREATE TABLE `cumple` (
  `Id` int(11) NOT NULL,
  `IdCliente` int(11) DEFAULT NULL,
  `FechaLlamada` date DEFAULT NULL,
  `Vence` date DEFAULT NULL,
  `Vencido` varchar(5) DEFAULT 'NO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tarea`
--

CREATE TABLE `tarea` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(220) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estado` enum('pendiente','completada') NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tarea`
--

INSERT INTO `tarea` (`id`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(69, 'lol', 'completada', '2025-06-25 19:56:19'),
(70, '11111111111111111111', 'pendiente', '2025-06-26 13:53:26'),
(71, '1111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111', 'pendiente', '2025-06-26 13:56:58'),
(72, 'hola', 'pendiente', '2025-06-27 16:22:39'),
(73, 'prueba', 'pendiente', '2025-06-27 16:23:14'),
(74, 'hola', 'completada', '2025-06-29 20:59:20'),
(75, '2222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222222223', 'completada', '2025-07-10 11:53:32'),
(77, 'aa', 'pendiente', '2025-07-10 17:59:11');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `Id` int(4) NOT NULL,
  `Usuario` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Contrasena` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `Rol` enum('Administrador','Salonero','Propietario') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`Id`, `Usuario`, `Contrasena`, `Rol`) VALUES
(1005, 'CHRISTIANGALLO', '$2y$10$8y6NGmtK8dt.UeYy1kGhNOE4FE4Sh7VkJT637yoUpaOaVAWnrZ/Na', 'Propietario'),
(1006, 'ADMIN', '$2y$10$1R4g89Q1q/1373ifxsCjPuAzcRGMAkVScmPxatN4AQJCP0KGlGxD6', 'Administrador'),
(1007, 'SALONERO', '$2y$10$FvfD6TpuDE0/cWYeP2jntehlEr8fNzuaa3.yRnJoVgJbvN97YmAbm', 'Salonero'),
(1008, 'PEPEGRILLO', '$2y$10$blSqiZ/dRgO/pazFsDKurulHHKWnnUO9H/WOr2stTuBuGPrzuYITq', 'Propietario'),
(1010, '1223', '$2y$10$73xQfNHQrbOHmNVXayKS4eXYAoJFemXR09uvezF3wO/L5Mc3ppoWe', 'Administrador'),
(1011, 'CHRISTIAN EDUARDO PANIAGU', '$2y$10$xFhNezxhZ5qfvzYFcLNcBOJvn8M0EuCy50VQP4k1snPVty8UrJrIq', 'Propietario'),
(1012, 'PRUEBA', '$2y$10$pv5Vc/qmvlJ55cRcXETOieJBcZEp5aoZsV7jxY8Zvz0io8D3dEgMC', 'Salonero'),
(1013, 'PRUEBAA', '$2y$10$/nBpj22Vrqp2nvqfT.64muW.sVLSiUoVhAnk6TI3Je3k.7knYNdbC', 'Salonero'),
(1014, 'PRUEBAAA', '$2y$10$sRyChUQVj8Lpx2iPkt20i.6mF0eyLFj/cvXmarOnT670zCxpaNm8G', 'Administrador'),
(1015, 'REYMAN1', '$2y$10$F2VRB8/qaVjn2e5UfeGc7OpnkYMmxjKxikiL8RUdjto4/xudOs1jW', 'Administrador'),
(1016, 'REYMAN2', '$2y$10$lAcHfWiLBnwbyObM9rQ0fu5iEfMhDM0xlKoCAcK/aXfcHEzc7e5S2', 'Salonero'),
(1017, 'REYMAN3', '$2y$10$K/y5w6RsKRvUJkxay/ZQr.x3xPr/p258QgCgwMEL9rA7UXiJkEE7e', 'Propietario'),
(1018, 'PRUEBADEFINITIVA', '$2y$10$q5itKCf/xV3BnSHWmaqcdepoai8jc6vcXEtrDtXu1qQMv1eZzJxHO', 'Administrador');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bitacora`
--
ALTER TABLE `bitacora`
  ADD KEY `IdUsuario` (`IdUsuario`);

--
-- Indexes for table `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`Id`) USING BTREE;

--
-- Indexes for table `codigo`
--
ALTER TABLE `codigo`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IdCliente` (`IdCliente`);

--
-- Indexes for table `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`IdCompra`) USING BTREE,
  ADD KEY `idCliente` (`IdCliente`) USING BTREE;

--
-- Indexes for table `cumple`
--
ALTER TABLE `cumple`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IdCliente` (`IdCliente`);

--
-- Indexes for table `tarea`
--
ALTER TABLE `tarea`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1019;

--
-- AUTO_INCREMENT for table `cliente`
--
ALTER TABLE `cliente`
  MODIFY `Id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `codigo`
--
ALTER TABLE `codigo`
  MODIFY `Id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2566;

--
-- AUTO_INCREMENT for table `compra`
--
ALTER TABLE `compra`
  MODIFY `IdCompra` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28540;

--
-- AUTO_INCREMENT for table `cumple`
--
ALTER TABLE `cumple`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tarea`
--
ALTER TABLE `tarea`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `Id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1019;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `codigo`
--
ALTER TABLE `codigo`
  ADD CONSTRAINT `codigo_ibfk_1` FOREIGN KEY (`IdCliente`) REFERENCES `cliente` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`IdCliente`) REFERENCES `cliente` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cumple`
--
ALTER TABLE `cumple`
  ADD CONSTRAINT `cumple_ibfk_1` FOREIGN KEY (`IdCliente`) REFERENCES `cliente` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
