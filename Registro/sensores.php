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

// Verificar rol
$rol = $_SESSION['rol'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($rol == 'Administrador' || $rol == 'Ganadero' || $rol == 'Veterinario') {
        // Operaciones CRUD
        if (isset($_POST['create'])) {
            // Crear nuevo registro de sensor
            $tipo_sensor = $_POST['tipo_sensor'];
            $descripcion = $_POST['descripcion'];
            $fecha_instalacion = $_POST['fecha_instalacion'];
            $id_animal = $_POST['id_animal'];
            $sql = "INSERT INTO Sensores (tipo_sensor, descripcion, fecha_instalacion, id_animal) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $tipo_sensor, $descripcion, $fecha_instalacion, $id_animal);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['update'])) {
            // Actualizar registro de sensor existente
            $id_sensor = $_POST['id_sensor'];
            $tipo_sensor = $_POST['tipo_sensor'];
            $descripcion = $_POST['descripcion'];
            $fecha_instalacion = $_POST['fecha_instalacion'];
            $id_animal = $_POST['id_animal'];
            $sql = "UPDATE Sensores SET tipo_sensor=?, descripcion=?, fecha_instalacion=?, id_animal=? WHERE id_sensor=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $tipo_sensor, $descripcion, $fecha_instalacion, $id_animal, $id_sensor);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            // Eliminar registro de sensor
            $id_sensor = $_POST['id_sensor'];
            $sql = "DELETE FROM Sensores WHERE id_sensor=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_sensor);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        echo "Acceso denegado. No tienes permisos para realizar esta acción.";
    }
}

// Consultar los sensores
$sql = "SELECT id_sensor, tipo_sensor, descripcion, fecha_instalacion, id_animal FROM Sensores";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensores CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="panel-container">
        <header>
            <h1>Gestión de Sensores</h1>
        </header>
        
        <form method="POST" action="sensores.php">
            <input type="hidden" name="id_sensor" id="id_sensor">
            <label for="tipo_sensor">Tipo de Sensor:</label>
            <select name="tipo_sensor" id="tipo_sensor" required>
                <option value="Temperatura">Temperatura</option>
                <option value="Ritmo Cardiaco">Ritmo Cardiaco</option>
                <option value="Actividad">Actividad</option>
            </select>
            <label for="descripcion">Descripción:</label>
            <input type="text" name="descripcion" id="descripcion">
            <label for="fecha_instalacion">Fecha de Instalación:</label>
            <input type="date" name="fecha_instalacion" id="fecha_instalacion" required>
            <label for="id_animal">ID Animal:</label>
            <input type="text" name="id_animal" id="id_animal">
            <button type="submit" name="create">Crear Sensor</button>
            <button type="submit" name="update">Actualizar Sensor</button>
            <button type="submit" name="delete">Eliminar Sensor</button>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>ID Sensor</th>
                    <th>Tipo de Sensor</th>
                    <th>Descripción</th>
                    <th>Fecha de Instalación</th>
                    <th>ID Animal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row["id_sensor"]) . "</td>
                            <td>" . htmlspecialchars($row["tipo_sensor"]) . "</td>
                            <td>" . htmlspecialchars($row["descripcion"]) . "</td>
                            <td>" . htmlspecialchars($row["fecha_instalacion"]) . "</td>
                            <td>" . htmlspecialchars($row["id_animal"]) . "</td>
                            <td>
                                <button onclick='editSensor(" . htmlspecialchars(json_encode($row)) . ")'>Editar</button>
                                <form method='POST' action='sensores.php' style='display:inline;'>
                                    <input type='hidden' name='id_sensor' value='" . htmlspecialchars($row["id_sensor"]) . "'>
                                    <button type='submit' name='delete'>Eliminar</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No hay sensores registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <footer>
            <a href="panel.php" class="logout-btn">Volver al panel</a>
        </footer>
    </div>
    
    <script>
    function editSensor(sensor) {
        document.getElementById('id_sensor').value = sensor.id_sensor;
        document.getElementById('tipo_sensor').value = sensor.tipo_sensor;
        document.getElementById('descripcion').value = sensor.descripcion;
        document.getElementById('fecha_instalacion').value = sensor.fecha_instalacion;
        document.getElementById('id_animal').value = sensor.id_animal;
    }
    </script>
</body>
</html>
