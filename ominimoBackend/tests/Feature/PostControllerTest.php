<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_paginated_posts_list()
    {
        $posts = Post::factory()->count(15)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'title', 'content', 'user_id', 'created_at', 'updated_at']
                    ],
                    'current_page',
                    'per_page',
                    'total'
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(10, count($response->json('data.data')));
    }

    public function test_can_get_single_post_with_relationships()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $comments = Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'content',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'user' => ['id', 'name', 'email'],
                    'comments' => [
                        '*' => [
                            'id',
                            'comment',
                            'user_id',
                            'post_id',
                            'user' => ['id', 'name', 'email']
                        ]
                    ]
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals($post->id, $response->json('data.id'));
    }

    public function test_authenticated_user_can_create_post()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $postData = [
            'title' => 'Test Post Title',
            'content' => 'This is test content for the post.'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/posts', $postData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'content',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'user' => ['id', 'name', 'email']
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post Title',
            'content' => 'This is test content for the post.',
            'user_id' => $user->id
        ]);
    }

    public function test_unauthenticated_user_cannot_create_post()
    {
        $postData = [
            'title' => 'Test Post Title',
            'content' => 'This is test content for the post.'
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertStatus(401);
    }

    public function test_create_post_requires_title_and_content()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/posts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }

    public function test_post_owner_can_update_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test-token')->plainTextToken;

        $updateData = [
            'title' => 'Updated Post Title',
            'content' => 'Updated post content.'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'title', 'content', 'user_id']
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Post Title',
            'content' => 'Updated post content.'
        ]);
    }

    public function test_non_owner_cannot_update_post()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);
        $token = $otherUser->createToken('test-token')->plainTextToken;

        $updateData = [
            'title' => 'Updated Post Title',
            'content' => 'Updated post content.'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_post_owner_can_delete_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post deleted successfully'
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_non_owner_cannot_delete_post()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);
        $token = $otherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_update_post()
    {
        $post = Post::factory()->create();

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ];

        $response = $this->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_post()
    {
        $post = Post::factory()->create();

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(401);
    }

    public function test_posts_index_includes_user_and_comments_relationships()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        Comment::factory()->count(2)->create(['post_id' => $post->id]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);

        $posts = $response->json('data.data');
        $firstPost = $posts[0];

        $this->assertArrayHasKey('user', $firstPost);
        $this->assertArrayHasKey('comments', $firstPost);
    }

    public function test_can_get_nonexistent_post_returns_404()
    {
        $response = $this->getJson('/api/posts/999999');

        $response->assertStatus(404);
    }
}
