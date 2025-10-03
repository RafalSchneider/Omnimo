<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_comments_for_post()
    {
        $post = Post::factory()->create();
        $comments = Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'comment',
                        'user_id',
                        'post_id',
                        'created_at',
                        'updated_at',
                        'user' => ['id', 'name', 'email']
                    ]
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertCount(3, $response->json('data'));
    }

    public function test_comments_are_ordered_by_latest_first()
    {
        $post = Post::factory()->create();


        $firstComment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_at' => now()->subHours(2)
        ]);
        $secondComment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_at' => now()->subHour()
        ]);
        $thirdComment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_at' => now()
        ]);

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertStatus(200);

        $comments = $response->json('data');


        $this->assertEquals($thirdComment->id, $comments[0]['id']);
        $this->assertEquals($secondComment->id, $comments[1]['id']);
        $this->assertEquals($firstComment->id, $comments[2]['id']);
    }

    public function test_authenticated_user_can_create_comment()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $commentData = [
            'comment' => 'This is a test comment.'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/posts/{$post->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'comment',
                    'user_id',
                    'post_id',
                    'created_at',
                    'updated_at',
                    'user' => ['id', 'name', 'email']
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'comment' => 'This is a test comment.',
            'user_id' => $user->id,
            'post_id' => $post->id
        ]);
    }

    public function test_unauthenticated_user_cannot_create_comment()
    {
        $post = Post::factory()->create();
        $commentData = [
            'comment' => 'This is a test comment.'
        ];

        $response = $this->postJson("/api/posts/{$post->id}/comments", $commentData);

        $response->assertStatus(401);
    }

    public function test_create_comment_requires_comment_field()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/posts/{$post->id}/comments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_comment_owner_can_delete_comment()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_post_owner_can_delete_comment_on_their_post()
    {
        $postOwner = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $postOwner->id]);
        $comment = Comment::factory()->create([
            'user_id' => $commentAuthor->id,
            'post_id' => $post->id
        ]);
        $token = $postOwner->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_random_user_cannot_delete_comment()
    {
        $commentOwner = User::factory()->create();
        $randomUser = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $commentOwner->id]);
        $token = $randomUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_delete_comment()
    {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(401);
    }

    public function test_can_get_comments_for_nonexistent_post_returns_404()
    {
        $response = $this->getJson('/api/posts/999999/comments');

        $response->assertStatus(404);
    }

    public function test_cannot_create_comment_for_nonexistent_post()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $commentData = [
            'comment' => 'This is a test comment.'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/posts/999999/comments', $commentData);

        $response->assertStatus(404);
    }

    public function test_comment_validation_enforces_string_type()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $commentData = [
            'comment' => 123
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/posts/{$post->id}/comments", $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_comment_validation_enforces_minimum_length()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $commentData = [
            'comment' => ''
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/posts/{$post->id}/comments", $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_comment_validation_enforces_maximum_length()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $commentData = [
            'comment' => str_repeat('a', 1001)
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/posts/{$post->id}/comments", $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }
}
