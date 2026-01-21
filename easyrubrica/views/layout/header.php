<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyRubrica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .navbar { background-color: #fff; border-bottom: 1px solid #dee2e6; }
        .nav-link { color: #0d6efd !important; font-weight: 500; transition: color 0.2s ease; }
        .nav-link:hover { color: #6c757d !important; }

        /* NUEVOS COLORES COORDINADOS */
        .icon-home { color: #ff8c00; }    /* Naranja oscuro (Casa) */
        .icon-ajustes { color: #212529; } /* Gris oscuro */
        .icon-rubricas { color: #d63384; } /* Magenta/Rosa (Rúbricas) */
        .icon-clases { color: #198754; }   /* Verde */
        .icon-asignar { color: #ffc107; }  /* Amarillo/Ocre */
        .icon-evaluar { color: #0dcaf0; }  /* Cian */
        .icon-notas { color: #6610f2; }    /* Violeta */

        .nav-link i { margin-right: 5px; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light shadow-sm py-2">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php?action=home">
            <i class="fa-solid fa-chart-simple me-2"></i>EasyRubrica
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=home">
                        <i class="fa-solid fa-house icon-home"></i> Gestión Global
                    </a>
                </li>

                <?php if(isset($currentUser['rol']) && $currentUser['rol'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=ajustes">
                        <i class="fa-solid fa-gear icon-ajustes"></i> Ajustes
                    </a>
                </li>
                <?php endif; ?>

                <?php if(isset($currentUser['rol']) && $currentUser['rol'] !== 'alumno'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=rubricas">
                        <i class="fa-solid fa-list icon-rubricas"></i> Rúbricas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=gestion_clases_lista">
                        <i class="fa-solid fa-chalkboard-user icon-clases"></i> Clases
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=asignar_rubricas">
                        <i class="fa-solid fa-user-plus icon-asignar"></i> Asignar
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=evaluar">
                        <i class="fa-solid fa-pen-to-square icon-evaluar"></i> Evaluar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=notas">
                        <i class="fa-solid fa-graduation-cap icon-notas"></i> Notas
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active fw-bold text-primary" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-circle-user fs-5 text-primary"></i> 
                        <?= htmlspecialchars($currentUser['nombre'] ?? 'Usuario') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><span class="dropdown-item-text small text-muted text-uppercase fw-bold"><?= htmlspecialchars($currentUser['rol'] ?? 'Sin rol') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="index.php?action=logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="py-4">
