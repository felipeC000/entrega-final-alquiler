<?php
require_once 'includes/auth.php';
require_once 'includes/layout.php';
redirigirSiNoAutenticado();

$db  = getDB();
$uid = (int)$_SESSION['usuario_id'];

// Acción cancelar (solo si no ha iniciado)
if (isset($_POST['cancelar'])) {
    $vid  = (int)$_POST['vehiculo_id'];
    $fi   = $_POST['fecha_inicio'];
    // Solo cancelar si la reserva no ha iniciado
    $del = $db->prepare("DELETE FROM reservas WHERE usuario_id=? AND vehiculo_id=? AND fecha_inicio=? AND fecha_inicio > CURDATE()");
    $del->bind_param('iis', $uid, $vid, $fi);
    $del->execute();
    $del->close();
    header('Location: reservas.php?msg=cancelada');
    exit;
}

$msg = $_GET['msg'] ?? '';

$sql = "
    SELECT r.*, v.marca, v.modelo, v.placa, v.color, v.precio_dia,
           p.monto, p.metodo_pago, p.fecha_pago
    FROM reservas r
    JOIN vehiculos v ON v.id = r.vehiculo_id
    LEFT JOIN pagos p ON p.usuario_id = r.usuario_id AND p.vehiculo_id = r.vehiculo_id AND p.fecha_inicio = r.fecha_inicio
    WHERE r.usuario_id = ?
    ORDER BY r.fecha_inicio DESC
";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $uid);
$stmt->execute();
$reservas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

$emojis = ['🚙','🚗','🚘','🛻','🚐'];
renderHead('Mis reservas');
?>
<body>
<?php renderNav('reservas'); ?>
<div class="main-content">
<div class="container">

  <div class="page-header">
    <h2 class="page-title">Mis reservas</h2>
    <a href="vehiculos.php"><button class="btn btn-primary">+ Nueva reserva</button></a>
  </div>

  <?php if($msg==='cancelada'): ?>
    <div class="alert alert-success">Reserva cancelada correctamente.</div>
  <?php endif; ?>

  <?php if(empty($reservas)): ?>
    <div class="card" style="text-align:center;padding:3rem">
      <div style="font-size:3rem;margin-bottom:1rem">📋</div>
      <h3 style="margin-bottom:.5rem">Aún no tienes reservas</h3>
      <p style="color:var(--text2);margin-bottom:1.5rem">Explora nuestros vehículos y haz tu primera reserva.</p>
      <a href="vehiculos.php"><button class="btn btn-primary">Explorar vehículos</button></a>
    </div>

  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:1rem">
    <?php foreach($reservas as $i => $r):
      $dias   = (strtotime($r['fecha_fin']) - strtotime($r['fecha_inicio'])) / 86400;
      $hoy    = time();
      $inicio = strtotime($r['fecha_inicio']);
      $fin    = strtotime($r['fecha_fin']);
      if ($hoy < $inicio)         { $estado='Próxima';   $eClass='badge-blue';  }
      elseif ($hoy <= $fin)       { $estado='En curso';  $eClass='badge-green'; }
      else                        { $estado='Finalizada';$eClass='badge-gray';  }
      $restantes = max(0, ceil(($fin - $hoy) / 86400));
      $emoji = $emojis[$r['vehiculo_id'] % count($emojis)];
      $codigo = '#AR-' . str_pad(2040 + $r['vehiculo_id'] + $i, 4, '0', STR_PAD_LEFT);
    ?>
    <div class="card">
      <!-- HEADER -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
        <div style="display:flex;align-items:center;gap:.75rem">
          <div style="display:flex;flex-direction:column">
            <div style="display:flex;align-items:center;gap:.5rem">
              <span style="font-family:'Syne',sans-serif;font-weight:600;font-size:.95rem"><?= $codigo ?></span>
              <span class="badge <?= $eClass ?>"><?= $estado ?></span>
            </div>
            <?php if($estado==='En curso' && $restantes>0): ?>
              <span style="font-size:.75rem;color:var(--text3);margin-top:.2rem"><?= $restantes ?> días restantes</span>
            <?php endif; ?>
          </div>
        </div>
        <span style="font-size:.75rem;color:var(--text3)">
          <?php if($r['metodo_pago']): ?>
            💳 <?= ucfirst($r['metodo_pago']) ?>
          <?php endif; ?>
        </span>
      </div>

      <!-- BODY -->
      <div style="display:flex;gap:1rem;align-items:flex-start;flex-wrap:wrap">
        <!-- Vehículo visual -->
        <div style="text-align:center;flex-shrink:0">
          <div style="width:90px;height:65px;background:var(--bg3);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:2.2rem">
            <?= $emoji ?>
          </div>
          <div style="font-size:.65rem;color:var(--text3);margin-top:.3rem;letter-spacing:.08em"><?= htmlspecialchars($r['placa']) ?></div>
        </div>

        <!-- Info vehículo -->
        <div style="flex:1;min-width:160px">
          <h4 style="font-size:1rem;font-weight:600"><?= htmlspecialchars($r['marca'].' '.$r['modelo']) ?></h4>
          <p style="font-size:.78rem;color:var(--text2);margin:.15rem 0 .75rem"><?= htmlspecialchars($r['color']) ?> · <?= $r['anio'] ?? '' ?></p>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
            <div>
              <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text3)">Recogida</div>
              <div style="font-weight:500;font-size:.9rem;margin-top:.15rem"><?= date('d M', $inicio) ?></div>
              <div style="font-size:.72rem;color:var(--text2)">El Dorado · 10:00</div>
            </div>
            <div>
              <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text3)">Devolución</div>
              <div style="font-weight:500;font-size:.9rem;margin-top:.15rem"><?= date('d M', $fin) ?></div>
              <div style="font-size:.72rem;color:var(--text2)">El Dorado · 10:00</div>
            </div>
          </div>
        </div>

        <!-- Monto y acciones -->
        <div style="text-align:right;flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:.5rem">
          <div>
            <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:1.2rem">
              <?= $r['monto'] ? '$'.number_format($r['monto'],0,',','.') : '—' ?>
            </div>
            <div style="font-size:.72rem;color:var(--text2)"><?= $dias ?> días · $<?= number_format($r['precio_dia'],0,',','.') ?>/día</div>
          </div>
          <div style="display:flex;gap:.4rem;flex-wrap:wrap;justify-content:flex-end">
            <button class="btn btn-secondary" style="font-size:.78rem;padding:.4rem .8rem"
              onclick="alert('Descargando contrato PDF para <?= addslashes($r['marca'].' '.$r['modelo']) ?>...')">
              📄 Contrato
            </button>
            <button class="btn btn-secondary" style="font-size:.78rem;padding:.4rem .8rem"
              onclick="alert('Abriendo ubicación del vehículo en el mapa...')">
              📍 Ubicación
            </button>
            <?php if($estado === 'En curso'): ?>
            <button class="btn btn-secondary" style="font-size:.78rem;padding:.4rem .8rem"
              onclick="alert('Funcionalidad de extensión próximamente disponible.')">
              ⏱ Extender
            </button>
            <?php endif; ?>
            <?php if($estado === 'Próxima'): ?>
            <form method="POST" onsubmit="return confirm('¿Seguro que deseas cancelar esta reserva?')">
              <input type="hidden" name="cancelar" value="1">
              <input type="hidden" name="vehiculo_id" value="<?= $r['vehiculo_id'] ?>">
              <input type="hidden" name="fecha_inicio" value="<?= $r['fecha_inicio'] ?>">
              <button type="submit" class="btn btn-danger" style="font-size:.78rem;padding:.4rem .8rem">✕ Cancelar</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
</div>
<?php renderFoot(); ?>
