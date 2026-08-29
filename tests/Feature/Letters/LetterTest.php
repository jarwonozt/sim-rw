<?php

namespace Tests\Feature\Letters;

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

        $this->actingAs($ketuaRt)->get(route('letters.index'))->assertForbidden();
    }

    public function test_sekretaris_can_issue_a_letter_with_an_auto_generated_number(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $resident = Resident::factory()->create();
        $template = LetterTemplate::factory()->create(['is_active' => true]);

        $response = $this->actingAs($sekretaris)->post(route('letters.store'), [
            'resident_id' => $resident->id,
            'letter_template_id' => $template->id,
            'purpose' => 'Keperluan administrasi bank',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('letters', [
            'resident_id' => $resident->id,
            'letter_template_id' => $template->id,
            'issued_by' => $sekretaris->id,
        ]);

        $letter = Letter::first();
        $this->assertMatchesRegularExpression('#^001/RW-\d{3}/[IVX]+/\d{4}$#', $letter->letter_number);
    }

    public function test_sequential_letters_increment_the_letter_number(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $template = LetterTemplate::factory()->create(['is_active' => true]);

        foreach (range(1, 3) as $i) {
            $this->actingAs($sekretaris)->post(route('letters.store'), [
                'resident_id' => Resident::factory()->create()->id,
                'letter_template_id' => $template->id,
                'purpose' => "Keperluan ke-{$i}",
            ]);
        }

        $numbers = Letter::orderBy('id')->pluck('letter_number')->map(
            fn ($number) => (int) explode('/', $number)[0]
        );

        $this->assertSame([1, 2, 3], $numbers->all());
    }

    public function test_inactive_template_cannot_be_used(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $resident = Resident::factory()->create();
        $template = LetterTemplate::factory()->create(['is_active' => false]);

        $response = $this->actingAs($sekretaris)->post(route('letters.store'), [
            'resident_id' => $resident->id,
            'letter_template_id' => $template->id,
            'purpose' => 'Coba template nonaktif',
        ]);

        $response->assertSessionHasErrors('letter_template_id');
    }

    public function test_letter_pdf_can_be_downloaded(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $letter = Letter::factory()->create();

        $response = $this->actingAs($sekretaris)->get(route('letters.download', $letter));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
