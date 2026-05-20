<?php
function renderHead($titulo = 'AutoRent') {
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($titulo) ?> | AutoRent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --green:#22c55e;--green-dark:#16a34a;
  --bg:#0f1117;--bg2:#181c27;--bg3:#1e2336;--bg4:#252b3b;
  --border:#2d3449;--border2:#3a4260;
  --text:#f1f5f9;--text2:#94a3b8;--text3:#64748b;
  --red:#ef4444;--amber:#f59e0b;
  --radius:12px;--radius-sm:8px;--radius-lg:18px;
}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
h1,h2,h3,h4,h5{font-family:'Syne',sans-serif}
a{text-decoration:none;color:inherit}
input,button,select,textarea{font-family:'DM Sans',sans-serif}

/* NAV */
.navbar{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.5rem;background:var(--bg2);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100}
.nav-brand{display:flex;align-items:center;gap:.6rem;text-decoration:none}
.nav-icon{width:34px;height:34px;background:var(--green);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.1rem}
.nav-brand-name{font-family:'Syne',sans-serif;font-weight:700;font-size:1.1rem;color:var(--text)}
.nav-right{display:flex;align-items:center;gap:.75rem}
.nav-user{display:flex;align-items:center;gap:.5rem;font-size:.85rem;color:var(--text2)}
.nav-avatar{width:32px;height:32px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;color:#fff}
.nav-links{display:flex;gap:.25rem}
.nav-link{padding:.45rem .9rem;border-radius:6px;font-size:.85rem;color:var(--text2);transition:all .2s;cursor:pointer}
.nav-link:hover,.nav-link.active{background:var(--bg3);color:var(--text)}
.btn-logout{padding:.4rem .9rem;border-radius:6px;border:1px solid var(--border);background:transparent;color:var(--text2);font-size:.82rem;cursor:pointer;transition:all .2s}
.btn-logout:hover{border-color:var(--red);color:var(--red)}

/* BOTTOM NAV (mobile) */
.bottom-nav{display:none;position:fixed;bottom:0;left:0;right:0;background:var(--bg2);border-top:1px solid var(--border);z-index:100}
.bnav-items{display:flex}
.bnav-item{flex:1;display:flex;flex-direction:column;align-items:center;padding:.6rem .25rem;font-size:.58rem;color:var(--text3);gap:.25rem;cursor:pointer;text-decoration:none;transition:color .2s}
.bnav-item.active,.bnav-item:hover{color:var(--green)}
.bnav-item svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:1.8}

/* ALERTS */
.alert{padding:.75rem 1rem;border-radius:var(--radius-sm);font-size:.85rem;margin-bottom:1rem}
.alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#86efac}
.alert-info{background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.3);color:#93c5fd}

/* CARDS */
.card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem}
.card-sm{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1rem}

/* BADGES */
.badge{display:inline-block;font-size:.65rem;font-weight:600;padding:.25rem .65rem;border-radius:50px;letter-spacing:.04em}
.badge-green{background:rgba(34,197,94,.15);color:var(--green)}
.badge-amber{background:rgba(245,158,11,.15);color:var(--amber)}
.badge-blue{background:rgba(96,165,250,.15);color:#60a5fa}
.badge-red{background:rgba(239,68,68,.15);color:var(--red)}
.badge-gray{background:rgba(148,163,184,.1);color:var(--text2)}

/* FORMS */
.form-group{display:flex;flex-direction:column;gap:.4rem;margin-bottom:1rem}
.form-label{font-size:.7rem;letter-spacing:.07em;text-transform:uppercase;color:var(--text2);font-weight:500}
.form-control{background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.7rem 1rem;color:var(--text);font-size:.9rem;width:100%;transition:border-color .2s;outline:none}
.form-control:focus{border-color:var(--green)}
.form-control::placeholder{color:var(--text3)}
.btn{border:none;border-radius:var(--radius-sm);padding:.7rem 1.25rem;font-family:'Syne',sans-serif;font-weight:600;font-size:.9rem;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:.4rem}
.btn-primary{background:var(--green);color:#fff}
.btn-primary:hover{background:var(--green-dark)}
.btn-secondary{background:var(--bg3);border:1px solid var(--border);color:var(--text)}
.btn-secondary:hover{border-color:var(--border2)}
.btn-danger{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:var(--red)}
.btn-danger:hover{background:rgba(239,68,68,.25)}
.btn-block{width:100%;justify-content:center}

/* TABLE */
.table-wrap{overflow-x:auto;border-radius:var(--radius);border:1px solid var(--border)}
table{width:100%;border-collapse:collapse;font-size:.85rem}
th{background:var(--bg3);padding:.75rem 1rem;text-align:left;color:var(--text2);font-weight:500;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--border)}
td{padding:.75rem 1rem;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(255,255,255,.02)}

/* LAYOUT */
.container{max-width:1100px;margin:0 auto;padding:2rem 1.5rem}
.container-sm{max-width:480px;margin:0 auto;padding:2rem 1rem}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem}
.page-title{font-size:1.4rem;font-weight:700}
.grid-2{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem}
.grid-3{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}
.grid-4{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.75rem}
.stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1.1rem}
.stat-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text3);margin-bottom:.35rem}
.stat-value{font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:700}
.stat-sub{font-size:.78rem;color:var(--text2);margin-top:.2rem}

/* SCROLLBAR */
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}

/* MAIN CONTENT PADDING (for bottom nav on mobile) */
.main-content{padding-bottom:80px}

@media(max-width:768px){
  .bottom-nav{display:block}
  .nav-links,.nav-user span,.btn-logout{display:none}
  .container{padding:1.25rem 1rem}
  .grid-4{grid-template-columns:1fr 1fr}
}
</style>
<?php } ?>

<?php
function renderNav($paginaActiva = '') {
    $nombre = $_SESSION['usuario_nombre'] ?? '';
    $inicial = strtoupper(mb_substr($nombre, 0, 1));
    $rol = $_SESSION['usuario_rol'] ?? 'cliente';
?>
<nav class="navbar">
  <a href="home.php" class="nav-brand">
    <div class="nav-icon">🚗</div>
    <span class="nav-brand-name">AutoRent</span>
  </a>
  <div class="nav-right">
    <div class="nav-links">
      <a href="home.php" class="nav-link <?= $paginaActiva==='home'?'active':'' ?>">Inicio</a>
      <a href="vehiculos.php" class="nav-link <?= $paginaActiva==='vehiculos'?'active':'' ?>">Vehículos</a>
      <a href="reservas.php" class="nav-link <?= $paginaActiva==='reservas'?'active':'' ?>">Mis reservas</a>
      <?php if($rol==='admin'): ?>
      <a href="admin.php" class="nav-link <?= $paginaActiva==='admin'?'active':'' ?>">Admin</a>
      <?php endif; ?>
    </div>
    <div class="nav-user">
      <div class="nav-avatar"><?= $inicial ?></div>
      <span><?= htmlspecialchars($nombre) ?></span>
    </div>
    <a href="logout.php"><button class="btn-logout">Salir</button></a>
  </div>
</nav>

<!-- Bottom nav mobile -->
<nav class="bottom-nav">
  <div class="bnav-items">
    <a href="home.php" class="bnav-item <?= $paginaActiva==='home'?'active':'' ?>">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>INICIO
    </a>
    <a href="vehiculos.php" class="bnav-item <?= $paginaActiva==='vehiculos'?'active':'' ?>">
      <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>BUSCAR
    </a>
    <a href="reservas.php" class="bnav-item <?= $paginaActiva==='reservas'?'active':'' ?>">
      <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>RESERVAS
    </a>
    <?php if($rol==='admin'): ?>
    <a href="admin.php" class="bnav-item <?= $paginaActiva==='admin'?'active':'' ?>">
      <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>ADMIN
    </a>
    <?php endif; ?>
    <a href="logout.php" class="bnav-item">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>SALIR
    </a>
  </div>
</nav>
<?php } ?>

<?php
function renderFoot() {
?>
</body>
</html>
<?php } ?>
