<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

final class Navigation
{
    public static function header(): array
    {
        $nav = config('navigation.header', []);

        return self::normalizeRoot($nav);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeRoot(array $items): array
    {
        $out = [];

        foreach ($items as $item) {
            $type = self::type($item);

            if ($type === 'separator') {
                $out[] = ['type' => 'separator'];

                continue;
            }

            if ($type === 'route') {
                if (self::routeExists($item['route'] ?? null)) {
                    $out[] = $item;
                }

                continue;
            }

            if ($type === 'dropdown') {
                $children = self::normalizeDropdownItems($item['items'] ?? []);
                if (! empty($children)) {
                    $item['items'] = $children;
                    $out[] = $item;
                }

                continue;
            }

            // unknown types ignored
        }

        return self::trimSeparators($out);
    }

    private static function type(array $item): string
    {
        // Robust gegen ' Route ', 'ROUTE', etc.
        return strtolower(trim((string) ($item['type'] ?? 'route')));
    }

    private static function routeExists(mixed $routeName): bool
    {
        return is_string($routeName) && $routeName !== '' && Route::has($routeName);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeDropdownItems(array $items): array
    {
        $out = [];

        foreach ($items as $item) {
            $type = self::type($item);

            if ($type === 'label') {
                $out[] = $item;

                continue;
            }

            if ($type === 'separator') {
                $out[] = ['type' => 'separator'];

                continue;
            }

            if ($type === 'route') {
                // Robust: wenn 'route' fehlt/leer ist, skippen.
                // Wenn route vorhanden ist und existiert, übernehmen.
                if (self::routeExists($item['route'] ?? null)) {
                    $out[] = $item;
                }

                continue;
            }

            // dropdowns innerhalb dropdowns bewusst ignorieren
        }

        return self::trimSeparators($out);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private static function trimSeparators(array $items): array
    {
        // collapse duplicate separators
        $collapsed = [];
        $prevSep = false;

        foreach ($items as $item) {
            $isSep = (($item['type'] ?? null) === 'separator');
            if ($isSep && $prevSep) {
                continue;
            }
            $collapsed[] = $item;
            $prevSep = $isSep;
        }

        while (! empty($collapsed) && (($collapsed[0]['type'] ?? null) === 'separator')) {
            array_shift($collapsed);
        }

        while (! empty($collapsed) && ((end($collapsed)['type'] ?? null) === 'separator')) {
            array_pop($collapsed);
        }

        return $collapsed;
    }
}
