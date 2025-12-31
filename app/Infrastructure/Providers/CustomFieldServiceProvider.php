<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\CustomField\Handlers\CreateCustomFieldGroupHandler;
use App\Application\CustomField\Handlers\CreateCustomFieldHandler;
use App\Application\CustomField\Handlers\CreateCustomFieldOptionHandler;
use App\Application\CustomField\Handlers\DeleteCustomFieldGroupHandler;
use App\Application\CustomField\Handlers\DeleteCustomFieldHandler;
use App\Application\CustomField\Handlers\DeleteCustomFieldOptionHandler;
use App\Application\CustomField\Handlers\GetAvailableEntityTypesHandler;
use App\Application\CustomField\Handlers\GetCustomFieldByIdHandler;
use App\Application\CustomField\Handlers\GetCustomFieldGroupByIdHandler;
use App\Application\CustomField\Handlers\GetCustomFieldGroupsHandler;
use App\Application\CustomField\Handlers\GetCustomFieldOptionsHandler;
use App\Application\CustomField\Handlers\GetCustomFieldsByEntityHandler;
use App\Application\CustomField\Handlers\GetCustomFieldValuesForEntityHandler;
use App\Application\CustomField\Handlers\ReorderCustomFieldGroupsHandler;
use App\Application\CustomField\Handlers\ReorderCustomFieldsHandler;
use App\Application\CustomField\Handlers\SetCustomFieldValueHandler;
use App\Application\CustomField\Handlers\ToggleCustomFieldActiveHandler;
use App\Application\CustomField\Handlers\UpdateCustomFieldGroupHandler;
use App\Application\CustomField\Handlers\UpdateCustomFieldHandler;
use App\Application\CustomField\Handlers\UpdateCustomFieldOptionHandler;
use App\Application\CustomField\Services\CustomFieldService;
use App\Domain\CustomField\Repositories\CustomFieldGroupRepositoryInterface;
use App\Domain\CustomField\Repositories\CustomFieldRepositoryInterface;
use App\Domain\CustomField\Repositories\CustomFieldValueRepositoryInterface;
use App\Domain\CustomField\Repositories\EntityTypeRepositoryInterface;
use App\Domain\CustomField\Services\CustomFieldNameGeneratorInterface;
use App\Domain\CustomField\Services\CustomFieldOptionsTableManagerInterface;
use App\Domain\CustomField\ValueObjects\EntityType;
use App\Infrastructure\Persistence\Eloquent\CustomFieldCounterModel;
use App\Infrastructure\Persistence\Eloquent\CustomFieldGroupModel;
use App\Infrastructure\Persistence\Eloquent\CustomFieldModel;
use App\Infrastructure\Persistence\Eloquent\CustomFieldValueModel;
use App\Infrastructure\Persistence\Repositories\EloquentCustomFieldGroupRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCustomFieldRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCustomFieldValueRepository;
use App\Infrastructure\Persistence\Repositories\EloquentEntityTypeRepository;
use App\Infrastructure\Services\CustomFieldNameGenerator;
use App\Infrastructure\Services\CustomFieldOptionsTableManager;
use Illuminate\Support\ServiceProvider;

final class CustomFieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar modelos Eloquent
        $this->app->singleton(CustomFieldGroupModel::class);
        $this->app->singleton(CustomFieldModel::class);
        $this->app->singleton(CustomFieldValueModel::class);
        $this->app->singleton(CustomFieldCounterModel::class);

        // Registrar repositorios
        $this->app->singleton(CustomFieldGroupRepositoryInterface::class, function ($app) {
            return new EloquentCustomFieldGroupRepository($app->make(CustomFieldGroupModel::class));
        });

        $this->app->singleton(CustomFieldRepositoryInterface::class, function ($app) {
            return new EloquentCustomFieldRepository($app->make(CustomFieldModel::class));
        });

        $this->app->singleton(CustomFieldValueRepositoryInterface::class, function ($app) {
            return new EloquentCustomFieldValueRepository($app->make(CustomFieldValueModel::class));
        });

        $this->app->singleton(EntityTypeRepositoryInterface::class, EloquentEntityTypeRepository::class);

        // Registrar servicios de dominio
        $this->app->singleton(CustomFieldNameGeneratorInterface::class, function ($app) {
            return new CustomFieldNameGenerator($app->make(CustomFieldCounterModel::class));
        });

        $this->app->singleton(CustomFieldOptionsTableManagerInterface::class, function ($app) {
            return new CustomFieldOptionsTableManager();
        });

        // Registrar Handlers
        $this->registerHandlers();

        // Registrar servicio principal
        $this->app->singleton(CustomFieldService::class);
    }

    private function registerHandlers(): void
    {
        // Group Handlers
        $this->app->singleton(CreateCustomFieldGroupHandler::class);
        $this->app->singleton(UpdateCustomFieldGroupHandler::class);
        $this->app->singleton(DeleteCustomFieldGroupHandler::class);
        $this->app->singleton(GetCustomFieldGroupsHandler::class);
        $this->app->singleton(GetCustomFieldGroupByIdHandler::class);
        $this->app->singleton(ReorderCustomFieldGroupsHandler::class);

        // Field Handlers
        $this->app->singleton(CreateCustomFieldHandler::class);
        $this->app->singleton(UpdateCustomFieldHandler::class);
        $this->app->singleton(DeleteCustomFieldHandler::class);
        $this->app->singleton(GetCustomFieldsByEntityHandler::class);
        $this->app->singleton(GetCustomFieldByIdHandler::class);
        $this->app->singleton(ReorderCustomFieldsHandler::class);
        $this->app->singleton(ToggleCustomFieldActiveHandler::class);

        // Value Handlers
        $this->app->singleton(SetCustomFieldValueHandler::class);
        $this->app->singleton(GetCustomFieldValuesForEntityHandler::class);

        // Option Handlers
        $this->app->singleton(CreateCustomFieldOptionHandler::class);
        $this->app->singleton(UpdateCustomFieldOptionHandler::class);
        $this->app->singleton(DeleteCustomFieldOptionHandler::class);
        $this->app->singleton(GetCustomFieldOptionsHandler::class);

        // Entity Type Handlers
        $this->app->singleton(GetAvailableEntityTypesHandler::class);
    }

    public function boot(): void
    {
        // Inyectar repositorio en EntityType Value Object
        EntityType::setRepository($this->app->make(EntityTypeRepositoryInterface::class));
    }
}
