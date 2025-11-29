<?php

declare(strict_types=1);

namespace App\Domain\Lead\Entities;

use App\Domain\Lead\ValueObjects\SourceType;

/**
 * Entidad de dominio para leads (contactos).
 */
final class Lead
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $message,
        public readonly SourceType $sourceType,
        public readonly ?string $sourceSiteId,
        public readonly ?string $sourceUrl,
        public readonly ?array $metadata,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
        public readonly ?\DateTimeImmutable $deletedAt = null,
    ) {}

    public static function create(
        string $id,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $message = null,
        SourceType $sourceType = SourceType::MANUAL,
        ?string $sourceSiteId = null,
        ?string $sourceUrl = null,
        ?array $metadata = null,
    ): self {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            phone: $phone,
            message: $message,
            sourceType: $sourceType,
            sourceSiteId: $sourceSiteId,
            sourceUrl: $sourceUrl,
            metadata: $metadata,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function hasContact(): bool
    {
        return $this->email !== null || $this->phone !== null;
    }

    public function getWhatsAppUrl(): ?string
    {
        if ($this->phone === null) {
            return null;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $this->phone);
        return "https://wa.me/{$cleanPhone}";
    }

    public function getPhoneUrl(): ?string
    {
        if ($this->phone === null) {
            return null;
        }

        return "tel:{$this->phone}";
    }

    public function getEmailUrl(): ?string
    {
        if ($this->email === null) {
            return null;
        }

        return "mailto:{$this->email}";
    }
}
