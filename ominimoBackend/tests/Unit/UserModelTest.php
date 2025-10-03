<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_fillable_attributes()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $user = User::create($userData);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_hidden_attributes()
    {
        $user = User::factory()->create();
        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    public function test_user_casts_attributes()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
        $this->assertTrue(is_string($user->password));
    }

    public function test_user_has_many_posts()
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->posts());
        $this->assertCount(3, $user->posts);
        $this->assertEquals($posts->pluck('id')->sort(), $user->posts->pluck('id')->sort());
    }

    public function test_user_has_many_comments()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comments = Comment::factory()->count(2)->create([
            'user_id' => $user->id,
            'post_id' => $post->id
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->comments());
        $this->assertCount(2, $user->comments);
        $this->assertEquals($comments->pluck('id')->sort(), $user->comments->pluck('id')->sort());
    }

    public function test_user_has_api_tokens_trait()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->assertInstanceOf(PersonalAccessToken::class, $token->accessToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test-token'
        ]);
    }

    public function test_user_factory_creates_valid_user()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertTrue(filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false);
    }
}
