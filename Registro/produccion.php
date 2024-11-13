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
            // Crear nuevo registro de producción de leche
            $id_animal = $_POST['id_animal'];
            $cantidad_leche = $_POST['cantidad_leche'];
            $fecha = $_POST['fecha'];
            $sql = "INSERT INTO Produccion_Leche (id_animal, cantidad_leche, fecha) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isd", $id_animal, $cantidad_leche, $fecha);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['update'])) {
            // Actualizar registro de producción de leche existente
            $id_produccion = $_POST['id_produccion'];
            $id_animal = $_POST['id_animal'];
            $cantidad_leche = $_POST['cantidad_leche'];
            $fecha = $_POST['fecha'];
            $sql = "UPDATE Produccion_Leche SET id_animal=?, cantidad_leche=?, fecha=? WHERE id_produccion=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isdi", $id_animal, $cantidad_leche, $fecha, $id_produccion);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            // Eliminar registro de producción de leche
            $id_produccion = $_POST['id_produccion'];
            $sql = "DELETE FROM Produccion_Leche WHERE id_produccion=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_produccion);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        echo "Acceso denegado. No tienes permisos para realizar esta acción.";
    }
}

// Consultar la producción de leche
$sql = "SELECT id_produccion, id_animal, cantidad_leche, fecha FROM Produccion_Leche";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producción de Leche CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="panel-container">
        <header>
            <h1>Gestión de Producción de Leche</h1>
        </header>
        
        <form method="POST" action="produccion.php">
            <input type="hidden" name="id_produccion" id="id_produccion">
            <label for="id_animal">ID Animal:</label>
            <input type="text" name="id_animal" id="id_animal" required>
            <label for="cantidad_leche">Cantidad de Leche (litros):</label>
            <input type="number" step="0.01" name="cantidad_leche" id="cantidad_leche" required>
            <label for="fecha">Fecha:</label>
            <input type="date" name="fecha" id="fecha" required>
            <button type="submit" name="create">Crear Registro</button>
            <button type="submit" name="update">Actualizar Registro</button>
            <button type="submit" name="delete">Eliminar Registro</button>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>ID Producción</th>
                    <th>ID Animal</th>
                    <th>Cantidad de Leche (litros)</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row["id_produccion"]) . "</td>
                            <td>" . htmlspecialchars($row["id_animal"]) . "</td>
                            <td>" . htmlspecialchars($row["cantidad_leche"]) . "</td>
                            <td>" . htmlspecialchars($row["fecha"]) . "</td>
                            <td>
                                <button onclick='editProduccion(" . htmlspecialchars(json_encode($row)) . ")'>Editar</button>
                                <form method='POST' action='produccion.php' style='display:inline;'>
                                    <input type='hidden' name='id_produccion' value='" . htmlspecialchars($row["id_produccion"]) . "'>
                                    <button type='submit' name='delete'>Eliminar</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay registros de producción de leche.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <footer>
            <a href="panel.php" class="logout-btn">Volver al panel</a>
        </footer>
    </div>
    
    <script>
    function editProduccion(produccion) {
        document.getElementById('id_produccion').value = produccion.id_produccion;
        document.getElementById('id_animal').value = produccion.id_animal;
        document.getElementById('cantidad_leche').value = produccion.cantidad_leche;
        document.getElementById('fecha').value = produccion.fecha;
    }
    </script>
</body>
</html>
