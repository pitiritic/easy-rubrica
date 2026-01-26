<?php 
// Corrección para evitar el error Deprecated: se asegura que sea string
$nota_url = (string)($_GET['nota'] ?? '');
$alumno_url = (string)($_GET['alumno'] ?? '');
?>

<?php if($mensaje): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fa-solid fa-circle-check me-1"></i>
        <?= htmlspecialchars((string)$mensaje) ?>
        <?php if($alumno_url !== '' || $nota_url !== ''): ?>
            <strong>
                (<?= htmlspecialchars($alumno_url) ?>: <?= htmlspecialchars($nota_url) ?>)
            </strong>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(isset($error) && $error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars((string)$error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-list-ul"></i> Tareas Disponibles</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php if(empty($tareas_disponibles)): ?>
                    <div class="p-4 text-center text-muted">No hay tareas activas.</div>
                <?php else: ?>
                    <?php foreach($tareas_disponibles as $t): ?>
                        <div class="list-group-item p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars((string)$t['titulo']) ?></h6>
                                <span class="badge rounded-pill bg-success"><i class="fa-solid fa-clock"></i> Activa</span>
                            </div>
                            <small class="text-muted d-block mb-2">Clase: <?= htmlspecialchars((string)$t['clase_nombre']) ?></small>
                            <button class="btn btn-sm btn-outline-primary w-100 fw-bold mt-1" 
                                    onclick="seleccionarTarea(<?= (int)$t['tarea_id'] ?>, <?= (int)$t['rubrica_id'] ?>, '<?= addslashes((string)$t['titulo']) ?>')">
                                Evaluar ahora <i class="fa-solid fa-chevron-right ms-1"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div id="welcome_message" class="card border-0 shadow-sm p-5 text-center bg-white rounded-3">
            <i class="fa-solid fa-file-signature text-light mb-3" style="font-size: 4rem;"></i>
            <h4 class="text-muted">Selecciona una tarea para comenzar</h4>
            <p class="text-muted small">Podrás evaluar a los alumnos matriculados en la clase correspondiente.</p>
        </div>

        <div id="eval_form_container" style="display: none;">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-primary" id="display_tarea_nombre">Tarea</h6>
                    <span class="badge bg-light text-primary border small">Rúbrica activa</span>
                </div>
                <div class="card-body bg-light p-3">
                    <form id="formEvaluacion" method="POST" action="index.php?action=evaluar">
                        <input type="hidden" name="guardar_evaluacion" value="1">
                        <input type="hidden" name="tarea_id" id="input_tarea_id">
                        <input type="hidden" name="rubrica_id" id="input_rubrica_id">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small"><i class="fa-solid fa-user-graduate me-1"></i>Alumno a evaluar:</label>
                            <select name="evaluado_id" id="select_alumno" class="form-select form-select-sm border-0 shadow-sm" required>
                                <option value="">-- Selecciona un alumno --</option>
                            </select>
                        </div>

                        <div id="criterios_container"></div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-success fw-bold shadow-sm" name="guardar_evaluacion">
                                <i class="fa-solid fa-save me-2"></i>Guardar Evaluación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const currentUserId = <?= json_encode($_SESSION['user_id']) ?>;

function seleccionarTarea(tareaId, rubricaId, titulo) {
    document.getElementById('welcome_message').style.display = 'none';
    document.getElementById('eval_form_container').style.display = 'block';
    document.getElementById('display_tarea_nombre').innerText = titulo;
    document.getElementById('input_tarea_id').value = tareaId;
    document.getElementById('input_rubrica_id').value = rubricaId;
    
    fetch('api/get_alumnos.php?tarea_id=' + tareaId)
        .then(res => res.json())
        .then(data => {
            let html = '<option value="">-- Selecciona un alumno --</option>';
            if(data.length === 0) {
                html = '<option value="">No hay alumnos pendientes</option>';
            } else {
                data.forEach(a => {
                    let aviso = (a.id == currentUserId) ? ' (ERES TÚ - AUTOEVALUACIÓN)' : '';
                    html += `<option value="${a.id}">${a.nombre}${aviso}</option>`;
                });
            }
            document.getElementById('select_alumno').innerHTML = html;
        });

    const container = document.getElementById('criterios_container');
    container.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';

    fetch('api/get_rubrica.php?id=' + rubricaId)
        .then(res => res.json())
        .then(data => {
            let html = '';
            data.forEach(c => {
                html += `
                <div class="card mb-2 border-light shadow-sm">
                    <div class="card-header fw-bold bg-white py-2 text-secondary small">${c.nombre}</div>
                    <div class="card-body p-2">
                        <div class="row g-2">`;
                
                c.niveles.forEach(n => {
                    let color = n.valor <= 1 ? 'danger' : (n.valor <= 2 ? 'warning' : (n.valor <= 3 ? 'info' : 'success'));
                    html += `
                    <div class="col-md-3">
                        <input type="radio" class="btn-check" name="criterios[${c.id}]" id="crit_${c.id}_${n.id}" value="${n.valor}" required>
                        <label class="btn btn-outline-${color} w-100 h-100 text-start p-3 shadow-sm" for="crit_${c.id}_${n.id}">
                            <div class="fw-bold fs-6 mb-1">${n.etiqueta} (${n.valor})</div>
                            <div class="small lh-sm text-dark">${n.descriptor}</div>
                        </label>
                    </div>`;
                });
                
                html += `</div></div></div>`;
            });
            container.innerHTML = html;
        });
}
</script>