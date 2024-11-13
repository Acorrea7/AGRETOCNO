<?php
session_start();

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['email']) || !isset($_SESSION['rol'])) {
    header("Location: index.php");
    exit();
}

// Obtener el rol del usuario desde la sesión
$role = $_SESSION['rol'];

// Para depuración, imprime el rol del usuario (puedes quitar esto una vez verificado)
// echo "Rol del usuario: " . htmlspecialchars($role);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario</title>
    <link rel="stylesheet" href="panel.css">
</head>
<body>
    <div class="panel-container">
        <header>
            <h1>Bienvenido al Panel de AGROTECNO</h1>
        </header>
        <nav>
        <ul class="menu">
    <?php if ($role == 'Administrador' || $role == 'Ganadero' || $role == 'Veterinario'): ?>
        <li><a href="alertas.php">Alertas</a></li>
    <?php endif; ?>

    <?php if ($role == 'Administrador' || $role == 'Ganadero'): ?>
        <li><a href="animales.php">Animales</a></li>
    <?php endif; ?>

    <?php if ($role == 'Administrador' || $role == 'Veterinario'): ?>
        <li><a href="historias.php">Historias</a></li>
    <?php endif; ?>

    <?php if ($role == 'Administrador' || $role == 'Ganadero' || $role == 'Veterinario'): ?>
        <li><a href="reportes.php">Reportes</a></li>
    <?php endif; ?>

    <?php if ($role == 'Administrador'): ?>
        <li><a href="produccion.php">Producción</a></li>
        <li><a href="sensores.php">Sensores</a></li>
        <li><a href="usuarios.php">Usuarios</a></li>
    <?php endif; ?>
</ul>

        </nav>
        <footer>
            <a href="logout.php" class="logout-btn">Cerrar sesión</a>
        </footer>
    </div>
</body>
</html>
