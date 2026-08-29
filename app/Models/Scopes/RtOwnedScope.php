<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts a query to the RT(s) led by the authenticated Ketua RT.
 *
 * PRD Bagian 6.2: Ketua RT tidak boleh melihat data warga di RT lain.
 * Super Admin, Ketua RW, Sekretaris, and Bendahara are unrestricted by design
 * (their access boundaries are enforced by role middleware instead).
 */
class RtOwnedScope implements Scope
{
    public function __construct(private readonly string $rtColumn = 'rt_id') {}

    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'ketua_rt') {
            return;
        }

        $builder->whereIn($model->qualifyColumn($this->rtColumn), function ($query) use ($user) {
            $query->select('id')->from('master_rt')->where('ketua_rt_id', $user->id);
        });
    }
}
