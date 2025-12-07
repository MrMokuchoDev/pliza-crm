<?php

declare(strict_types=1);

namespace App\Application\DealComment\Commands;

use Illuminate\Support\Collection;

/**
 * Elimina comentarios de múltiples negocios en una operación.
 *
 * Se usa cuando se elimina un Lead para borrar los comentarios
 * de todos sus Deals asociados en cascada.
 *
 * Ejemplo: Lead tiene 3 Deals → este comando elimina los comentarios
 * de los 3 Deals en una sola query.
 */
readonly class DeleteCommentsByDealsCommand
{
    /**
     * @param  Collection<int, string>|array<string>  $dealIds
     */
    public function __construct(
        public Collection|array $dealIds,
    ) {}
}
