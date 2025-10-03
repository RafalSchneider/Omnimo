<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $users = \App\Models\User::factory(3)->create([
            'password' => \Illuminate\Support\Facades\Hash::make('password')
        ]);

        foreach ($users as $user) {
            $posts = \App\Models\Post::factory(2)->create([
                'user_id' => $user->id
            ]);

            foreach ($posts as $post) {
                \App\Models\Comment::factory(3)->create([
                    'post_id' => $post->id,
                    'user_id' => $users->random()->id
                ]);
            }
        }
    }
}
