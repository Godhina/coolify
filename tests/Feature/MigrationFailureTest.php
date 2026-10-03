<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->failedMarker = storage_path('framework/testing/migration-failed');
    config(['constants.migration.failed_marker' => $this->failedMarker]);
    File::delete($this->failedMarker);
});

afterEach(function () {
    File::delete($this->failedMarker);
});

test('a failed migration fails the command and records the cause', function () {
    app('migrator')->path(base_path('tests/Fixtures/Migrations'));

    expect(fn () => Artisan::call('start:migration'))->toThrow(RuntimeException::class);

    expect(File::get($this->failedMarker))->toBe('Intentionally broken migration (test fixture).');
});

test('a successful migration clears the failure marker', function () {
    File::put($this->failedMarker, 'stale');

    $this->artisan('start:migration')->assertSuccessful();

    expect(File::exists($this->failedMarker))->toBeFalse();
});

test('requests are answered with the cause while the failure marker exists', function () {
    File::put($this->failedMarker, 'relation "teams" does not exist');

    $this->get('/login')
        ->assertStatus(503)
        ->assertSee('The database migration failed.')
        ->assertSee('relation "teams" does not exist');

    $this->getJson('/api/v1/servers')
        ->assertStatus(503)
        ->assertJson(['message' => 'Database migration failed: relation "teams" does not exist']);

    $this->get('/api/health')->assertOk();
});

test('the migration and seeder oneshots pass their exit code to s6', function (string $service) {
    $up = file_get_contents(base_path("docker/production/etc/s6-overlay/s6-rc.d/{$service}/up"));

    expect($up)->not->toContain('foreground');
})->with(['db-migration', 'init-seeder']);

test('a failed oneshot does not stop the container', function (string $dockerfile) {
    expect(file_get_contents(base_path($dockerfile)))->toContain('ENV S6_BEHAVIOUR_IF_STAGE2_FAILS=1');
})->with(['docker/production/Dockerfile', 'docker/development/Dockerfile']);
