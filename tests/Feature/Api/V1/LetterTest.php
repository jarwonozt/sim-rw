<?php

namespace Tests\Feature\Api\V1;

use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterTest extends TestCase
{
    use RefreshDatabase;

    public function test_ketua_rt_cannot_issue_letters(): void
    {
        $ketuaRt = User::factory()->role('ketua_rt')->create();

        $this->actingAs($ketuaRt, 'sanctum')->getJson(route('api.v1.letters.index'))->assertForbidden();
    }

    public function test_sekretaris_can_issue_a_letter_with_an_auto_generated_number(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $resident = Resident::factory()->create();
        $template = LetterTemplate::factory()->create(['is_active' => true]);

        $response = $this->actingAs($sekretaris, 'sanctum')->postJson(route('api.v1.letters.store'), [
            'resident_id' => $resident->id,
            'letter_template_id' => $template->id,
            'purpose' => 'Keperluan administrasi bank',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('letters', [
            'resident_id' => $resident->id,
            'letter_template_id' => $template->id,
            'issued_by' => $sekretaris->id,
        ]);
    }

    public function test_letter_pdf_can_be_downloaded(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $letter = Letter::factory()->create();

        $response = $this->actingAs($sekretaris, 'sanctum')->get(route('api.v1.letters.download', $letter));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
