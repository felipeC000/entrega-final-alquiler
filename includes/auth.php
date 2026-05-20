<?php
require_once __DIR__ . '/db.php';

function iniciarSesionSegura() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function usuarioAutenticado() {
    iniciarSesionSegura();
    return isset($_SESSION['usuario_id']);
}

function redirigirSiNoAutenticado() {
    if (!usuarioAutenticado()) {
        header('Location: index.php');
        exit;
    }
}

function redirigirSiAutenticado() {
    if (usuarioAutenticado()) {
        header('Location: home.php');
        exit;
    }
}

function login($correo, $contrasena) {
    $db = getDB();
    $hash = hash('sha256', $contrasena);
    $stmt = $db->prepare("SELECT id, nombre, correo, rol FROM usuarios WHERE correo = ? AND contrasena = ?");
    $stmt->bind_param('ss', $correo, $hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $db->close();
    if ($user) {
        iniciarSesionSegura();
        $_SESSION['usuario_id']     = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_correo'] = $user['correo'];
        $_SESSION['usuario_rol']    = $user['rol'];
        return true;
    }
    return false;
}

function logout() {
    iniciarSesionSegura();
    session_destroy();
    header('Location: index.php');
    exit;
}

function registrar($nombre, $correo, $contrasena) {
    $db = getDB();
    // Verificar si el correo ya existe
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close(); $db->close();
        return ['ok' => false, 'msg' => 'Este correo ya está registrado.'];
    }
    $stmt->close();
    $hash = hash('sha256', $contrasena);
    $stmt2 = $db->prepare("INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, 'cliente')");
    $stmt2->bind_param('sss', $nombre, $correo, $hash);
    $ok = $stmt2->execute();
    $id = $db->insert_id;
    $stmt2->close(); $db->close();
    if ($ok) {
        iniciarSesionSegura();
        $_SESSION['usuario_id']     = $id;
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_correo'] = $correo;
        $_SESSION['usuario_rol']    = 'cliente';
        return ['ok' => true];
    }
    return ['ok' => false, 'msg' => 'Error al registrar. Intenta de nuevo.'];
}
?>
