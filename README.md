# DAM Platform — Digital Asset Management

Plataforma web de gestión de activos digitales desarrollada con Laravel 10, con integración de IA para generación automática de metadatos.

## 🚀 Características principales

- **Autenticación completa** — registro, login, logout con Laravel Breeze
- **Sistema de roles** — Admin, Editor y Viewer con middleware propio
- **Gestión de assets** — subida, listado, edición y borrado de archivos
- **IA integrada** — generación automática de título, descripción y etiquetas con Google Gemini
- **Gestión de categorías** — categorías y subcategorías para organizar assets
- **Panel de administración** — gestión de usuarios y roles
- **API REST** — endpoints con autenticación por token (Sanctum)
- **Dashboard** — estadísticas y gráficos en tiempo real (Chart.js)
- **Activity log** — registro de todas las acciones de los usuarios
- **Tests** — suite de tests de control de acceso con PHPUnit

## 📸 Capturas de pantalla

### Dashboard
![Dashboard](screenshots/01-dashboard.jpg)

### Listado de assets
![Assets](screenshots/02-assets-list.jpg)

### Detalle de asset con metadatos generados por IA
![Asset Detail](screenshots/03-asset-detail-ai.jpg)

### Subida de asset
![Upload](screenshots/04-asset-upload.jpg)

### Panel de administración de usuarios
![Admin Users](screenshots/05-admin-users.jpg)

### Gestión de categorías
![Categories](screenshots/06-categories.jpg)

### API REST en Postman
![API](screenshots/07-api-postman.jpg)

### Exportación a Excel
![Excel](screenshots/08-excel-export.jpg)

## 🛠️ Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 10 (PHP 8.1) |
| Frontend | Blade + Alpine.js + Tailwind CSS |
| Base de datos | MySQL |
| Autenticación API | Laravel Sanctum |
| IA | Google Gemini API |
| Gráficos | Chart.js |
| Tests | PHPUnit |
| Control de versiones | Git + GitHub |

## ⚙️ Instalación local

### Requisitos previos
- PHP 8.1+
- Composer
- MySQL
- Node.js y npm

### Pasos
```bash
# 1. Clonar el repositorio
git clone https://github.com/Rubenesky/dam-platform.git
cd dam-platform

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JS
npm install

# 4. Copiar el archivo de entorno
cp .env.example .env

# 5. Generar la clave de la aplicación
php artisan key:generate

# 6. Configurar la base de datos en .env
DB_DATABASE=dam_platform
DB_USERNAME=root
DB_PASSWORD=

# 7. Añadir la API key de Gemini en .env
GEMINI_API_KEY=tu_api_key

# 8. Ejecutar migraciones
php artisan migrate

# 9. Crear enlace simbólico para storage
php artisan storage:link

# 10. Compilar assets
npm run dev

# 11. Arrancar el servidor
php artisan serve
```

## 🔐 Roles y permisos

| Acción | Admin | Editor | Viewer |
|--------|-------|--------|--------|
| Ver assets | ✅ | ✅ | ✅ |
| Subir assets | ✅ | ✅ | ❌ |
| Editar assets | ✅ | ✅ | ❌ |
| Borrar assets | ✅ | ❌ | ❌ |
| Gestionar categorías | ✅ | ❌ | ❌ |
| Gestionar usuarios | ✅ | ❌ | ❌ |
| Acceso a API | ✅ | ✅ | ✅ |

## 🤖 Integración con IA

Al subir un archivo, la plataforma realiza automáticamente una llamada a la API de Google Gemini que analiza el nombre y tipo del archivo y genera:

- **Título** descriptivo (máximo 60 caracteres)
- **Descripción** útil (máximo 200 caracteres)
- **Etiquetas** relevantes en español (3-5 tags)

El usuario puede editar estos metadatos después de la subida. Los metadatos generados por IA se marcan con el indicador ✨ en la interfaz.

## 🌐 API REST

La API usa autenticación por token con Laravel Sanctum.

### Autenticación
```http
POST /api/login
Content-Type: application/json

{
    "email": "usuario@ejemplo.com",
    "password": "contraseña"
}
```

Respuesta:
```json
{
    "success": true,
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "Nombre",
        "email": "usuario@ejemplo.com",
        "role": "admin"
    }
}
```

### Endpoints disponibles

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/login` | Obtener token de acceso |
| POST | `/api/logout` | Cerrar sesión |
| GET | `/api/user` | Datos del usuario autenticado |
| GET | `/api/assets` | Listar assets (paginado) |
| GET | `/api/assets/{id}` | Ver un asset |
| DELETE | `/api/assets/{id}` | Eliminar un asset |

### Ejemplo de petición autenticada
```http
GET /api/assets
Authorization: Bearer 1|abc123...
```

Respuesta:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "original_name": "imagen.jpg",
            "mime_type": "image/jpeg",
            "size_kb": 24.88,
            "status": "processed",
            "url": "http://localhost:8000/storage/assets/uuid.jpg",
            "uploaded_by": "Nombre Usuario",
            "metadata": {
                "title": "Título generado por IA",
                "description": "Descripción generada por IA",
                "tags": ["tag1", "tag2"],
                "ai_generated": true
            },
            "categories": [],
            "created_at": "2026-03-30T09:58:58.000000Z"
        }
    ],
    "meta": {
        "total": 1,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

## 🧪 Tests
```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar solo los tests de assets
php artisan test --filter AssetTest
```

## 📁 Estructura del proyecto
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/UserController.php
│   │   ├── Api/AssetApiController.php
│   │   ├── Api/AuthApiController.php
│   │   ├── AssetController.php
│   │   ├── CategoryController.php
│   │   └── DashboardController.php
│   └── Middleware/
│       └── CheckRole.php
├── Models/
│   ├── ActivityLog.php
│   ├── Asset.php
│   ├── AssetMetadata.php
│   ├── Category.php
│   └── User.php
├── Services/
│   └── GeminiService.php
└── Traits/
    └── LogsActivity.php
database/
└── migrations/
    ├── create_assets_table.php
    ├── create_asset_metadata_table.php
    ├── create_categories_table.php
    ├── create_asset_category_table.php
    └── create_activity_log_table.php
```

## 👨‍💻 Autor

Desarrollado por **Rubén Jiménez Cebrián** como proyecto de portfolio para el módulo DAW.