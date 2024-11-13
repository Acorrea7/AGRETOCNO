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

// Operaciones CRUD
$rol = $_SESSION['rol'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($rol == 'Administrador' || $rol == 'Ganadero' || $rol == 'Veterinario') {
        if (isset($_POST['create'])) {
            // Crear nueva alerta
            $id_animal = $_POST['id_animal'];
            $tipo_alerta = $_POST['tipo_alerta'];
            $descripcion = $_POST['descripcion'];
            $fecha_hora = $_POST['fecha_hora'];
            $estado = $_POST['estado'];
            $sql = "INSERT INTO alertas (id_animal, tipo_alerta, descripcion, fecha_hora, estado) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issss", $id_animal, $tipo_alerta, $descripcion, $fecha_hora, $estado);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['update'])) {
            // Actualizar alerta existente
            $id_alerta = $_POST['id_alerta'];
            $id_animal = $_POST['id_animal'];
            $tipo_alerta = $_POST['tipo_alerta'];
            $descripcion = $_POST['descripcion'];
            $fecha_hora = $_POST['fecha_hora'];
            $estado = $_POST['estado'];
            $sql = "UPDATE alertas SET id_animal=?, tipo_alerta=?, descripcion=?, fecha_hora=?, estado=? WHERE id_alerta=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssi", $id_animal, $tipo_alerta, $descripcion, $fecha_hora, $estado, $id_alerta);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            // Eliminar alerta
            $id_alerta = $_POST['id_alerta'];
            $sql = "DELETE FROM alertas WHERE id_alerta=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_alerta);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Consultar las alertas
$sql = "SELECT id_alerta, id_animal, tipo_alerta, descripcion, fecha_hora, estado FROM alertas";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="panel-container">
        <header>
            <h1>Gestión de Alertas</h1>
        </header>
        
        <form method="POST" action="alertas.php">
            <input type="hidden" name="id_alerta" id="id_alerta">
            <label for="id_animal">ID Animal:</label>
            <input type="text" name="id_animal" id="id_animal" required>
            <label for="tipo_alerta">Tipo de Alerta:</label>
            <input type="text" name="tipo_alerta" id="tipo_alerta" required>
            <label for="descripcion">Descripción:</label>
            <input type="text" name="descripcion" id="descripcion" required>
            <label for="fecha_hora">Fecha y Hora:</label>
            <input type="datetime-local" name="fecha_hora" id="fecha_hora" required>
            <label for="estado">Estado:</label>
            <input type="text" name="estado" id="estado" required>
            <button type="submit" name="create">Crear Alerta</button>
            <button type="submit" name="update">Actualizar Alerta</button>
            <button type="submit" name="delete">Eliminar Alerta</button>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>ID Alerta</th>
                    <th>ID Animal</th>
                    <th>Tipo de Alerta</th>
                    <th>Descripción</th>
                    <th>Fecha y Hora</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row["id_alerta"]) . "</td>
                            <td>" . htmlspecialchars($row["id_animal"]) . "</td>
                            <td>" . htmlspecialchars($row["tipo_alerta"]) . "</td>
                            <td>" . htmlspecialchars($row["descripcion"]) . "</td>
                            <td>" . htmlspecialchars($row["fecha_hora"]) . "</td>
                            <td>" . htmlspecialchars($row["estado"]) . "</td>
                            <td>
                                <button onclick='editAlert(" . htmlspecialchars(json_encode($row)) . ")'>Editar</button>
                                <form method='POST' action='alertas.php' style='display:inline;'>
                                    <input type='hidden' name='id_alerta' value='" . htmlspecialchars($row["id_alerta"]) . "'>
                                    <button type='submit' name='delete'>Eliminar</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No hay alertas registradas.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <footer>
            <a href="panel.php" class="logout-btn">Volver al panel</a>
        </footer>
    </div>
    
    <script>
    function editAlert(alerta) {
        document.getElementById('id_alerta').value = alerta.id_alerta;
        document.getElementById('id_animal').value = alerta.id_animal;
        document.getElementById('tipo_alerta').value = alerta.tipo_alerta;
        document.getElementById('descripcion').value = alerta.descripcion;
        document.getElementById('fecha_hora').value = alerta.fecha_hora;
        document.getElementById('estado').value = alerta.estado;
    }
    </script>
</body>
</html>
