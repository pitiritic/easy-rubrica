<?php
// easyrubrica/views/rubricas.view.php

if (!isset($mode) || $mode !== 'edit' || !isset($rubrica_a_editar)) {
    $rubrica_a_editar = [
        'id' => '',
        'nombre' => '',
        'asignatura' => '',
        'descripcion' => '',
        'competencias_string' => '',
        'criterios' => []
    ];
}
?>
<div class="container py-4">

    <?php if(isset($mensaje) && $mensaje): ?><div class="alert alert-success alert-dismissible fade show shadow-sm"><?= $mensaje ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if(isset($error) && $error): ?><div class="alert alert-danger alert-dismissible fade show shadow-sm"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <style>
        .btn-action {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 6px;
            transition: all 0.2s ease;
            border-width: 1.5px;
        }
        .btn-action i { font-size: 1rem; margin: 0 !important; }
        .action-group { gap: 8px !important; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .action-form { margin: 0; display: inline-block; }
    </style>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="action" value="rubricas">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="f_asignatura" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Asignatura --</option>
                        <?php foreach(($asignaturas_disponibles ?? []) as $item): ?>
                            <option value="<?= htmlspecialchars($item) ?>" <?= (isset($_GET['f_asignatura']) && $_GET['f_asignatura'] == $item) ? 'selected' : '' ?>><?= htmlspecialchars($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="f_competencia" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Competencia --</option>
                        <?php foreach(($competencias_unicas ?? []) as $item): ?>
                            <option value="<?= htmlspecialchars($item) ?>" <?= (isset($_GET['f_competencia']) && $_GET['f_competencia'] == $item) ? 'selected' : '' ?>><?= htmlspecialchars($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <a href="?action=rubricas" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="accordion mb-4 shadow-sm" id="accordionImport">
        <div class="accordion-item border-0">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-white text-success fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCsv">
                    <i class="fa-solid fa-file-csv me-2"></i> Importar Rúbricas desde CSV
                </button>
            </h2>
            <div id="collapseCsv" class="accordion-collapse collapse" data-bs-parent="#accordionImport">
                <div class="accordion-body bg-light">
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <form method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                                <input type="hidden" name="importar_csv" value="1">
                                <input type="file" name="archivo_csv" class="form-control" accept=".csv" required>
                                <button class="btn btn-success fw-bold px-4">Subir</button>
                            </form>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="?action=descargar_plantilla_rubrica" class="btn btn-outline-success btn-sm">
                                <i class="fa-solid fa-download"></i> Descargar Plantilla
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5 border-top border-4 <?= ($mode == 'edit') ? 'border-warning' : 'border-primary' ?>">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 <?= ($mode == 'edit') ? 'text-warning' : 'text-primary' ?>">
                <i class="fa-solid <?= ($mode == 'edit') ? 'fa-pen-to-square' : 'fa-plus-circle' ?>"></i>
                <?= ($mode == 'edit') ? 'Editando Rúbrica: '.htmlspecialchars($rubrica_a_editar['nombre']) : 'Diseñador de Rúbricas' ?>
            </h5>
            <?php if($mode == 'edit'): ?><a href="?action=rubricas" class="btn btn-outline-secondary btn-sm">Cancelar Edición</a><?php endif; ?>
        </div>
        <div class="card-body">
            <form method="POST" id="rubricaForm">
                <input type="hidden" name="guardar_rubrica" value="1">
                <?php if($mode == 'edit'): ?>
                    <input type="hidden" name="id_rubrica" value="<?= $rubrica_a_editar['id'] ?>">
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="fw-bold small text-muted">NOMBRE RÚBRICA</label>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($rubrica_a_editar['nombre']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small text-muted">ASIGNATURA</label>
                        <input type="text" name="asignatura" class="form-control" list="listaAsignaturas" value="<?= htmlspecialchars($rubrica_a_editar['asignatura']) ?>">
                        <datalist id="listaAsignaturas"><?php foreach(($asignaturas_disponibles ?? []) as $a) echo "<option value='".htmlspecialchars($a)."'>"; ?></datalist>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="fw-bold small text-muted">DESCRIPCIÓN</label>
                    <input type="text" name="descripcion" class="form-control" value="<?= htmlspecialchars($rubrica_a_editar['descripcion']) ?>">
                </div>

                <div class="mb-4 p-3 bg-light rounded border">
                    <label class="fw-bold small text-success mb-2"><i class="fa-solid fa-tags"></i> COMPETENCIAS</label>
                    <div class="d-flex flex-wrap gap-2 p-2 bg-white border rounded align-items-center" id="tags-container" style="min-height: 45px;">
                        <input type="text" id="tag-input" class="border-0 flex-grow-1" style="outline: none;" placeholder="Escribir y Enter...">
                    </div>
                    <input type="hidden" name="competencias_hidden" id="competencias_hidden" value="<?= htmlspecialchars($rubrica_a_editar['competencias_string']) ?>">
                </div>

                <div id="criterios-container"></div>

                <div class="mt-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addCriterio()"><i class="fa-solid fa-plus"></i> Añadir Criterio</button>
                    <button type="submit" class="btn <?= ($mode == 'edit') ? 'btn-warning text-dark' : 'btn-primary' ?> fw-bold px-4">
                        <?= ($mode == 'edit') ? 'ACTUALIZAR RÚBRICA' : 'GUARDAR RÚBRICA' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fa-solid fa-list"></i> Biblioteca</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light small text-muted">
                        <tr><th class="ps-4">Rúbrica</th><th>Clasificación</th><th>Autor</th><th class="text-end pe-4">Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach(($lista_rubricas ?? []) as $r):
                            $es_admin = ($currentUser['rol'] == 'admin');
                            $es_autor = ($r['autor_id'] == $currentUser['id']);
                        ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <a href="#" class="fw-bold text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#modal<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></a>
                                <div class="modal fade" id="modal<?= $r['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><?= htmlspecialchars($r['nombre']) ?></h5>
                                                <button class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body bg-light">
                                                <?php foreach(($r['datos_completos'] ?? []) as $c): ?>
                                                    <div class="card mb-2">
                                                        <div class="card-header fw-bold"><?= htmlspecialchars($c['nombre']) ?></div>
                                                        <div class="card-body">
                                                            <div class="row text-center small">
                                                                <?php foreach(($c['niveles'] ?? []) as $n): ?>
                                                                    <div class="col-3 border-end"><strong><?= $n['valor'] ?></strong><br><?= htmlspecialchars($n['descriptor']) ?></div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-white text-primary border"><?= htmlspecialchars($r['asignatura']) ?></span><br>
                                <!-- CORRECCIÓN AQUÍ: Uso de ?? [] para evitar error de variable indefinida o nula -->
                                <?php foreach(($r['lista_competencias'] ?? []) as $tag): ?>
                                    <span class="badge bg-success-subtle text-success border me-1"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td><small><?= $es_autor ? 'Tú' : htmlspecialchars($r['autor_nombre'] ?? 'Sistema') ?></small></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end action-group">
                                    <a href="?action=rubricas&export_csv_id=<?= $r['id'] ?>" class="btn btn-action btn-outline-success" title="Descargar CSV"><i class="fa-solid fa-file-csv"></i></a>
                                    <a href="?action=exportar_pdf&id=<?= $r['id'] ?>" target="_blank" class="btn btn-action btn-outline-danger" title="PDF"><i class="fa-solid fa-file-pdf"></i></a>
                                    <form method="POST" class="action-form" onsubmit="return confirm('¿Clonar rúbrica?');"><input type="hidden" name="duplicar_rubrica_id" value="<?= $r['id'] ?>"><button class="btn btn-action btn-outline-primary" title="Clonar"><i class="fa-solid fa-copy"></i></button></form>
                                    <?php if($es_admin || $es_autor): ?>
                                        <a href="?action=rubricas&edit_id=<?= $r['id'] ?>" class="btn btn-action" style="border-color: #6610f2; color: #6610f2;" title="Editar"><i class="fa-solid fa-pen"></i></a>
                                        <form method="POST" class="action-form" onsubmit="return confirm('¿Eliminar rúbrica?');"><input type="hidden" name="borrar_rubrica_id" value="<?= $r['id'] ?>"><button class="btn btn-action btn-outline-danger" title="Borrar"><i class="fa-solid fa-trash"></i></button></form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let cIndex = 0;
const tagInput = document.getElementById('tag-input');
const tagsContainer = document.getElementById('tags-container');
const hiddenInput = document.getElementById('competencias_hidden');
let tags = [];

function renderTags() {
    tagsContainer.querySelectorAll('span.badge').forEach(s => s.remove());
    tags.forEach((t, i) => {
        const span = document.createElement('span');
        span.className = 'badge bg-success-subtle text-success border d-flex align-items-center gap-2';
        span.innerHTML = `${t} <i class="fa-solid fa-xmark" onclick="removeTag(${i})" style="cursor:pointer"></i>`;
        tagsContainer.insertBefore(span, tagInput);
    });
    hiddenInput.value = tags.join(',');
}
function removeTag(i) { tags.splice(i, 1); renderTags(); }
tagInput.addEventListener('keydown', e => { if(e.key === 'Enter') { e.preventDefault(); const v = tagInput.value.trim(); if(v && !tags.includes(v)) { tags.push(v); renderTags(); } tagInput.value = ''; } });

function addCriterio(nombre='', d1='', d2='', d3='', d4='') {
    let h = `<div class="card mb-3 bg-light border p-3" id="row-${cIndex}">
        <div class="d-flex justify-content-between mb-2">
            <label class="fw-bold text-primary">Criterio ${cIndex+1}</label>
            <button type="button" class="btn-close" onclick="this.closest('.card').remove()"></button>
        </div>
        <input type="text" name="criterio[${cIndex}]" class="form-control mb-2 fw-bold" value="${nombre}" required placeholder="Nombre del criterio">
        <div class="row g-2">
            <div class="col-md-3"><textarea name="desc_${cIndex}_1" class="form-control form-control-sm" rows="2" placeholder="Nivel 1">${d1}</textarea></div>
            <div class="col-md-3"><textarea name="desc_${cIndex}_2" class="form-control form-control-sm" rows="2" placeholder="Nivel 2">${d2}</textarea></div>
            <div class="col-md-3"><textarea name="desc_${cIndex}_3" class="form-control form-control-sm" rows="2" placeholder="Nivel 3">${d3}</textarea></div>
            <div class="col-md-3"><textarea name="desc_${cIndex}_4" class="form-control form-control-sm" rows="2" placeholder="Nivel 4">${d4}</textarea></div>
        </div>
    </div>`;
    document.getElementById('criterios-container').insertAdjacentHTML('beforeend', h);
    cIndex++;
}

document.addEventListener('DOMContentLoaded', function() {
    if(hiddenInput.value) {
        tags = hiddenInput.value.split(',').filter(t => t.trim() !== '');
        renderTags();
    }

    <?php if($mode == 'edit' && !empty($rubrica_a_editar['criterios'])): ?>
        <?php foreach($rubrica_a_editar['criterios'] as $c):
            $d = [1=>'', 2=>'', 3=>'', 4=>''];
            if(isset($c['niveles'])) {
                foreach($c['niveles'] as $n) {
                    $d[$n['valor']] = str_replace(["\r", "\n"], " ", addslashes($n['descriptor']));
                }
            }
        ?>
        addCriterio('<?= addslashes($c['nombre']) ?>', '<?= $d[1] ?>', '<?= $d[2] ?>', '<?= $d[3] ?>', '<?= $d[4] ?>');
        <?php endforeach; ?>
    <?php else: ?>
        addCriterio();
    <?php endif; ?>
});
</script>