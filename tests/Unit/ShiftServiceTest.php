<?php

namespace Tests\Unit;

use App\Models\ShiftNote;
use App\Models\User;
use App\Services\ShiftService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private ShiftService $shifts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, OrganizationSeeder::class, AdminSeeder::class]);

        $this->operator = User::where('email', 'operator@lendora.test')->firstOrFail();
        $this->shifts = app(ShiftService::class);
    }

    public function test_current_shift_exposes_label_window_and_active_flag(): void
    {
        $shift = $this->shifts->current($this->operator->organization_id);

        $this->assertArrayHasKey('label', $shift);
        $this->assertArrayHasKey('start', $shift);
        $this->assertArrayHasKey('end', $shift);
        $this->assertIsBool($shift['active']);
    }

    public function test_store_note_keeps_operator_and_shift_label(): void
    {
        $note = $this->shifts->store($this->operator, 'Baterai sudah dicas penuh.');

        $this->assertSame('Baterai sudah dicas penuh.', $note->note);
        $this->assertSame($this->operator->id, $note->user_id);
        $this->assertSame($this->operator->organization_id, $note->organization_id);
        $this->assertNotNull($note->shift);
        $this->assertSame(1, ShiftNote::count());
        $this->assertSame($note->id, $this->shifts->lastNote($this->operator->organization_id)->id);
    }
}
