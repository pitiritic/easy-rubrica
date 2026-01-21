<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h3 class="text-primary fw-bold mb-0">
            <i class="fa-solid fa-chart-line"></i> 
            <?= $currentUser['rol'] === 'alumno' ? 'Mis Calificaciones' : 'Panel de Calificaciones' ?>
        </h3>

        <div class="d-flex gap-2 align-items-center">
            <a href="index.php" class="btn btn-outline-secondary btn-sm px-3 bg-white shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <?php if(empty($notas_agrupadas) && $currentUser['rol'] !== 'alumno'): ?>
        <div class="alert alert-info shadow-sm">
            <i class="fa-solid fa-circle-info me-2"></i> No se han encontrado evaluaciones vinculadas a tus tareas o clases.
        </div>
    <?php endif; ?>

    <?php if($currentUser['rol'] === 'alumno'): ?>
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4">Tarea / Actividad</th>
                            <th>Clase</th>
                            <th class="text-center">Nota Final</th>
                            <th class="text-end pe-4">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $encontrado = false;
                        foreach($notas_agrupadas as $id_p => $bloque): 
                            $encontrado = true;
                            // Calculamos la media del bloque para el alumno
                            $total_bloque = 0;
                            $count_bloque = 0;
                            foreach($bloque['subbloques'] as $sub){
                                foreach($sub['evaluaciones'] as $ev){
                                    $total_bloque += $ev['calificacion_final'];
                                    $count_bloque++;
                                }
                            }
                            $nota_media = $count_bloque > 0 ? $total_bloque / $count_bloque : 0;
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($bloque['nombre']) ?></span>
                                </td>
                                <td><span class="text-muted small"><?= htmlspecialchars($bloque['extra']) ?></span></td>
                                <td class="text-center">
                                    <span class="h5 mb-0 fw-bold <?= $nota_media >= 5 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($nota_media, 2) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="badge bg-success-subtle text-success border">Evaluada</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if(!$encontrado): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-3 d-block"></i>
                                    Aún no tienes calificaciones registradas.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <div class="d-flex align-items-center mb-4 d-print-none gap-3">
            <div class="btn-group shadow-sm">
                <a href="?action=notas&vista=por_tarea" class="btn <?= $vista_actual == 'por_tarea' ? 'btn-primary' : 'btn-outline-primary' ?> shadow-none">Por Tareas</a>
                <a href="?action=notas&vista=por_alumno" class="btn <?= $vista_actual == 'por_alumno' ? 'btn-primary' : 'btn-outline-primary' ?> shadow-none">Por Alumnos</a>
            </div>
            <div class="search-box-narrow">
                <div class="input-group shadow-sm">
                    <input type="text" id="searchInput" class="form-control border-end-0" placeholder="Buscar..." onkeyup="filterResults()">
                    <span class="input-group-text bg-white border-start-0 text-primary"><i class="fa-solid fa-magnifying-glass"></i></span>
                </div>
            </div>
        </div>

        <div class="accordion shadow-sm border-0" id="accordionNotas">
            <?php foreach($notas_agrupadas as $id_p => $bloque): ?>
                <div class="accordion-item border-0 mb-2 rounded overflow-hidden nota-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $id_p ?>">
                            <div class="d-flex justify-content-between w-100 align-items-center pe-3">
                                <div>
                                    <i class="fa-solid <?= $bloque['icono'] ?> me-2 text-primary"></i>
                                    <span class="fw-bold item-name"><?= htmlspecialchars($bloque['nombre']) ?></span>
                                    <?php if(!empty($bloque['extra'])): ?>
                                        <span class="badge bg-light text-dark ms-2 border fw-normal small"><?= htmlspecialchars($bloque['extra']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="?action=notas&export_csv=<?= substr($id_p, 1) ?>&tipo_export=<?= substr($id_p, 0, 1) == 'T' ? 'tarea' : 'alumno' ?>"
                                   class="btn btn-sm btn-outline-success border-0 py-0" title="Exportar CSV">
                                    <i class="fa-solid fa-file-csv fa-lg"></i>
                                </a>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse<?= $id_p ?>" class="accordion-collapse collapse" data-bs-parent="#accordionNotas">
                        <div class="accordion-body bg-white">
                            <?php foreach($bloque['subbloques'] as $id_sub => $sub): ?>
                                <div class="card mb-3 border-light shadow-none">
                                    <div class="card-header bg-light py-2 small fw-bold text-uppercase text-muted"><?= htmlspecialchars($sub['titulo_principal']) ?></div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0 align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-3">Evaluador</th>
                                                        <th>Tipo</th>
                                                        <th class="text-center">Nota</th>
                                                        <th class="text-end pe-3">Fecha</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $suma_sub = 0; foreach($sub['evaluaciones'] as $ev): $suma_sub += $ev['calificacion_final']; ?>
                                                        <tr>
                                                            <td class="ps-3 fw-bold small text-primary"><?= htmlspecialchars($ev['evaluador_nombre'] ?? 'Externo') ?></td>
                                                            <td><span class="badge bg-info small fw-normal"><?= ucfirst($ev['tipo']) ?></span></td>
                                                            <td class="text-center fw-bold"><?= number_format($ev['calificacion_final'], 2) ?></td>
                                                            <td class="text-end pe-3 text-muted small"><?= date('d/m/y', strtotime($ev['fecha'])) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function filterResults() {
        const input = document.getElementById('searchInput');
        if(!input) return;
        const filter = input.value.toLowerCase();
        const items = document.getElementsByClassName('nota-item');
        for (let i = 0; i < items.length; i++) {
            const nameSpan = items[i].getElementsByClassName('item-name')[0];
            const nameText = nameSpan.textContent || nameSpan.innerText;
            items[i].style.display = nameText.toLowerCase().indexOf(filter) > -1 ? "" : "none";
        }
    }
</script>

<style>
    .search-box-narrow { width: 220px; }
    .accordion-button:not(.collapsed) { background-color: #f0f7ff; color: #0d6efd; box-shadow: none; border-bottom: 1px solid #dee2e6; }
    .table-light th { font-size: 0.75rem; color: #777; text-transform: uppercase; }
    .nota-item { border: 1px solid #eee !important; }
</style>
