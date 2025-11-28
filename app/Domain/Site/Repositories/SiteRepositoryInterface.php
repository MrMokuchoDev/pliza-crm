<?php

declare(strict_types=1);

namespace App\Domain\Site\Repositories;

use App\Domain\Site\Entities\Site;

interface SiteRepositoryInterface
{
    public function findById(string $id): ?Site;

    public function findByApiKey(string $apiKey): ?Site;

    public function findByDomain(string $domain): ?Site;

    /** @return Site[] */
    public function findAll(): array;

    /** @return Site[] */
    public function findAllActive(): array;

    public function save(Site $site): void;

    public function delete(string $id): void;

    public function generateApiKey(): string;
}
