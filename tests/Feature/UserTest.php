<?php

namespace Tests\Feature;

use App\Helpers\Strings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_HEADERS = ['Accept' => 'application/json'];

    private function createUser(String $password = null): User {
        $args = $password ? ['password' => Hash::make($password)] : null;

        return User::factory()->create($args);
    }

    /**
     * Test a successful login.
     */
    public function testSuccessfulLogin(): void
    {
        $password = 'testPassword'.uniqid();
        $user = $this->createUser($password);

        $response = $this->post('/api/users/login',[
            'email' => $user->email,
            'password' => $password,
        ], static::DEFAULT_HEADERS);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    /**
     * Test login with missing credentials.
     */
    public function testLoginWithMissingCredentials(): void
    {
        $user = $this->createUser();

        // Providing no credentials
        $response = $this->post('/api/users/login', [], static::DEFAULT_HEADERS);
        $response->assertStatus(400);
        $response->assertContent('The email field is required. The password field is required.');

        // Providing an email without a password
        $response = $this->post('/api/users/login', [
            'email' => $user->email
        ], static::DEFAULT_HEADERS);
        $response->assertStatus(400);
        $response->assertContent('The password field is required.');

        // Providing a password without an email
        $response = $this->post('/api/users/login', [
            'password' => 'hello world'
        ], static::DEFAULT_HEADERS);
        $response->assertStatus(400);
        $response->assertContent('The email field is required.');
    }

    /**
     * Test logins with bad credentials.
     */
    public function testLoginWithBadCredentials(): void
    {
        $password = 'testPassword'.uniqid();
        $user = $this->createUser($password);

        // Correct email, incorrect password
        $response = $this->post('/api/users/login', [
            'email' => $user->email,
            'password' => 'bad password'
        ], static::DEFAULT_HEADERS);

        $response->assertStatus(401);
        $response->assertContent(Strings::LOGIN_FAILURE);
    }

    /**
     * Test requesting user data without a valid access token
     */
    public function testGetUserNotLoggedIn(): void {
        $user = $this->createUser();

        // Test without a token
        $response = $this->get("/api/users/{$user->id}", static::DEFAULT_HEADERS);
        $response->assertStatus(401);

        // Test an invalid token
        $response = $this->get("/api/users/{$user->id}", array_merge(
            static::DEFAULT_HEADERS,
            [
                'Authorization' => "Bearer InvalidToken"
            ]
        ));
        $response->assertStatus(401);
        $response->assertContent('{"message":"Unauthenticated."}');
    }

    /**
     * Test requesting user data with a valid access token
     */
    public function testGetUserLoggedIn(): void {
        $user = $this->createUser();
        $token = $user->createToken('api')->plainTextToken;

        // Test with a valid token
        $response = $this->get("/api/users/{$user->id}", array_merge(
            static::DEFAULT_HEADERS,
            [
                'Authorization' => "Bearer {$token}"
            ]
        ));

        $response->assertStatus(200);
        $response->assertExactJson([
            'id' => $user->id,
            'name' => $user->name
        ]);
    }
}
