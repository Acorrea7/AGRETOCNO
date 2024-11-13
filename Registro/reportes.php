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
$log = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($rol == 'Administrador' || $rol == 'Ganadero' || $rol == 'Veterinario') {
        // Operaciones CRUD
        if (isset($_POST['create'])) {
            // Crear nuevo reporte
            $id_usuario = $_POST['id_usuario'];
            $id_animal = $_POST['id_animal'];
            $tipo_reporte = $_POST['tipo_reporte'];
            $contenido = $_POST['contenido'];
            $sql = "INSERT INTO Reportes (id_usuario, id_animal, tipo_reporte, contenido) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiss", $id_usuario, $id_animal, $tipo_reporte, $contenido);
            $stmt->execute();
            $stmt->close();
            $log[] = "Reporte creado.";
        } elseif (isset($_POST['update'])) {
            // Actualizar reporte existente
            $id_reporte = $_POST['id_reporte'];
            $id_usuario = $_POST['id_usuario'];
            $id_animal = $_POST['id_animal'];
            $tipo_reporte = $_POST['tipo_reporte'];
            $contenido = $_POST['contenido'];
            $sql = "UPDATE Reportes SET id_usuario=?, id_animal=?, tipo_reporte=?, contenido=? WHERE id_reporte=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissi", $id_usuario, $id_animal, $tipo_reporte, $contenido, $id_reporte);
            $stmt->execute();
            $stmt->close();
            $log[] = "Reporte actualizado.";
        } elseif (isset($_POST['delete'])) {
            // Eliminar reporte
            $id_reporte = $_POST['id_reporte'];
            $sql = "DELETE FROM Reportes WHERE id_reporte=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_reporte);
            $stmt->execute();
            $stmt->close();
            $log[] = "Reporte eliminado.";
        }
    } else {
        echo "Acceso denegado. No tienes permisos para realizar esta acción.";
    }
}

// Consultar los reportes
$sql = "SELECT id_reporte, id_usuario, id_animal, tipo_reporte, contenido, fecha_hora FROM Reportes";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="panel-container">
        <header>
            <h1>Gestión de Reportes</h1>
        </header>
        
        <form method="POST" action="reportes.php">
            <input type="hidden" name="id_reporte" id="id_reporte">
            <label for="id_usuario">ID Usuario:</label>
            <input type="text" name="id_usuario" id="id_usuario" required>
            <label for="id_animal">ID Animal:</label>
            <input type="text" name="id_animal" id="id_animal">
            <label for="tipo_reporte">Tipo de Reporte:</label>
            <select name="tipo_reporte" id="tipo_reporte" required>
                <option value="Salud">Salud</option>
                <option value="Reproductivo">Reproductivo</option>
                <option value="Producción">Producción</option>
            </select>
            <label for="contenido">Contenido:</label>
            <textarea name="contenido" id="contenido" required></textarea>
            <button type="submit" name="create">Crear Reporte</button>
            <button type="submit" name="update">Actualizar Reporte</button>
            <button type="submit" name="delete">Eliminar Reporte</button>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>ID Reporte</th>
                    <th>ID Usuario</th>
                    <th>ID Animal</th>
                    <th>Tipo de Reporte</th>
                    <th>Contenido</th>
                    <th>Fecha y Hora</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row["id_reporte"]) . "</td>
                            <td>" . htmlspecialchars($row["id_usuario"]) . "</td>
                            <td>" . htmlspecialchars($row["id_animal"]) . "</td>
                            <td>" . htmlspecialchars($row["tipo_reporte"]) . "</td>
                            <td>" . htmlspecialchars($row["contenido"]) . "</td>
                            <td>" . htmlspecialchars($row["fecha_hora"]) . "</td>
                            <td>
                                <button onclick='editReporte(" . htmlspecialchars(json_encode($row)) . ")'>Editar</button>
                                <form method='POST' action='reportes.php' style='display:inline;'>
                                    <input type='hidden' name='id_reporte' value='" . htmlspecialchars($row["id_reporte"]) . "'>
                                    <button type='submit' name='delete'>Eliminar</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No hay reportes registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <footer>
            <a href="panel.php" class="logout-btn">Volver al panel</a>
        </footer>

        <h2>Registro de Acciones</h2>
        <ul>
            <?php
            foreach ($log as $entry) {
                echo "<li>" . htmlspecialchars($entry) . "</li>";
            }
            ?>
        </ul>
        
        <button onclick="generarReporte()">Generar Reporte de Acciones</button>
    </div>
    
    <script>
    function editReporte(reporte) {
        document.getElementById('id_reporte').value = reporte.id_reporte;
        document.getElementById('id_usuario').value = reporte.id_usuario;
        document.getElementById('id_animal').value = reporte.id_animal;
        document.getElementById('tipo_reporte').value = reporte.tipo_reporte;
        document.getElementById('contenido').value = reporte.contenido;
    }

    function generarReporte() {
        let log = <?php echo json_encode($log); ?>;
        let reporteContenido = "Reporte de Acciones:\n\n";
        log.forEach(function(entry) {
            reporteContenido += entry + "\n";
        });
        let blob = new Blob([reporteContenido], { type: "text/plain;charset=utf-8" });
        let link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "reporte_acciones.txt";
        link.click();
    }
    </script>
</body>
</html>
