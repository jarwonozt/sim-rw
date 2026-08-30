<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts inventory loans to items belonging to an RT led by the
 * authenticated Ketua RT. Relies on InventoryItem's own RtOwnedScope being
 * applied automatically inside the whereHas() subquery.
 */
class RtOwnedThroughInventoryItemScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'ketua_rt') {
            return;
        }

        $builder->whereHas('item');
    }
}
