<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\CacheOperationLog;
use App\Models\User;
use App\Services\System\CacheManagementService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class CacheManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Artisan::clearResolvedInstances();
        parent::tearDown();
    }

    public function test_guest_and_viewer_cannot_open_cache_management(): void
    {
        $this->get(route('admin.cache.index'))->assertRedirect(route('admin.login'));

        $this->actingAs($this->user(UserRole::Viewer))
            ->get(route('admin.cache.index'))
            ->assertForbidden();

        $this->actingAs($this->user(UserRole::Viewer))
            ->post(route('admin.cache.application.clear'))
            ->assertForbidden();
    }

    public function test_page_shows_real_cache_states_and_hides_cleanup_rows(): void
    {
        $this->actingAs($this->user(UserRole::Editor))
            ->get(route('admin.cache.index'))
            ->assertOk()
            ->assertSee('Cache Management')
            ->assertSee('Manage Laravel application caches and optimization files.')
            ->assertSee('Application Cache')
            ->assertSee('Configuration')
            ->assertSee('Compiled Files')
            ->assertSee('Available')
            ->assertSee('Never')
            ->assertSee(config('cache.default'))
            ->assertSee('Recent cache activity')
            ->assertDontSee('Error log')
            ->assertDontSee('Expired sessions')
            ->assertDontSee('Temporary uploads')
            ->assertDontSee('In use')
            ->assertDontSee('Already clear');
    }

    public function test_cache_action_updates_without_redirecting(): void
    {
        $this->actingAs($this->user(UserRole::Editor))
            ->postJson(route('admin.cache.application.clear'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('message', 'Application cache cleared successfully.')
            ->assertJsonStructure(['summary', 'rows', 'history']);
    }

    public function test_application_clear_is_allowlisted_and_recorded(): void
    {
        $admin = $this->user(UserRole::Editor);

        $this->actingAs($admin)
            ->from(route('admin.cache.index'))
            ->post(route('admin.cache.application.clear'), ['command' => 'route:cache'])
            ->assertRedirect(route('admin.cache.index'))
            ->assertSessionHas('success', 'Application cache cleared successfully.');

        $log = CacheOperationLog::query()->sole();
        $this->assertSame('application', $log->cache_type);
        $this->assertSame('clear', $log->action);
        $this->assertTrue($log->success);
        $this->assertSame($admin->id, $log->cleared_by);
        $this->assertNull($log->error_message);
    }

    public static function cacheOperations(): array
    {
        return [
            'config clear' => ['admin.cache.config.clear', 'config:clear', 'config.clear'],
            'config rebuild' => ['admin.cache.config.rebuild', 'config:cache', 'config.rebuild'],
            'routes clear' => ['admin.cache.routes.clear', 'route:clear', 'routes.clear'],
            'routes rebuild' => ['admin.cache.routes.rebuild', 'route:cache', 'routes.rebuild'],
            'views clear' => ['admin.cache.views.clear', 'view:clear', 'views.clear'],
            'views rebuild' => ['admin.cache.views.rebuild', 'view:cache', 'views.rebuild'],
            'events clear' => ['admin.cache.events.clear', 'event:clear', 'events.clear'],
            'events rebuild' => ['admin.cache.events.rebuild', 'event:cache', 'events.rebuild'],
            'compiled clear' => ['admin.cache.compiled.clear', 'clear-compiled', 'compiled.clear'],
            'all clear' => ['admin.cache.all.clear', 'optimize:clear', 'all.clear'],
        ];
    }

    #[DataProvider('cacheOperations')]
    public function test_each_route_calls_its_allowlisted_command(string $route, string $command, string $operation): void
    {
        $service = app(CacheManagementService::class);
        $this->assertSame($command, $service->operation($operation)['command']);
        $this->assertNull($service->operation('logs.clear'));

        Artisan::shouldReceive('all')->andReturn([$command => $command]);
        Artisan::shouldReceive('call')->once()->with($command)->andReturn(0);

        $this->actingAs($this->user(UserRole::Editor))
            ->from(route('admin.cache.index'))
            ->post(route($route), ['command' => 'migrate:fresh'])
            ->assertRedirect(route('admin.cache.index'))
            ->assertSessionHas('success');
    }

    public function test_failed_operation_is_recorded_without_secrets(): void
    {
        Artisan::shouldReceive('all')->andReturn(['cache:clear' => 'cache:clear']);
        Artisan::shouldReceive('call')
            ->once()
            ->with('cache:clear')
            ->andThrow(new RuntimeException('APP_KEY=secret at C:\\xampp\\secret\\.env'));

        $this->actingAs($this->user(UserRole::Editor))
            ->from(route('admin.cache.index'))
            ->post(route('admin.cache.application.clear'))
            ->assertRedirect(route('admin.cache.index'))
            ->assertSessionHasErrors('cache');

        $log = CacheOperationLog::query()->sole();
        $this->assertFalse($log->success);
        $this->assertSame('The command did not finish.', $log->error_message);
        $this->assertStringNotContainsString('APP_KEY', session('errors')->first('cache'));
        $this->assertStringNotContainsString('xampp', session('errors')->first('cache'));
    }

    public function test_cache_routes_use_auth_permission_and_csrf_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('admin.cache.application.clear');
        $middleware = $route->gatherMiddleware();

        $this->assertContains('web', $middleware);
        $this->assertContains('auth', $middleware);
        $this->assertContains('can:admin.cache.manage', $middleware);

        $csrf = new class(app(), app('encrypter')) extends ValidateCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        };
        $request = Request::create('/admin/cache/application/clear', 'POST');
        $request->setLaravelSession($this->app['session']->driver());

        $this->expectException(TokenMismatchException::class);
        $csrf->handle($request, fn () => response('ok'));
    }

    private function user(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
    }
}
