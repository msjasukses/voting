<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return $overrides + [
            'school_name' => 'SMP Negeri 99 Bandung',
            'school_year' => '2027/2028',
            'city' => 'Bandung',
            'header_subtitle' => 'Pemilihan Ketua OSIS',
            'title' => 'Pemilu Raya Siswa',
            'description' => 'Pemilihan Ketua OSIS periode baru',
            'motto' => 'Satu Suara Untuk Sekolah',
            'motto_caption' => 'Gunakan hak pilihmu.',
        ];
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_change_landing_page_texts(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->validPayload())
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settings', ['key' => 'title', 'value' => 'Pemilu Raya Siswa']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('SMP Negeri 99 Bandung')
            ->assertSee('Pemilihan Ketua OSIS 2027/2028')
            ->assertSee('Pemilu Raya Siswa')
            ->assertSee('Satu Suara Untuk Sekolah');
    }

    public function test_admin_can_upload_logo_and_restore_the_default(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $defaultLogo = config('voting.logo');

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->validPayload([
                'logo' => UploadedFile::fake()->image('logo-sekolah.png', 200, 200),
            ]))
            ->assertRedirect(route('admin.settings.edit'));

        $logo = config('voting.logo');
        $this->assertStringStartsWith('storage/branding/', $logo);
        Storage::disk('public')->assertExists('branding/'.basename($logo));
        $this->get(route('home'))->assertSee(asset($logo));

        $this->put(route('admin.settings.update'), $this->validPayload(['reset_logo' => '1']))
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame($defaultLogo, config('voting.logo'));
        $this->assertDatabaseMissing('settings', ['key' => 'logo']);
        Storage::disk('public')->assertMissing('branding/'.basename($logo));
    }

    public function test_svg_logo_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), $this->validPayload([
                'logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
            ]))
            ->assertSessionHasErrors('logo');

        $this->assertDatabaseMissing('settings', ['key' => 'logo']);
    }

    public function test_required_texts_must_be_filled(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), [])
            ->assertSessionHasErrors(['school_name', 'school_year', 'city', 'header_subtitle', 'title', 'motto']);

        $this->assertDatabaseCount('settings', 0);
    }

    public function test_voters_cannot_open_settings(): void
    {
        $voter = User::factory()->create(['email' => null, 'nis' => '000999']);

        $this->actingAs($voter)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($voter)->put(route('admin.settings.update'), $this->validPayload())->assertForbidden();
    }
}
