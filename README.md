# Easy Rúbrica 🚀

Easy Rúbrica es una plataforma integral para la gestión y aplicación de rúbricas de evaluación en entornos educativos. Diseñada para facilitar procesos de **heteroevaluación, coevaluación y autoevaluación**, la aplicación ofrece un entorno seguro, escalable y totalmente dockerizado.

---

## 🚀 Características Principales

### 👥 Gestión de Roles y Seguridad
* **Administrador**: Control total del sistema. Gestión de usuarios, clases, backups y opción de reset de fábrica desde un panel modular.
* **Profesor**: Capacidad para diseñar rúbricas, gestionar sus propias clases y asignar tareas de evaluación.
* **Alumno**: Acceso simplificado para realizar autoevaluaciones, coevaluaciones (con anonimato garantizado) y consultar sus notas finales.
* **Recuperación de Acceso**: Flujo de restablecimiento de contraseña mediante tokens de seguridad vinculados a Usuario + Email.

### 📝 Motor de Rúbricas Avanzado
* **Diseñador Visual**: Interfaz intuitiva para definir criterios y niveles de logro de forma dinámica.
* **Clasificación Potente**: Organización por asignatura y sistema de etiquetas (JSON) para la gestión de competencias.
* **Biblioteca de Rúbricas**: Buscador avanzado y filtrado en tiempo real para localizar plantillas rápidamente.
* **Exportación e Importación**: 
  * Generación de **PDF** imprimibles con un clic.
  * Carga masiva mediante **CSV** y descarga de plantillas oficiales.
* **Duplicación**: Clonación de rúbricas existentes para adaptarlas sin alterar los originales.

### 📊 Sistema de Evaluación
* **Ponderación Dinámica**: Configuración personalizada de pesos por tarea (ej. Heteroevaluación 60%, Coevaluación 30%, Autoevaluación 10%).
* **Resultados en Tiempo Real**: Cálculo automático de medias ponderadas y notas finales tras cada envío.
* **Privacidad Estricta**: Los alumnos acceden únicamente a su nota final, mientras que los profesores disponen del desglose detallado de todos los evaluadores.
* **Libro de Calificaciones**: Panel centralizado para la exportación de notas a formatos compatibles con hojas de cálculo.

---

## 🛠️ Instalación y Despliegue

El proyecto está diseñado bajo una arquitectura de microservicios mediante contenedores, lo que permite un despliegue "Plug & Play".

### Requisitos previos
* **Docker** y **Docker Compose** instalados en el sistema.

### Pasos para el inicio rápido
1. **Configurar el entorno**: Define las credenciales en tu archivo `docker-compose.yml`. El sistema sincroniza automáticamente estos datos con la aplicación.
2. **Levantar los servicios**:
   ```bash
   docker-compose up -d
