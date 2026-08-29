<?php

namespace Tests\Feature\Treasury;

use App\Models\Treasury;
use App\Models\TreasuryCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TreasuryReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_computes_correct_totals_for_the_selected_period(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();
        $income = TreasuryCategory::factory()->create(['type' => 'in']);
        $expense = TreasuryCategory::factory()->create(['type' => 'out']);

        Treasury::factory()->create([
            'treasury_category_id' => $income->id,
            'type' => 'in',
            'amount' => 200000,
            'transaction_date' => '2026-03-10',
        ]);
        Treasury::factory()->create([
            'treasury_category_id' => $expense->id,
            'type' => 'out',
            'amount' => 75000,
            'transaction_date' => '2026-03-15',
        ]);
        // Outside the filtered period — must not be counted.
        Treasury::factory()->create([
            'treasury_category_id' => $income->id,
            'type' => 'in',
            'amount' => 999999,
            'transaction_date' => '2026-04-01',
        ]);

        $response = $this->actingAs($bendahara)->get(route('treasury-report.index', [
            'year' => 2026,
            'month' => 3,
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('summary.total_masuk', 200000)
            ->where('summary.total_keluar', 75000)
            ->where('summary.saldo_akhir', 125000)
        );
    }

    public function test_excel_export_downloads(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();
        Treasury::factory()->create(['transaction_date' => Carbon::now()]);

        $response = $this->actingAs($bendahara)->get(route('treasury-report.export-excel', [
            'year' => now()->year,
        ]));

        $response->assertOk();
    }

    public function test_pdf_export_streams(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();
        Treasury::factory()->create(['transaction_date' => Carbon::now()]);

        $response = $this->actingAs($bendahara)->get(route('treasury-report.export-pdf', [
            'year' => now()->year,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
