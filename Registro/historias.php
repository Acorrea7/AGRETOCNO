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
            // Crear nuevo registro de historia reproductiva
            $id_animal = $_POST['id_animal'];
            $tipo_evento = $_POST['tipo_evento'];
            $fecha_evento = $_POST['fecha_evento'];
            $detalles = $_POST['detalles'];
            $sql = "INSERT INTO Historias_Reproductivas (id_animal, tipo_evento, fecha_evento, detalles) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $id_animal, $tipo_evento, $fecha_evento, $detalles);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['update'])) {
            // Actualizar registro de historia reproductiva existente
            $id_historia = $_POST['id_historia'];
            $id_animal = $_POST['id_animal'];
            $tipo_evento = $_POST['tipo_evento'];
            $fecha_evento = $_POST['fecha_evento'];
            $detalles = $_POST['detalles'];
            $sql = "UPDATE Historias_Reproductivas SET id_animal=?, tipo_evento=?, fecha_evento=?, detalles=? WHERE id_historia=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssi", $id_animal, $tipo_evento, $fecha_evento, $detalles, $id_historia);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            // Eliminar registro de historia reproductiva
            $id_historia = $_POST['id_historia'];
            $sql = "DELETE FROM Historias_Reproductivas WHERE id_historia=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_historia);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        echo "Acceso denegado. No tienes permisos para realizar esta acción.";
    }
}

// Consultar las historias reproductivas
$sql = "SELECT id_historia, id_animal, tipo_evento, fecha_evento, detalles FROM Historias_Reproductivas";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historias Reproductivas CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="panel-container">
        <header>
            <h1>Gestión de Historias Reproductivas</h1>
        </header>
        
        <form method="POST" action="historias.php">
            <input type="hidden" name="id_historia" id="id_historia">
            <label for="id_animal">ID Animal:</label>
            <input type="text" name="id_animal" id="id_animal" required>
            <label for="tipo_evento">Tipo de Evento:</label>
            <select name="tipo_evento" id="tipo_evento" required>
                <option value="Inseminación">Inseminación</option>
                <option value="Parto">Parto</option>
                <option value="Aborto">Aborto</option>
            </select>
            <label for="fecha_evento">Fecha del Evento:</label>
            <input type="date" name="fecha_evento" id="fecha_evento" required>
            <label for="detalles">Detalles:</label>
            <textarea name="detalles" id="detalles" required></textarea>
            <button type="submit" name="create">Crear Historia</button>
            <button type="submit" name="update">Actualizar Historia</button>
            <button type="submit" name="delete">Eliminar Historia</button>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>ID Historia</th>
                    <th>ID Animal</th>
                    <th>Tipo de Evento</th>
                    <th>Fecha del Evento</th>
                    <th>Detalles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row["id_historia"]) . "</td>
                            <td>" . htmlspecialchars($row["id_animal"]) . "</td>
                            <td>" . htmlspecialchars($row["tipo_evento"]) . "</td>
                            <td>" . htmlspecialchars($row["fecha_evento"]) . "</td>
                            <td>" . htmlspecialchars($row["detalles"]) . "</td>
                            <td>
                                <button onclick='editHistoria(" . htmlspecialchars(json_encode($row)) . ")'>Editar</button>
                                <form method='POST' action='historias.php' style='display:inline;'>
                                    <input type='hidden' name='id_historia' value='" . htmlspecialchars($row["id_historia"]) . "'>
                                    <button type='submit' name='delete'>Eliminar</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No hay historias reproductivas registradas.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <footer>
            <a href="panel.php" class="logout-btn">Volver al panel</a>
        </footer>
    </div>
    
    <script>
    function editHistoria(historia) {
        document.getElementById('id_historia').value = historia.id_historia;
        document.getElementById('id_animal').value = historia.id_animal;
        document.getElementById('tipo_evento').value = historia.tipo_evento;
        document.getElementById('fecha_evento').value = historia.fecha_evento;
        document.getElementById('detalles').value = historia.detalles;
    }
    </script>
</body>
</html>
