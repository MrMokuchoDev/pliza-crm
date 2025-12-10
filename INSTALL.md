# Pliza CRM - Guía de Instalación

## Requisitos

- PHP 8.2 o superior
- MySQL 5.7+ o MariaDB 10.3+
- Composer (solo para instalación con terminal)

---

## Opción A: Instalación con Terminal (VPS / Servidor Dedicado)

Para entornos con acceso SSH:

```bash
# 1. Clonar repositorio
cd /var/www
git clone https://github.com/MrMokuchoDev/pliza-crm.git
cd pliza-crm

# 2. Instalar dependencias
composer install --optimize-autoloader --no-dev

# 3. Configurar entorno
cp .env.example .env
nano .env  # Editar con tus datos de BD y APP_URL

# 4. Generar clave y ejecutar migraciones
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=SalePhaseSeeder

# 5. Crear usuario admin
php artisan tinker
# > App\Models\User::create(['name'=>'Admin','email'=>'admin@test.com','password'=>bcrypt('password')]);

# 6. Configurar permisos
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 7. Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### Configuración de Nginx

```nginx
server {
    listen 80;
    server_name tudominio.com;
    root /var/www/pliza-crm/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Configuración de Apache

```apache
<VirtualHost *:80>
    ServerName tudominio.com
    DocumentRoot /var/www/pliza-crm/public

    <Directory /var/www/pliza-crm/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## Opción A.2: Desarrollo Local

Para desarrollo con `artisan serve`:

```bash
git clone https://github.com/MrMokuchoDev/pliza-crm.git
cd pliza-crm
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=SalePhaseSeeder
php artisan serve  # http://localhost:8000
```

---

## Opción B: Instalación en Hosting Compartido (Sin Terminal)

Para hostings sin acceso SSH (cPanel, Plesk, etc.):

### Paso 1: Crear la Base de Datos

**IMPORTANTE:** Debes crear la base de datos ANTES de subir los archivos.

#### En cPanel:

1. Ir a **MySQL Databases**
2. Crear nueva base de datos (ej: `tuusuario_plizacrm`)
3. Crear nuevo usuario con contraseña segura
4. Asignar usuario a la base de datos con **ALL PRIVILEGES**

#### En Plesk:

1. Ir a **Databases** > **Add Database**
2. Completar nombre de BD y credenciales

> **Guarda estos datos**, los necesitarás en el instalador:
> - Host (generalmente `localhost`)
> - Nombre de la base de datos
> - Usuario
> - Contraseña

### Paso 2: Preparar el proyecto en tu máquina local

Primero necesitas preparar el proyecto en tu computadora:

```bash
# 1. Clonar el repositorio
git clone https://github.com/MrMokuchoDev/pliza-crm.git
cd pliza-crm

# 2. Instalar dependencias de PHP
composer install --optimize-autoloader --no-dev

# 3. Comprimir para subir (excluir archivos innecesarios)
zip -r ../plizacrm.zip . -x "*.git*" -x "node_modules/*" -x ".env" -x "tests/*" -x "phpunit.xml"
```

> **Nota:** Necesitas tener instalado [Composer](https://getcomposer.org/) en tu máquina local.

### Paso 3: Subir archivos

1. Accede al **File Manager** de tu hosting
2. Navega a `public_html` (o el directorio de tu dominio)
3. Sube el archivo `plizacrm.zip`
4. Extrae/descomprime el ZIP

### Paso 4: Preparar estructura (pre-instalación)

Si tu dominio apunta directamente a `public_html/` (sin subcarpeta `public/`):

1. Abre en tu navegador: `https://tudominio.com/pre-install.php`
2. Espera a que se complete el proceso
3. El archivo se eliminará automáticamente

### Paso 5: Ejecutar el instalador

1. Abre en tu navegador: `https://tudominio.com/install.php`
2. Sigue los pasos del asistente:
   - **Requisitos**: Verifica que tu servidor cumple los requisitos
   - **Base de datos**: Ingresa los datos que guardaste en el Paso 1
   - **Aplicación**: Configura URL, nombre y zona horaria
   - **Administrador**: Crea tu usuario de acceso
   - **Instalar**: Ejecuta la instalación

### Paso 6: Acceder al sistema

1. Ve a `https://tudominio.com/login`
2. Ingresa con el email y contraseña que configuraste

---

## Solución de Problemas

### Error 500 después de instalar

- Verifica que `.env` existe y tiene `APP_KEY`
- Verifica permisos de `storage/` (755)

### Error de conexión a base de datos

- Verifica que las credenciales en el instalador son correctas
- En cPanel, el usuario suele ser `tuusuario_nombrebd`

### La página no carga estilos

- Ejecuta el pre-install.php si no lo hiciste
- Verifica que `APP_URL` en `.env` coincide con tu dominio

---

## Licencia

Este proyecto está licenciado bajo AGPL-3.0. Ver archivo [LICENSE](LICENSE) para más detalles.
