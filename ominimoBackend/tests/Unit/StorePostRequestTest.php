<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\StorePostRequest;
use Illuminate\Support\Facades\Validator;

class StorePostRequestTest extends TestCase
{
    public function test_authorize_returns_true()
    {
        $request = new StorePostRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_method_returns_correct_validation_rules()
    {
        $request = new StorePostRequest();
        $rules = $request->rules();

        $expectedRules = [
            'title' => 'required|max:255',
            'content' => 'required',
        ];

        $this->assertEquals($expectedRules, $rules);
    }

    public function test_validation_passes_with_valid_data()
    {
        $request = new StorePostRequest();
        $data = [
            'title' => 'Valid Post Title',
            'content' => 'This is valid content for the post.'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_without_title()
    {
        $request = new StorePostRequest();
        $data = [
            'content' => 'This is valid content for the post.'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_validation_fails_without_content()
    {
        $request = new StorePostRequest();
        $data = [
            'title' => 'Valid Post Title'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_title()
    {
        $request = new StorePostRequest();
        $data = [
            'title' => '',
            'content' => 'This is valid content for the post.'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_content()
    {
        $request = new StorePostRequest();
        $data = [
            'title' => 'Valid Post Title',
            'content' => ''
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_title_too_long()
    {
        $request = new StorePostRequest();
        $data = [
            'title' => str_repeat('a', 256),
            'content' => 'This is valid content for the post.'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_title_at_max_length()
    {
        $request = new StorePostRequest();
        $data = [
            'title' => str_repeat('a', 255),
            'content' => 'This is valid content for the post.'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_long_content()
    {
        $request = new StorePostRequest();
        $data = [
            'title' => 'Valid Post Title',
            'content' => str_repeat('This is a very long content. ', 100)
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_minimal_valid_data()
    {
        $request = new StorePostRequest();
        $data = [
            'title' => 'A',
            'content' => 'A'
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_request_extends_form_request()
    {
        $request = new StorePostRequest();
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }

    public function test_validation_with_null_values()
    {
        $request = new StorePostRequest();
        $data = [
            'title' => null,
            'content' => null
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
    }
}
