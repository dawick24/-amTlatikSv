<?php
require_once '../../../db/database.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['email']); 
    $telefono = trim($_POST['telefono']);
    $region = trim($_POST['region']); 
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => '❌ Las contraseñas no coinciden.']);
        exit;
    }

    // Verificar si el correo ya está registrado
    $stmtCorreo = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo LIMIT 1");
    $stmtCorreo->execute([':correo' => $correo]);
    if ($stmtCorreo->fetch()) {
        echo json_encode(['success' => false, 'message' => '❌ El correo ya está registrado.']);
        exit;
    }

    // Verificar si el teléfono ya está registrado
    $stmtTelefono = $pdo->prepare("SELECT id FROM usuarios WHERE telefono = :telefono LIMIT 1");
    $stmtTelefono->execute([':telefono' => $telefono]);
    if ($stmtTelefono->fetch()) {
        echo json_encode(['success' => false, 'message' => '❌ El teléfono ya está registrado.']);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO usuarios (nombre, apellido, correo, telefono, region, password) 
                VALUES (:nombre, :apellido, :correo, :telefono, :region, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':correo' => $correo,
            ':telefono' => $telefono,
            ':region' => $region,
            ':password' => $passwordHash
        ]);

        echo json_encode([
            'success' => true,
            'user' => [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'correo' => $correo,
                'telefono' => $telefono,
                'region' => $region
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '❌ Error al registrar usuario: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
}
