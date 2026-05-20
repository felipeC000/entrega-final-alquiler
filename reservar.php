<?php
require_once 'includes/auth.php';
require_once 'includes/layout.php';
redirigirSiNoAutenticado();

$db = getDB();
$vehiculo_id  = (int)($_GET['vehiculo_id'] ?? 0);
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fecha_fin    = $_GET['fecha_fin']    ?? date('Y-m-d', strtotime('+5 days'));

if (!$vehiculo_id) { header('Location: vehiculos.php'); exit; }

$stmt = $db->prepare("SELECT * FROM vehiculos WHERE id = ?");
$stmt->bind_param('i', $vehiculo_id);
$stmt->execute();
$vehiculo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vehiculo) { header('Location: vehiculos.php'); exit; }

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fi = $_POST['fecha_inicio'] ?? '';
    $ff = $_POST['fecha_fin']    ?? '';
    $mp = $_POST['metodo_pago'] ?? 'tarjeta';
    $uid = (int)$_SESSION['usuario_id'];

    if (!$fi || !$ff) {
        $error = 'Selecciona las fechas de recogida y devolución.';
    } elseif ($ff <= $fi) {
        $error = 'La fecha de devolución debe ser posterior a la de recogida.';
    } else {
        // Verificar disponibilidad
        $chk = $db->prepare("SELECT COUNT(*) as c FROM reservas WHERE vehiculo_id = ? AND NOT (fecha_fin <= ? OR fecha_inicio >= ?)");
        $chk->bind_param('iss', $vehiculo_id, $fi, $ff);
        $chk->execute();
        $ocupado = $chk->get_result()->fetch_assoc()['c'];
        $chk->close();

        if ($ocupado > 0) {
            $error = 'Este vehículo no está disponible en las fechas seleccionadas.';
        } else {
            $dias   = (strtotime($ff) - strtotime($fi)) / 86400;
            $monto  = $dias * $vehiculo['precio_dia'];
            // Insertar reserva
            $ins = $db->prepare("INSERT INTO reservas (usuario_id, vehiculo_id, fecha_inicio, fecha_fin) VALUES (?,?,?,?)");
            $ins->bind_param('iiss', $uid, $vehiculo_id, $fi, $ff);
            $ins->execute();
            $ins->close();
            // Insertar pago
            $pag = $db->prepare("INSERT INTO pagos (usuario_id, vehiculo_id, fecha_inicio, monto, metodo_pago) VALUES (?,?,?,?,?)");
            $pag->bind_param('iisds', $uid, $vehiculo_id, $fi, $monto, $mp);
            $pag->execute();
            $pag->close();
            $success = 'reserva_ok';
            $fi_ok = $fi; $ff_ok = $ff; $monto_ok = $monto; $dias_ok = $dias;
        }
    }
}

$dias_prev = max(1, (strtotime($fecha_fin) - strtotime($fecha_inicio)) / 86400);
$monto_prev = $dias_prev * $vehiculo['precio_dia'];
$emojis = ['🚙','🚗','🚘','🛻','🚐'];
$emoji = $emojis[$vehiculo_id % count($emojis)];
renderHead('Reservar');
?>
<body>
<?php renderNav('vehiculos'); ?>
<div class="main-content">
<div class="container-sm">

  <div style="margin-bottom:1.5rem">
    <a href="vehiculos.php" style="display:inline-flex;align-items:center;gap:.4rem;color:var(--text2);font-size:.85rem">
      ← Volver a vehículos
    </a>
  </div>

  <?php if($success === 'reserva_ok'): ?>
  <div class="card" style="text-align:center;padding:2.5rem 2rem">
    <div style="font-size:3.5rem;margin-bottom:1rem">✅</div>
    <h2 style="margin-bottom:.5rem">¡Reserva confirmada!</h2>
    <p style="color:var(--text2);margin-bottom:1.5rem">
      <?= htmlspecialchars($vehiculo['marca'].' '.$vehiculo['modelo']) ?> ·
      <?= date('d M', strtotime($fi_ok)) ?> → <?= date('d M', strtotime($ff_ok)) ?> ·
      <?= $dias_ok ?> días
    </p>
    <div style="background:var(--bg3);border-radius:var(--radius-sm);padding:1rem;margin-bottom:1.5rem">
      <div style="font-size:.75rem;color:var(--text3);margin-bottom:.25rem">TOTAL PAGADO</div>
      <div style="font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:700;color:var(--green)">
        $<?= number_format($monto_ok,0,',','.') ?>
      </div>
      <div style="font-size:.75rem;color:var(--text2);margin-top:.25rem">
        $<?= number_format($vehiculo['precio_dia'],0,',','.') ?>/día · <?= ucfirst($_POST['metodo_pago']??'tarjeta') ?>
      </div>
    </div>
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
      <a href="reservas.php"><button class="btn btn-primary">Ver mis reservas</button></a>
      <a href="home.php"><button class="btn btn-secondary">Ir al inicio</button></a>
    </div>
  </div>

  <?php else: ?>

  <?php if($error): ?>
    <div class="alert alert-error" style="margin-bottom:1rem"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- DETALLE DEL VEHÍCULO -->
  <div class="card" style="margin-bottom:1rem">
    <div style="display:flex;align-items:center;gap:1rem">
      <div style="width:70px;height:60px;background:var(--bg3);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0">
        <?= $emoji ?>
      </div>
      <div style="flex:1">
        <h3 style="font-size:1rem;font-weight:600"><?= htmlspecialchars($vehiculo['marca'].' '.$vehiculo['modelo']) ?></h3>
        <p style="font-size:.78rem;color:var(--text2);margin:.15rem 0"><?= $vehiculo['anio'] ?> · <?= htmlspecialchars($vehiculo['color']) ?> · <?= htmlspecialchars($vehiculo['placa']) ?></p>
        <span class="badge badge-green">Disponible</span>
      </div>
      <div style="text-align:right">
        <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:1.1rem">
          $<?= number_format($vehiculo['precio_dia'],0,',','.') ?>
        </div>
        <div style="font-size:.72rem;color:var(--text2)">/día</div>
      </div>
    </div>
  </div>

  <!-- FORM RESERVA -->
  <div class="card">
    <h3 style="font-size:1rem;font-weight:600;margin-bottom:1.25rem">Datos de la reserva</h3>
    <form method="POST">
      <input type="hidden" name="vehiculo_id" value="<?= $vehiculo_id ?>">
      <div class="form-group">
        <label class="form-label">📅 Fecha de recogida</label>
        <input class="form-control" type="date" name="fecha_inicio" id="fi"
               value="<?= htmlspecialchars($_POST['fecha_inicio']??$fecha_inicio) ?>"
               min="<?= date('Y-m-d') ?>" onchange="calcTotal()">
      </div>
      <div class="form-group">
        <label class="form-label">📅 Fecha de devolución</label>
        <input class="form-control" type="date" name="fecha_fin" id="ff"
               value="<?= htmlspecialchars($_POST['fecha_fin']??$fecha_fin) ?>"
               min="<?= date('Y-m-d',strtotime('+1 day')) ?>" onchange="calcTotal()">
      </div>
      <div class="form-group">
        <label class="form-label">💳 Método de pago</label>
        <select class="form-control" name="metodo_pago">
          <option value="tarjeta">Tarjeta de crédito/débito</option>
          <option value="efectivo">Efectivo</option>
          <option value="transferencia">Transferencia bancaria</option>
        </select>
      </div>

      <!-- RESUMEN -->
      <div style="background:var(--bg3);border-radius:var(--radius-sm);padding:1rem;margin-bottom:1.25rem">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text3);margin-bottom:.75rem;font-weight:500">Resumen del costo</div>
        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.4rem">
          <span style="color:var(--text2)">Precio por día</span>
          <span>$<?= number_format($vehiculo['precio_dia'],0,',','.') ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.4rem">
          <span style="color:var(--text2)">Número de días</span>
          <span id="num-dias"><?= $dias_prev ?></span>
        </div>
        <div style="height:1px;background:var(--border);margin:.6rem 0"></div>
        <div style="display:flex;justify-content:space-between;font-family:'Syne',sans-serif;font-weight:700">
          <span>Total a pagar</span>
          <span style="color:var(--green)" id="total-monto">$<?= number_format($monto_prev,0,',','.') ?></span>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="font-size:1rem;padding:.9rem">
        ✅ Confirmar reserva
      </button>
    </form>
  </div>
  <?php endif; ?>

</div>
</div>
<script>
const precioDia = <?= $vehiculo['precio_dia'] ?>;
function calcTotal(){
  const fi = document.getElementById('fi').value;
  const ff = document.getElementById('ff').value;
  if(fi && ff && ff > fi){
    const dias = Math.round((new Date(ff)-new Date(fi))/(1000*60*60*24));
    const total = dias * precioDia;
    document.getElementById('num-dias').textContent = dias;
    document.getElementById('total-monto').textContent = '$' + total.toLocaleString('es-CO');
  }
}
</script>
<?php renderFoot(); ?>
