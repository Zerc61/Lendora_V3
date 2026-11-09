<?php

namespace Tests\Feature\Borrower;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\BorrowerShowcaseSeeder;
use Database\Seeders\CategoryAndAssetSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShowcaseSeedTest extends TestCase
{
    use RefreshDatabase;

    private User $borrower;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->seed([
            RoleSeeder::class,
            OrganizationSeeder::class,
            AdminSeeder::class,
            CategoryAndAssetSeeder::class,
            BorrowerShowcaseSeeder::class,
            SettingSeeder::class,
        ]);

        $this->borrower = User::where('email', 'ani@lendora.test')->firstOrFail();
    }

    public function test_showcase_user_has_academic_profile_and_bookings(): void
    {
        $this->assertSame('2207412089', $this->borrower->nim);
        $this->assertSame('Desain Komunikasi Visual', $this->borrower->study_program);
        $this->assertSame(5, $this->borrower->semester);
        $this->assertSame(3, Booking::where('borrower_id', $this->borrower->id)->count());
    }

    public function test_showcase_assets_have_long_descriptions_specs_and_photos(): void
    {
        $asset = Asset::where('code', 'LDR-CAM-0007')->firstOrFail();

        $this->assertGreaterThan(300, mb_strlen($asset->description));
        $this->assertGreaterThanOrEqual(6, count($asset->specifications));
        Storage::disk('public')->assertExists($asset->photos->first()->path);
    }

    public function test_borrower_pages_render_showcase_content(): void
    {
        $this->actingAs($this->borrower)
            ->get(route('borrower.home'))
            ->assertOk()
            ->assertSee('Ani Rahmawati')
            ->assertSee('Sony FX3');

        $this->actingAs($this->borrower)
            ->get(route('borrower.catalog.index', ['search' => 'Sony FX3']))
            ->assertOk()
            ->assertSee('Sony FX3 Cinema Line');

        $this->actingAs($this->borrower)
            ->get(route('borrower.catalog.show', Asset::where('code', 'LDR-CAM-0007')->firstOrFail()))
            ->assertOk()
            ->assertSee('S-Cinetone')
            ->assertSee('Aksesori');

        $this->actingAs($this->borrower)
            ->get(route('borrower.profile.edit'))
            ->assertOk()
            ->assertSee('2207412089')
            ->assertSee('Desain Komunikasi Visual')
            ->assertSee('Keluar');
    }
}
