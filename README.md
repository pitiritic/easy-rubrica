Registro de Cambios (Changelog) - Easy Rúbrica

Todas las modificaciones notables en este proyecto se documentan en este archivo. El formato se basa en Keep a Changelog, y este proyecto adhiere al Versionado Semántico.

[2.5.0] - 2026-01-20
✨ Añadido (Infraestructura y Portabilidad)
Sincronización Dinámica con Docker: Vinculación completa de db.php con las variables de entorno de docker-compose.yml mediante getenv().
Compatibilidad con Instalaciones Limpias: Refactorización de la lógica de conexión para permitir el cambio dinámico de nombres de base de datos y credenciales sin necesidad de modificar el código fuente.
♻️ Cambios y Mejoras
Optimización de db.php: Eliminación de valores fijos (hardcoded), sustituyéndolos por parámetros configurables desde la infraestructura de Docker.
Control de Excepciones de Conexión: Mejora del bloque try-catch en la conexión PDO para silenciar errores técnicos y permitir que index.php gestione correctamente la redirección al asistente de instalación.
🐞 Corregido
Error Crítico de Variable Indefinida: Resolución del Fatal Error Call to a member function query() on null en la línea 28 de index.php mediante la validación previa del estado del objeto de conexión.
Conflicto de Permisos SQL (Error 1044): Ajuste en la jerarquía de conexión para asegurar que el usuario definido en Docker tenga permisos inmediatos sobre la base de datos recién creada.
[2.4.0] - 2026-01-19
✨ Añadido (Seguridad y Estabilidad)
Sistema de Recuperación de Contraseña: Implementación de un flujo completo de restablecimiento mediante tokens de seguridad únicos y temporales.
Integración con PHPMailer: Configuración del motor de envío de correos mediante SMTP externo (Gmail, Outlook, etc.).
Gestión SMTP desde Ajustes: Vinculación dinámica del sistema de correo con la tabla ajustes_smtp para cambios de servidor sin tocar código.
Auto-Migración de Base de Datos: Script inteligente en db.php que detecta y añade automáticamente columnas o tablas faltantes tras restaurar backups antiguos.
♻️ Cambios y Mejoras
Optimización de Memoria en Evaluación: Refactorización de la vista evaluar.view.php para reducir el consumo de RAM en un 90%.
Carga Asíncrona (AJAX): Migración del selector de alumnos y rúbricas a peticiones asíncronas para mejorar la velocidad en grupos grandes.
Interfaz de Gestión de Clases: Implementación de ventanas modales para la creación, edición y clonación de clases.
🐞 Corregido
Error Crítico de Memoria: Resolución del Fatal Error "Allowed memory size exhausted" provocado por la duplicidad de lógica.
Error de Tabla no Encontrada: Corrección de las consultas en el controlador de recuperación que apuntaban a nombres de tabla inconsistentes (ajustes vs ajustes_smtp).
[2.3.0] - 2026-01-18
✨ Añadido (Gestión de Rúbricas y Privacidad)
Motor de Edición Dinámico: Carga profunda en rubricas.php que recupera la estructura completa para permitir modificaciones totales.
Exportación Portátil: Botón de descarga CSV con el formato exacto de la plantilla para respaldos.
Buscador Inteligente: Caja de búsqueda optimizada en el panel de notas con filtrado en tiempo real.
Identificación por Clase: Integración del nombre de la clase en todas las vistas de calificaciones.
♻️ Cambios y Mejoras
Jerarquía Multinivel: Rediseño del panel de profesores agrupando por Alumno/Tarea > Rúbrica > Evaluación.
Privacidad Estricta: Los alumnos ahora solo ven su Nota Media Final, garantizando el anonimato de los evaluadores en coevaluaciones.
🐞 Corregido
Warning de "lista_competencias": Decodificación forzada de JSON para evitar errores de índice indefinido.
Consistencia de Datos en CSV: Actualización de encabezados y codificación BOM UTF-8 para compatibilidad total con Excel.
[2.2.0] - 2026-01-13
✨ Añadido (Interfaz y Reportes)
Detalle de Evaluación: Nueva vista profesional con franja azul para desglosar notas individuales.
Automatización de Impresión: Scripts para lanzar el cuadro de diálogo de impresión automáticamente al cargar informes.
🐞 Corregido
Lógica de Doble Cabecera: Implementación de ob_start en index.php para eliminar menús duplicados.
Sincronización de PDF: Corrección en la recolección de datos antes de renderizar informes.
[2.1.0] - 2026-01-12
✨ Añadido (Privacidad y Clonación)
Propiedad de Clases: Cada profesor gestiona exclusivamente sus propios grupos.
Modo Supervisión (Admin): Interruptor "Ver todas" para que el administrador gestione cualquier clase del sistema.
Clonación Independiente: Sistema de duplicación de clases con generación automática de sufijos únicos para logins de alumnos.
[2.0.0] - 2026-01-09
✨ Añadido
Arquitectura Universal: Optimización para Docker en x86, x64 y ARM.
Asistente de Instalación: Lógica de detección de BD vacía para creación del primer Admin.
Reset de Fábrica: Opción en ajustes para limpieza total del sistema.
[1.0.0] - Versión Inicial (Base)
🚀 Características
Roles: Admin, Profesor y Alumno.
Evaluación: Motor de rúbricas con cálculo de notas en tiempo real.
Infraestructura: Stack PHP 8.2 + MariaDB 10.6 bajo Docker.
