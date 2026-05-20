<?php
require_once 'includes/auth.php';
require_once 'includes/layout.php';
redirigirSiNoAutenticado();

$db = getDB();
$busqueda     = trim($_GET['busqueda'] ?? '');
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';
$orden        = $_GET['orden'] ?? 'precio_asc';

$where  = '1=1';
$params = [];
$types  = '';

if ($busqueda) {
    $where   .= " AND (v.marca LIKE ? OR v.modelo LIKE ? OR v.placa LIKE ?)";
    $like     = "%$busqueda%";
    $params   = array_merge($params, [$like, $like, $like]);
    $types   .= 'sss';
}

// Excluir vehículos reservados en esas fechas
$excludeSQL = '';
if ($fecha_inicio && $fecha_fin) {
    $excludeSQL = "AND v.id NOT IN (
        SELECT vehiculo_id FROM reservas
        WHERE NOT (fecha_fin <= ? OR fecha_inicio >= ?)
    )";
    $params  = array_merge($params, [$fecha_inicio, $fecha_fin]);
    $types  .= 'ss';
}

$orderSQL = match($orden) {
    'precio_desc' => 'v.precio_dia DESC',
    'anio_desc'   => 'v.anio DESC',
    default        => 'v.precio_dia ASC',
};

$sql = "SELECT v.* FROM vehiculos v WHERE $where $excludeSQL ORDER BY $orderSQL";
$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result    = $stmt->get_result();
$vehiculos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

$emojis = ['🚙','🚗','🚘','🛻','🚐'];
$tipos  = ['Sedán','SUV','Hatchback','Pickup','Minivan'];
renderHead('Vehículos');
?>
<body>
<?php renderNav('vehiculos'); ?>
<div class="main-content">
<div class="container">

  <div class="page-header">
    <h2 class="page-title">Vehículos disponibles</h2>
    <span class="badge badge-green"><?= count($vehiculos) ?> encontrados</span>
  </div>

  <!-- FILTROS -->
  <form method="GET" class="card" style="margin-bottom:1.5rem">
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:.75rem;align-items:end;flex-wrap:wrap">
      <div class="form-group" style="margin:0">
        <label class="form-label">🔍 Buscar</label>
        <input class="form-control" type="text" name="busqueda" placeholder="Marca, modelo o placa..." value="<?= htmlspecialchars($busqueda) ?>">
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">📅 Desde</label>
        <input class="form-control" type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">📅 Hasta</label>
        <input class="form-control" type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">Ordenar por</label>
        <select class="form-control" name="orden">
          <option value="precio_asc"  <?= $orden==='precio_asc' ?'selected':'' ?>>Precio ↑</option>
          <option value="precio_desc" <?= $orden==='precio_desc'?'selected':'' ?>>Precio ↓</option>
          <option value="anio_desc"   <?= $orden==='anio_desc'  ?'selected':'' ?>>Más nuevo</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
  </form>

  <?php if(empty($vehiculos)): ?>
    <div class="card" style="text-align:center;padding:3rem">
      <div style="font-size:3rem;margin-bottom:1rem">🔍</div>
      <h3 style="margin-bottom:.5rem">Sin resultados</h3>
      <p style="color:var(--text2)">Prueba con otros filtros o fechas.</p>
      <a href="vehiculos.php"><button class="btn btn-secondary" style="margin-top:1rem">Ver todos</button></a>
    </div>
  <?php else: ?>
  <div class="grid-3">
    <?php foreach($vehiculos as $i => $v):
      $emoji = $emojis[$i % count($emojis)];
      $tipo  = $tipos[$i % count($tipos)];
    ?>
    <div class="card-sm" style="transition:border-color .2s" onmouseover="this.style.borderColor='var(--green)'" onmouseout="this.style.borderColor='var(--border)'">
      <div style="display:flex;justify-content:space-between;margin-bottom:.5rem">
        <span class="badge badge-green">Disponible</span>
        <small style="color:var(--text3)">Placa: <?= htmlspecialchars($v['placa']) ?></small>
      </div>
      <div style="height:90px;background:var(--bg3);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:3rem;margin-bottom:.75rem">
        <?= $emoji ?>
      </div>
      <h4 style="font-size:1rem;font-weight:600"><?= htmlspecialchars($v['marca'].' '.$v['modelo']) ?></h4>
      <p style="font-size:.78rem;color:var(--text2);margin:.2rem 0 .6rem"><?= $tipo ?> · <?= $v['anio'] ?> · <?= htmlspecialchars($v['color']) ?></p>
      <div style="display:flex;gap:.5rem;font-size:.75rem;color:var(--text3);margin-bottom:.75rem;flex-wrap:wrap">
        <span>⛽ Gasolina</span><span>👤 5 pasajeros</span><span>❄ A/C</span><span>⚙ Automático</span>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem">
        <div>
          <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:1.1rem">
            $<?= number_format($v['precio_dia'],0,',','.') ?>
          </span>
          <span style="font-size:.75rem;color:var(--text2)">/día</span>
        </div>
        <span style="font-size:.78rem;color:var(--amber)">★ 4.<?= (6+$i)%10 ?> (<?= 20+$i*7 ?> reseñas)</span>
      </div>
      <a href="reservar.php?vehiculo_id=<?= $v['id'] ?><?= $fecha_inicio?"&fecha_inicio=$fecha_inicio":'' ?><?= $fecha_fin?"&fecha_fin=$fecha_fin":'' ?>">
        <button class="btn btn-primary btn-block">Reservar este auto</button>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>
</div>
<?php renderFoot(); ?>
