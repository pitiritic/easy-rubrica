<div class="container py-4" id="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h3 class="text-primary fw-bold mb-0">
            <i class="fa-solid fa-chart-line"></i> 
            <?= $currentUser['rol'] === 'alumno' ? 'Mis Calificaciones' : 'Panel de Calificaciones' ?>
        </h3>
        <div class="d-flex gap-2 align-items-center">
            <a href="<?= $is_print_mode ? '?action=notas&vista='.$vista_actual : 'index.php?action=home' ?>" class="btn btn-outline-secondary btn-sm px-3 bg-white shadow-sm">
                <i class="fa-solid <?= $is_print_mode ? 'fa-rotate-left' : 'fa-arrow-left' ?> me-1"></i> 
                <?= $is_print_mode ? 'Ver todos' : 'Volver' ?>
            </a>
        </div>
    </div>

    <!-- Cabecera de impresión general (Lista) -->
    <div class="d-none d-print-block mb-4 text-center">
        <h2 class="fw-bold">Informe de Calificaciones - <?= $vista_actual == 'por_alumno' ? 'Detalle por Alumno' : 'Detalle por Tarea' ?></h2>
        <p class="text-muted">Generado el <?= date('d/m/Y H:i') ?></p>
        <hr>
    </div>

    <?php if($currentUser['rol'] === 'alumno'): ?>
        <!-- VISTA ALUMNO (ANÓNIMA) -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="fa-solid fa-graduation-cap me-2"></i> Mis Notas por Tarea</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-muted text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 50%;">Tarea / Actividad</th>
                                <th style="width: 30%;">Clase</th>
                                <th class="text-center" style="width: 20%;">Nota Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($notas_agrupadas as $n): 
                                $media = 0; $total_sub = 0;
                                foreach($n['subbloques'] as $sb) { $media += $sb['suma_notas']; $total_sub += $sb['total_evals']; }
                                $final = $total_sub > 0 ? $media / $total_sub : 0;
                            ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($n['nombre']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($n['extra']) ?></span></td>
                                    <td class="text-center">
                                        <span class="badge <?= $final >= 5 ? 'bg-success' : 'bg-danger' ?> fs-6 px-3">
                                            <?= number_format($final, 2) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- VISTA PROFESOR / ADMIN -->
        <div class="d-flex align-items-center mb-4 d-print-none gap-3">
            <div class="btn-group shadow-sm">
                <a href="?action=notas&vista=por_tarea" class="btn <?= $vista_actual == 'por_tarea' ? 'btn-primary' : 'btn-outline-primary' ?>">Por Tareas</a>
                <a href="?action=notas&vista=por_alumno" class="btn <?= $vista_actual == 'por_alumno' ? 'btn-primary' : 'btn-outline-primary' ?>">Por Alumnos</a>
            </div>
            <div class="search-box-narrow">
                <input type="text" id="searchInput" class="form-control" placeholder="Filtrar..." onkeyup="filterResults()">
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
                        <div class="accordion-body bg-white p-0">
                            <?php foreach($bloque['subbloques'] as $sub): 
                                $media = $sub['total_evals'] > 0 ? $sub['suma_notas'] / $sub['total_evals'] : 0;
                            ?>
                                <div class="card border-0 rounded-0 border-bottom">
                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                        <a href="?action=notas&vista=<?= $vista_actual ?>&export_pdf=<?= $vista_actual=='por_tarea' ? $sub['evaluado_id'] : $sub['tarea_id'] ?>&tipo_export=<?= $vista_actual=='por_tarea' ? 'alumno' : 'tarea' ?>" class="text-decoration-none text-dark fw-bold text-uppercase small">
                                            <?= htmlspecialchars($sub['titulo_principal']) ?> <i class="fa-solid fa-file-pdf ms-1 text-danger d-print-none"></i>
                                        </a>
                                        <span class="badge <?= $media >= 5 ? 'bg-success' : 'bg-danger' ?>">Media: <?= number_format($media, 2) ?></span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 align-middle">
                                            <thead class="table-light x-small text-muted">
                                                <tr>
                                                    <th class="ps-3" style="width: 35%;">EVALUADOR</th>
                                                    <th class="text-center" style="width: 20%;">TIPO</th>
                                                    <th class="text-center" style="width: 20%;">NOTA</th>
                                                    <th class="text-end pe-3" style="width: 25%;">FECHA</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($sub['evaluaciones'] as $ev): ?>
                                                    <tr>
                                                        <td class="ps-3 fw-bold small text-dark">
                                                            <a href="javascript:void(0)" class="text-dark text-decoration-none" onclick="verDetalleRubrica(<?= $ev['id'] ?>, '<?= addslashes($bloque['nombre']) ?>', '<?= addslashes($sub['titulo_principal']) ?>')">
                                                                <?= htmlspecialchars($ev['evaluador_nombre']) ?> <i class="fa-solid fa-eye ms-1 text-primary d-print-none"></i>
                                                            </a>
                                                        </td>
                                                        <td class="text-center"><span class="badge <?= $ev['tipo']=='auto'?'bg-success':($ev['tipo']=='hetero'?'bg-danger':'bg-primary') ?> small fw-normal" style="width: 90px;"><?= ucfirst($ev['tipo']) ?></span></td>
                                                        <td class="text-center fw-bold text-dark"><?= number_format($ev['calificacion_final'], 2) ?></td>
                                                        <td class="text-end pe-3 text-muted small">
                                                            <?= date('d/m/y', strtotime($ev['fecha'])) ?>
                                                            <button type="button" class="btn btn-sm text-danger p-0 ms-2 d-print-none" onclick="confirmarBorradoEval(<?= $ev['id'] ?>)"><i class="fa-solid fa-trash-can"></i></button>
                                                        </td>
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

<!-- Modal Detalle Rubrica -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title fw-bold text-primary w-100" id="tituloModal">Detalle</h5>
                <div class="d-print-none">
                    <button type="button" class="btn btn-sm btn-outline-danger me-2" onclick="window.print()"><i class="fa-solid fa-file-pdf me-1"></i> Imprimir PDF</button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body bg-white p-3" id="contenidoDetalle"></div>
        </div>
    </div>
</div>

<!-- Modal Borrado -->
<div class="modal fade" id="modalBorrarEval" tabindex="-1"><div class="modal-dialog modal-sm"><form method="POST" class="modal-content"><div class="modal-header bg-danger text-white py-2"><h6>Borrar Evaluación</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body py-3"><input type="hidden" name="borrar_evaluacion" value="1"><input type="hidden" name="eval_id" id="borrar_eval_id"><p class="small">¿Seguro que quieres eliminar esta calificación?</p><div class="form-check form-switch small"><input class="form-check-input" type="checkbox" name="permitir_reintento" id="re_check" value="1" checked><label class="form-check-label fw-bold" for="re_check">Permitir reintento</label></div></div><div class="modal-footer py-1"><button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">CONFIRMAR</button></div></form></div></div>

<script>
    function filterResults() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        Array.from(document.getElementsByClassName('nota-item')).forEach(item => {
            item.style.display = item.querySelector('.item-name').innerText.toLowerCase().includes(query) ? "" : "none";
        });
    }

    function confirmarBorradoEval(id) { document.getElementById('borrar_eval_id').value = id; new bootstrap.Modal(document.getElementById('modalBorrarEval')).show(); }

    function verDetalleRubrica(evaluacionId, nameOne, nameTwo) {
        const container = document.getElementById('contenidoDetalle');
        container.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>';
        new bootstrap.Modal(document.getElementById('modalDetalle')).show();

        fetch('api/get_detalle_evaluacion.php?id=' + evaluacionId)
            .then(res => res.json())
            .then(data => {
                document.getElementById('tituloModal').innerHTML = `
                    <div style="font-size: 1rem; color: #000 !important; line-height: 1.4;">
                        Evaluador: <strong>${data.evaluador_nombre}</strong> | Alumno: <strong>${nameOne}</strong><br>
                        Tarea: <strong>${nameTwo}</strong> | Nota: <span class="badge bg-primary fs-6">${parseFloat(data.calificacion_final).toFixed(2)}</span>
                    </div>`;
                
                let html = '<div class="row g-2">';
                data.criterios.forEach(c => {
                    html += `
                    <div class="col-12 mb-3">
                        <div class="card border-dark shadow-none rounded-0">
                            <div class="card-header bg-light fw-bold py-1 text-dark" style="border-bottom: 2px solid #000 !important; font-size:0.9rem;">${c.nombre}</div>
                            <div class="card-body p-0 bg-white">
                                <div class="row g-0">`;
                    c.niveles.forEach(n => {
                        const esSel = (n.valor == c.valor_seleccionado);
                        const colors = {1: '#dc3545', 2: '#ffc107', 3: '#0dcaf0', 4: '#198754'};
                        const bg = esSel ? `background-color: ${colors[n.valor]} !important; color: ${n.valor==2?'#000':'#fff'} !important;` : 'background-color: #fff !important; color: #000 !important;';
                        
                        html += `
                        <div class="col-3">
                            <div class="d-flex flex-column text-center square-box" style="${bg} border: 1px solid #000 !important; min-height:115px;">
                                <div class="fw-bold border-bottom py-1" style="font-size:0.6rem; background:rgba(0,0,0,0.05); height:22px; color:inherit !important;">${n.etiqueta} (${n.valor})</div>
                                <div class="p-2 text-start flex-grow-1" style="font-size:0.75rem; line-height:1.1; font-weight:700; color:inherit !important;">${n.descriptor}</div>
                            </div>
                        </div>`;
                    });
                    html += `</div></div></div></div>`;
                });
                container.innerHTML = html + '</div>';
            });
    }

    // Lógica para abrir acordeón por Tarea desde Dashboard
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tareaId = urlParams.get('tarea_id');
        if (tareaId) {
            const targetCollapse = document.getElementById('collapseT' + tareaId);
            if (targetCollapse) {
                new bootstrap.Collapse(targetCollapse, { show: true });
                targetCollapse.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });

    <?php if($is_print_mode): ?>
        window.addEventListener('load', () => { setTimeout(() => { window.print(); }, 1200); });
        window.onafterprint = () => { window.location.href = "?action=notas&vista=<?= $vista_actual ?>"; };
    <?php endif; ?>
</script>

<style>
    .search-box-narrow { width: 250px; }
    .square-box { border-radius: 0 !important; }
    
    @media print {
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        html, body { background: #fff !important; margin: 0 !important; padding: 0 !important; height: auto !important; }

        .d-print-none, .navbar, .btn-group, .search-box-narrow, .accordion-button::after, .btn-close, .modal-backdrop { display: none !important; }

        /* ORIENTACIÓN LANDSCAPE SIEMPRE PARA RÚBRICAS INDIVIDUALES */
        @page { size: A4 landscape; margin: 0.5cm; }
        
        <?php if($is_print_mode && $vista_actual == 'por_tarea'): ?>
            /* Si es el informe general de una tarea, usamos vertical */
            @page { size: A4 portrait; margin: 1cm; }
        <?php endif; ?>

        /* Ocultar fondo cuando el modal está abierto para evitar páginas en blanco */
        body.modal-open #main-container { display: none !important; }
        body.modal-open .modal { position: absolute !important; left: 0; top: 0; width: 100% !important; display: block !important; overflow: visible !important; visibility: visible !important; }
        body.modal-open .modal-dialog { max-width: 100% !important; width: 100% !important; margin: 0 !important; }
        body.modal-open .modal-content { border: none !important; }

        /* Caso impresión de Listado */
        body:not(.modal-open) #main-container { display: block !important; width: 100% !important; }
        body:not(.modal-open) .collapse { display: block !important; height: auto !important; }
        body:not(.modal-open) .accordion-item { border: 1px solid #000 !important; margin-bottom: 10px !important; break-inside: avoid; }

        .card { border: 1.5px solid #000 !important; break-inside: avoid !important; }
        .card-header { background-color: #eee !important; color: #000 !important; }
        strong, div, span, p { color: #000 !important; }
    }
</style>