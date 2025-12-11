# Actualización de Pliza CRM

## Requisitos

Tu servidor debe tener estas extensiones PHP habilitadas:

- **php-zip** (obligatoria para descomprimir actualizaciones)
- **php-curl** (para descargar desde GitHub)

> La mayoría de hostings las incluyen por defecto. Si ves el error "Class ZipArchive not found", contacta a tu proveedor para habilitar `php-zip`.

---

## Cómo Actualizar

### Opción 1: Desde el Panel de Administración (Recomendado)

1. Inicia sesión como administrador
2. Ve a **Configuración → Actualizaciones** (`/admin/updates`)
3. Haz clic en **Verificar Actualizaciones**
4. Si hay una nueva versión, haz clic en **Actualizar Ahora**

El sistema automáticamente:
- Crea un backup de tu configuración
- Descarga la nueva versión
- Aplica los cambios
- Ejecuta migraciones de base de datos
- Limpia las cachés

### Opción 2: Script Web (Hosting sin Panel)

Si no tienes acceso al panel de administración:

1. Descarga `update.php` desde [GitHub Releases](https://github.com/MrMokuchoDev/pliza-crm/releases)
2. Súbelo a la raíz de tu instalación de Pliza CRM
3. Accede a `https://tudominio.com/update.php`
4. Sigue los pasos en pantalla

> El script se elimina automáticamente después de la actualización.

### Opción 3: Manual con SSH

```bash
cd /ruta/a/pliza-crm
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan optimize
```

---

## Archivos Preservados

Durante la actualización, estos archivos **NO se sobrescriben**:

- `.env` (tu configuración)
- `storage/app/public` (archivos subidos)
- `storage/app/backups` (backups existentes)
- `storage/logs` (logs del sistema)

---

## Backups

El sistema crea un backup automático antes de cada actualización. Los backups se guardan en `storage/app/backups/` y se mantienen los últimos 3.

---

## Solución de Problemas

| Error | Solución |
|-------|----------|
| "Class ZipArchive not found" | Habilitar extensión `php-zip` en el hosting |
| "Failed to download" | Verificar conexión a GitHub o descargar manualmente |
| "Permission denied" | Dar permisos de escritura a `storage/` y `bootstrap/cache/` |

Si la actualización falla, puedes restaurar desde el backup más reciente en `storage/app/backups/`.

---

## Más Información

- [Changelog](CHANGELOG.md) - Historial de cambios
- [Repositorio](https://github.com/MrMokuchoDev/pliza-crm) - Código fuente y releases
