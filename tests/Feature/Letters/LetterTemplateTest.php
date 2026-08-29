<?php

namespace Tests\Feature\Letters;

use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_bendahara_cannot_manage_letter_templates(): void
    {
        $bendahara = User::factory()->role('bendahara')->create();

        $this->actingAs($bendahara)->get(route('letter-templates.index'))->assertForbidden();
    }

    public function test_sekretaris_can_create_a_letter_template(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $response = $this->actingAs($sekretaris)->post(route('letter-templates.store'), [
            'name' => 'Surat Keterangan Domisili',
            'type' => 'domisili',
            'content' => '<p>Nama: [nama_penduduk], NIK: [nik]</p>',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('letter-templates.index'));
        $this->assertDatabaseHas('letter_templates', ['name' => 'Surat Keterangan Domisili']);
    }

    public function test_template_already_used_cannot_be_deleted(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();
        $template = LetterTemplate::factory()->create();
        Letter::factory()->create(['letter_template_id' => $template->id]);

        $this->actingAs($sekretaris)->delete(route('letter-templates.destroy', $template));

        $this->assertDatabaseHas('letter_templates', ['id' => $template->id]);
    }
}
