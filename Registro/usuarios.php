<?php
session_start();

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['email']) || !isset($_SESSION['rol'])) {
    header("Location: index.php");
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

// Consultar los usuarios
$sql = "SELECT id_usuario, nombre, apellido, email, rol FROM usuarios"; // Ajusta los nombres de las columnas según sea necesario
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios Registrados</title>
    <link rel="stylesheet" href="panel.css">
</head>
<body>
    <div class="panel-container">
        <header>
            <h1>Usuarios Registrados</h1>
        </header>
        <table>
            <thead>
                <tr>
                    <th>id_usuario</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Salida de datos de cada fila
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row["id_usuario"]) . "</td>
                            <td>" . htmlspecialchars($row["nombre"]) . "</td>
                            <td>" . htmlspecialchars($row["apellido"]) . "</td>
                            <td>" . htmlspecialchars($row["email"]) . "</td>
                            <td>" . htmlspecialchars($row["rol"]) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay usuarios registrados.</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
        <footer>
            <a href="panel.php" class="logout-btn">Volver al panel</a>
        </footer>
    </div>
</body>
</html>
