<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border-top border-4 border-primary">
        <div>
            <h3 class="mb-0 fw-bold text-dark">Mis Calificaciones</h3>
            <p class="text-muted mb-0">Resumen de notas finales obtenidas</p>
        </div>
        <i class="fa-solid fa-graduation-cap fa-2xl text-primary opacity-25"></i>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Actividad / Tarea</th>
                            <th>Rúbrica aplicada</th>
                            <th class="text-center">Nota Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($mis_resultados)): ?>
                            <tr><td colspan="3" class="text-center py-5 text-muted">Aún no tienes evaluaciones registradas.</td></tr>
                        <?php else: ?>
                            <?php foreach($mis_resultados as $res): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($res['titulo']) ?></div>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($res['rubrica']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6 px-3">
                                            <?= number_format($res['final'], 2) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #666; }
    .badge { border-radius: 8px; }
</style>
