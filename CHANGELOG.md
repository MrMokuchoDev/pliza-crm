# Changelog

Todos los cambios notables en este proyecto seran documentados en este archivo.

El formato esta basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Sin publicar]

### Agregado
- Sistema de actualizacion automatica desde GitHub Releases
- Panel de actualizaciones en `/admin/updates`
- Script de actualizacion web `update.php` para hosting compartido
- Backups automaticos antes de actualizar
- Cliente GitHub API para verificar nuevas versiones
- Soporte para canales de actualizacion (stable/beta)

## [1.0.0] - 2025-12-09

### Agregado
- Sistema completo de gestion de leads (CRUD)
- Vista Kanban con drag & drop para pipeline de ventas
- Vista de lista con filtros y busqueda
- Sistema de notas/comentarios por lead
- Gestion de fases de venta personalizables
- Widgets embebibles para captura de leads:
  - Boton de WhatsApp
  - Boton de llamada
  - Formulario de contacto
- Gestion de sitios web con API keys
- Panel de mantenimiento para hosting compartido
- Instalador web sin necesidad de SSH
- Arquitectura hexagonal + CQRS
- Dashboard con estadisticas basicas
- Autenticacion de usuarios
- Diseno responsive mobile-first

### Caracteristicas Tecnicas
- Laravel 12 + Livewire 3 + Alpine.js
- Tailwind CSS para estilos
- SortableJS para drag & drop
- Soporte para hosting compartido (sin SSH)
- UUIDs como identificadores primarios
- Soft deletes en leads
