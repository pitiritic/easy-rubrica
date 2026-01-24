<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary d-flex align-items-center gap-2 m-0">
            <i class="fa-solid fa-users fs-4"></i> Gestión de Usuarios
        </h3>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalImportar">
                <i class="fa-solid fa-file-import me-2"></i> Importar CSV
            </button>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario
            </button>
            <a href="index.php" class="btn btn-light btn-sm border px-3">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $mensaje ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm overflow-hidden">
        <form id="form-masivo" method="POST">
            <input type="hidden" name="borrar_masivo" value="1">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 40px;"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Clases</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): 
                            $es_dueno = (isset($u['creador_id']) && $u['creador_id'] == $currentUser['id']);
                            // Un profesor solo borra alumnos que él creó. Un admin borra todo.
                            $puede_borrar = ($currentUser['rol'] === 'admin' || ($currentUser['rol'] === 'profesor' && $es_dueno && $u['rol'] === 'alumno'));
                            // Un profesor solo edita perfil (nombre/pass) de sus alumnos. Si no es suyo, solo cambia clases.
                            $puede_editar_perfil = ($currentUser['rol'] === 'admin' || ($currentUser['rol'] === 'profesor' && $es_dueno));
                            
                            $badge_color = 'bg-primary'; 
                            if ($u['rol'] === 'admin') $badge_color = 'bg-danger'; 
                            if ($u['rol'] === 'profesor') $badge_color = 'bg-success'; 
                        ?>
                        <tr>
                            <td class="ps-4">
                                <?php if($puede_borrar): ?>
                                    <input type="checkbox" name="usuarios_ids[]" value="<?= $u['id'] ?>" class="user-check form-check-input">
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['usuario']) ?></td>
                            <td><span class="badge <?= $badge_color ?> rounded-pill fw-normal"><?= ucfirst($u['rol']) ?></span></td>
                            <td><span class="badge bg-light text-dark border fw-normal small"><?= htmlspecialchars($u['clase_nombre']) ?></span></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <?php if($currentUser['rol'] === 'admin' || $u['rol'] === 'alumno'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick='abrirEditar(<?= json_encode($u) ?>, <?= json_encode($u['clases_ids']) ?>, <?= $puede_editar_perfil ? 1 : 0 ?>)'>
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if($puede_borrar): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="borrarUno(<?= $u['id'] ?>)">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if($currentUser['rol'] === 'admin' || $currentUser['rol'] === 'profesor'): ?>
                <div class="p-3 bg-light border-top">
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmarMasivo()">
                        <i class="fa-solid fa-trash me-1"></i> Borrar seleccionados
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            <div class="modal-header text-white py-3" style="background-color: #198754;">
                <h5 class="modal-title">Importación Masiva (CSV)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="importar_csv" value="1">
                <div class="mb-4 p-3 border rounded bg-light text-center">
                    <p class="small text-muted mb-2">¿No tienes el formato correcto?</p>
                    <a href="?action=usuarios&descargar_plantilla=1" class="btn btn-sm btn-outline-success">
                        <i class="fa-solid fa-download me-1"></i> Descargar Plantilla CSV
                    </a>
                </div>
                <p class="small text-muted mb-3">Soporta separador coma (,) o punto y coma (;).</p>
                <input type="file" name="archivo_csv" class="form-control bg-light" accept=".csv" required>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn w-100 py-2 fw-bold text-white" style="background-color: #198754;">IMPORTAR USUARIOS</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title">Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="crear_usuario" value="1">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Usuario</label>
                    <input type="text" name="usuario" class="form-control bg-light" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nombre</label>
                    <input type="text" name="nombre" class="form-control bg-light" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email</label>
                    <input type="email" name="email" class="form-control bg-light">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Rol</label>
                    <select name="rol" class="form-select bg-light">
                        <option value="alumno">Alumno</option>
                        <?php if($currentUser['rol']=='admin'): ?>
                            <option value="profesor">Profesor</option>
                            <option value="admin">Admin</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Contraseña</label>
                    <input type="password" name="password" class="form-control bg-light" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Asignar Clases</label>
                    <select name="clases[]" class="form-select bg-light" multiple style="height: 100px;">
                        <?php foreach($all_clases as $cl): ?>
                            <option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">CREAR USUARIO</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="editar_usuario" value="1">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header text-white py-3" style="background-color: #6610f2;">
                <h5 class="modal-title">Editar Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="aviso-lectura" class="alert alert-info small py-2 d-none">
                    <i class="fa-solid fa-circle-info me-2"></i> Solo tienes permiso para gestionar las clases de este alumno.
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Usuario</label>
                        <input type="text" name="usuario" id="edit_usuario" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Nueva Contraseña (opcional)</label>
                        <input type="password" name="password" id="edit_password" class="form-control bg-light">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Nombre</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control bg-light">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Rol</label>
                        <select name="rol" id="edit_rol" class="form-select bg-light">
                            <option value="alumno">Alumno</option>
                            <?php if($currentUser['rol']=='admin'): ?>
                                <option value="profesor">Profesor</option>
                                <option value="admin">Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Clases</label>
                        <select name="clases[]" id="edit_clases" class="form-select bg-light" multiple style="height: 100px;">
                            <?php foreach($all_clases as $cl): ?>
                                <option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn w-100 py-2 fw-bold text-white" style="background-color: #6610f2;">GUARDAR CAMBIOS</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirEditar(u, clasesIds, puedeEditarPerfil) {
    document.getElementById('edit_id').value = u.id;
    document.getElementById('edit_usuario').value = u.usuario;
    document.getElementById('edit_nombre').value = u.nombre;
    document.getElementById('edit_email').value = u.email || "";
    document.getElementById('edit_rol').value = u.rol;
    
    // Bloqueo de campos si no es el dueño
    const campos = ['edit_usuario', 'edit_nombre', 'edit_email', 'edit_rol', 'edit_password'];
    campos.forEach(id => {
        const el = document.getElementById(id);
        if(el.tagName === 'SELECT') {
            el.disabled = !puedeEditarPerfil;
        } else {
            el.readOnly = !puedeEditarPerfil;
        }
    });

    // Mostrar/Ocultar aviso de permisos
    document.getElementById('aviso-lectura').classList.toggle('d-none', puedeEditarPerfil === 1);

    const s = document.getElementById('edit_clases');
    Array.from(s.options).forEach(o => o.selected = clasesIds.includes(parseInt(o.value)));
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}

function borrarUno(id) {
    if(confirm('¿Eliminar usuario?')) {
        const f = document.getElementById('form-masivo');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'usuarios_ids[]';
        input.value = id;
        f.appendChild(input);
        f.submit();
    }
}

const selectAll = document.getElementById('select-all'), userChecks = document.querySelectorAll('.user-check');
if(selectAll) {
    selectAll.addEventListener('change', () => userChecks.forEach(c => c.checked = selectAll.checked));
}

function confirmarMasivo() {
    const seleccionados = document.querySelectorAll('.user-check:checked').length;
    if(seleccionados === 0) {
        alert('Selecciona al menos un usuario.');
        return;
    }
    if(confirm('¿Eliminar los ' + seleccionados + ' usuarios seleccionados?')) {
        document.getElementById('form-masivo').submit();
    }
}
</script>