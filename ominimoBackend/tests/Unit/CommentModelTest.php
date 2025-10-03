<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_fillable_attributes()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $commentData = [
            'comment' => 'This is a test comment.',
            'post_id' => $post->id,
            'user_id' => $user->id
        ];

        $comment = Comment::create($commentData);

        $this->assertEquals('This is a test comment.', $comment->comment);
        $this->assertEquals($post->id, $comment->post_id);
        $this->assertEquals($user->id, $comment->user_id);
    }

    public function test_comment_belongs_to_post()
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $comment->post());
        $this->assertEquals($post->id, $comment->post->id);
        $this->assertEquals($post->title, $comment->post->title);
    }

    public function test_comment_belongs_to_user()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $comment->user());
        $this->assertEquals($user->id, $comment->user->id);
        $this->assertEquals($user->name, $comment->user->name);
    }

    public function test_comment_factory_creates_valid_comment()
    {
        $comment = Comment::factory()->create();

        $this->assertNotNull($comment->comment);
        $this->assertNotNull($comment->post_id);
        $this->assertNotNull($comment->user_id);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'comment' => $comment->comment,
            'post_id' => $comment->post_id,
            'user_id' => $comment->user_id
        ]);
    }

    public function test_comment_timestamps_are_set()
    {
        $comment = Comment::factory()->create();

        $this->assertNotNull($comment->created_at);
        $this->assertNotNull($comment->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $comment->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $comment->updated_at);
    }

    public function test_comment_can_be_updated()
    {
        $comment = Comment::factory()->create();
        $originalUpdatedAt = $comment->updated_at;

        sleep(1);
        $comment->update([
            'comment' => 'Updated comment text'
        ]);

        $this->assertEquals('Updated comment text', $comment->comment);
        $this->assertTrue($comment->updated_at->gt($originalUpdatedAt));
    }

    public function test_comment_can_be_deleted()
    {
        $comment = Comment::factory()->create();
        $commentId = $comment->id;

        $comment->delete();

        $this->assertDatabaseMissing('comments', ['id' => $commentId]);
    }

    public function test_comment_with_post_and_user_relationships()
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $post = Post::factory()->create(['title' => 'Test Post']);
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'comment' => 'Test comment content'
        ]);

        $comment->load(['user', 'post']);

        $this->assertEquals('Test User', $comment->user->name);
        $this->assertEquals('Test Post', $comment->post->title);
        $this->assertEquals('Test comment content', $comment->comment);
    }
}
