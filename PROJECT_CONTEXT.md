# Proyecto: SWAT Protection 51 - Ecosistema Digital (Versión PRO)

## 🎯 Objetivo General
Transformación digital del área de Recursos Humanos y Administración de una empresa de seguridad. El sistema automatiza la distribución de documentos laborales (Boletas, Contratos, Altas/Bajas) y sirve como un sistema inmutable de blindaje legal ante inspecciones gubernamentales (SUNAFIL).

## 🛠️ Stack Tecnológico
- **Backend:** Laravel 11 (PHP 8.4) dockerizado con Laravel Sail.
- **Frontend:** Vue 3 (Composition API) + Tailwind CSS y/o PrimeVue. Orientado a diseño a medida, UI/UX limpio y responsivo.
- **Base de Datos:** PostgreSQL en Supabase (Conexión directa vía Transaction Pooler).
- **Almacenamiento:** Supabase Storage (S3 Protocol) con Buckets Privados.
- **Autenticación:** Laravel Sanctum.

## 🔐 Reglas de Autenticación de Usuarios
- **Prioridad de Login:** El acceso principal es a través de **Correo Electrónico** (proveedor de la empresa) y Contraseña.
- **Fallback (Respaldo):** Se permite el inicio de sesión usando el número de documento (`document_number`) si el correo falla o no está disponible, siempre y cuando el usuario exista y la columna `is_active` sea `true`.

## ⚙️ Procesos en Segundo Plano (Background Logic)
Cada vez que un Administrador interactúa con el sistema, se desencadenan acciones invisibles para el usuario pero vitales para el negocio:
1. **El Flujo de Subida (Upload Flow):** Cuando se sube un documento válido, el sistema debe:
    - Guardar el PDF físicamente en el Bucket Privado de Supabase.
    - Registrar la ruta y metadatos en la tabla `documents`.
    - **Auditoría:** Guardar en `audit_logs` qué admin subió el documento, el ID del documento, su IP y el User-Agent.
    - **Notificación:** Disparar un Job/Queue que envíe un correo electrónico automático al trabajador notificando que tiene un nuevo documento disponible para descarga.

2. **El Flujo de Lectura (Download Flow):** Cuando el trabajador visualiza o descarga su documento:
    - **Auditoría Legal:** El sistema intercepta la petición y graba en `audit_logs` la fecha, hora exacta, IP y la acción (`downloaded`). Esta es la prueba legal de entrega.

## 🚨 Gestión de Errores y Casuísticas (Edge Cases)
El sistema está preparado para el error humano (Ej: El administrador sube la boleta de Juan en el perfil de Pedro, o con un monto equivocado).

* **Acción del Admin:** El administrador se da cuenta del error y presiona "Eliminar" en el documento erróneo.
* **Reacción del Sistema (Backend):**
    1.  **Limpieza Física:** El archivo PDF se elimina definitivamente de Supabase Storage para no consumir espacio basura.
    2.  **Borrado Lógico (Soft Delete):** El registro en la tabla `documents` **NO se borra**, se marca con la fecha en la columna `deleted_at` para ocultarlo del frontend, pero mantenerlo en base de datos.
    3.  **Auditoría Inmutable:** Se crea un registro en `audit_logs` indicando qué admin ejecutó la acción `deleted` sobre ese documento específico.
    4.  **Gestión de Notificaciones (UX):** No se envía un correo de "Documento Eliminado" al trabajador para evitar alarmas o confusiones. El sistema simplemente oculta el documento.
* **Flujo de Corrección:** El administrador sube el documento correcto. El sistema trata esto como una subida completamente nueva, generando su respectivo log de `uploaded` y enviando la notificación estándar de "Nuevo documento disponible".

## 🗄️ Arquitectura de Base de Datos
- **Users:** Identificación por `email` y `document_number` (String flexible, soporta DNI/CE/Pasaporte). Roles gestionados por columna `role`.
- **Documents:** Relacionado a Users. Usa `SoftDeletes`.
- **AuditLogs:** Tabla inmutable. Nunca recibe updates ni deletes.
