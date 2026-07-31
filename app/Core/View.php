<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Renders a PHP view inside a layout. Kept deliberately simple (no compiled
 * template language) so the whole rendering pipeline is auditable in one
 * file. Views live in app/Views and use plain PHP with the e() escaping
 * helper — the original project echoed $_POST/$_SESSION values straight
 * into HTML with no escaping anywhere, which is a stored/reflected XSS
 * vector on nearly every page (booking names, city, search box, etc.)
 */
final class View
{
    private static string $basePath = __DIR__ . '/../Views/';

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $data['csrf_field'] = Csrf::field();
        $data['csrf_token'] = Csrf::token();

        $content = self::renderFile($view, $data);

        if ($layout === null) {
            return $content;
        }

        $data['__content'] = $content;
        return self::renderFile($layout, $data);
    }

    private static function renderFile(string $view, array $data): string
    {
        $path = self::$basePath . str_replace('.', '/', $view) . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
