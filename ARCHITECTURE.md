# Arquitectura de Pliza CRM

Guía para desarrolladores sobre la arquitectura del proyecto.

## TL;DR

- **Commands** para escribir datos, **Queries** para leer
- **Services** orquestan Commands/Queries
- **Livewire/Controllers** solo llaman a Services, nunca a Models directamente
- Excepciones: relaciones Eloquent y queries simples en vistas están OK

## Patrones Utilizados

| Patrón | Qué hace | Ejemplo en el proyecto |
|--------|----------|------------------------|
| **Hexagonal** | Separa dominio de infraestructura | `Domain/` vs `Infrastructure/` |
| **DDD** | Modela según el negocio | `Lead`, `Deal`, `SalePhase` como entidades |
| **CQRS** | Separa lectura de escritura | `CreateLeadCommand` vs `GetLeadByIdQuery` |

## Estructura de Directorios

```
app/
├── Domain/                     # Lógica de negocio pura (sin Laravel)
│   ├── Lead/
│   ├── Deal/
│   ├── SalePhase/
│   ├── Note/
│   ├── Site/
│   └── User/
│       └── ValueObjects/
│           └── Permission.php  # Enum de permisos
│
├── Application/                # Casos de uso
│   ├── Lead/
│   │   ├── Commands/           # CreateLeadCommand, UpdateLeadCommand...
│   │   ├── Queries/            # GetLeadByIdQuery, ListLeadsQuery...
│   │   ├── Handlers/           # Ejecutan los Commands/Queries
│   │   └── Services/           # LeadService (orquestador)
│   ├── Deal/
│   ├── Dashboard/
│   └── ...
│
├── Infrastructure/             # Implementaciones concretas
│   ├── Persistence/Eloquent/   # LeadModel, DealModel...
│   └── Http/
│       └── Livewire/           # Componentes de UI
│
└── Models/                     # User (Laravel Breeze)
```

## CQRS en la Práctica

```
ESCRITURA (Commands)              LECTURA (Queries)
─────────────────────            ─────────────────────
CreateLeadCommand                GetLeadByIdQuery
UpdateLeadCommand                ListLeadsQuery
DeleteLeadCommand                SearchLeadsQuery
AssignLeadCommand                GetDashboardStatsQuery
ChangeLeadPhaseCommand

→ Modifican BD                   → Solo SELECT
→ Retornan void o ID             → Retornan datos
```

## Flujo de una Petición

```
Usuario → Livewire → Service → Handler → Model → BD
              ↓          ↓         ↓
           Valida    Crea el   Ejecuta
           input    Command    lógica
```

**Ejemplo real del proyecto:**

```php
// 1. Livewire recibe la acción
// resources/views/livewire/leads/lead-list.php
public function deleteLead(string $id): void
{
    $this->leadService->delete($id);  // Solo llama al service
    $this->dispatch('lead-deleted');
}

// 2. Service orquesta
// app/Application/Lead/Services/LeadService.php
public function delete(string $id): void
{
    $command = new DeleteLeadCommand($id);
    $this->deleteHandler->handle($command);
}

// 3. Handler ejecuta la lógica
// app/Application/Lead/Handlers/DeleteLeadHandler.php
public function handle(DeleteLeadCommand $command): void
{
    $lead = $this->leadModel->findOrFail($command->id);
    $lead->delete();  // Soft delete
}
```

## Reglas y Excepciones

### ✅ Siempre hacer

```php
// Livewire llama a Service
$this->leadService->create($data);

// Handler tiene la lógica de negocio
public function handle(CreateLeadCommand $cmd): string
{
    // Validaciones de negocio aquí
    if ($this->isDuplicate($cmd->email)) {
        throw new DuplicateLeadException();
    }
    return $this->model->create([...])->id;
}
```

### ⚠️ Excepciones aceptables

```php
// 1. Relaciones Eloquent en vistas (read-only)
$lead->salePhase->name           // OK
$lead->notes()->latest()->get()  // OK

// 2. Queries simples de UI sin lógica
LeadModel::count()               // OK para dashboard
$user->role->name                // OK para mostrar info

// 3. Componentes Livewire pequeños y simples
// Si solo es un CRUD básico sin lógica, está OK simplificar
```

### ❌ Nunca hacer

```php
// Lógica de negocio en Livewire
public function store()
{
    // ❌ MAL: validación de negocio aquí
    if (Lead::where('email', $this->email)->exists()) {
        // ...
    }
    Lead::create([...]);
}

// Lógica de negocio en Controller
public function update(Request $request, $id)
{
    // ❌ MAL: reglas de negocio aquí
    $lead = Lead::find($id);
    if ($lead->phase->is_closed) {
        // ...
    }
}
```

## Agregar Nueva Funcionalidad

### Ejemplo: Agregar "Marcar Lead como Favorito"

```bash
# 1. Crear el Command
app/Application/Lead/Commands/ToggleFavoriteCommand.php

# 2. Crear el Handler
app/Application/Lead/Handlers/ToggleFavoriteHandler.php

# 3. Agregar método al Service existente
app/Application/Lead/Services/LeadService.php

# 4. Llamar desde Livewire
```

**Código:**

```php
// 1. Command
class ToggleFavoriteCommand
{
    public function __construct(
        public string $leadId,
        public string $userId,
    ) {}
}

// 2. Handler
class ToggleFavoriteHandler
{
    public function __construct(
        private readonly LeadModel $model,
    ) {}

    public function handle(ToggleFavoriteCommand $cmd): bool
    {
        $lead = $this->model->findOrFail($cmd->leadId);
        $lead->is_favorite = !$lead->is_favorite;
        $lead->save();

        return $lead->is_favorite;
    }
}

// 3. Service
public function toggleFavorite(string $leadId, string $userId): bool
{
    return $this->toggleFavoriteHandler->handle(
        new ToggleFavoriteCommand($leadId, $userId)
    );
}

// 4. Livewire
public function toggleFavorite(): void
{
    $this->isFavorite = $this->leadService->toggleFavorite(
        $this->leadId,
        auth()->id()
    );
}
```

## Módulos Existentes

| Módulo | Ubicación | Responsabilidad |
|--------|-----------|-----------------|
| Lead | `Application/Lead/` | Gestión de contactos/leads |
| Deal | `Application/Deal/` | Negocios/oportunidades |
| SalePhase | `Application/SalePhase/` | Fases del pipeline |
| Note | `Application/Note/` | Notas en leads/deals |
| Site | `Application/Site/` | Sitios web para widgets |
| Dashboard | `Application/Dashboard/` | Estadísticas |
| User | `Domain/User/` | Roles y permisos |

## Errores Comunes

### Componente Livewire no encontrado

```
Unable to find component: [leads.lead-list]
```

**Causa:** El componente no está registrado en el bootstrap de Livewire.

**Solución:** Agregar el componente en `app/Providers/AppServiceProvider.php`:

```php
use Livewire\Livewire;
use App\Infrastructure\Http\Livewire\Leads\LeadList;

public function boot(): void
{
    // Registrar componentes Livewire
    Livewire::component('leads.lead-list', LeadList::class);
}
```

> Los componentes en `Infrastructure/Http/Livewire/` no se auto-descubren porque están fuera de `app/Livewire/`. Deben registrarse manualmente.

### "Class not found" al inyectar Handler

```php
// ❌ Olvidaste registrar en el Service Provider
// o el namespace está mal

// ✅ Verifica el namespace
namespace App\Application\Lead\Handlers;  // Correcto
namespace App\Application\Leads\Handlers; // Incorrecto (Leads vs Lead)
```

### Handler no recibe dependencias

```php
// ❌ Instanciando manualmente
$handler = new CreateLeadHandler();

// ✅ Usar el container
$handler = app(CreateLeadHandler::class);
// o inyectar en constructor del Service
```

### Lógica duplicada entre Commands

```php
// ❌ Copiar validaciones en cada Handler

// ✅ Extraer a un Service de dominio
// app/Domain/Lead/Services/LeadValidationService.php
```

## Testing

```php
// Handler se testea aislado
public function test_create_lead_handler(): void
{
    $handler = app(CreateLeadHandler::class);

    $id = $handler->handle(new CreateLeadCommand(
        name: 'Test',
        email: 'test@example.com',
        phone: '+1234567890',
    ));

    $this->assertDatabaseHas('leads', ['id' => $id]);
}
```

## Cuándo NO usar esta arquitectura

Para features muy simples (ej: cambiar un flag booleano), está OK simplificar:

```php
// Feature simple: marcar notificación como leída
// No necesita Command/Handler, un método en el modelo basta
$notification->markAsRead();
```

La arquitectura es una guía, no una religión. El objetivo es **código mantenible**, no burocracia.
