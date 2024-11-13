<?php
// Inicia la sesión
session_start();

// Si el usuario ya está autenticado, redirigir al panel
if (isset($_SESSION['email'])) {
    header("Location: panel.php");
    exit();
}

// Configuración de la base de datos
$servername = "localhost";
$port = 3306;
$username = "root";
$password = "";
$dbname = "agrotecnosimplificada";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si se han enviado los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Consultar la contraseña y el rol del usuario
    $sql = "SELECT contraseña, rol FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password, $role);
        $stmt->fetch();

        // Verificar la contraseña
        if (password_verify($password, $hashed_password)) {
            // Guardar información en la sesión
            $_SESSION['email'] = $email;
            $_SESSION['rol'] = $role;

            // Redirigir al panel de usuario
            header("Location: panel.php");
            exit();
        } else {
            echo '<p style="color:red;">Contraseña incorrecta.</p>';
        }
    } else {
        echo '<p style="color:red;">Usuario no encontrado.</p>';
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AGROTECNO</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <img src="imagenes/agrotecno.png" alt="Agrotecno Logo" class="logo">
    <h1>Iniciar Sesión</h1>
    <form id="loginForm" method="POST" action="">
      <label for="email">Correo electrónico:</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Contraseña:</label>
      <input type="password" id="password" name="password" required>

      <div class="btn-container">
        <button class="btn-login" type="submit">Iniciar sesión</button>
        <a href="Register.php">
            <button class="btn-register" type="button">Registrarse</button>
        </a>
      </div>
    </form>
</div>
</body>
</html>
