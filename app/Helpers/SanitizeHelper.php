<?php

namespace App\Helpers;

class SanitizeHelper
{
    /**
     * Sanitize HTML input to prevent XSS attacks.
     */
    public static function sanitizeHtml(string $input): string
    {
        // Strip HTML tags except for basic formatting
        $allowed = '<p><br><strong><em><ul><ol><li><a>';
        return strip_tags($input, $allowed);
    }

    /**
     * Sanitize text input (remove HTML tags).
     */
    public static function sanitizeText(string $input): string
    {
        return strip_tags($input);
    }

    /**
     * Sanitize user input for display.
     */
    public static function escapeForDisplay(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean and validate URL.
     */
    public static function sanitizeUrl(string $url): ?string
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}

