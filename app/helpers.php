<?php

use Illuminate\Database\Eloquent\Model;

if (! function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return app()->getLocale() === 'ar';
    }
}

if (! function_exists('text_dir')) {
    function text_dir(): string
    {
        return is_rtl() ? 'rtl' : 'ltr';
    }
}

if (! function_exists('localized')) {
    /**
     * Pick Arabic field when locale is ar and value exists.
     */
    function localized(mixed $model, string $field = 'name', ?string $arField = null): ?string
    {
        if ($model === null) {
            return null;
        }

        $arField ??= $field.'_ar';

        if (is_rtl()) {
            $ar = is_array($model) ? ($model[$arField] ?? null) : ($model->{$arField} ?? null);
            if (filled($ar)) {
                return $ar;
            }
        }

        return is_array($model) ? ($model[$field] ?? null) : ($model->{$field} ?? null);
    }
}

if (! function_exists('pdf_view')) {
    function pdf_view(string $base): string
    {
        return is_rtl() ? "pdf.{$base}-ar" : "pdf.{$base}";
    }
}
