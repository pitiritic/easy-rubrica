<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary d-flex align-items-center gap-2 m-0"><i class="fa-solid fa-users fs-4"></i> Gestión de Usuarios</h3>
        <div class="d-flex gap-2">
            <button type="button" id="btn-borrar-masivo" onclick="if(confirm('¿Borrar seleccionados?')) document.getElementById('form-masivo').submit();" class="btn btn-danger btn-sm px-3" style="display: none;">
                <i class="fa-solid fa-trash-can me-1"></i> Borrar seleccionados
            </button>
            <button class="btn btn-outline-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalImportar"><i class="fa-solid fa-file-import me-1"></i> Importar CSV</button>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalCrear"><i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario</button>
            <a href="index.php" class="btn btn-light btn-sm border px-3"><i class="fa-solid fa-arrow-left"></i> Volver</a>
        </div>
    </div>

    <div class="mb-3 d-flex justify-content-end">
        <div class="input-group" style="max-width: 300px;">
            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
            <input type="text" id="inputBusqueda" class="form-control border-start-0" placeholder="Buscar usuarios...">
        </div>
    </div>

    <?php if ($mensaje): ?><div class="alert alert-success alert-dismissible fade show shadow-sm"><?= $mensaje ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show shadow-sm"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <div class="card border-0 shadow-sm overflow-hidden">
        <form id="form-masivo" method="POST">
            <input type="hidden" name="borrar_masivo" value="1">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th class="ps-4" style="width: 40px;"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th><a href="?action=usuarios&order_by=nombre&dir=<?= $order_by==='nombre'&&$dir==='ASC'?'desc':'asc' ?>" class="text-dark text-decoration-none">Nombre <i class="fa-solid fa-sort small ms-1"></i></a></th>
                            <th><a href="?action=usuarios&order_by=usuario&dir=<?= $order_by==='usuario'&&$dir==='ASC'?'desc':'asc' ?>" class="text-dark text-decoration-none">Usuario <i class="fa-solid fa-sort small ms-1"></i></a></th>
                            <th><a href="?action=usuarios&order_by=rol&dir=<?= $order_by==='rol'&&$dir==='ASC'?'desc':'asc' ?>" class="text-dark text-decoration-none">Rol <i class="fa-solid fa-sort small ms-1"></i></a></th>
                            <th><a href="?action=usuarios&order_by=clase_nombre&dir=<?= $order_by==='clase_nombre'&&$dir==='ASC'?'desc':'asc' ?>" class="text-dark text-decoration-none">Clases <i class="fa-solid fa-sort small ms-1"></i></a></th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): 
                            $es_yo = ($u['id'] == $currentUser['id']);
                            $es_dueno = (isset($u['creador_id']) && $u['creador_id'] == $currentUser['id']);
                            $puede_borrar = (!$es_yo && ($currentUser['rol'] === 'admin' || ($currentUser['rol'] === 'profesor' && $es_dueno && $u['rol'] === 'alumno')));
                            $badge_class = ($u['rol'] === 'admin') ? 'bg-danger' : (($u['rol'] === 'profesor') ? 'bg-success' : 'bg-primary');
                        ?>
                        <tr class="fila-usuario">
                            <td class="ps-4"><?php if($puede_borrar): ?><input type="checkbox" name="usuarios_ids[]" value="<?= $u['id'] ?>" class="user-check form-check-input"><?php endif; ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($u['nombre'] ?? '') ?> <?= $es_yo ? '<small class="text-muted">(Tú)</small>' : '' ?></td>
                            <td><?= htmlspecialchars($u['usuario'] ?? '') ?></td>
                            <td><span class="badge <?= $badge_class ?> rounded-pill"><?= ucfirst($u['rol'] ?? '') ?></span></td>
                            <td class="small"><?= htmlspecialchars($u['clase_nombre'] ?? '') ?></td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick='abrirEditar(<?= json_encode($u) ?>, <?= json_encode($u['clases_ids'] ?? []) ?>, <?= ($currentUser['rol']==='admin'||$es_dueno)?1:0 ?>)'><i class="fa-solid fa-pen"></i></button>
                                <?php if($puede_borrar): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="borrarUno(<?= $u['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<!-- MODAL IMPORTAR -->
<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog"><form method="POST" enctype="multipart/form-data" class="modal-content"><div class="modal-header bg-success text-white"><h5>Importar Usuarios</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
        <div class="alert alert-info py-2 small"><a href="?action=usuarios&descargar_plantilla=1" class="fw-bold text-decoration-none"><i class="fa-solid fa-download"></i> Descargar plantilla</a></div>
        <input type="hidden" name="importar_csv" value="1"><input type="file" name="archivo_csv" class="form-control" required></div><div class="modal-footer"><button type="submit" class="btn btn-success w-100 fw-bold">IMPORTAR</button></div></form></div>
</div>

<!-- MODAL CREAR -->
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog"><form method="POST" class="modal-content"><div class="modal-header bg-primary text-white"><h5>Nuevo Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="crear_usuario" value="1"><div class="mb-2"><label class="small fw-bold">Usuario</label><input type="text" name="usuario" class="form-control" required></div><div class="mb-2"><label class="small fw-bold">Nombre</label><input type="text" name="nombre" class="form-control" required></div><div class="mb-2"><label class="small fw-bold">Email</label><input type="email" name="email" class="form-control"></div><div class="mb-2"><label class="small fw-bold">Rol</label><select name="rol" class="form-select"><option value="alumno">Alumno</option><?php if($currentUser['rol']==='admin'): ?><option value="profesor">Profesor</option><option value="admin">Admin</option><?php endif; ?></select></div><div class="mb-2"><label class="small fw-bold">Password</label><input type="password" name="password" class="form-control" required></div><div class="mb-2"><label class="small fw-bold">Clases</label><select name="clases[]" class="form-select" multiple><?php foreach($all_clases as $cl): ?><option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['nombre']) ?></option><?php endforeach; ?></select></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary w-100 fw-bold">CREAR</button></div></form></div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog"><form method="POST" class="modal-content"><div class="modal-header bg-primary text-white"><h5>Editar Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="editar_usuario" value="1"><input type="hidden" name="id" id="edit_id">
        <div id="aviso-permisos" class="alert alert-warning py-1 small d-none">Solo puedes gestionar las clases.</div>
        <div class="mb-2"><label class="small fw-bold">Usuario</label><input type="text" name="usuario" id="edit_usuario" class="form-control" required></div>
        <div class="mb-2"><label class="small fw-bold">Nombre</label><input type="text" name="nombre" id="edit_nombre" class="form-control" required></div>
        <div class="mb-2"><label class="small fw-bold">Email</label><input type="email" name="email" id="edit_email" class="form-control"></div>
        <div class="mb-2"><label class="small fw-bold">Rol</label><select name="rol" id="edit_rol" class="form-select"><option value="alumno">Alumno</option><?php if($currentUser['rol']==='admin'): ?><option value="profesor">Profesor</option><option value="admin">Admin</option><?php endif; ?></select></div>
        <div class="mb-2"><label class="small fw-bold">Password (opcional)</label><input type="password" name="password" class="form-control"></div>
        <div class="mb-2"><label class="small fw-bold">Clases</label><select name="clases[]" id="edit_clases" class="form-select" multiple><?php foreach($all_clases as $cl): ?><option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['nombre']) ?></option><?php endforeach; ?></select></div>
    </div><div class="modal-footer"><button type="submit" class="btn btn-primary w-100 fw-bold">GUARDAR CAMBIOS</button></div></form></div>
</div>

<script>
const btnBorrarMasivo = document.getElementById('btn-borrar-masivo');

function toggleBorrarBtn() {
    const seleccionados = document.querySelectorAll('.user-check:checked').length;
    btnBorrarMasivo.style.display = seleccionados > 0 ? 'inline-block' : 'none';
}

document.getElementById('inputBusqueda').addEventListener('keyup', function() {
    let q = this.value.toLowerCase();
    document.querySelectorAll('.fila-usuario').forEach(tr => tr.style.display = tr.innerText.toLowerCase().includes(q) ? '' : 'none');
});

function abrirEditar(u, cIds, puedePerfil) {
    document.getElementById('edit_id').value = u.id;
    document.getElementById('edit_usuario').value = u.usuario || '';
    document.getElementById('edit_nombre').value = u.nombre || '';
    document.getElementById('edit_email').value = u.email || '';
    document.getElementById('edit_rol').value = u.rol || 'alumno';
    ['edit_usuario','edit_nombre','edit_email','edit_rol'].forEach(id => {
        document.getElementById(id).readOnly = !puedePerfil;
        if(id==='edit_rol') document.getElementById(id).disabled = !puedePerfil;
    });
    document.getElementById('aviso-permisos').classList.toggle('d-none', puedePerfil);
    let sel = document.getElementById('edit_clases');
    Array.from(sel.options).forEach(o => o.selected = cIds.includes(parseInt(o.value)));
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}

function borrarUno(id) {
    if(confirm('¿Eliminar usuario?')) {
        let f = document.getElementById('form-masivo');
        let i = document.createElement('input'); 
        i.type='hidden'; i.name='usuarios_ids[]'; i.value=id;
        f.appendChild(i); f.submit();
    }
}

document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.user-check').forEach(cb => cb.checked = this.checked);
    toggleBorrarBtn();
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('user-check')) { toggleBorrarBtn(); }
});
</script>