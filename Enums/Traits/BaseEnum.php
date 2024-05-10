<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use ValueError;

trait BaseEnum
{
    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return Lang::has($this->getNamespace())
            ? $this->translated()
            : $this->defaultLabel();
    }

    public function toHtml(): string
    {
        if (! method_exists(self::class, 'color')) {
            return $this->getLabel();
        }

        return sprintf("<span class='badge bg-label-%s'>%s</span>", $this->getColor(), $this->getLabel());
    }

    public function getComment(): string
    {
        $comment = [];

        foreach (self::cases() as $item) {
            $comment[] = implode(': ', [$item->value, $item->label()]);
        }

        return implode(', ', $comment);
    }

    public function is(null|self|string $value): bool
    {
        if (! $value instanceof self) {
            $value = self::tryFrom($value);
        }

        return $this === $value;
    }

    public function isNot(mixed $value): bool
    {
        return ! $this->is($value);
    }

    public static function tryFromName(string $name): ?static
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }

    public static function fromName(string $name): static
    {
        return self::tryFromName($name) ?? throw new ValueError($name.' is not a valid case for enum '.self::class);
    }

    public static function options(bool $translated = false): array
    {
        if (! $translated) {
            return array_column(self::cases(), 'value', 'name');
        }

        return Arr::mapWithKeys(self::cases(), fn (self $case) => [$case->getLabel() => $case->value]);
    }

    private function getNamespace(): string
    {
        $class = static::class;

        return "enums.$class.$this->name";
    }

    private function translated(): string
    {
        return Lang::get($this->getNamespace());
    }

    private function defaultLabel(): string
    {
        return Str::of($this->name)->replace(['-', '_'], ' ')->apa();
    }
}
