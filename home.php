<?php
require_once 'includes/auth.php';
require_once 'includes/layout.php';
redirigirSiNoAutenticado();

$db = getDB();

// Stats rápidas
$totalVehiculos = $db->query("SELECT COUNT(*) as c FROM vehiculos")->fetch_assoc()['c'];
$totalReservas  = $db->query("SELECT COUNT(*) as c FROM reservas WHERE usuario_id = " . (int)$_SESSION['usuario_id'])->fetch_assoc()['c'];
$gastoTotal     = $db->query("SELECT COALESCE(SUM(monto),0) as t FROM pagos WHERE usuario_id = " . (int)$_SESSION['usuario_id'])->fetch_assoc()['t'];

// Vehículos destacados (todos, con estado de disponibilidad)
$categoria = $_GET['categoria'] ?? 'Todos';
$categorias = ['Todos','Sedán','SUV','Eléctrico','Deportivo'];
// Mapa de tipo (como no hay columna tipo, deducimos por modelo)
$sql = "SELECT * FROM vehiculos ORDER BY id LIMIT 12";
$result = $db->query($sql);
$vehiculos = [];
while ($row = $result->fetch_assoc()) {
    $vehiculos[] = $row;
}

// Reserva activa del usuario
$resActiva = $db->query("
    SELECT r.*, v.marca, v.modelo, v.placa, v.color, v.precio_dia,
           p.monto, p.metodo_pago
    FROM reservas r
    JOIN vehiculos v ON v.id = r.vehiculo_id
    LEFT JOIN pagos p ON p.usuario_id = r.usuario_id AND p.vehiculo_id = r.vehiculo_id AND p.fecha_inicio = r.fecha_inicio
    WHERE r.usuario_id = " . (int)$_SESSION['usuario_id'] . "
    ORDER BY r.fecha_fin DESC LIMIT 1
")->fetch_assoc();

$db->close();

$emojis = ['🚙','🚗','🚘','🛻','🚐'];
$tipos  = ['Sedán','SUV','Hatchback','Pickup','Minivan'];
$badges = ['disponible','popular','nuevo'];
renderHead('Inicio');
?>
<body>
<?php renderNav('home'); ?>
<div class="main-content">
<div class="container">

  <!-- GREETING -->
  <div style="margin-bottom:1.5rem">
    <h2 style="font-size:1.6rem;font-weight:700;letter-spacing:-.02em">
      Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?> 👋
    </h2>
    <p style="color:var(--text2);margin-top:.25rem">¿A dónde quieres ir hoy?</p>
  </div>

  <!-- STATS -->
  <div class="grid-4" style="margin-bottom:1.5rem">
    <div class="stat-card">
      <div class="stat-label">Vehículos disponibles</div>
      <div class="stat-value"><?= $totalVehiculos ?></div>
      <div class="stat-sub">En toda la flota</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Mis reservas</div>
      <div class="stat-value"><?= $totalReservas ?></div>
      <div class="stat-sub">Total acumuladas</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total pagado</div>
      <div class="stat-value" style="font-size:1.2rem">$<?= number_format($gastoTotal,0,',','.') ?></div>
      <div class="stat-sub">COP acumulados</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Estado</div>
      <div class="stat-value" style="font-size:1rem;color:var(--green)">Activo</div>
      <div class="stat-sub">Cuenta verificada</div>
    </div>
  </div>

  <!-- SEARCH CARD -->
  <div class="card" style="margin-bottom:1.5rem">
    <form method="GET" action="vehiculos.php" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:1rem;align-items:end">
      <div class="form-group" style="margin:0">
        <label class="form-label">📍 Lugar de recogida</label>
        <input class="form-control" type="text" name="ciudad" placeholder="Bogotá, Colombia" value="<?= htmlspecialchars($_GET['ciudad']??'') ?>">
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">📅 Desde</label>
        <input class="form-control" type="date" name="fecha_inicio" value="<?= htmlspecialchars($_GET['fecha_inicio']??date('Y-m-d')) ?>">
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">📅 Hasta</label>
        <input class="form-control" type="date" name="fecha_fin" value="<?= htmlspecialchars($_GET['fecha_fin']??date('Y-m-d',strtotime('+5 days'))) ?>">
      </div>
      <button type="submit" class="btn btn-primary" style="white-space:nowrap">Buscar autos</button>
    </form>
  </div>

  <!-- RESERVA ACTIVA -->
  <?php if($resActiva): ?>
  <div style="margin-bottom:1.5rem">
    <div class="page-header" style="margin-bottom:.75rem">
      <h3 style="font-size:1.05rem;font-weight:600">Reserva activa</h3>
      <a href="reservas.php" style="font-size:.82rem;color:var(--green)">Ver todas →</a>
    </div>
    <?php
      $dias = (strtotime($resActiva['fecha_fin']) - strtotime($resActiva['fecha_inicio'])) / 86400;
      $restantes = max(0, ceil((strtotime($resActiva['fecha_fin']) - time()) / 86400));
    ?>
    <div class="card" style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap">
      <div style="font-size:3rem">🚗</div>
      <div style="flex:1;min-width:180px">
        <div style="font-weight:600;font-size:1rem"><?= htmlspecialchars($resActiva['marca'].' '.$resActiva['modelo']) ?></div>
        <div style="font-size:.8rem;color:var(--text2);margin:.2rem 0"><?= htmlspecialchars($resActiva['placa']) ?> · <?= htmlspecialchars($resActiva['color']) ?></div>
        <div style="font-size:.8rem;color:var(--text2)">
          <?= date('d M', strtotime($resActiva['fecha_inicio'])) ?> → <?= date('d M', strtotime($resActiva['fecha_fin'])) ?>
          &nbsp;·&nbsp; <?= $dias ?> días
        </div>
      </div>
      <div style="text-align:right">
        <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:1.2rem">
          $<?= number_format($resActiva['monto'],0,',','.') ?>
        </div>
        <div style="font-size:.75rem;color:var(--text2);margin:.2rem 0">
          $<?= number_format($resActiva['precio_dia'],0,',','.') ?>/día
        </div>
        <?php if($restantes > 0): ?>
          <span class="badge badge-green"><?= $restantes ?> días restantes</span>
        <?php else: ?>
          <span class="badge badge-gray">Finalizada</span>
        <?php endif; ?>
      </div>
      <a href="reservas.php" class="btn btn-secondary" style="font-size:.82rem">Ver detalle</a>
    </div>
  </div>
  <?php endif; ?>

  <!-- VEHÍCULOS DESTACADOS -->
  <div class="page-header">
    <h3 style="font-size:1.05rem;font-weight:600">Destacados</h3>
    <a href="vehiculos.php" style="font-size:.82rem;color:var(--green)">Ver todos →</a>
  </div>

  <div class="grid-3">
    <?php foreach($vehiculos as $i => $v):
      $emoji  = $emojis[$i % count($emojis)];
      $badge  = $badges[$i % count($badges)];
      $badgeLabel = $badge==='disponible'?'Disponible':($badge==='popular'?'Popular':'Nuevo');
      $badgeClass = $badge==='disponible'?'badge-green':($badge==='popular'?'badge-amber':'badge-blue');
    ?>
    <div class="card-sm" style="cursor:pointer;transition:border-color .2s" onmouseover="this.style.borderColor='var(--green)'" onmouseout="this.style.borderColor='var(--border)'">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem">
        <span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
        <span style="color:var(--text3);font-size:1rem;cursor:pointer">♡</span>
      </div>
      <div style="height:80px;background:var(--bg3);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:2.5rem;margin-bottom:.75rem">
        <?= $emoji ?>
      </div>
      <h4 style="font-size:.9rem;font-weight:600"><?= htmlspecialchars($v['marca'].' '.$v['modelo']) ?></h4>
      <p style="font-size:.75rem;color:var(--text2);margin:.15rem 0 .5rem"><?= htmlspecialchars($tipos[$i%count($tipos)]) ?> · <?= $v['anio'] ?></p>
      <div style="display:flex;gap:.5rem;font-size:.72rem;color:var(--text3);margin-bottom:.6rem;flex-wrap:wrap">
        <span>⛽ Gas</span><span>👤 5</span><span>❄ A/C</span>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div style="font-family:'Syne',sans-serif;font-weight:700">
          $<?= number_format($v['precio_dia'],0,',','.') ?><span style="font-size:.72rem;font-weight:400;color:var(--text2)">/día</span>
        </div>
        <span style="font-size:.75rem;color:var(--amber)">★ 4.<?= (7+$i)%10 ?></span>
      </div>
      <a href="reservar.php?vehiculo_id=<?= $v['id'] ?>" style="display:block;margin-top:.75rem">
        <button class="btn btn-primary btn-block" style="font-size:.82rem;padding:.55rem">Reservar</button>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

</div><!-- /container -->
</div><!-- /main-content -->
<?php renderFoot(); ?>
