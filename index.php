<?php
require_once 'includes/auth.php';
redirigirSiAutenticado();

$error = '';
$success = '';
$tab = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {

        if ($_POST['action'] === 'login') {
            $correo    = trim($_POST['correo'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';
            if (!$correo || !$contrasena) {
                $error = 'Completa todos los campos.';
            } elseif (login($correo, $contrasena)) {
                header('Location: home.php');
                exit;
            } else {
                $error = 'Correo o contraseña incorrectos.';
            }
            $tab = 'login';

        } elseif ($_POST['action'] === 'register') {
            $nombre    = trim($_POST['nombre'] ?? '');
            $correo    = trim($_POST['correo'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';
            $tab = 'register';
            if (!$nombre || !$correo || !$contrasena) {
                $error = 'Completa todos los campos.';
            } elseif (strlen($contrasena) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } else {
                $res = registrar($nombre, $correo, $contrasena);
                if ($res['ok']) {
                    header('Location: home.php');
                    exit;
                } else {
                    $error = $res['msg'];
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>AutoRent – Iniciar sesión</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#22c55e;--green-dark:#16a34a;--bg:#0f1117;--bg2:#181c27;--bg3:#1e2336;--border:#2d3449;--border2:#3a4260;--text:#f1f5f9;--text2:#94a3b8;--text3:#64748b;--red:#ef4444;--radius:12px;--radius-sm:8px;--radius-lg:18px}
body{font-family:'DM Sans',sans-serif;background:radial-gradient(ellipse at 50% -10%,#1a2744 0%,var(--bg) 55%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;color:var(--text)}
h1,h2{font-family:'Syne',sans-serif}
.card{width:100%;max-width:400px;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2.5rem 2rem;display:flex;flex-direction:column;gap:1.4rem}
.logo{display:flex;flex-direction:column;align-items:center;gap:.5rem;text-align:center}
.logo-icon{width:54px;height:54px;background:var(--green);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.7rem}
.logo h1{font-size:1.7rem;font-weight:700;letter-spacing:-.02em}
.logo p{font-size:.8rem;color:var(--text2)}
.tabs{display:grid;grid-template-columns:1fr 1fr;background:var(--bg3);border-radius:var(--radius-sm);padding:4px;gap:4px}
.tab{padding:.5rem;border:none;border-radius:6px;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.875rem;font-weight:500;background:transparent;color:var(--text2);transition:all .2s}
.tab.active{background:var(--green);color:#fff}
.alert{padding:.7rem 1rem;border-radius:var(--radius-sm);font-size:.83rem}
.alert-err{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.form-group{display:flex;flex-direction:column;gap:.4rem}
.form-label{font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:var(--text2);font-weight:500}
.input-wrap{position:relative}
.form-control{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.72rem 1rem;color:var(--text);font-size:.9rem;width:100%;transition:border-color .2s;outline:none;font-family:'DM Sans',sans-serif}
.form-control:focus{border-color:var(--green)}
.form-control::placeholder{color:var(--text3)}
.forgot{text-align:right;font-size:.8rem;color:var(--green);cursor:pointer}
.remember{display:flex;align-items:center;gap:.6rem;font-size:.85rem;color:var(--text2)}
.remember input{accent-color:var(--green);width:16px;height:16px;cursor:pointer}
.btn-main{background:var(--green);color:#fff;border:none;border-radius:var(--radius-sm);padding:.85rem;font-family:'Syne',sans-serif;font-size:1rem;font-weight:600;cursor:pointer;width:100%;transition:background .2s;letter-spacing:.02em}
.btn-main:hover{background:var(--green-dark)}
.divider{display:flex;align-items:center;gap:.75rem;color:var(--text3);font-size:.78rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border)}
.social-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.btn-social{display:flex;align-items:center;justify-content:center;gap:.5rem;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.7rem;cursor:pointer;font-size:.85rem;color:var(--text);transition:border-color .2s;font-family:'DM Sans',sans-serif}
.btn-social:hover{border-color:var(--border2)}
.register-link{text-align:center;font-size:.85rem;color:var(--text2)}
.register-link a{color:var(--green);cursor:pointer}
.pane{display:none;flex-direction:column;gap:1rem}
.pane.active{display:flex}
.hint{font-size:.75rem;color:var(--text3);text-align:center;padding:.5rem;background:var(--bg3);border-radius:var(--radius-sm);line-height:1.5}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-icon">🚗</div>
    <h1>AutoRent</h1>
    <p>Alquiler de vehículos</p>
  </div>

  <div class="tabs">
    <button class="tab <?= $tab==='login'?'active':'' ?>" onclick="switchTab('login')">Iniciar sesión</button>
    <button class="tab <?= $tab==='register'?'active':'' ?>" onclick="switchTab('register')">Registrarse</button>
  </div>

  <?php if($error): ?>
  <div class="alert alert-err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- LOGIN -->
  <div id="pane-login" class="pane <?= $tab==='login'?'active':'' ?>">
    <form method="POST" style="display:flex;flex-direction:column;gap:1rem">
      <input type="hidden" name="action" value="login">
      <div class="form-group">
        <label class="form-label">Correo electrónico</label>
        <input class="form-control" type="email" name="correo" placeholder="usuario@correo.com" value="<?= htmlspecialchars($_POST['correo']??'') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Contraseña</label>
        <input class="form-control" type="password" name="contrasena" placeholder="••••••••" required>
        <div class="forgot">¿Olvidaste tu contraseña?</div>
      </div>
      <div class="remember">
        <input type="checkbox" name="recordar" id="recordar">
        <label for="recordar">Mantener sesión iniciada</label>
      </div>
      <button type="submit" class="btn-main">Iniciar sesión</button>
    </form>
    <div class="divider">O CONTINÚA CON</div>
    <div class="social-grid">
      <button class="btn-social">🔵 Google</button>
      <button class="btn-social">🍎 Apple</button>
    </div>
    <div class="register-link">¿No tienes cuenta? <a onclick="switchTab('register')">Regístrate aquí</a></div>
    <div class="hint">Demo: <strong>samuel@gmail.com</strong> / <strong>123456</strong></div>
  </div>

  <!-- REGISTER -->
  <div id="pane-register" class="pane <?= $tab==='register'?'active':'' ?>">
    <form method="POST" style="display:flex;flex-direction:column;gap:1rem">
      <input type="hidden" name="action" value="register">
      <div class="form-group">
        <label class="form-label">Nombre completo</label>
        <input class="form-control" type="text" name="nombre" placeholder="Tu nombre completo" value="<?= htmlspecialchars($_POST['nombre']??'') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Correo electrónico</label>
        <input class="form-control" type="email" name="correo" placeholder="tu@correo.com" value="<?= htmlspecialchars($_POST['correo']??'') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Contraseña</label>
        <input class="form-control" type="password" name="contrasena" placeholder="Mínimo 6 caracteres" required>
      </div>
      <button type="submit" class="btn-main">Crear cuenta</button>
    </form>
    <div class="register-link">¿Ya tienes cuenta? <a onclick="switchTab('login')">Inicia sesión</a></div>
  </div>
</div>

<script>
function switchTab(t){
  ['login','register'].forEach(p=>{
    document.getElementById('pane-'+p).classList.toggle('active',p===t);
    document.querySelectorAll('.tab').forEach((b,i)=>{b.classList.toggle('active',(i===0&&t==='login')||(i===1&&t==='register'))});
  });
}
</script>
</body>
</html>
