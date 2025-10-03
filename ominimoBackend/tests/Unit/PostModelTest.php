<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_fillable_attributes()
    {
        $user = User::factory()->create();
        $postData = [
            'title' => 'Test Post Title',
            'content' => 'This is test content for the post.',
            'user_id' => $user->id
        ];

        $post = Post::create($postData);

        $this->assertEquals('Test Post Title', $post->title);
        $this->assertEquals('This is test content for the post.', $post->content);
        $this->assertEquals($user->id, $post->user_id);
    }

    public function test_post_belongs_to_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $post->user());
        $this->assertEquals($user->id, $post->user->id);
        $this->assertEquals($user->name, $post->user->name);
    }

    public function test_post_has_many_comments()
    {
        $post = Post::factory()->create();
        $comments = Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $post->comments());
        $this->assertCount(3, $post->comments);
        $this->assertEquals($comments->pluck('id')->sort(), $post->comments->pluck('id')->sort());
    }

    public function test_post_factory_creates_valid_post()
    {
        $post = Post::factory()->create();

        $this->assertNotNull($post->title);
        $this->assertNotNull($post->content);
        $this->assertNotNull($post->user_id);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content
        ]);
    }

    public function test_post_timestamps_are_set()
    {
        $post = Post::factory()->create();

        $this->assertNotNull($post->created_at);
        $this->assertNotNull($post->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $post->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $post->updated_at);
    }

    public function test_post_can_be_updated()
    {
        $post = Post::factory()->create();
        $originalUpdatedAt = $post->updated_at;

        sleep(1);
        $post->update([
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ]);

        $this->assertEquals('Updated Title', $post->title);
        $this->assertEquals('Updated content', $post->content);
        $this->assertTrue($post->updated_at->gt($originalUpdatedAt));
    }

    public function test_post_can_be_deleted()
    {
        $post = Post::factory()->create();
        $postId = $post->id;

        $post->delete();

        $this->assertDatabaseMissing('posts', ['id' => $postId]);
    }

    public function test_deleting_post_deletes_associated_comments()
    {
        $post = Post::factory()->create();
        $comments = Comment::factory()->count(2)->create(['post_id' => $post->id]);

        $post->delete();

        foreach ($comments as $comment) {
            $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        }
    }
}
