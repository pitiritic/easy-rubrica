<?php
// Aseguramos que las variables existen para evitar errores
$nombre_evaluado = $nombre_evaluado ?? 'Alumno';
$detalles = $detalles ?? [];
?>

<style>
    /* Estilos forzados para replicar la Imagen 2 */
    .header-azul {
        background-color: #0d6efd !important;
        color: white !important;
        padding: 20px !important;
        border-radius: 8px !important;
        margin-bottom: 25px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }
    .btn-volver-blanco {
        background-color: white !important;
        color: #333 !important;
        text-decoration: none !important;
        padding: 8px 16px !important;
        border-radius: 5px !important;
        font-weight: bold !important;
        font-size: 0.9rem !important;
    }
    .franja-info {
        background-color: #e3f2fd !important;
        color: #0d47a1 !important;
        padding: 10px !important;
        text-align: center !important;
        font-weight: bold !important;
        border-radius: 5px !important;
        margin-bottom: 20px !important;
    }
    .tabla-profesional {
        width: 100% !important;
        border-collapse: collapse !important;
        background: white !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
    }
    .tabla-profesional thead {
        background-color: #f8f9fa !important;
    }
    .tabla-profesional th {
        padding: 15px !important;
        text-align: left !important;
        border-bottom: 2px solid #dee2e6 !important;
        color: #444 !important;
    }
    .tabla-profesional td {
        padding: 15px !important;
        border-bottom: 1px solid #eee !important;
        vertical-align: middle !important;
    }
    .nota-final-badge {
        background-color: #0d6efd !important;
        color: white !important;
        padding: 5px 12px !important;
        border-radius: 20px !important;
        font-weight: bold !important;
        display: inline-block !important;
    }
    .texto-footer {
        margin-top: 20px !important;
        padding: 15px !important;
        border-left: 4px solid #0d6efd !important;
        background: #fdfdfd !important;
        font-size: 0.9rem !important;
        color: #666 !important;
    }
</style>

<div class="container py-4" style="font-family: sans-serif; max-width: 1000px; margin: 0 auto;">
    
    <div class="header-azul">
        <div>
            <h2 style="margin: 0; font-size: 1.8rem;">Detalle de Evaluación</h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Alumno: <?= htmlspecialchars($nombre_evaluado) ?></p>
        </div>
        <a href="javascript:history.back()" class="btn-volver-blanco">Volver</a>
    </div>

    <div class="franja-info">
        Desglose detallado de las valoraciones individuales recibidas
    </div>

    <table class="tabla-profesional">
        <thead>
            <tr>
                <th style="width: 40%;">Evaluador</th>
                <th style="width: 20%;">Tipo</th>
                <th style="width: 20%; text-align: center;">Calificación</th>
                <th style="width: 20%; text-align: center;">Calificación Final</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($detalles)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 30px; color: #888;">
                        No se han encontrado registros de evaluación.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($detalles as $d): ?>
                <tr>
                    <td>
                        <div style="font-weight: bold; color: #333;"><?= htmlspecialchars($d['evaluador_nombre']) ?></div>
                        <div style="font-size: 0.8rem; color: #777; text-transform: capitalize;"><?= htmlspecialchars($d['evaluador_rol']) ?></div>
                    </td>
                    <td style="color: #555;">
                        <?php 
                            $labels = ['hetero' => 'Heteroevaluación', 'co' => 'Coevaluación', 'auto' => 'Autoevaluación'];
                            echo $labels[$d['tipo']] ?? $d['tipo'];
                        ?>
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        <?= number_format($d['calificacion_final'], 2) ?>
                    </td>
                    <td style="text-align: center;">
                        <span class="nota-final-badge">
                            <?= number_format($d['calificacion_final'], 2) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="texto-footer">
        <strong>Información:</strong> Este reporte muestra el desglose detallado de las valoraciones individuales recibidas para esta tarea.
    </div>
</div>
