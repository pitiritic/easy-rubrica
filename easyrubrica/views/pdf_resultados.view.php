<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Calificaciones</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 20px; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 0.9rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #eee; text-align: left; padding: 10px; border: 1px solid #ddd; font-size: 0.8rem; }
        td { padding: 10px; border: 1px solid #ddd; font-size: 0.9rem; }
        .nota-final { font-weight: bold; color: #0d6efd; }
        .no-print { text-align: right; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Imprimir o Guardar PDF</button>
    </div>

    <div class="header">
        <h1 style="color: #0d6efd; margin: 0;">EasyRubrica - Informe de Calificaciones</h1>
        <p style="margin: 5px 0;"><strong>Tarea:</strong> <?= htmlspecialchars($tarea['titulo']) ?> | <strong>Clase:</strong> <?= htmlspecialchars($tarea['clase_nombre']) ?></p>
        <p style="font-size: 0.8rem; color: #666;">Fecha: <?= date('d/m/Y') ?></p>
    </div>

    <div class="info-box">
        <div style="display: flex; justify-content: space-between;">
            <div><strong>Rúbrica:</strong> <?= htmlspecialchars($tarea['rubrica_nombre']) ?></div>
            <div>
                <strong>Ponderación:</strong> 
                H: <?= $tarea['peso_hetero'] ?>% | C: <?= $tarea['peso_coeval'] ?>% | A: <?= $tarea['peso_auto'] ?>%
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ALUMNO / ESTUDIANTE</th>
                <th style="text-align:center;">HETERO-EVAL.</th>
                <th style="text-align:center;">CO-EVAL.</th>
                <th style="text-align:center;">AUTO-EVAL.</th>
                <th style="text-align:center;">NOTA FINAL</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alumnos_notas as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['nombre']) ?></td>
                <td style="text-align:center;"><?= number_format($a['nota_hetero'], 2) ?></td>
                <td style="text-align:center;"><?= number_format($a['nota_coeval'], 2) ?></td>
                <td style="text-align:center;"><?= number_format($a['nota_auto'], 2) ?></td>
                <td style="text-align:center;" class="nota-final"><?= number_format($a['nota_final'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
