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
        <div class="card shadow-sm border-0 d-print-none">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4">Tarea / Actividad</th>
                            <th>Clase</th>
                            <th class="text-center" style="width: 120px;">Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($notas_agrupadas as $n): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($n['nombre']) ?></td>
                                <td><?= htmlspecialchars($n['extra']) ?></td>
                                <td class="text-center">
                                    <?php foreach($n['subbloques'] as $sub): ?>
                                        <span class="badge bg-primary fs-6 px-3"><?= number_format($sub['suma_notas'] / $sub['total_evals'], 2) ?></span>
                                    <?php endforeach; ?>
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
                                                    <th class="ps-3">EVALUADOR</th>
                                                    <th class="text-center" style="width: 120px;">TIPO</th>
                                                    <th class="text-center" style="width: 100px;">NOTA</th>
                                                    <th class="text-end pe-3" style="width: 100px;">FECHA</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($sub['evaluaciones'] as $ev): ?>
                                                    <tr>
                                                        <td class="ps-3 fw-bold small text-uppercase">
                                                            <a href="javascript:void(0)" class="text-decoration-none text-dark" onclick="verDetalleRubrica(<?= $ev['id'] ?>, '<?= addslashes($bloque['nombre']) ?>', '<?= addslashes($sub['titulo_principal']) ?>')">
                                                                <?= htmlspecialchars($ev['evaluador_nombre']) ?>
                                                            </a>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php 
                                                                $tipo = strtolower($ev['tipo']);
                                                                $badgeClass = ($tipo == 'auto') ? 'bg-success' : (($tipo == 'hetero') ? 'bg-danger' : 'bg-primary');
                                                                $tipoText = ($tipo == 'hetero') ? 'Hetero' : (($tipo == 'auto') ? 'Auto' : 'Coeval');
                                                            ?>
                                                            <span class="badge <?= $badgeClass ?> small fw-normal d-inline-block" style="width: 80px;">
                                                                <?= $tipoText ?>
                                                            </span>
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

<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light d-flex justify-content-between align-items-center py-2">
                <div id="tituloModalContainer" class="w-100">
                    <h5 class="modal-title fw-bold text-primary" id="tituloModal">Detalle</h5>
                </div>
                <div class="d-print-none">
                    <button type="button" class="btn btn-sm btn-outline-danger me-2" onclick="window.print()">
                        <i class="fa-solid fa-file-pdf me-1"></i> Imprimir PDF
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body bg-light" id="contenidoDetalle"></div>
        </div>
    </div>
</div>

<script>
    function filterResults() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        Array.from(document.getElementsByClassName('nota-item')).forEach(item => {
            const text = item.querySelector('.item-name').innerText.toLowerCase();
            item.style.display = text.includes(query) ? "" : "none";
        });
    }

    function verDetalleRubrica(evaluacionId, nombreAlumno, nombreRubrica) {
        const container = document.getElementById('contenidoDetalle');
        const titulo = document.getElementById('tituloModal');
        
        container.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>';
        const myModal = new bootstrap.Modal(document.getElementById('modalDetalle'));
        myModal.show();

        fetch('api/get_detalle_evaluacion.php?id=' + evaluacionId)
            .then(res => res.json())
            .then(data => {
                const tipo = data.tipo_evaluacion.toLowerCase();
                const badgeClass = (tipo == 'auto') ? 'bg-success' : ((tipo == 'hetero') ? 'bg-danger' : 'bg-primary');

                titulo.innerHTML = `
                    <div style="font-size: 1.1rem;">
                        Alumno: <span class="text-dark">${nombreAlumno}</span> | 
                        Rúbrica: <span class="text-dark">${nombreRubrica}</span>
                    </div>
                    <div class="mt-1">
                        Evaluador: <span class="text-dark">${data.evaluador_nombre || 'N/A'}</span> | 
                        Nota: <span class="badge bg-primary fs-5">${parseFloat(data.calificacion_final || 0).toFixed(2)}</span>
                        <span class="badge ${badgeClass} ms-2">${data.tipo_evaluacion}</span>
                    </div>
                `;

                let html = '<div class="row g-2">';
                data.criterios.forEach(c => {
                    html += `
                    <div class="col-12 mb-2">
                        <div class="card border-0 shadow-sm rubric-card">
                            <div class="card-header bg-white fw-bold py-1" style="font-size:0.85rem; border-bottom: 2px solid #0d6efd;">${c.nombre}</div>
                            <div class="card-body p-2"><div class="row g-1">`;
                    c.niveles.forEach(n => {
                        const esSeleccionado = (n.valor == c.valor_seleccionado);
                        const color = n.valor <= 1 ? 'danger' : (n.valor <= 2 ? 'warning' : (n.valor <= 3 ? 'info' : 'success'));
                        const claseBoton = esSeleccionado 
                            ? `btn-${color} active border-3 shadow-sm` 
                            : `btn-outline-dark bg-secondary bg-opacity-25 text-dark border-secondary`;
                        
                        html += `
                        <div class="col-3">
                            <div class="btn ${claseBoton} w-100 p-1 d-flex flex-column align-items-center justify-content-center square-btn" style="min-height:100px; cursor:default; pointer-events:none;">
                                <div class="fw-bold mb-1" style="font-size: 0.8rem;">${n.etiqueta} (${n.valor})</div>
                                <div class="descriptor-text" style="font-size: 0.75rem; line-height: 1.2;">${n.descriptor}</div>
                            </div>
                        </div>`;
                    });
                    html += `</div></div></div></div>`;
                });
                html += '</div>';
                container.innerHTML = html;
            });
    }

    <?php if($is_print_mode): ?>
    window.addEventListener('load', () => {
        setTimeout(() => { window.print(); }, 800);
    });
    <?php endif; ?>
</script>

<style>
    .search-box-narrow { width: 220px; }
    .x-small { font-size: 0.7rem; color: #777; text-transform: uppercase; }
    .accordion-button:not(.collapsed) { background-color: #f8f9fa; color: #0d6efd; box-shadow: none; }
    
    @media print {
        @page { size: A4 landscape; margin: 5mm; }
        body { visibility: hidden !important; background: white !important; height: auto !important; }
        #modalDetalle, #modalDetalle * { visibility: visible !important; }
        #modalDetalle { 
            position: absolute !important; 
            left: 0 !important; 
            top: 0 !important; 
            width: 100% !important; 
            display: block !important; 
            overflow: visible !important;
        }
        .modal-dialog { max-width: 100% !important; width: 100% !important; margin: 0 !important; }
        .modal-content { border: none !important; box-shadow: none !important; }
        .modal-header { border-bottom: 2px solid #0d6efd !important; padding: 10px !important; }
        .modal-body { background: white !important; padding: 10px !important; }
        .rubric-card { break-inside: avoid; border: 1px solid #eee !important; margin-bottom: 10px !important; }
        .square-btn { 
            min-height: 130px !important; 
            border-radius: 8px !important;
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
        }
        .col-3 { width: 25% !important; float: left !important; }
        .row { display: block !important; }
        .row::after { content: ""; display: table; clear: both; }
        .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>