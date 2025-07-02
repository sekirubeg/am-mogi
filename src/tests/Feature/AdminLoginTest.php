<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Admin;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    protected function createAdmin($email = 'admin@gmail.com', $password = 'adminpassword')
    {
        return Admin::factory()->create([
            'name' => 'Admin User',
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    public function testAdminLoginExcludingEmail()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $adminData = [
            'password' => 'adminpassword',
        ];
        $this->createAdmin('admin@gmail.com', 'password');
        $response = $this->post('/admin/login', $adminData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }
    public function testAdminLoginExcludingPassword()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $adminData = [
            'email' => 'admin@gmail.com',
        ];
        $this->createAdmin('admin@gmail.com', 'password');
        $response = $this->post('/admin/login', $adminData);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);
        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }
    public function testAdminLoginFail()
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $adminData = [
            'email' => 'wrong@gmail.com',
            'password' => 'adminpassword',
        ];
        $this->createAdmin('admin@gmail.com', 'password');
        $response = $this->post('/admin/login', $adminData);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }

}
