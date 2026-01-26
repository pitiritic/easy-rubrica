<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary d-flex align-items-center gap-2 m-0"><i class="fa-solid fa-users fs-4"></i> Gestión de Usuarios</h3>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalImportar"><i class="fa-solid fa-file-import me-2"></i> Importar CSV</button>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalCrear"><i class="fa-solid fa-user-plus me-1"></i> Nuevo Usuario</button>
            <a href="index.php" class="btn btn-light btn-sm border px-3"><i class="fa-solid fa-arrow-left"></i> Volver</a>
        </div>
    </div>

    <div class="mb-3 d-flex justify-content-start">
        <div class="input-group" style="max-width: 350px;">
            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
            <input type="text" id="inputBusqueda" class="form-control border-start-0" placeholder="Buscar por nombre, login o rol...">
        </div>
    </div>

    <?php if ($mensaje): ?><div class="alert alert-success alert-dismissible fade show"><?= $mensaje ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <div class="card border-0 shadow-sm overflow-hidden">
        <form id="form-masivo" method="POST">
            <input type="hidden" name="borrar_masivo" value="1">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 40px;"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th><a href="?action=usuarios&order_by=nombre&dir=<?= $dir==='ASC'?'desc':'asc' ?>" class="text-dark text-decoration-none">Nombre <i class="fa-solid fa-sort small"></i></a></th>
                            <th><a href="?action=usuarios&order_by=usuario&dir=<?= $dir==='ASC'?'desc':'asc' ?>" class="text-dark text-decoration-none">Usuario <i class="fa-solid fa-sort small"></i></a></th>
                            <th><a href="?action=usuarios&order_by=rol&dir=<?= $dir==='ASC'?'desc':'asc' ?>" class="text-dark text-decoration-none">Rol <i class="fa-solid fa-sort small"></i></a></th>
                            <th><a href="?action=usuarios&order_by=clase_nombre&dir=<?= $dir==='ASC'?'desc':'asc' ?>" class="text-dark text-decoration-none">Clases <i class="fa-solid fa-sort small"></i></a></th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): 
                            $es_yo = ($u['id'] == $currentUser['id']);
                            $es_dueno = (isset($u['creador_id']) && $u['creador_id'] == $currentUser['id']);
                            $puede_borrar = (!$es_yo && ($currentUser['rol'] === 'admin' || ($currentUser['rol'] === 'profesor' && $es_dueno && $u['rol'] === 'alumno')));
                            $badge = ($u['rol']==='admin'?'bg-danger':($u['rol']==='profesor'?'bg-success':'bg-primary'));
                        ?>
                        <tr class="fila-usuario">
                            <td class="ps-4"><?php if($puede_borrar): ?><input type="checkbox" name="usuarios_ids[]" value="<?= $u['id'] ?>" class="user-check form-check-input"><?php endif; ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($u['nombre']) ?> <?= $es_yo?'(Tú)':'' ?></td>
                            <td><?= htmlspecialchars($u['usuario']) ?></td>
                            <td><span class="badge <?= $badge ?> rounded-pill"><?= ucfirst($u['rol']) ?></span></td>
                            <td class="small"><?= htmlspecialchars($u['clase_nombre'] ?: '-') ?></td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick='abrirEditar(<?= json_encode($u) ?>, <?= json_encode($u['clases_ids']) ?>, <?= ($currentUser['rol']==='admin'||$es_dueno)?1:0 ?>)'>
                                    <i class="fa-solid fa-pen"></i>
                                </button>
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

<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog"><form method="POST" enctype="multipart/form-data" class="modal-content"><div class="modal-header bg-success text-white"><h5>Importar CSV</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
        <div class="mb-3 alert alert-info py-2 small"><a href="?action=usuarios&descargar_plantilla=1" class="text-decoration-none fw-bold"><i class="fa-solid fa-download"></i> Descargar plantilla CSV</a></div>
        <input type="hidden" name="importar_csv" value="1"><input type="file" name="archivo_csv" class="form-control" required></div><div class="modal-footer"><button type="submit" class="btn btn-success w-100">IMPORTAR</button></div></form></div>
</div>

<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog"><form method="POST" class="modal-content"><div class="modal-header bg-primary text-white"><h5>Nuevo Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="crear_usuario" value="1"><div class="mb-2"><label>Usuario</label><input type="text" name="usuario" class="form-control" required></div><div class="mb-2"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div><div class="mb-2"><label>Email</label><input type="email" name="email" class="form-control"></div><div class="mb-2"><label>Rol</label><select name="rol" class="form-select"><option value="alumno">Alumno</option><?php if($currentUser['rol']==='admin'): ?><option value="profesor">Profesor</option><option value="admin">Admin</option><?php endif; ?></select></div><div class="mb-2"><label>Password</label><input type="password" name="password" class="form-control" required></div><div class="mb-2"><label>Clases</label><select name="clases[]" class="form-select" multiple><?php foreach($all_clases as $cl): ?><option value="<?= $cl['id'] ?>"><?= $cl['nombre'] ?></option><?php endforeach; ?></select></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary w-100">CREAR</button></div></form></div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog"><form method="POST" class="modal-content"><div class="modal-header bg-primary text-white"><h5>Editar Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="editar_usuario" value="1"><input type="hidden" name="id" id="edit_id">
        <div id="aviso-p" class="alert alert-warning py-1 small d-none">Solo puedes gestionar las clases.</div>
        <div class="mb-2"><label>Usuario</label><input type="text" name="usuario" id="edit_u" class="form-control" required></div>
        <div class="mb-2"><label>Nombre</label><input type="text" name="nombre" id="edit_n" class="form-control" required></div>
        <div class="mb-2"><label>Email</label><input type="email" name="email" id="edit_e" class="form-control"></div>
        <div class="mb-2"><label>Rol</label><select name="rol" id="edit_r" class="form-select"><option value="alumno">Alumno</option><?php if($currentUser['rol']==='admin'): ?><option value="profesor">Profesor</option><option value="admin">Admin</option><?php endif; ?></select></div>
        <div class="mb-2"><label>Password (opcional)</label><input type="password" name="password" class="form-control"></div>
        <div class="mb-2"><label>Clases</label><select name="clases[]" id="edit_cl" class="form-select" multiple><?php foreach($all_clases as $cl): ?><option value="<?= $cl['id'] ?>"><?= $cl['nombre'] ?></option><?php endforeach; ?></select></div>
    </div><div class="modal-footer"><button type="submit" class="btn btn-primary w-100">GUARDAR</button></div></form></div>
</div>

<script>
document.getElementById('inputBusqueda').addEventListener('keyup', function() {
    let q = this.value.toLowerCase();
    document.querySelectorAll('.fila-usuario').forEach(tr => tr.style.display = tr.innerText.toLowerCase().includes(q) ? '' : 'none');
});

function abrirEditar(u, cIds, puedeP) {
    document.getElementById('edit_id').value = u.id;
    document.getElementById('edit_u').value = u.usuario;
    document.getElementById('edit_n').value = u.nombre;
    document.getElementById('edit_e').value = u.email || '';
    document.getElementById('edit_r').value = u.rol;
    ['edit_u','edit_n','edit_e','edit_r'].forEach(id => {
        document.getElementById(id).readOnly = !puedeP;
        if(id==='edit_r') document.getElementById(id).disabled = !puedeP;
    });
    document.getElementById('aviso-p').classList.toggle('d-none', puedeP);
    let sel = document.getElementById('edit_cl');
    Array.from(sel.options).forEach(o => o.selected = cIds.includes(o.value));
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}

function borrarUno(id) {
    if(confirm('¿Eliminar?')) {
        let f = document.getElementById('form-masivo');
        let i = document.createElement('input'); i.type='hidden'; i.name='usuarios_ids[]'; i.value=id;
        f.appendChild(i); f.submit();
    }
}
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.user-check').forEach(cb => cb.checked = this.checked);
});
</script>