<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\StoreCommentRequest;
use Illuminate\Support\Facades\Validator;

class StoreCommentRequestTest extends TestCase
{
    public function test_authorize_returns_true()
    {
        $request = new StoreCommentRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_method_returns_correct_validation_rules()
    {
        $request = new StoreCommentRequest();
        $rules = $request->rules();

        $expectedRules = [
            'comment' => 'required|string|min:1|max:1000',
        ];

        $this->assertEquals($expectedRules, $rules);
    }

    public function test_validation_passes_with_valid_data()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => 'This is a valid comment.'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_without_comment()
    {
        $request = new StoreCommentRequest();
        $data = [];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_comment()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => ''
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_null_comment()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => null
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_non_string_comment()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => 123
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_array_comment()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => ['This', 'is', 'an', 'array']
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_boolean_comment()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => true
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_minimum_length_comment()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => 'A'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_maximum_length_comment()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => str_repeat('a', 1000)
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_comment_too_long()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => str_repeat('a', 1001)
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_special_characters()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => 'This comment has special characters: @#$%^&*()_+-=[]{}|;:,.<>?'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_unicode_characters()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => 'This comment has unicode: 🚀 ñáéíóú 中文 العربية'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_multiline_comment()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => "This is a multiline comment.\nIt spans multiple lines.\nAnd should be valid."
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_request_extends_form_request()
    {
        $request = new StoreCommentRequest();
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }

    public function test_validation_with_whitespace_only_comment()
    {
        $request = new StoreCommentRequest();
        $data = [
            'comment' => '   '
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
    }

    public function test_validation_edge_cases()
    {
        $request = new StoreCommentRequest();

        $data = [
            'comment' => "\t\n\r"
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $data = [
            'comment' => "  Valid comment with spaces  "
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }
}
