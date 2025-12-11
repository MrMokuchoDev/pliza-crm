<?php

declare(strict_types=1);

namespace App\Application\Update\DTOs;

class UpdateResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $fromVersion = null,
        public readonly ?string $toVersion = null,
        public readonly array $logs = [],
        public readonly array $errors = [],
        public readonly ?string $backupPath = null,
    ) {}

    /**
     * Create a successful result.
     */
    public static function success(
        string $message,
        ?string $fromVersion = null,
        ?string $toVersion = null,
        array $logs = [],
        ?string $backupPath = null,
    ): self {
        return new self(
            success: true,
            message: $message,
            fromVersion: $fromVersion,
            toVersion: $toVersion,
            logs: $logs,
            errors: [],
            backupPath: $backupPath,
        );
    }

    /**
     * Create a failed result.
     */
    public static function failure(
        string $message,
        array $errors = [],
        array $logs = [],
    ): self {
        return new self(
            success: false,
            message: $message,
            logs: $logs,
            errors: $errors,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'from_version' => $this->fromVersion,
            'to_version' => $this->toVersion,
            'logs' => $this->logs,
            'errors' => $this->errors,
            'backup_path' => $this->backupPath,
        ];
    }
}
