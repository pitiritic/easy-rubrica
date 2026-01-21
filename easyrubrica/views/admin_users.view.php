<div class="container py-4">
    <style>
        .btn-action { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; padding: 0; border-radius: 6px; transition: all 0.2s ease; border-width: 1.5px; }
        .btn-action i { font-size: 1rem; margin: 0 !important; }
        .action-group { gap: 8px !important; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .btn-header-csv { background-color: white; border: 1px solid #dee2e6; color: #333; font-weight: 500; }
        .btn-header-csv:hover { background-color: #f8f9fa; border-color: #c1c9d0; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary d-flex align-items-center gap-2 m-0" style="font-weight: 500;">
            <i class="fa-solid fa-users fs-4"></i> Gestión de Usuarios
        </h3>
        <div class="d-flex gap-2">
            <a href="?action=usuarios&descargar_plantilla=1" class="btn btn-header-csv btn-sm shadow-sm px-3">
                <i class="fa-solid fa-file-csv me-2" style="color: #198754;"></i> Descargar Plantilla
            </a>
            <button class="btn btn-header-csv btn-sm shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalImportar">
                <i class="fa-solid fa-file-import me-2" style="color: #198754;"></i> Importación Masiva
            </button>
            <button class="btn btn-primary btn-sm shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario
            </button>
            <a href="index.php" class="btn btn-light btn-sm border shadow-sm px-3 bg-white"><i class="fa-solid fa-arrow-left"></i> Volver</a>
        </div>
    </div>

    <?php if ($mensaje): ?><div class="alert alert-success shadow-sm alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= $mensaje ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger shadow-sm alert-dismissible fade show"><i class="fa-solid fa-circle-exclamation me-2"></i><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <form id="searchForm" method="GET" action="index.php" class="input-group input-group-sm border rounded">
                        <input type="hidden" name="action" value="usuarios">
                        <span class="input-group-text bg-white border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="q" id="input-search" class="form-control border-0 shadow-none" style="width: 250px;" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                    </form>
                </div>
                <div class="col-auto">
                    <button type="button" id="btn-borrar-masivo" class="btn btn-danger btn-sm d-none" onclick="confirmarMasivo()">
                        <i class="fa-solid fa-trash-can me-1"></i> Borrar seleccionados
                    </button>
                </div>
            </div>
        </div>
        
        <form id="form-masivo" method="POST">
            <input type="hidden" name="borrar_masivo" value="1">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.95rem;">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 40px;"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th><a href="?action=usuarios&q=<?=urlencode($search)?>&order_by=nombre&dir=<?=$next_dir?>" class="text-decoration-none text-secondary">Nombre <?= ($order_by === 'nombre') ? ($dir === 'ASC' ? '↑' : '↓') : '' ?></a></th>
                            <th><a href="?action=usuarios&q=<?=urlencode($search)?>&order_by=usuario&dir=<?=$next_dir?>" class="text-decoration-none text-secondary">Usuario <?= ($order_by === 'usuario') ? ($dir === 'ASC' ? '↑' : '↓') : '' ?></a></th>
                            <th><a href="?action=usuarios&q=<?=urlencode($search)?>&order_by=rol&dir=<?=$next_dir?>" class="text-decoration-none text-secondary">Rol <?= ($order_by === 'rol') ? ($dir === 'ASC' ? '↑' : '↓') : '' ?></a></th>
                            <th><a href="?action=usuarios&q=<?=urlencode($search)?>&order_by=clase_nombre&dir=<?=$next_dir?>" class="text-decoration-none text-secondary">Clases <?= ($order_by === 'clase_nombre') ? ($dir === 'ASC' ? '↑' : '↓') : '' ?></a></th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="ps-4"><input type="checkbox" name="usuarios_ids[]" value="<?= $u['id'] ?>" class="user-check form-check-input"></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['usuario']) ?></td>
                            <td><span class="badge <?= ($u['rol'] == 'admin') ? 'bg-danger' : (($u['rol'] == 'profesor') ? 'bg-success' : 'bg-primary') ?> rounded-pill fw-normal"><?= ucfirst($u['rol']) ?></span></td>
                            <td><span class="badge bg-light text-dark border fw-normal small"><?= htmlspecialchars($u['clase_nombre'] ?: '-') ?></span></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end action-group">
                                    <button type="button" class="btn btn-action" style="border-color: #6610f2; color: #6610f2;" onclick='abrirEditar(<?= json_encode($u) ?>, <?= json_encode($u['clases_ids']) ?>)'><i class="fa-solid fa-pen"></i></button>
                                    <button type="button" class="btn btn-action btn-outline-danger" onclick="borrarUno(<?= $u['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalCrear" tabindex="-1"><div class="modal-dialog"><form method="POST" class="modal-content border-0 shadow"><div class="modal-header bg-primary text-white border-0 py-3"><h5 class="modal-title">Nuevo Usuario</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-4"><input type="hidden" name="crear_usuario" value="1"><div class="mb-3"><label class="form-label small fw-bold">Usuario</label><input type="text" name="usuario" class="form-control bg-light" required></div><div class="mb-3"><label class="form-label small fw-bold">Nombre</label><input type="text" name="nombre" class="form-control bg-light" required></div><div class="mb-3"><label class="form-label small fw-bold">Email</label><input type="email" name="email" class="form-control bg-light"></div><div class="mb-3"><label class="form-label small fw-bold">Rol</label><select name="rol" class="form-select bg-light"><option value="alumno">Alumno</option><option value="profesor">Profesor</option><?php if($currentUser['rol']=='admin'): ?><option value="admin">Admin</option><?php endif; ?></select></div><div class="mb-3"><label class="form-label small fw-bold">Contraseña</label><input type="password" name="password" class="form-control bg-light" required></div><div class="mb-3"><label class="form-label small fw-bold">Asignar Clases</label><select name="clases[]" class="form-select bg-light" multiple style="height: 100px;"><?php foreach($all_clases as $cl): ?><option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['nombre']) ?></option><?php endforeach; ?></select></div></div><div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="btn btn-primary w-100 py-2 fw-bold">CREAR USUARIO</button></div></form></div></div>

<div class="modal fade" id="modalEditar" tabindex="-1"><div class="modal-dialog modal-lg"><form method="POST" class="modal-content border-0 shadow"><input type="hidden" name="editar_usuario" value="1"><input type="hidden" name="id" id="edit_id"><div class="modal-header text-white border-0 py-3" style="background-color: #6610f2;"><h5 class="modal-title">Editar Usuario</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-4"><div class="row"><div class="col-md-6 mb-3"><label class="form-label small fw-bold">Usuario</label><input type="text" name="usuario" id="edit_usuario" class="form-control bg-light" required></div><div class="col-md-6 mb-3"><label class="form-label small fw-bold">Nueva Contraseña (opcional)</label><input type="password" name="password" class="form-control bg-light"></div></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label small fw-bold">Nombre</label><input type="text" name="nombre" id="edit_nombre" class="form-control bg-light" required></div><div class="col-md-6 mb-3"><label class="form-label small fw-bold">Email</label><input type="email" name="email" id="edit_email" class="form-control bg-light"></div></div><div class="row"><div class="col-md-6 mb-3"><label class="form-label small fw-bold">Rol</label><select name="rol" id="edit_rol" class="form-select bg-light"><option value="alumno">Alumno</option><option value="profesor">Profesor</option><?php if($currentUser['rol']=='admin'): ?><option value="admin">Admin</option><?php endif; ?></select></div><div class="col-md-6 mb-3"><label class="form-label small fw-bold">Clases</label><select name="clases[]" id="edit_clases" class="form-select bg-light" multiple style="height: 100px;"><?php foreach($all_clases as $cl): ?><option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['nombre']) ?></option><?php endforeach; ?></select></div></div></div><div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="btn w-100 py-2 fw-bold text-white" style="background-color: #6610f2;">GUARDAR CAMBIOS</button></div></form></div></div>

<div class="modal fade" id="modalImportar" tabindex="-1"><div class="modal-dialog"><form method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow"><div class="modal-header text-white border-0 py-3" style="background-color: #198754;"><h5 class="modal-title">Importación Masiva (CSV)</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-4"><input type="hidden" name="importar_csv" value="1"><p class="small text-muted mb-3">Soporta separador coma (,) o punto y coma (;).</p><input type="file" name="archivo_csv" class="form-control bg-light" accept=".csv" required></div><div class="modal-footer border-0 p-4 pt-0"><button type="submit" class="btn w-100 py-2 fw-bold text-white" style="background-color: #198754;">IMPORTAR USUARIOS</button></div></form></div></div>

<script>
// --- BÚSQUEDA INSTANTÁNEA ---
let searchTimeout;
document.getElementById('input-search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => { document.getElementById('searchForm').submit(); }, 500);
});

// Cursor al final al recargar
const searchInput = document.getElementById('input-search');
if (searchInput.value !== "") {
    searchInput.focus();
    const val = searchInput.value; searchInput.value = ''; searchInput.value = val;
}

function abrirEditar(u, clasesIds) {
    document.getElementById('edit_id').value = u.id;
    document.getElementById('edit_usuario').value = u.usuario;
    document.getElementById('edit_nombre').value = u.nombre;
    document.getElementById('edit_email').value = u.email || "";
    document.getElementById('edit_rol').value = u.rol;
    const s = document.getElementById('edit_clases');
    Array.from(s.options).forEach(o => o.selected = clasesIds.includes(parseInt(o.value)));
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}

function borrarUno(id) {
    if(confirm('¿Eliminar usuario?')) {
        const f = document.getElementById('form-masivo');
        f.innerHTML += `<input type="hidden" name="usuarios_ids[]" value="${id}">`;
        f.submit();
    }
}

const selectAll = document.getElementById('select-all'), userChecks = document.querySelectorAll('.user-check'), btnBorrarMasivo = document.getElementById('btn-borrar-masivo');
function updateBorrarBtn() { btnBorrarMasivo.classList.toggle('d-none', !Array.from(userChecks).some(c => c.checked)); }
if(selectAll) { selectAll.addEventListener('change', () => { userChecks.forEach(c => c.checked = selectAll.checked); updateBorrarBtn(); }); }
userChecks.forEach(c => c.addEventListener('change', updateBorrarBtn));
function confirmarMasivo() { if(confirm('¿Eliminar seleccionados?')) document.getElementById('form-masivo').submit(); }
</script>
