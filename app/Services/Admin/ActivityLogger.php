<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /** @var array<int, array<string, mixed>> */
    private static array $before = [];

    public static function remember(Model $model): void
    {
        if (! $model->exists || $model->getKey() === null) {
            return;
        }

        self::$before[spl_object_id($model)] = $model->getOriginal();
    }

    public static function log(
        string $action,
        ?string $module = null,
        ?Model $subject = null,
        ?string $description = null,
        array $properties = []
    ): void {
        $properties = self::withChanges($action, $subject, $properties);
        $page = self::adminPage();
        if ($page !== null && ! array_key_exists('page', $properties)) {
            $properties['page'] = $page;
        }

        ActivityLog::query()->create([
            'user_id' => Auth::guard('web')->id(),
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => self::agent(),
            'location' => self::place(Request::ip()),
        ]);
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    private static function withChanges(string $action, ?Model $subject, array $properties): array
    {
        if (! $subject instanceof Model) {
            return $properties;
        }

        if ($action === 'delete') {
            $source = self::$before[spl_object_id($subject)] ?? $subject->getAttributes();
            $removed = self::identify(self::snapshot($subject, is_array($source) ? $source : []));
            if ($removed !== [] && ! array_key_exists('removed', $properties)) {
                $properties['removed'] = $removed;
            }

            return $properties;
        }

        if ($action === 'create' || $subject->wasRecentlyCreated) {
            if (! array_key_exists('new', $properties) && ! array_key_exists('after', $properties)) {
                $added = self::identify(self::snapshot($subject, $subject->getAttributes()));
                if ($added !== []) {
                    $properties['new'] = $added;
                }
            }

            return $properties;
        }

        if (array_key_exists('old', $properties) || array_key_exists('before', $properties)) {
            return $properties;
        }

        $changes = $subject->getChanges();
        $original = self::$before[spl_object_id($subject)] ?? [];
        $old = [];
        $new = [];
        foreach ($changes as $key => $value) {
            if (self::hidden($subject, (string) $key)) {
                continue;
            }
            $old[$key] = $original[$key] ?? null;
            $new[$key] = $value;
        }
        $old = self::snapshot($subject, $old);
        $new = self::snapshot($subject, $new);
        if ($new !== []) {
            $properties['old'] = $old;
            $properties['new'] = $new;
        }

        return $properties;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function snapshot(Model $subject, array $values): array
    {
        $out = [];
        foreach ($values as $key => $value) {
            if (! is_string($key) || self::hidden($subject, $key)) {
                continue;
            }
            $out[$key] = self::plain($key, $value);
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function identify(array $values): array
    {
        $preferred = ['name', 'title', 'heading', 'slug', 'email', 'key', 'page', 'status', 'company_name', 'category'];
        $picked = array_intersect_key($values, array_flip($preferred));

        return $picked !== [] ? $picked : array_slice($values, 0, 6, true);
    }

    private static function hidden(Model $subject, string $key): bool
    {
        if (in_array($key, ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'created_at', 'updated_at', 'deleted_at'], true)) {
            return true;
        }

        return str_contains($key, 'password') || in_array($key, $subject->getHidden(), true);
    }

    private static function plain(string $key, mixed $value): mixed
    {
        if (in_array($key, ['is_visible', 'is_active', 'is_featured'], true)) {
            return (int) $value === 1 || $value === true ? 'Yes' : 'No';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d M Y, h:i A');
        }
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (is_array($value)) {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return strlen((string) $json) > 400 ? substr((string) $json, 0, 400).'…' : $json;
        }
        if (is_string($value) && strlen($value) > 400) {
            return substr($value, 0, 400).'…';
        }

        return $value;
    }

    private static function adminPage(): ?string
    {
        $referer = Request::header('referer');
        if (! is_string($referer) || $referer === '') {
            return null;
        }
        $path = parse_url($referer, PHP_URL_PATH);

        return is_string($path) && str_starts_with($path, '/admin') ? $path : null;
    }

    private static function agent(): ?string
    {
        $agent = Request::userAgent();

        return is_string($agent) && $agent !== '' ? substr($agent, 0, 255) : null;
    }

    private static function place(?string $ip): string
    {
        if ($ip === null || $ip === '' || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'On this computer';
        }

        if (app()->runningUnitTests()) {
            return 'Not available';
        }

        try {
            $response = Http::timeout(2)->acceptJson()->get('http://ip-api.com/json/'.$ip, [
                'fields' => 'status,city,regionName,country',
            ]);
            if (! $response->ok() || $response->json('status') !== 'success') {
                return 'Not available';
            }
            $place = collect([$response->json('city'), $response->json('regionName'), $response->json('country')])->filter()->implode(', ');

            return $place !== '' ? $place : 'Not available';
        } catch (\Throwable) {
            return 'Not available';
        }
    }
}
