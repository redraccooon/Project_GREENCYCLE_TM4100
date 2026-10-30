<?php

namespace Tests\Feature;

use App\Enums\TreeStatus;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreeCareTest extends TestCase
{
    // Restablece la base de datos SQLite en memoria antes de cada prueba de esta clase
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Congela el reloj (sin microsegundos, que la base de datos no almacena), por lo que now()
        // es idéntico tanto dentro de la prueba como dentro del controlador
        $this->freezeSecond();
    }

    public function test_a_user_can_care_for_their_active_tree(): void
    {
        $user = User::factory()->create();
        $tree = Tree::factory()->for($user)->create(['health' => 60, 'progress' => 0]);

        $response = $this->actingAs($user)->postJson("/api/trees/{$tree->id}/care");

        $response->assertOk()
            ->assertJsonPath('message', 'Cuidado aplicado con éxito')
            ->assertJsonPath('tree.progress', 1)
            ->assertJsonPath('tree.health', 80)
            ->assertJsonPath('tree.status', 'ACTIVE')
            ->assertJsonPath('tree.next_care_at', now()->addMinutes(60)->toIso8601ZuluString());

        $tree->refresh();
        $this->assertSame(1, $tree->progress);
        $this->assertTrue($tree->next_care_at->equalTo(now()->addMinutes(60)));
        $this->assertNotNull($tree->last_care_at);
    }

    public function test_a_second_care_during_cooldown_is_rejected_without_changing_progress(): void
    {
        $user = User::factory()->create();
        $tree = Tree::factory()->for($user)->create();

        $this->actingAs($user)->postJson("/api/trees/{$tree->id}/care")->assertOk();
        $response = $this->actingAs($user)->postJson("/api/trees/{$tree->id}/care");

        $response->assertConflict()
            ->assertJsonPath('error', 'El árbol se encuentra en período de enfriamiento.')
            ->assertJsonPath('code', 'TREE_ON_COOLDOWN')
            ->assertJsonPath('next_care_at', now()->addMinutes(60)->toIso8601ZuluString());

        $this->assertSame(1, $tree->refresh()->progress);
    }

    public function test_five_valid_cares_level_up_the_tree_and_reset_progress(): void
    {
        $user = User::factory()->create();
        $tree = Tree::factory()->for($user)->create();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)->postJson("/api/trees/{$tree->id}/care")->assertOk();

            // Saltar el tiempo de espera para que el siguiente cuidado sea válido
            $this->travel(61)->minutes();
        }

        $tree->refresh();
        $this->assertSame(1, $tree->level);
        $this->assertSame(0, $tree->progress);
        $this->assertSame(TreeStatus::Active, $tree->status);
    }

    public function test_caring_a_level_9_tree_with_progress_4_makes_it_mature(): void
    {
        $user = User::factory()->create();
        $tree = Tree::factory()->for($user)->create(['level' => 9, 'progress' => 4]);

        $response = $this->actingAs($user)->postJson("/api/trees/{$tree->id}/care");

        $response->assertOk()
            ->assertJsonPath('tree.level', 10)
            ->assertJsonPath('tree.status', 'MATURE');

        $tree->refresh();
        $this->assertSame(10, $tree->level);
        $this->assertSame(TreeStatus::Mature, $tree->status);
        $this->assertNotNull($tree->matured_at);
    }

    public function test_a_user_cannot_care_for_another_users_tree(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $tree = Tree::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->postJson("/api/trees/{$tree->id}/care");

        $response->assertForbidden();
        $this->assertSame(0, $tree->refresh()->progress);
        $this->assertNull($tree->next_care_at);
    }

    public function test_a_dead_tree_cannot_be_cared_for(): void
    {
        $user = User::factory()->create();
        $tree = Tree::factory()->for($user)->dead()->create();

        $response = $this->actingAs($user)->postJson("/api/trees/{$tree->id}/care");

        $response->assertConflict()
            ->assertJsonPath('code', 'TREE_NOT_CAREABLE')
            ->assertJsonPath('status', 'DEAD');

        $this->assertSame(0, $tree->refresh()->health);
    }

    public function test_a_mature_tree_cannot_be_cared_for(): void
    {
        $user = User::factory()->create();
        $tree = Tree::factory()->for($user)->mature()->create();

        $response = $this->actingAs($user)->postJson("/api/trees/{$tree->id}/care");

        $response->assertConflict()->assertJsonPath('status', 'MATURE');
        $this->assertSame(10, $tree->refresh()->level);
    }

    public function test_health_never_goes_above_100(): void
    {
        $user = User::factory()->create();
        $tree = Tree::factory()->for($user)->create(['health' => 90]);

        $this->actingAs($user)->postJson("/api/trees/{$tree->id}/care")
            ->assertOk()
            ->assertJsonPath('tree.health', 100);
    }

    public function test_a_guest_cannot_care_for_a_tree(): void
    {
        $tree = Tree::factory()->create();

        $this->postJson("/api/trees/{$tree->id}/care")->assertUnauthorized();
    }
}
