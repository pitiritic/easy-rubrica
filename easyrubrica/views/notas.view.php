<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h3 class="text-primary fw-bold mb-0">
            <i class="fa-solid fa-chart-line"></i> 
            <?= $currentUser['rol'] === 'alumno' ? 'Mis Calificaciones' : 'Panel de Calificaciones' ?>
        </h3>
        <div class="d-flex gap-2 align-items-center">
            <a href="?action=notas&vista=<?= $vista_actual ?>" class="btn btn-outline-secondary btn-sm px-3 bg-white shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="d-none d-print-block mb-4 text-center">
        <h2 class="fw-bold">Informe de Calificaciones</h2>
        <p class="text-muted small">Generado por EasyRúbrica el <?= date('d/m/Y H:i') ?></p>
        <hr>
    </div>

    <?php if($currentUser['rol'] === 'alumno'): ?>
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4">Tarea / Actividad</th>
                            <th>Clase</th>
                            <th class="text-center" style="width: 120px;">Nota Final</th>
                            <th class="text-end pe-4 d-print-none" style="width: 120px;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($notas_agrupadas as $id_p => $bloque): 
                             $total_b = 0; $count_b = 0;
                             foreach($bloque['subbloques'] as $sb) { $total_b += $sb['suma_notas']; $count_b += $sb['total_evals']; }
                             $nota_media = $count_b > 0 ? $total_b / $count_b : 0;
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($bloque['nombre']) ?></td>
                                <td><span class="text-muted small"><?= htmlspecialchars($bloque['extra']) ?></span></td>
                                <td class="text-center fw-bold <?= $nota_media >= 5 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($nota_media, 2) ?>
                                </td>
                                <td class="text-end pe-4 d-print-none">
                                    <span class="badge bg-success-subtle text-success border">Evaluada</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex align-items-center mb-4 d-print-none gap-3">
            <div class="btn-group shadow-sm">
                <a href="?action=notas&vista=por_tarea" class="btn <?= $vista_actual == 'por_tarea' ? 'btn-primary' : 'btn-outline-primary' ?>">Por Tareas</a>
                <a href="?action=notas&vista=por_alumno" class="btn <?= $vista_actual == 'por_alumno' ? 'btn-primary' : 'btn-outline-primary' ?>">Por Alumnos</a>
            </div>
            <div class="search-box-narrow">
                <div class="input-group shadow-sm">
                    <input type="text" id="searchInput" class="form-control border-end-0" placeholder="Filtrar..." onkeyup="filterResults()">
                    <span class="input-group-text bg-white border-start-0 text-primary"><i class="fa-solid fa-magnifying-glass"></i></span>
                </div>
            </div>
        </div>

        <div class="accordion shadow-sm border-0" id="accordionNotas">
            <?php foreach($notas_agrupadas as $id_p => $bloque): ?>
                <div class="accordion-item border-0 mb-2 rounded overflow-hidden nota-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $is_print_mode ? '' : 'collapsed' ?> py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $id_p ?>">
                            <div class="d-flex justify-content-between w-100 align-items-center pe-3">
                                <div>
                                    <i class="fa-solid <?= $bloque['icono'] ?> me-2 text-primary"></i>
                                    <span class="fw-bold item-name"><?= htmlspecialchars($bloque['nombre']) ?></span>
                                    <span class="badge bg-light text-dark ms-2 border fw-normal small"><?= htmlspecialchars($bloque['extra']) ?></span>
                                </div>
                                <div class="d-print-none">
                                    <a href="?action=notas&export_csv=<?= substr($id_p, 1) ?>&tipo_export=<?= substr($id_p, 0, 1) == 'T' ? 'tarea' : 'alumno' ?>" class="btn btn-sm btn-outline-success border-0"><i class="fa-solid fa-file-csv fa-lg"></i></a>
                                    <a href="?action=notas&vista=<?= $vista_actual ?>&export_pdf=<?= substr($id_p, 1) ?>&tipo_export=<?= substr($id_p, 0, 1) == 'T' ? 'tarea' : 'alumno' ?>" class="btn btn-sm btn-outline-danger border-0"><i class="fa-solid fa-file-pdf fa-lg"></i></a>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse<?= $id_p ?>" class="accordion-collapse collapse <?= $is_print_mode ? 'show' : '' ?>" data-bs-parent="#accordionNotas">
                        <div class="accordion-body bg-white">
                            <?php foreach($bloque['subbloques'] as $sub): 
                                $media = $sub['total_evals'] > 0 ? $sub['suma_notas'] / $sub['total_evals'] : 0;
                            ?>
                                <div class="card mb-3 border-light shadow-none">
                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                        <span class="small fw-bold text-uppercase text-muted"><?= htmlspecialchars($sub['titulo_principal']) ?></span>
                                        <span class="badge <?= $media >= 5 ? 'bg-success' : 'bg-danger' ?>">Nota: <?= number_format($media, 2) ?></span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 align-middle">
                                            <thead class="table-light x-small">
                                                <tr>
                                                    <th class="ps-3">Evaluador</th>
                                                    <th style="width: 120px; text-align: center;">Tipo</th>
                                                    <th style="width: 100px; text-align: center;">Nota</th>
                                                    <th class="text-end pe-3" style="width: 100px;">Fecha</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($sub['evaluaciones'] as $ev): ?>
                                                    <tr>
                                                        <td class="ps-3 fw-bold small"><?= htmlspecialchars($ev['evaluador_nombre']) ?></td>
                                                        <td style="width: 120px; text-align: center;">
                                                            <span class="badge bg-info small fw-normal d-inline-block" style="width: 70px;"><?= ucfirst($ev['tipo']) ?></span>
                                                        </td>
                                                        <td class="text-center fw-bold" style="width: 100px;"><?= number_format($ev['calificacion_final'], 2) ?></td>
                                                        <td class="text-end pe-3 text-muted small" style="width: 100px;"><?= date('d/m/y', strtotime($ev['fecha'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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
        const query = document.getElementById('searchInput').value.toLowerCase();
        Array.from(document.getElementsByClassName('nota-item')).forEach(item => {
            const text = item.querySelector('.item-name').innerText.toLowerCase();
            item.style.display = text.includes(query) ? "" : "none";
        });
    }

    <?php if($is_print_mode): ?>
    window.addEventListener('load', () => {
        setTimeout(() => {
            window.print();
        }, 800);
    });
    <?php endif; ?>
</script>

<style>
    .search-box-narrow { width: 220px; }
    .x-small { font-size: 0.7rem; color: #777; text-transform: uppercase; }
    .accordion-button:not(.collapsed) { background-color: #f8f9fa; color: #0d6efd; box-shadow: none; }
    
    /* Forzar alineación de tablas */
    .table { table-layout: fixed; }
    .table th, .table td { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    
    @media print {
        .d-print-none, .btn, .search-box-narrow, .accordion-button::after { display: none !important; }
        .accordion-collapse { display: block !important; visibility: visible !important; }
        .accordion-button { background: #eee !important; border: 1px solid #ccc !important; color: black !important; }
        .card { border: 1px solid #eee !important; page-break-inside: avoid; }
        body { padding: 0; background: white; }
        .container { max-width: 100% !important; width: 100% !important; }
    }
</style>
