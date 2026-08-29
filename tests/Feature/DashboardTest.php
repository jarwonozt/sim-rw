<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\FamilyHead;
use App\Models\Resident;
use App\Models\Treasury;
use App\Models\TreasuryCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_population_pyramid_buckets_residents_by_age_band_and_gender(): void
    {
        $user = User::factory()->role('ketua_rw')->create();
        $familyHead = FamilyHead::factory()->create();

        Resident::factory()->create([
            'family_head_id' => $familyHead->id,
            'gender' => 'L',
            'birth_date' => Carbon::now()->subYears(3), // masuk band 0-4
        ]);
        Resident::factory()->create([
            'family_head_id' => $familyHead->id,
            'gender' => 'P',
            'birth_date' => Carbon::now()->subYears(30), // masuk band 30-34
        ]);
        Resident::factory()->create([
            'family_head_id' => $familyHead->id,
            'gender' => 'L',
            'birth_date' => Carbon::now()->subYears(80), // masuk band terbuka 75+
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('populationPyramid.0.age_band', '0-4')
            ->where('populationPyramid.0.male', 1)
            ->where('populationPyramid.0.female', 0)
            ->where('populationPyramid.6.age_band', '30-34')
            ->where('populationPyramid.6.female', 1)
            ->where('populationPyramid.15.age_band', '75+')
            ->where('populationPyramid.15.male', 1)
        );
    }

    public function test_residents_without_birth_date_are_excluded_from_the_pyramid(): void
    {
        $user = User::factory()->role('ketua_rw')->create();
        $familyHead = FamilyHead::factory()->create();

        Resident::factory()->create([
            'family_head_id' => $familyHead->id,
            'birth_date' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('populationPyramid', fn ($bands) => collect($bands)->sum('male') + collect($bands)->sum('female') === 0)
        );
    }

    public function test_finance_widgets_are_hidden_for_roles_without_finance_access(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $response = $this->actingAs($sekretaris)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('monthlyTrend', null)
            ->where('budgetAllocation', null)
            ->where('stats.total_saldo_kas', null)
        );
    }

    public function test_monthly_trend_sums_transactions_into_the_correct_month_bucket(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $category = TreasuryCategory::factory()->create(['type' => 'in']);

        Treasury::factory()->create([
            'treasury_category_id' => $category->id,
            'type' => 'in',
            'amount' => 500000,
            'transaction_date' => now()->startOfMonth(),
        ]);

        $response = $this->actingAs($ketuaRw)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('monthlyTrend.5.in', 500000)
        );
    }

    public function test_recent_activity_is_only_visible_to_super_admin_and_ketua_rw(): void
    {
        ActivityLog::factory()->create();

        $bendahara = User::factory()->role('bendahara')->create();
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $this->actingAs($bendahara)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page->where('recentActivity', null));

        $this->actingAs($ketuaRw)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page->has('recentActivity', 1));
    }
}
