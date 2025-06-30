<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{

    use RefreshDatabase;
    public function testUserRegistrationExcludingName(){
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $userData = [
            'email' => 'test@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $response = $this->post('/register', $userData);

        $response->assertStatus(302);

        $response->assertSessionHasErrors(['name']);
        $errors = session('errors');
        $this->assertEquals('お名前を入力してください', $errors->first('name'));
    }

    public function testUserRegistrationExcludingEmail(){
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $userData = [
            'name' => 'Test User',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $response = $this->post('/register', $userData);

        $response->assertStatus(302);

        $response->assertSessionHasErrors(['email']);
        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));

    }
    public function testUserRegistrationLessThan8Password(){
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $userData = [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ];
        $response = $this->post('/register', $userData);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $errors = session('errors');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }
    public function testUserRegistrationPasswordConfirmationNotMatch(){
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $userData = [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'differentpassword',
        ];
        $response = $this->post('/register', $userData);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password_confirmation']);
        $errors = session('errors');
        $this->assertEquals('パスワードと一致しません', $errors->first('password_confirmation'));
    }
    public function testUserRegistrationExcludingPassword(){
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $userData = [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password_confirmation' => 'password',
        ];
        $response = $this->post('/register', $userData);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));

    }
    public function testUserRegistrationSuccess(){
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $userData = [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $response = $this->post('/register', $userData);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'email' => 'test@gmail.com',
            'name' => 'Test User',
        ]);
        $this->assertNotNull(User::where('email', 'test@gmail.com')->first());
    }

}
