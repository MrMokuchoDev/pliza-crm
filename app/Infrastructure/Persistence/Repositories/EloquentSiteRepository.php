<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Site\Entities\Site;
use App\Domain\Site\Repositories\SiteRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\SiteModel;
use Illuminate\Support\Str;

class EloquentSiteRepository implements SiteRepositoryInterface
{
    public function findById(string $id): ?Site
    {
        $model = SiteModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByApiKey(string $apiKey): ?Site
    {
        $model = SiteModel::where('api_key', $apiKey)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByDomain(string $domain): ?Site
    {
        $model = SiteModel::where('domain', $domain)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(): array
    {
        return SiteModel::orderBy('name')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->toArray();
    }

    public function findAllActive(): array
    {
        return SiteModel::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->toArray();
    }

    public function save(Site $site): void
    {
        SiteModel::updateOrCreate(
            ['id' => $site->id],
            [
                'name' => $site->name,
                'domain' => $site->domain,
                'api_key' => $site->apiKey,
                'is_active' => $site->isActive,
                'settings' => $site->settings,
                'created_at' => $site->createdAt,
            ]
        );
    }

    public function delete(string $id): void
    {
        SiteModel::destroy($id);
    }

    public function generateApiKey(): string
    {
        do {
            $apiKey = 'sk_' . Str::random(32);
        } while (SiteModel::where('api_key', $apiKey)->exists());

        return $apiKey;
    }

    private function toDomain(SiteModel $model): Site
    {
        return new Site(
            id: $model->id,
            name: $model->name,
            domain: $model->domain,
            apiKey: $model->api_key,
            isActive: $model->is_active,
            settings: $model->settings,
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()) : null,
        );
    }
}
