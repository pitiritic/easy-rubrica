<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border-top border-4 border-success">
        <div>
            <h3 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($tarea['titulo']) ?></h3>
            <p class="text-muted mb-0">Informe de Calificaciones: <?= htmlspecialchars($tarea['clase_nombre']) ?></p>
        </div>
        
        <div class="d-flex gap-3 d-print-none">
            <button onclick="window.print();" class="btn-icon border-danger text-danger" title="Imprimir / PDF">
                <i class="fa-solid fa-file-pdf fa-lg"></i>
            </button>
            
            <a href="?action=ver_resultados&id=<?= $tarea['id'] ?>&export=csv" class="btn-icon border-success text-success" title="Exportar CSV">
                <i class="fa-solid fa-file-csv fa-lg"></i>
            </a>

            <a href="index.php?action=asignar_rubricas" class="btn-icon border-primary text-primary" title="Volver">
                <i class="fa-solid fa-arrow-rotate-left fa-lg"></i>
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-success text-white">
                        <tr>
                            <th class="text-start ps-4 py-3">Alumno</th>
                            <th>Hetero (<?= $tarea['peso_hetero'] ?>%)</th>
                            <th>Coeval (<?= $tarea['peso_coeval'] ?>%)</th>
                            <th>Auto (<?= $tarea['peso_auto'] ?>%)</th>
                            <th class="bg-white text-primary border-start">NOTA FINAL</th>
                            <th class="bg-white text-dark border-start d-print-none">Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($alumnos_notas)): ?>
                            <?php foreach($alumnos_notas as $row): ?>
                                <tr>
                                    <td class="text-start ps-4 fw-bold text-dark">
                                        <?= htmlspecialchars($row['nombre']) ?>
                                    </td>
                                    <td><?= number_format($row['hetero'], 2) ?></td>
                                    <td><?= number_format($row['coeval'], 2) ?></td>
                                    <td><?= number_format($row['auto'], 2) ?></td>
                                    <td class="fw-bold text-primary border-start">
                                        <?= number_format($row['final'], 2) ?>
                                    </td>
                                    <td class="border-start d-print-none">
                                        <div class="d-flex justify-content-center">
                                            <a href="index.php?action=detalle_evaluacion&tarea_id=<?= $tarea['id'] ?>&alumno_id=<?= $row['id_alumno'] ?>" 
                                               class="btn-icon border-primary text-primary" 
                                               style="width: 35px; height: 35px; border-radius: 8px;" 
                                               title="Ver quién evaluó">
                                                <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-5 text-muted text-center">
                                    No hay calificaciones registradas para esta tarea aún.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estética de botones de iconos redondos/cuadrados */
    .btn-icon {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border: 2px solid;
        border-radius: 12px;
        transition: all 0.2s;
        text-decoration: none;
        cursor: pointer;
        padding: 0;
    }

    .btn-icon:hover {
        transform: scale(1.1);
        background-color: #f8f9fa;
        color: inherit;
    }

    /* Estilos de tabla */
    .table thead th { 
        border: none; 
        font-weight: 600; 
        text-transform: uppercase; 
        font-size: 0.85rem; 
    }
    
    .border-start { 
        border-left: 1px solid #dee2e6 !important; 
    }

    /* Estilos específicos para impresión */
    @media print {
        .d-print-none, nav, .navbar, footer, .btn-icon, .btn { 
            display: none !important; 
        }
        .container { 
            width: 100% !important; 
            max-width: 100% !important; 
            margin: 0; 
            padding: 0; 
        }
        .card { 
            border: none !important; 
            box-shadow: none !important; 
        }
        .bg-success { 
            background-color: #198754 !important; 
            -webkit-print-color-adjust: exact; 
            color: white !important; 
        }
        .text-primary {
            color: #0d6efd !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
