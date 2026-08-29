<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts a query to residents belonging to a family head in an RT led by
 * the authenticated Ketua RT. Relies on FamilyHead's own RtOwnedScope being
 * applied automatically inside the whereHas() subquery.
 */
class RtOwnedThroughFamilyHeadScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'ketua_rt') {
            return;
        }

        $builder->whereHas('familyHead');
    }
}
