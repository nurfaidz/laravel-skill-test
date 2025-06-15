<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(Gate::class)
            ->policy(Post::class, \App\Policies\PostPolicy::class);
    }

    public function test_lists_posts()
    {
        $user = User::factory()->create();

        Post::factory()->count(2)->create([
            'user_id' => $user->id,
            'is_draft' => false,
            'published_at' => now()->subHour(),
        ]);

        Post::factory()->count(2)->create([
            'user_id' => $user->id,
            'is_draft' => true,
            'published_at' => null,
        ]);

        Post::factory()->create([
            'user_id' => $user->id,
            'is_draft' => false,
            'published_at' => now()->addHour(),
        ]);

        $response = $this->getJson('/posts');
        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_create_post_requires_authentication()
    {
        $postData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'is_draft' => false,
            'published_at' => now(),
        ];

        $response = $this->postJson('/posts', $postData);
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'is_draft' => false,
        ];

        $response = $this->postJson('/posts', $postData);
        $response->assertCreated()
            ->assertJsonFragment([
                'title' => $postData['title'],
                'content' => $postData['content'],
                'is_draft' => $postData['is_draft'],
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => $postData['title'],
            'content' => $postData['content'],
            'is_draft' => false,
            'user_id' => $user->id,
        ]);
    }

    public function test_create_draft_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'is_draft' => true,
        ];

        $response = $this->postJson('/posts', $postData);
        $response->assertCreated()
            ->assertJsonFragment([
                'title' => $postData['title'],
                'content' => $postData['content'],
                'is_draft' => true,
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => $postData['title'],
            'content' => $postData['content'],
            'is_draft' => true,
            'user_id' => $user->id,
        ]);
    }

    public function test_create_scheduled_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $futureDate = now()->addDays(7);

        $postData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'is_draft' => false,
            'published_at' => $futureDate,
        ];

        $response = $this->postJson('/posts', $postData);

        $response->assertCreated()
            ->assertJsonFragment([
                'title' => $postData['title'],
                'content' => $postData['content'],
                'is_draft' => false,
                'published_at' => $futureDate->toISOString(),
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => $postData['title'],
            'is_draft' => false,
        ]);
    }

    public function test_show_published_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'is_draft' => false,
            'published_at' => now()->subHour(),
        ]);

        $response = $this->getJson("/posts/{$post->id}");
        $response->assertOk();
    }

    public function test_show_draft_post_returns_404()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'is_draft' => true,
            'published_at' => null,
        ]);

        $response = $this->getJson("/posts/{$post->id}");
        $response->assertNotFound();
    }

    public function test_show_scheduled_post_returns_404()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'is_draft' => false,
            'published_at' => now()->addHour(),
        ]);

        $response = $this->getJson("/posts/{$post->id}");
        $response->assertNotFound();
    }

    public function test_update_post_requires_authentication()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ];

        $response = $this->putJson("/posts/{$post->id}", $updateData);
        $response->assertUnauthorized();
    }

    public function test_user_can_update_own_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ];

        $response = $this->putJson("/posts/{$post->id}", $updateData);
        $response->assertOk();

        $this->assertDatabaseHas(Post::class, [
            'id' => $post->id,
            'title' => $updateData['title'],
            'content' => $updateData['content'],
        ]);
    }

    public function test_user_cannot_update_other_post()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $updateData = [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
        ];

        $response = $this->putJson("/posts/{$post->id}", $updateData);
        $response->assertForbidden();
    }

    public function test_delete_post_requires_authentication()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/posts/{$post->id}");
        $response->assertUnauthorized();
    }

    public function test_user_can_delete_own_post()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/posts/{$post->id}");
        $response->assertNoContent();
        $this->assertDatabaseMissing(Post::class, [
            'id' => $post->id,
        ]);
    }

    public function test_user_cannot_delete_other_post()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson("/posts/{$post->id}");
        $response->assertForbidden();
    }

    public function test_validate_post_creation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => '',
            'content' => '',
        ];

        $response = $this->postJson('/posts', $postData);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'content',
            ]);
    }

    public function test_validate_post_update()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => '',
            'content' => '',
        ];

        $response = $this->putJson("/posts/{$post->id}", $updateData);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'content',
            ]);
    }
}
