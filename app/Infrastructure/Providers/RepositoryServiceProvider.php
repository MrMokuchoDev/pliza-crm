<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Lead\Repositories\LeadRepositoryInterface;
use App\Domain\Note\Repositories\NoteRepositoryInterface;
use App\Domain\SalePhase\Repositories\SalePhaseRepositoryInterface;
use App\Domain\Site\Repositories\SiteRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentLeadRepository;
use App\Infrastructure\Persistence\Repositories\EloquentNoteRepository;
use App\Infrastructure\Persistence\Repositories\EloquentSalePhaseRepository;
use App\Infrastructure\Persistence\Repositories\EloquentSiteRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        SalePhaseRepositoryInterface::class => EloquentSalePhaseRepository::class,
        LeadRepositoryInterface::class => EloquentLeadRepository::class,
        NoteRepositoryInterface::class => EloquentNoteRepository::class,
        SiteRepositoryInterface::class => EloquentSiteRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    public function boot(): void
    {
        //
    }
}
