<?php

use App\Models\User;
use App\Models\Vacancy;
use Carbon\CarbonImmutable;

function vacancyPayload(array $overrides = []): array
{
    return array_replace([
        'translations' => ['id' => ['title' => 'Teknisi Mekanik', 'description' => 'Memelihara peralatan industri.', 'requirements' => 'Pengalaman perawatan mesin.']],
        'location' => 'Morowali', 'employment_type' => 'full_time',
        'application_email' => 'recruitment@artabumisentraindustri.com',
        'status' => 'draft', 'closing_date' => now('Asia/Jakarta')->addWeek()->toDateString(),
    ], $overrides);
}

test('guests and ordinary users cannot manage vacancies', function () {
    $this->get('/hr/vacancies')->assertRedirect('/login');
    $this->post('/hr/vacancies', vacancyPayload())->assertRedirect('/login');
    $user = User::factory()->create();
    $vacancy = Vacancy::create(vacancyPayload());
    $this->actingAs($user)->get('/hr/vacancies')->assertForbidden();
    $this->post('/hr/vacancies', vacancyPayload())->assertForbidden();
    $this->put('/hr/vacancies/'.$vacancy->id, vacancyPayload(['status' => 'published']))->assertForbidden();
    expect($vacancy->fresh()->status)->toBe('draft');
});

test('HR must verify their email before accessing management', function () {
    $user = User::factory()->unverified()->create(['email' => 'recruitment@artabumisentraindustri.com']);
    $this->actingAs($user)->get('/hr/vacancies')->assertRedirect(route('verification.notice'));
    $this->post('/hr/vacancies', vacancyPayload())->assertRedirect(route('verification.notice'));
});

test('HR can draft publish edit and close a persistent vacancy', function () {
    $hr = User::factory()->create(['email' => 'recruitment@artabumisentraindustri.com']);
    $this->actingAs($hr)->get('/hr/vacancies/create')->assertOk();
    $this->post('/hr/vacancies', vacancyPayload())->assertSessionHasNoErrors()->assertRedirect();
    $vacancy = Vacancy::firstOrFail();
    $this->get('/hr/vacancies')->assertOk()->assertSee('Teknisi Mekanik');
    $this->get('/hr/vacancies/'.$vacancy->id.'/edit')->assertOk();
    $this->get('/id/careers')->assertDontSee('Teknisi Mekanik');
    $this->get('/id/careers/'.$vacancy->id)->assertNotFound();
    $this->put('/hr/vacancies/'.$vacancy->id, vacancyPayload(['status' => 'published']))->assertSessionHasNoErrors();
    $this->get('/id/careers')->assertSee('Teknisi Mekanik');
    $this->get('/id/careers/'.$vacancy->id)->assertOk()->assertSee('mailto:recruitment@artabumisentraindustri.com', false);
    $this->put('/hr/vacancies/'.$vacancy->id, vacancyPayload(['status' => 'closed']))->assertSessionHasNoErrors();
    $this->get('/id/careers')->assertDontSee('Teknisi Mekanik');
    $this->get('/id/careers/'.$vacancy->id)->assertNotFound();
});

test('expired published vacancies are hidden and deadline day is inclusive in Jakarta', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-01 18:00:00', 'UTC'));
    $expired = Vacancy::create(vacancyPayload(['status' => 'published', 'closing_date' => '2026-10-01']));
    $today = Vacancy::create(vacancyPayload(['status' => 'published', 'closing_date' => '2026-10-02']));
    $this->get('/id/careers/'.$expired->id)->assertNotFound();
    $this->get('/id/careers/'.$today->id)->assertOk();
});

test('career pages support translations fallback and escaped content', function () {
    $vacancy = Vacancy::create(vacancyPayload(['status' => 'published', 'translations' => [
        'id' => ['title' => 'Teknisi Mekanik', 'description' => '<script>alert(1)</script>', 'requirements' => 'Pengalaman'],
        'en' => ['title' => 'Mechanical Technician', 'description' => 'Maintain equipment.', 'requirements' => 'Experience'],
    ]]));
    $this->get('/en/careers/'.$vacancy->id)->assertOk()->assertSee('Mechanical Technician')->assertSee('/zh/careers/'.$vacancy->id, false);
    $this->get('/zh/careers/'.$vacancy->id)->assertOk()->assertSee('Teknisi Mekanik')->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
    $this->get('/zh/careers')->assertOk()->assertSee('招聘');
    $this->get('/id')->assertSee('/id/careers', false);
});

test('HR input validation rejects unsafe email status and partial translations', function () {
    $this->actingAs(User::factory()->create(['email' => 'recruitment@artabumisentraindustri.com']));
    $payload = vacancyPayload(['status' => 'invalid', 'application_email' => "hr@example.com\r\nBcc:someone@example.com"]);
    $payload['translations']['en'] = ['title' => 'Partial translation'];
    $this->post('/hr/vacancies', $payload)->assertSessionHasErrors(['status', 'application_email', 'translations.en.description', 'translations.en.requirements']);
    expect(Vacancy::count())->toBe(0);
});

test('registration cannot grant HR privileges through submitted fields', function () {
    $this->post('/register', ['name' => 'Visitor', 'email' => 'visitor@example.com', 'password' => 'Valid-password-123', 'password_confirmation' => 'Valid-password-123', 'is_hr' => true]);
    expect(User::where('email', 'visitor@example.com')->firstOrFail()->is_hr)->toBeFalse();
});
