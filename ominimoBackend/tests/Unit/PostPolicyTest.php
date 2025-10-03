<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Policies\PostPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected PostPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PostPolicy();
    }

    public function test_view_any_allows_everyone_including_guests()
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->viewAny($user));

        $this->assertTrue($this->policy->viewAny(null));
    }

    public function test_view_allows_everyone_including_guests()
    {
        $post = Post::factory()->create();

        $user = User::factory()->create();
        $this->assertTrue($this->policy->view($user, $post));

        $this->assertTrue($this->policy->view(null, $post));
    }

    public function test_create_allows_authenticated_users_only()
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->create($user));
    }

    public function test_update_allows_only_post_owner()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->update($owner, $post));

        $this->assertFalse($this->policy->update($otherUser, $post));
    }

    public function test_delete_allows_only_post_owner()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->delete($owner, $post));

        $this->assertFalse($this->policy->delete($otherUser, $post));
    }

    public function test_restore_allows_only_post_owner()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->restore($owner, $post));

        $this->assertFalse($this->policy->restore($otherUser, $post));
    }

    public function test_force_delete_allows_only_post_owner()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->forceDelete($owner, $post));

        $this->assertFalse($this->policy->forceDelete($otherUser, $post));
    }

    public function test_multiple_posts_same_owner()
    {
        $owner = User::factory()->create();
        $post1 = Post::factory()->create(['user_id' => $owner->id]);
        $post2 = Post::factory()->create(['user_id' => $owner->id]);


        $this->assertTrue($this->policy->update($owner, $post1));
        $this->assertTrue($this->policy->update($owner, $post2));
        $this->assertTrue($this->policy->delete($owner, $post1));
        $this->assertTrue($this->policy->delete($owner, $post2));
    }

    public function test_policy_consistency_across_all_ownership_methods()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $owner->id]);


        $ownerResults = [
            $this->policy->update($owner, $post),
            $this->policy->delete($owner, $post),
            $this->policy->restore($owner, $post),
            $this->policy->forceDelete($owner, $post)
        ];

        $otherUserResults = [
            $this->policy->update($otherUser, $post),
            $this->policy->delete($otherUser, $post),
            $this->policy->restore($otherUser, $post),
            $this->policy->forceDelete($otherUser, $post)
        ];

        foreach ($ownerResults as $result) {
            $this->assertTrue($result);
        }
        foreach ($otherUserResults as $result) {
            $this->assertFalse($result);
        }
    }
}
