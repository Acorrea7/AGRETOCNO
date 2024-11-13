<?php
// Inicia la sesión
session_start();

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
    $name = trim($_POST['name'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');

    // Validar que todos los campos estén completos
    if ($name && $lastname && filter_var($email, FILTER_VALIDATE_EMAIL) && $password && $role) {
        // Verificar si el correo electrónico ya existe
        $checkEmailQuery = "SELECT email FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($checkEmailQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "Error: El correo electrónico ya está registrado. Por favor, usa otro correo.";
        } else {
            // Almacenar la contraseña de forma segura (hash)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insertar el nuevo usuario
            $insertQuery = "INSERT INTO usuarios (nombre, apellido, email, contraseña, rol) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sssss", $name, $lastname, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                echo "Usuario registrado correctamente con el rol: " . htmlspecialchars($role);
                header("Location: index.php");
                exit();
            } else {
                echo "Error al registrar usuario: " . $stmt->error;
            }
        }
        $stmt->close();
    } else {
        echo "Por favor completa todos los campos correctamente.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - AGROTECNO</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Registro de Usuario</h1>
    <form method="POST" action="">
        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" required>

        <label for="lastname">Apellido:</label>
        <input type="text" id="lastname" name="lastname" required>

        <label for="email">Email (Nombre de usuario):</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <label for="role">Rol:</label>
        <select id="role" name="role" required>
            <option value="ganadero">Ganadero</option>
            <option value="admin">Administrador</option>
            <option value="veterinario">Veterinario</option>
        </select>

        <button type="submit">Registrarse</button>
    </form>
</body>
</html>
