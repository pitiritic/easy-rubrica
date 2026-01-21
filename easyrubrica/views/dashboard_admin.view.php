<style>
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    /* Hacemos que toda la tarjeta sea clicable */
    .stretched-link::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1;
        content: "";
    }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded shadow-sm border-start border-5 border-primary">
        <div>
            <h2 class="fw-bold text-primary mb-0">Panel de Administración</h2>
            <p class="text-muted mb-0">Gestión global del centro y configuración.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark border"><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y') ?></span>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center p-4 position-relative d-flex flex-column">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 align-self-center" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h5 class="fw-bold">Usuarios</h5>
                    <p class="text-muted small">Administrar profesores y alumnos. Importación masiva CSV.</p>
                    <a href="?action=gestion_usuarios" class="btn btn-outline-primary w-100 fw-bold stretched-link mt-auto">Gestionar Usuarios</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center p-4 position-relative d-flex flex-column">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3 align-self-center" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <h5 class="fw-bold">Clases y Grupos</h5>
                    <p class="text-muted small">Crear, renombrar y organizar los grupos de clase.</p>
                    <a href="?action=gestion_clases_lista" class="btn btn-outline-success w-100 fw-bold stretched-link mt-auto">Gestionar Clases</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center p-4 position-relative d-flex flex-column">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3 align-self-center" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <h5 class="fw-bold">Tareas y Evaluaciones</h5>
                    <p class="text-muted small">Asignar rúbricas a clases, controlar fechas y estados.</p>
                    <a href="?action=asignar_rubricas" class="btn btn-outline-warning text-dark w-100 fw-bold stretched-link mt-auto">Gestionar Tareas</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center p-4 position-relative d-flex flex-column">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3 align-self-center" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <h5 class="fw-bold">Diseñador de Rúbricas</h5>
                    <p class="text-muted small">Crea, edita, clona o importa instrumentos de evaluación.</p>
                    <a href="?action=crear_rubrica" class="btn btn-outline-info text-dark w-100 fw-bold stretched-link mt-auto">Ir al Diseñador</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body text-center p-4 position-relative d-flex flex-column">
                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 align-self-center" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="fa-solid fa-gears"></i>
                    </div>
                    <h5 class="fw-bold">Ajustes del Sistema</h5>
                    <p class="text-muted small">Copias de seguridad, restauración y reset de fábrica.</p>
                    <a href="?action=ajustes" class="btn btn-outline-secondary w-100 fw-bold stretched-link mt-auto">Ir a Ajustes</a>
                </div>
            </div>
        </div>

    </div>
</div>
