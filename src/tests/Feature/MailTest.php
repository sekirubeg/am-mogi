<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class MailTest extends TestCase
{
    use RefreshDatabase;

    //会員登録後、認証メールが送信される
    public function testRegistrationSendsVerificationEmail()
    {
        Notification::fake(); // 通知（メール送信）をモック
        // テスト用のユーザーデータを作成
        $userData = [
            'name' => 'Test User',
            'email' => 'user@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        // ユーザー登録を実行
        $response = $this->post(route('register'), $userData);
        // レスポンスのステータスコードを確認
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'email' => 'user@gmail.com',
            'name' => 'Test User',
        ]);
        $user = User::where('email', $userData['email'])->first();
        // 認証メールが送信されたことを検証
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    //メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
    public function testEmailVerificationRedirectsToVerificationPage()
    {
        // テスト用のユーザーデータを作成
        $user = User::factory()->create([
            'email_verified_at' => null, // 未認証状態
        ]);
        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);

        //このリンクがあれば遷移できることが確定する
        $response->assertSee('<a href="https://mailtrap.io/" class="button">認証はこちらから</a>', false);
    }

    //メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
    public function testEmailVerificationCompletesAndRedirectsToAttendancePage()
    {
        // テスト用のユーザーデータを作成
        $user = User::factory()->create([
            'email_verified_at' => null, // 未認証状態
        ]);

        // 署名付きURLを生成（これがないと認証されない）
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        $response->assertRedirect(route('attendance'));
    }
}
