<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4 text-white p-3 rounded shadow-sm" style="background-color: #1b355c;">
        <h3 class="mb-0"><i class="fa-solid fa-clipboard-check"></i> Auditoría de Eventos</h3>
        <div class="d-flex gap-2">
            <a href="?action=auditoria&do=exportar_csv&q=<?=urlencode($busqueda)?>&evento=<?=urlencode($filtro_evento)?>" class="btn btn-success btn-sm fw-bold">
                <i class="fa-solid fa-file-csv"></i> Exportar CSV
            </a>
            <a href="index.php?action=ajustes" class="btn btn-light btn-sm fw-bold">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Buscador -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <input type="hidden" name="action" value="auditoria">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Buscar por usuario, detalle o IP..." value="<?= htmlspecialchars($busqueda) ?>">
                </div>
                <div class="col-md-4">
                    <select name="evento" class="form-select form-select-sm">
                        <option value="">-- Todos los eventos --</option>
                        <?php foreach($eventos_disponibles as $ev): ?>
                            <option value="<?= $ev ?>" <?= $filtro_evento == $ev ? 'selected' : '' ?>><?= $ev ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">FILTRAR</button>
                    <a href="?action=auditoria" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-eraser"></i></a>
                </div>
            </form>
        </div>
    </div>

    <?php if(isset($mensaje) && $mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-4 border-success"><?= $mensaje ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted text-uppercase">
                        <tr>
                            <th class="ps-4">Fecha y Hora</th>
                            <th>Usuario</th>
                            <th>Evento / Acción</th>
                            <th>Detalles</th>
                            <th class="text-center pe-4">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $l): ?>
                        <tr>
                            <td class="ps-4 small text-nowrap"><?= $l['fecha'] ?></td>
                            <td><span class="badge bg-primary px-2 py-1"><?= htmlspecialchars($l['usuario_nombre']) ?></span></td>
                            <td><div class="fw-bold small text-dark"><?= htmlspecialchars($l['evento']) ?></div></td>
                            <td><div class="small text-muted"><?= htmlspecialchars($l['detalles']) ?></div></td>
                            <td class="text-center pe-4 small text-secondary"><code><?= $l['ip'] ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación con persistencia -->
    <?php if ($paginas_totales > 1): ?>
    <nav class="mb-4">
        <ul class="pagination pagination-sm justify-content-center">
            <?php for($i=1; $i<=$paginas_totales; $i++): ?>
                <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                    <a class="page-link" href="?action=auditoria&p=<?= $i ?>&q=<?=urlencode($busqueda)?>&evento=<?=urlencode($filtro_evento)?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <?php if($total > 0): ?>
    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border small">
        <div class="text-muted">Mostrando <strong><?= count($logs) ?></strong> de <strong><?= $total ?></strong> registros.</div>
        <form method="POST" onsubmit="return confirm('¿Borrar historial?');"><button type="submit" name="borrar_todo" class="btn btn-outline-danger btn-sm fw-bold"><i class="fa-solid fa-eraser"></i> LIMPIAR REGISTRO</button></form>
    </div>
    <?php endif; ?>
</div>