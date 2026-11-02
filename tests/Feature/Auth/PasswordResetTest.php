<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'it@artabumisentraindustri.com']);

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'it@artabumisentraindustri.com']);

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get(route('password.reset', $notification->token));

        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'it@artabumisentraindustri.com']);

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        return true;
    });
});

test('company email is normalized and other domains are rejected', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'it@artabumisentraindustri.com']);

    $this->post(route('password.email'), ['email' => ' IT@ARTABUMISENTRAINDUSTRI.COM '])
        ->assertSessionHasNoErrors();
    Notification::assertSentTo($user, ResetPassword::class);

    foreach (['it@example.com', 'it@artabumisentraindustri.com.evil.test', 'invalid', ['invalid']] as $email) {
        $this->post(route('password.email'), ['email' => $email])->assertSessionHasErrors('email');
    }
    Notification::assertCount(1);
});

test('unknown company account does not receive a reset email', function () {
    Notification::fake();
    $this->post(route('password.email'), ['email' => 'missing@artabumisentraindustri.com'])
        ->assertSessionHasErrors('email');
    Notification::assertNothingSent();
});

test('reset token changes password once and cannot be reused', function () {
    $user = User::factory()->create(['email' => 'it@artabumisentraindustri.com']);
    $token = Password::broker('users')->createToken($user);
    $payload = ['email' => $user->email, 'token' => $token, 'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!'];

    $this->post(route('password.update'), $payload)->assertSessionHasNoErrors();
    expect(Hash::check('NewPassword123!', $user->fresh()->password))->toBeTrue();
    expect(Hash::check('password', $user->fresh()->password))->toBeFalse();
    $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
});

test('expired reset token does not change password', function () {
    $user = User::factory()->create(['email' => 'it@artabumisentraindustri.com']);
    $token = Password::broker('users')->createToken($user);
    $original = $user->password;
    $this->travel(config('auth.passwords.users.expire') + 1)->minutes();

    $this->post(route('password.update'), [
        'email' => $user->email, 'token' => $token,
        'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!',
    ])->assertSessionHasErrors('email');
    expect($user->fresh()->password)->toBe($original);
});

test('reset email uses configured website and includes token and recipient', function () {
    config(['app.url' => 'https://artabumisentraindustri.com']);
    $user = User::factory()->create(['email' => 'it@artabumisentraindustri.com']);
    $mail = (new ResetPassword('sample-token'))->toMail($user);

    expect($mail->subject)->toBe('Reset Password Website Artabumi');
    expect($mail->actionUrl)->toStartWith('https://artabumisentraindustri.com/reset-password/sample-token');
    expect($mail->actionUrl)->toContain('email=it%40artabumisentraindustri.com');
});
