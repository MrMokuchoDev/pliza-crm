<?php

declare(strict_types=1);

namespace App\Application\CustomField\Handlers;

use App\Application\CustomField\Commands\ReorderCustomFieldGroupsCommand;
use App\Domain\CustomField\Repositories\CustomFieldGroupRepositoryInterface;
use Ramsey\Uuid\Uuid;

final class ReorderCustomFieldGroupsHandler
{
    public function __construct(
        private readonly CustomFieldGroupRepositoryInterface $groupRepository,
    ) {}

    public function handle(ReorderCustomFieldGroupsCommand $command): void
    {
        $order = 1;
        foreach ($command->groupIds as $groupId) {
            $uuid = Uuid::fromString($groupId);
            $group = $this->groupRepository->findById($uuid);
            if ($group) {
                $group->changeOrder($order);
                $this->groupRepository->save($group);
                $order++;
            }
        }
    }
}
