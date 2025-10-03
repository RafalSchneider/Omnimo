<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'comments'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }

    public function store(StorePostRequest $request)
    {
        $post = Post::create(
            array_merge(
                $request->validated(),
                ['user_id' => Auth::id()]
            )
        );

        $post->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post,
        ], 201);
    }

    public function show(Post $post)
    {
        $post->load(['user', 'comments.user']);

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    public function update(StorePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        $post->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post,
        ]);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
