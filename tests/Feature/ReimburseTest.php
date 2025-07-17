<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReimburseTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_lists_own_reimburses()
    {
        $otherUser = \App\Models\User::factory()->create();

        $reimburses = \App\Models\Reimburse::factory()->count(2)->create([
            'requested_by' => $this->user->id,
        ]);

        $otherReimburses = \App\Models\Reimburse::factory()->count(2)->create([
            'requested_by' => $otherUser->id,
        ]);

        $this->get('/reimburses')
            ->assertSuccessful()
            ->assertJsonMissing($otherReimburses->toArray())
            ->assertJson(fn (\Illuminate\Testing\Fluent\AssertableJson $json) => $json->has(2)
                ->first(fn (\Illuminate\Testing\Fluent\AssertableJson $json) => $json->where('requested_by', $this->user->id)
                    ->etc()
                )
            );
    }

    public function test_view_own_reimburse()
    {
        $reimburse = \App\Models\Reimburse::factory()->create([
            'requested_by' => $this->user->id,
        ]);

        $this->get("/reimburses/{$reimburse->id}")
            ->assertSuccessful()
            ->assertJson(fn (\Illuminate\Testing\Fluent\AssertableJson $json) => $json->where('id', $reimburse->id)
                ->where('requested_by', $this->user->id)
                ->etc()
            );
    }

    public function test_cannot_view_other_user_reimburse()
    {
        $otherUser = \App\Models\User::factory()->create();
        $reimburse = \App\Models\Reimburse::factory()->create([
            'requested_by' => $otherUser->id,
        ]);

        $this->get("/reimburses/{$reimburse->id}")
            ->assertNotFound();
    }
}
