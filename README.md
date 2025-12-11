# Pliza CRM

Sistema ligero de gestión de leads con widgets embebibles para captura desde múltiples sitios web.

## Características

- **Captura de leads** mediante widgets embebibles (WhatsApp, Llamada, Formulario)
- **Pipeline de ventas** con vista Kanban drag & drop
- **Gestión de contactos** con notas y seguimiento
- **Multi-sitio** - Captura leads desde múltiples dominios
- **Instalador web** - Funciona en hosting compartido sin terminal
- **Responsive** - Diseño mobile-first

## Requisitos

- PHP 8.2+
- MySQL 5.7+ / MariaDB 10.3+
- Composer (solo para preparar el proyecto)

## Instalación

Ver [INSTALL.md](INSTALL.md) para instrucciones detalladas.

## Actualización

Ver [UPDATE.md](UPDATE.md) para instrucciones de actualización.

### Instalación rápida (desarrollo)

```bash
git clone https://github.com/MrMokuchoDev/pliza-crm.git
cd pliza-crm
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=SalePhaseSeeder
php artisan serve
```

### Hosting compartido

El proyecto incluye un instalador web. Ver [INSTALL.md](INSTALL.md) para más detalles.

## Stack Tecnológico

- **Backend:** Laravel 12
- **Frontend:** Livewire 3 + Alpine.js + Tailwind CSS
- **Arquitectura:** Hexagonal + DDD + CQRS

## Widgets Embebibles

Genera código embed para capturar leads desde cualquier sitio web:

```html
<!-- Botón WhatsApp -->
<script src="https://tudominio.com/widget.js"
        data-site-id="UUID"
        data-type="whatsapp"
        data-phone="+57300123456">
</script>
```

## Licencia

Este proyecto está licenciado bajo [AGPL-3.0](LICENSE).
