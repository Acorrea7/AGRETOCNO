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
    if ($rol == 'Administrador' || $rol == 'Ganadero') {
        if (isset($_POST['create'])) {
            // Crear nuevo registro de animal
            $id_ganadero = $_POST['id_ganadero'];
            $nombre = $_POST['nombre'];
            $fecha_nacimiento = $_POST['fecha_nacimiento'];
            $sexo = $_POST['sexo'];
            $raza = $_POST['raza'];
            $estado_reproductivo = $_POST['estado_reproductivo'];
            $sql = "INSERT INTO Animales (id_ganadero, nombre, fecha_nacimiento, sexo, raza, estado_reproductivo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssss", $id_ganadero, $nombre, $fecha_nacimiento, $sexo, $raza, $estado_reproductivo);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['update'])) {
            // Actualizar registro de animal existente
            $id_animal = $_POST['id_animal'];
            $id_ganadero = $_POST['id_ganadero'];
            $nombre = $_POST['nombre'];
            $fecha_nacimiento = $_POST['fecha_nacimiento'];
            $sexo = $_POST['sexo'];
            $raza = $_POST['raza'];
            $estado_reproductivo = $_POST['estado_reproductivo'];
            $sql = "UPDATE Animales SET id_ganadero=?, nombre=?, fecha_nacimiento=?, sexo=?, raza=?, estado_reproductivo=? WHERE id_animal=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssi", $id_ganadero, $nombre, $fecha_nacimiento, $sexo, $raza, $estado_reproductivo, $id_animal);
            $stmt->execute();
            $stmt->close();
        } elseif (isset($_POST['delete'])) {
            // Eliminar registro de animal
            $id_animal = $_POST['id_animal'];
            $sql = "DELETE FROM Animales WHERE id_animal=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_animal);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Consultar los animales
$sql = "SELECT id_animal, id_ganadero, nombre, fecha_nacimiento, sexo, raza, estado_reproductivo FROM Animales";
$result = $conn->query($sql);

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animales CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="panel-container">
        <header>
            <h1>Gestión de Animales</h1>
        </header>
        
        <form method="POST" action="crud_animales.php">
            <input type="hidden" name="id_animal" id="id_animal">
            <label for="id_ganadero">ID Ganadero:</label>
            <input type="text" name="id_ganadero" id="id_ganadero" required>
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" required>
            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required>
            <label for="sexo">Sexo:</label>
            <select name="sexo" id="sexo" required>
                <option value="Macho">Macho</option>
                <option value="Hembra">Hembra</option>
            </select>
            <label for="raza">Raza:</label>
            <input type="text" name="raza" id="raza" required>
            <label for="estado_reproductivo">Estado Reproductivo:</label>
            <select name="estado_reproductivo" id="estado_reproductivo" required>
                <option value="Celo">Celo</option>
                <option value="Gestación">Gestación</option>
                <option value="Parto">Parto</option>
                <option value="Normal">Normal</option>
            </select>
            <button type="submit" name="create">Crear Animal</button>
            <button type="submit" name="update">Actualizar Animal</button>
            <button type="submit" name="delete">Eliminar Animal</button>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>ID Animal</th>
                    <th>ID Ganadero</th>
                    <th>Nombre</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Sexo</th>
                    <th>Raza</th>
                    <th>Estado Reproductivo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row["id_animal"]) . "</td>
                            <td>" . htmlspecialchars($row["id_ganadero"]) . "</td>
                            <td>" . htmlspecialchars($row["nombre"]) . "</td>
                            <td>" . htmlspecialchars($row["fecha_nacimiento"]) . "</td>
                            <td>" . htmlspecialchars($row["sexo"]) . "</td>
                            <td>" . htmlspecialchars($row["raza"]) . "</td>
                            <td>" . htmlspecialchars($row["estado_reproductivo"]) . "</td>
                            <td>
                                <button onclick='editAnimal(" . htmlspecialchars(json_encode($row)) . ")'>Editar</button>
                                <form method='POST' action='crud_animales.php' style='display:inline;'>
                                    <input type='hidden' name='id_animal' value='" . htmlspecialchars($row["id_animal"]) . "'>
                                    <button type='submit' name='delete'>Eliminar</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No hay animales registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <footer>
            <a href="panel.php" class="logout-btn">Volver al panel</a>
        </footer>
    </div>
    
    <script>
    function editAnimal(animal) {
        document.getElementById('id_animal').value = animal.id_animal;
        document.getElementById('id_ganadero').value = animal.id_ganadero;
        document.getElementById('nombre').value = animal.nombre;
        document.getElementById('fecha_nacimiento').value = animal.fecha_nacimiento;
        document.getElementById('sexo').value = animal.sexo;
        document.getElementById('raza').value = animal.raza;
        document.getElementById('estado_reproductivo').value = animal.estado_reproductivo;
    }
    </script>
</body>
</html>
