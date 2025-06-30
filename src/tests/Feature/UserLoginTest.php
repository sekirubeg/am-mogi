<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;
    protected function createUser($email = 'test@gmail.com', $password = 'password')
    {
        return User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    public function testUserLoginExcludingEmail()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $userData = [
            'password' => 'password',
        ];
        $this->createUser('test@gmail.com', 'password');
        $response = $this->post('/login', $userData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }
    public function testUserLoginExcludingPassword()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $userData = [
            'email' => 'test@gmail.com',
        ];
        $this->createUser('test@gmail.com', 'password');
        $response = $this->post('/login', $userData);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }
    public function testUserLoginFail()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $userData = [
            'email' => 'wrong@gmail.com',
            'password' => 'password',
        ];
        $this->createUser('test@gmail.com', 'password');
        $response = $this->post('/login', $userData);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }
}
