<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-success fw-bold"><i class="fa-solid fa-chalkboard-user me-2"></i>Gestión de Clases</h3>
        <div class="d-flex align-items-center">
            <div class="btn-group btn-group-sm me-3" role="group">
                <a href="?action=gestion_clases_lista&set_vista=grid" class="btn <?= $vista_actual=='grid'?'btn-success':'btn-outline-success' ?>" title="Vista Iconos">
                    <i class="fa-solid fa-grid-2"></i>
                </a>
                <a href="?action=gestion_clases_lista&set_vista=lista" class="btn <?= $vista_actual=='lista'?'btn-success':'btn-outline-success' ?>" title="Vista Lista">
                    <i class="fa-solid fa-list"></i>
                </a>
            </div>

            <?php if($currentUser['rol'] === 'admin'): ?>
                <div class="form-check form-switch me-3 mt-1">
                    <input class="form-check-input" type="checkbox" id="toggleVerTodas" <?= $ver_todas ? 'checked' : '' ?> 
                           onchange="window.location.href='?action=gestion_clases_lista&toggle_ver_todas=' + (this.checked ? '1' : '0')">
                    <label class="form-check-label small fw-bold" for="toggleVerTodas">Ver todas</label>
                </div>
            <?php endif; ?>
            <a href="index.php?action=home" class="btn btn-outline-secondary btn-sm">Volver</a>
        </div>
    </div>

    <?php if($mensaje): ?> <div class="alert alert-success py-2 small"><?= $mensaje ?></div> <?php endif; ?>
    <?php if($error): ?> <div class="alert alert-danger py-2 small"><?= $error ?></div> <?php endif; ?>

    <div class="row mb-4 align-items-center">
        <div class="col-auto">
            <div class="input-group input-group-custom shadow-sm" style="width: 160px;"> 
                <span class="input-group-text bg-white border-end-0 text-muted ps-2 pe-1">
                    <i class="fa-solid fa-magnifying-glass small"></i>
                </span>
                <input type="text" id="inputBuscarClase" 
                       class="form-control border-start-0 py-2 ps-1" 
                       style="font-size: 0.85rem;"
                       placeholder="Filtrar..." 
                       onkeyup="filtrarClases()">
            </div>
        </div>
        
        <div class="col text-end">
            <button type="button" class="btn btn-primary fw-bold btn-sm shadow-sm py-2" style="width: 160px; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <i class="fa-solid fa-plus me-2"></i>Nueva Clase
            </button>
        </div>
    </div>

    <?php if($vista_actual === 'grid'): ?>
        <div class="row g-4" id="contenedorClases">
            <?php foreach ($clases as $clase): ?>
                <div class="col-md-6 col-lg-4 clase-card">
                    <div class="card h-100 shadow-sm border-0 border-top border-success border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 nombre-clase"><?= htmlspecialchars($clase['nombre']) ?></h6>
                                <span class="badge bg-success"><?= $clase['num_alumnos'] ?> Alum.</span>
                            </div>
                            <p class="x-small text-muted mb-2">Autor: <?= htmlspecialchars($clase['autor_nombre']) ?></p>
                            
                            <div class="bg-white border rounded mb-3 p-2" style="height: 100px; overflow-y: auto; font-size: 0.85rem;">
                                <?php
                                $stmtA = $pdo->prepare("SELECT u.id, u.nombre FROM usuarios u JOIN clase_usuario cu ON u.id = cu.usuario_id WHERE cu.clase_id = ? ORDER BY u.nombre ASC");
                                $stmtA->execute([$clase['id']]);
                                $alumnos = $stmtA->fetchAll();
                                foreach($alumnos as $alu): ?>
                                    <div class="d-flex justify-content-between border-bottom py-1">
                                        <span><?= htmlspecialchars($alu['nombre']) ?></span>
                                        <form method="POST" onsubmit="return confirm('¿Quitar alumno de la clase?');">
                                            <input type="hidden" name="eliminar_vinculo" value="1">
                                            <input type="hidden" name="clase_id" value="<?= $clase['id'] ?>">
                                            <input type="hidden" name="usuario_id" value="<?= $alu['id'] ?>">
                                            <button type="submit" class="btn btn-link p-0 text-danger" style="font-size: 0.7rem;"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex gap-1">
                                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="abrirVincular(<?= $clase['id'] ?>, '<?= htmlspecialchars($clase['nombre']) ?>')" title="Vincular Alumno"><i class="fa-solid fa-user-plus"></i></button>
                                <button class="btn btn-outline-warning btn-sm flex-fill" onclick="abrirClonar(<?= $clase['id'] ?>, '<?= htmlspecialchars($clase['nombre']) ?>')" title="Clonar Clase"><i class="fa-solid fa-copy"></i></button>
                                <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="abrirEditar(<?= $clase['id'] ?>, '<?= htmlspecialchars($clase['nombre']) ?>')" title="Editar Nombre"><i class="fa-solid fa-pen"></i></button>
                                <form method="POST" class="flex-fill" onsubmit="return confirm('¿Eliminar esta clase definitivamente?');">
                                    <input type="hidden" name="eliminar_clase" value="1"><input type="hidden" name="clase_id" value="<?= $clase['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" title="Eliminar Clase"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-success text-white">
                        <tr>
                            <th>Clase</th>
                            <th>Autor</th>
                            <th class="text-center">Alumnos</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clases as $clase): ?>
                            <tr class="clase-fila">
                                <td class="fw-bold nombre-clase"><?= htmlspecialchars($clase['nombre']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($clase['autor_nombre']) ?></span></td>
                                <td class="text-center"><?= $clase['num_alumnos'] ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" onclick="abrirVincular(<?= $clase['id'] ?>, '<?= htmlspecialchars($clase['nombre']) ?>')"><i class="fa-solid fa-user-plus"></i></button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="abrirClonar(<?= $clase['id'] ?>, '<?= htmlspecialchars($clase['nombre']) ?>')"><i class="fa-solid fa-copy"></i></button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="abrirEditar(<?= $clase['id'] ?>, '<?= htmlspecialchars($clase['nombre']) ?>')"><i class="fa-solid fa-pen"></i></button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?');">
                                        <input type="hidden" name="eliminar_clase" value="1"><input type="hidden" name="clase_id" value="<?= $clase['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Nueva Clase</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre de la Clase</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: 2º ESO - Matemáticas" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" name="crear_clase" class="btn btn-primary">Crear Clase</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold">Editar Nombre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="clase_id" id="edit_clase_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nuevo nombre</label>
                    <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="editar_clase" class="btn btn-warning fw-bold">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalVincular" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Vincular Alumno a: <span id="vincular_titulo" class="fw-bold"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="clase_id" id="vincular_clase_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Seleccionar Alumno</label>
                    <select name="usuario_id" class="form-select" required>
                        <option value="">Seleccione un alumno...</option>
                        <?php foreach($todos_los_alumnos as $alu): ?>
                            <option value="<?= $alu['id'] ?>"><?= htmlspecialchars($alu['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="vincular_alumno" class="btn btn-primary">Añadir Alumno</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalClonar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa-solid fa-copy me-2"></i>Clonar Clase</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="clase_id_original" id="clonar_clase_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre de la nueva clase</label>
                    <input type="text" name="nuevo_nombre" id="clonar_nuevo_nombre" class="form-control" required>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="copiar_alumnos" id="copiarAlumnosCheck" value="1" checked>
                    <label class="form-check-label" for="copiarAlumnosCheck">Copiar también los alumnos matriculados</label>
                </div>
                <?php if($currentUser['rol'] === 'admin'): ?>
                <div class="mt-3">
                    <label class="form-label fw-bold">Asignar a profesor (Solo Admin)</label>
                    <select name="nuevo_autor_id" class="form-select">
                        <?php foreach($profesores as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id']==$currentUser['id']?'selected':'' ?>><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="submit" name="clonar_clase" class="btn btn-info text-white">Clonar Ahora</button>
            </div>
        </form>
    </div>
</div>

<style>
    .input-group-custom .input-group-text, 
    .input-group-custom .form-control {
        border-radius: 8px !important;
        border-color: #dee2e6;
    }
    .input-group-custom .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    .input-group-custom .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
    .input-group-custom .form-control:focus {
        border-color: #198754;
        box-shadow: none;
    }
    .x-small { font-size: 0.75rem; }
</style>

<script>
// Filtrado instantáneo
function filtrarClases() {
    const query = document.getElementById('inputBuscarClase').value.toLowerCase();
    document.querySelectorAll('.clase-card').forEach(card => {
        const nombre = card.querySelector('.nombre-clase').innerText.toLowerCase();
        card.style.display = nombre.includes(query) ? "" : "none";
    });
    document.querySelectorAll('.clase-fila').forEach(row => {
        const nombre = row.querySelector('.nombre-clase').innerText.toLowerCase();
        row.style.display = nombre.includes(query) ? "" : "none";
    });
}

// Funciones para abrir modales y pasar datos
function abrirEditar(id, nombre) {
    const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
    document.getElementById('edit_clase_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    modal.show();
}

function abrirClonar(id, nombre) {
    const modal = new bootstrap.Modal(document.getElementById('modalClonar'));
    document.getElementById('clonar_clase_id').value = id;
    document.getElementById('clonar_nuevo_nombre').value = nombre + " (Copia)";
    modal.show();
}

function abrirVincular(id, nombre) {
    const modal = new bootstrap.Modal(document.getElementById('modalVincular'));
    document.getElementById('vincular_clase_id').value = id;
    document.getElementById('vincular_titulo').innerText = nombre;
    modal.show();
}
</script>
