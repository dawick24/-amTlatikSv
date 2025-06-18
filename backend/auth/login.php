<?php
require '../../../db/database.php';

header('Content-Type: application/json');
session_start();

$response = ['success' => false, 'message' => ''];

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email']) || empty($data['password'])) {
    $response['message'] = 'Email y contraseña son obligatorios';
    echo json_encode($response);
    exit;
}

try {
    // Buscar usuario por correo
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch();

    if ($user && password_verify($data['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $response = [
            'success' => true,
            'message' => '¡Bienvenido ' . $user['nombre'] . '!',
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'correo' => $user['correo']
            ]
        ];
    } else {
        $response['message'] = 'Credenciales incorrectas';
    }

} catch (PDOException $e) {
    $response['message'] = 'Error en el servidor: ' . $e->getMessage();
}

echo json_encode($response);
