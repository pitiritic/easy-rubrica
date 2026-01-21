<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border-top border-4 border-primary">
        <div>
            <h3 class="mb-0 fw-bold text-dark">Evaluaciones de: <span class="text-primary"><?= htmlspecialchars($nombre_evaluado) ?></span></h3>
            <p class="text-muted mb-0"><i class="fa-solid fa-book"></i> Tarea: <?= htmlspecialchars($titulo_tarea) ?></p>
        </div>
        
        <div class="d-flex gap-3">
            <a href="index.php?action=ver_resultados&id=<?= $tarea_id ?>" class="btn-icon border-primary text-primary" title="Volver a Resultados">
                <i class="fa-solid fa-arrow-rotate-left fa-lg"></i>
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Evaluador</th>
                            <th>Relación (Tipo)</th>
                            <th>Fecha</th>
                            <th class="text-center">Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($detalles)): ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted">No se han encontrado registros de evaluación.</td></tr>
                        <?php else: ?>
                            <?php foreach($detalles as $d): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($d['evaluador_nombre']) ?></div>
                                    </td>
                                    <td>
                                        <?php 
                                        // Colores según el tipo de evaluación
                                        $color = ($d['tipo'] == 'auto') ? 'secondary' : (($d['tipo'] == 'coeval') ? 'info' : 'success');
                                        $label = ($d['tipo'] == 'auto') ? 'Autoevaluación' : (($d['tipo'] == 'coeval') ? 'Coevaluación' : 'Heteroevaluación');
                                        ?>
                                        <span class="badge rounded-pill bg-<?= $color ?>"><?= $label ?></span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d/m/Y H:i', strtotime($d['fecha'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold"><?= number_format($d['calificacion_final'], 2) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilo para los botones de iconos cuadrados redondeados para seguir la estética */
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
    }

    .btn-icon:hover {
        transform: scale(1.1);
        background-color: #f8f9fa;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Ajustes de tabla */
    .table thead th { 
        font-size: 0.85rem; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        color: #666;
    }
    
    .badge { 
        font-weight: 500; 
        padding: 0.5em 0.8em;
    }
</style>
