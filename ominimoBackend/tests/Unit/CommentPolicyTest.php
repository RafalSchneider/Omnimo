<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected CommentPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CommentPolicy();
    }

    public function test_view_any_denies_all_users()
    {
        $user = User::factory()->create();
        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_view_denies_all_users()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $this->assertFalse($this->policy->view($user, $comment));
    }

    public function test_create_denies_all_users()
    {
        $user = User::factory()->create();
        $this->assertFalse($this->policy->create($user));
    }

    public function test_update_denies_all_users()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($this->policy->update($user, $comment));
    }

    public function test_delete_allows_comment_owner()
    {
        $commentOwner = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $commentOwner->id]);

        $this->assertTrue($this->policy->delete($commentOwner, $comment));
    }

    public function test_delete_allows_post_owner()
    {
        $postOwner = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $postOwner->id]);
        $comment = Comment::factory()->create([
            'user_id' => $commentAuthor->id,
            'post_id' => $post->id
        ]);

        $comment->load('post');

        $this->assertTrue($this->policy->delete($postOwner, $comment));
    }

    public function test_delete_denies_non_owners()
    {
        $postOwner = User::factory()->create();
        $commentOwner = User::factory()->create();
        $randomUser = User::factory()->create();

        $post = Post::factory()->create(['user_id' => $postOwner->id]);
        $comment = Comment::factory()->create([
            'user_id' => $commentOwner->id,
            'post_id' => $post->id
        ]);

        $comment->load('post');

        $this->assertFalse($this->policy->delete($randomUser, $comment));
    }

    public function test_delete_when_user_owns_both_post_and_comment()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id
        ]);

        $comment->load('post');

        $this->assertTrue($this->policy->delete($user, $comment));
    }

    public function test_restore_denies_all_users()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($this->policy->restore($user, $comment));
    }

    public function test_force_delete_denies_all_users()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($this->policy->forceDelete($user, $comment));
    }

    public function test_delete_policy_with_multiple_scenarios()
    {

        $postOwner = User::factory()->create();
        $commentOwner = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $postOwner->id]);
        $comment1 = Comment::factory()->create([
            'user_id' => $commentOwner->id,
            'post_id' => $post->id
        ]);
        $comment1->load('post');

        $this->assertTrue($this->policy->delete($commentOwner, $comment1));
        $this->assertTrue($this->policy->delete($postOwner, $comment1));

        $randomUser = User::factory()->create();
        $this->assertFalse($this->policy->delete($randomUser, $comment1));
    }

    public function test_policy_consistency_for_restrictive_methods()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);


        $restrictiveMethods = [
            $this->policy->viewAny($user),
            $this->policy->view($user, $comment),
            $this->policy->create($user),
            $this->policy->update($user, $comment),
            $this->policy->restore($user, $comment),
            $this->policy->forceDelete($user, $comment)
        ];

        foreach ($restrictiveMethods as $result) {
            $this->assertFalse($result);
        }
    }
}
