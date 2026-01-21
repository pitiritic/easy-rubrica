<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EasyRubrica - <?= htmlspecialchars($rubrica['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos generales de pantalla */
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', sans-serif;
        }

        /* CONFIGURACIÓN DE IMPRESIÓN */
        @media print {
            @page {
                size: landscape; /* Orientación horizontal */
                margin: 1cm;
            }
            
            /* OCULTAR BOTONES EN PDF/IMPRESIÓN  */
            .no-print { 
                display: none !important; 
            }

            body { 
                background-color: white !important; 
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .container-fluid { 
                width: 100%; 
                padding: 0; 
                margin: 0;
            }

            tr { 
                page-break-inside: avoid; 
            }
        }

        /* Estilos de la tabla */
        .header-info { 
            border-bottom: 2px solid #0d6efd; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }

        .table-pdf {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .table-pdf thead th { 
            background-color: #f8f9fa !important; 
            text-align: center; 
            font-size: 0.8rem; 
            border: 1px solid #dee2e6;
        }

        .table-pdf td { 
            font-size: 0.85rem; 
            vertical-align: top; 
            border: 1px solid #dee2e6;
            padding: 8px;
            word-wrap: break-word;
        }

        .criterio-celda { 
            background-color: #f1f4f9 !important; 
            font-weight: bold; 
            width: 15%;
        }

        .nivel-celda { width: 21.25%; }

        .footer-pdf { 
            margin-top: 20px; 
            font-size: 0.75rem; 
            color: #6c757d; 
        }
    </style>
</head>
<body onload="window.print()">

<div class="container-fluid px-4 my-4">
    <div class="d-flex justify-content-between no-print mb-4">
        <a href="index.php?action=rubricas" class="btn btn-secondary btn-sm">← Volver a la web</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm">Confirmar Impresión / PDF</button>
    </div>

    <div class="header-info">
        <div class="row align-items-center">
            <div class="col-8">
                <h2 class="fw-bold text-primary mb-0"><?= htmlspecialchars($rubrica['nombre']) ?></h2>
                <p class="mb-0 text-muted"><?= htmlspecialchars($rubrica['descripcion']) ?></p>
            </div>
            <div class="col-4 text-end small">
                <strong>Asignatura:</strong> <?= htmlspecialchars($rubrica['asignatura']) ?><br>
                <strong>Autor:</strong> <?= htmlspecialchars($rubrica['autor_nombre']) ?><br>
                <strong>Fecha:</strong> <?= date('d/m/Y') ?>
            </div>
        </div>
    </div>

    <table class="table table-bordered table-pdf">
        <thead>
            <tr>
                <th style="width: 15%;">Criterio</th>
                <th style="width: 21.25%;">Excelente (4)</th>
                <th style="width: 21.25%;">Bueno (3)</th>
                <th style="width: 21.25%;">Aceptable (2)</th>
                <th style="width: 21.25%;">Insuficiente (1)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($criterios as $c): ?>
            <tr>
                <td class="criterio-celda"><?= htmlspecialchars($c['nombre']) ?></td>
                <?php 
                    // Mapeo manual para asegurar compatibilidad con PHP < 8.0
                    $map = array(4 => '', 3 => '', 2 => '', 1 => '');
                    if (isset($c['niveles'])) {
                        foreach ($c['niveles'] as $n) {
                            $map[$n['valor']] = $n['descriptor'];
                        }
                    }
                ?>
                <td class="nivel-celda"><?= nl2br(htmlspecialchars($map[4])) ?></td>
                <td class="nivel-celda"><?= nl2br(htmlspecialchars($map[3])) ?></td>
                <td class="nivel-celda"><?= nl2br(htmlspecialchars($map[2])) ?></td>
                <td class="nivel-celda"><?= nl2br(htmlspecialchars($map[1])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer-pdf d-flex justify-content-between">
        <span>Generado por <strong>EasyRubrica</strong></span>
        <span>https://rubricadesarrollo.jmmorenas.com</span>
    </div>
</div>

</body>
</html>
