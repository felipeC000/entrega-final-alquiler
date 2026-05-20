<?php
require_once 'includes/auth.php';
require_once 'includes/layout.php';
redirigirSiNoAutenticado();

// Solo admin
if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    header('Location: home.php'); exit;
}

$db  = getDB();
$tab = $_GET['tab'] ?? 'vehiculos';
$msg = '';

// ── ACCIONES ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Agregar vehículo
    if (isset($_POST['add_vehiculo'])) {
        $placa     = strtoupper(trim($_POST['placa']));
        $marca     = trim($_POST['marca']);
        $modelo    = trim($_POST['modelo']);
        $anio      = (int)$_POST['anio'];
        $color     = trim($_POST['color']);
        $precio    = (float)str_replace([',','.'], ['','.'], $_POST['precio_dia']);
        $stmt = $db->prepare("INSERT INTO vehiculos (placa,marca,modelo,anio,color,precio_dia) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('sssiss', $placa, $marca, $modelo, $anio, $color, $precio);
        $stmt->execute(); $stmt->close();
        $msg = '✅ Vehículo agregado correctamente.';
        $tab = 'vehiculos';
    }

    // Eliminar vehículo
    if (isset($_POST['del_vehiculo'])) {
        $id = (int)$_POST['id'];
        $db->query("DELETE FROM vehiculos WHERE id = $id");
        $msg = '🗑 Vehículo eliminado.';
        $tab = 'vehiculos';
    }

    // Eliminar usuario
    if (isset($_POST['del_usuario'])) {
        $id = (int)$_POST['id'];
        $db->query("DELETE FROM usuarios WHERE id = $id AND rol != 'admin'");
        $msg = '🗑 Usuario eliminado.';
        $tab = 'usuarios';
    }

    header("Location: admin.php?tab=$tab&msg=" . urlencode($msg));
    exit;
}

if (isset($_GET['msg'])) $msg = $_GET['msg'];

// ── DATA ─────────────────────────────────────────────────────
$vehiculos = $db->query("SELECT * FROM vehiculos ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$usuarios  = $db->query("SELECT * FROM usuarios ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$reservas  = $db->query("
    SELECT r.*, u.nombre as u_nombre, u.correo as u_correo,
           v.marca, v.modelo, v.placa, v.precio_dia, p.monto, p.metodo_pago
    FROM reservas r
    JOIN usuarios u ON u.id = r.usuario_id
    JOIN vehiculos v ON v.id = r.vehiculo_id
    LEFT JOIN pagos p ON p.usuario_id=r.usuario_id AND p.vehiculo_id=r.vehiculo_id AND p.fecha_inicio=r.fecha_inicio
    ORDER BY r.fecha_inicio DESC
")->fetch_all(MYSQLI_ASSOC);

$ingresos_total = array_sum(array_column($reservas,'monto'));
$db->close();

renderHead('Admin');
?>
<body>
<?php renderNav('admin'); ?>
<div class="main-content">
<div class="container">

  <div class="page-header">
    <h2 class="page-title">Panel de administración</h2>
    <span class="badge badge-amber">Admin</span>
  </div>

  <?php if($msg): ?>
    <div class="alert alert-success" style="margin-bottom:1rem"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <!-- KPIs -->
  <div class="grid-4" style="margin-bottom:1.5rem">
    <div class="stat-card">
      <div class="stat-label">Vehículos</div>
      <div class="stat-value"><?= count($vehiculos) ?></div>
      <div class="stat-sub">En la flota</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Usuarios</div>
      <div class="stat-value"><?= count($usuarios) ?></div>
      <div class="stat-sub">Registrados</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Reservas</div>
      <div class="stat-value"><?= count($reservas) ?></div>
      <div class="stat-sub">Total acumuladas</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Ingresos totales</div>
      <div class="stat-value" style="font-size:1.1rem">$<?= number_format($ingresos_total,0,',','.') ?></div>
      <div class="stat-sub">COP generados</div>
    </div>
  </div>

  <!-- TABS -->
  <div style="display:flex;gap:.4rem;margin-bottom:1.25rem;border-bottom:1px solid var(--border);padding-bottom:0">
    <?php foreach(['vehiculos'=>'🚗 Vehículos','usuarios'=>'👤 Usuarios','reservas'=>'📋 Reservas'] as $k=>$label): ?>
    <a href="admin.php?tab=<?= $k ?>"
       style="padding:.6rem 1.1rem;font-size:.85rem;border-radius:8px 8px 0 0;cursor:pointer;
              background:<?= $tab===$k?'var(--bg2)':'transparent' ?>;
              color:<?= $tab===$k?'var(--green)':'var(--text2)' ?>;
              border:<?= $tab===$k?'1px solid var(--border)':'1px solid transparent' ?>;
              border-bottom:<?= $tab===$k?'1px solid var(--bg2)':'none' ?>;
              margin-bottom:-1px;">
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- ═══ TAB VEHÍCULOS ═══ -->
  <?php if($tab === 'vehiculos'): ?>

  <div class="grid-2" style="margin-bottom:1.25rem;align-items:start">
    <!-- FORM AGREGAR -->
    <div class="card">
      <h3 style="font-size:.95rem;font-weight:600;margin-bottom:1rem">➕ Agregar vehículo</h3>
      <form method="POST">
        <input type="hidden" name="add_vehiculo" value="1">
        <div class="form-group">
          <label class="form-label">Placa</label>
          <input class="form-control" name="placa" placeholder="ABC123" required maxlength="10">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
          <div class="form-group">
            <label class="form-label">Marca</label>
            <input class="form-control" name="marca" placeholder="Toyota" required>
          </div>
          <div class="form-group">
            <label class="form-label">Modelo</label>
            <input class="form-control" name="modelo" placeholder="Corolla" required>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem">
          <div class="form-group">
            <label class="form-label">Año</label>
            <input class="form-control" name="anio" type="number" min="2000" max="2030" value="2024" required>
          </div>
          <div class="form-group">
            <label class="form-label">Color</label>
            <input class="form-control" name="color" placeholder="Blanco" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Precio/día (COP)</label>
          <input class="form-control" name="precio_dia" type="number" min="0" step="1000" placeholder="150000" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Agregar vehículo</button>
      </form>
    </div>

    <!-- LISTA -->
    <div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Placa</th><th>Vehículo</th><th>Año</th><th>Precio/día</th><th>Acción</th></tr>
          </thead>
          <tbody>
          <?php foreach($vehiculos as $v): ?>
            <tr>
              <td><code style="font-size:.78rem;color:var(--green)"><?= htmlspecialchars($v['placa']) ?></code></td>
              <td>
                <div style="font-weight:500"><?= htmlspecialchars($v['marca'].' '.$v['modelo']) ?></div>
                <div style="font-size:.73rem;color:var(--text2)"><?= htmlspecialchars($v['color']) ?></div>
              </td>
              <td><?= $v['anio'] ?></td>
              <td style="font-family:'Syne',sans-serif;font-weight:600">$<?= number_format($v['precio_dia'],0,',','.') ?></td>
              <td>
                <form method="POST" onsubmit="return confirm('¿Eliminar este vehículo?')">
                  <input type="hidden" name="del_vehiculo" value="1">
                  <input type="hidden" name="id" value="<?= $v['id'] ?>">
                  <button type="submit" class="btn btn-danger" style="font-size:.75rem;padding:.3rem .7rem">✕</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ═══ TAB USUARIOS ═══ -->
  <?php elseif($tab === 'usuarios'): ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Registro</th><th>Acción</th></tr>
      </thead>
      <tbody>
      <?php foreach($usuarios as $u): ?>
        <tr>
          <td style="color:var(--text3)">#<?= $u['id'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:.6rem">
              <div style="width:28px;height:28px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;color:#fff;flex-shrink:0">
                <?= strtoupper(mb_substr($u['nombre'],0,1)) ?>
              </div>
              <span style="font-weight:500"><?= htmlspecialchars($u['nombre']) ?></span>
            </div>
          </td>
          <td style="color:var(--text2)"><?= htmlspecialchars($u['correo']) ?></td>
          <td>
            <span class="badge <?= $u['rol']==='admin'?'badge-amber':'badge-blue' ?>">
              <?= ucfirst($u['rol']) ?>
            </span>
          </td>
          <td style="font-size:.78rem;color:var(--text2)"><?= date('d M Y', strtotime($u['fecha_registro'])) ?></td>
          <td>
            <?php if($u['rol']!=='admin'): ?>
            <form method="POST" onsubmit="return confirm('¿Eliminar usuario?')">
              <input type="hidden" name="del_usuario" value="1">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="submit" class="btn btn-danger" style="font-size:.75rem;padding:.3rem .7rem">✕</button>
            </form>
            <?php else: ?>
              <span style="font-size:.75rem;color:var(--text3)">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ═══ TAB RESERVAS ═══ -->
  <?php elseif($tab === 'reservas'): ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Usuario</th><th>Vehículo</th><th>Desde</th><th>Hasta</th><th>Días</th><th>Total</th><th>Pago</th><th>Estado</th></tr>
      </thead>
      <tbody>
      <?php foreach($reservas as $r):
        $dias = (strtotime($r['fecha_fin']) - strtotime($r['fecha_inicio'])) / 86400;
        $hoy  = time();
        $ini  = strtotime($r['fecha_inicio']);
        $fin  = strtotime($r['fecha_fin']);
        if ($hoy < $ini)    { $est='Próxima';   $ec='badge-blue';  }
        elseif($hoy<=$fin)  { $est='En curso';  $ec='badge-green'; }
        else                { $est='Finalizada';$ec='badge-gray';  }
      ?>
      <tr>
        <td>
          <div style="font-weight:500;font-size:.85rem"><?= htmlspecialchars($r['u_nombre']) ?></div>
          <div style="font-size:.72rem;color:var(--text2)"><?= htmlspecialchars($r['u_correo']) ?></div>
        </td>
        <td>
          <div style="font-weight:500;font-size:.85rem"><?= htmlspecialchars($r['marca'].' '.$r['modelo']) ?></div>
          <div style="font-size:.72rem;color:var(--text2)"><?= htmlspecialchars($r['placa']) ?></div>
        </td>
        <td><?= date('d M Y', $ini) ?></td>
        <td><?= date('d M Y', $fin) ?></td>
        <td style="text-align:center"><?= $dias ?></td>
        <td style="font-family:'Syne',sans-serif;font-weight:600">
          <?= $r['monto'] ? '$'.number_format($r['monto'],0,',','.') : '—' ?>
        </td>
        <td style="font-size:.78rem;color:var(--text2)"><?= ucfirst($r['metodo_pago']??'—') ?></td>
        <td><span class="badge <?= $ec ?>"><?= $est ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

</div>
</div>
<?php renderFoot(); ?>
