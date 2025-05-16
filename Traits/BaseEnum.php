<?php

declare(strict_types=1);

namespace App\Enums\Traits;

use Error;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use UnitEnum;
use ValueError;

trait BaseEnum
{
    public static function __callStatic(string $name, mixed $args): null|int|string
    {
        return self::fromName(name: $name)->value;
    }

    public static function names(): array
    {
        return array_column(
            array: self::cases(),
            column_key: 'name',
        );
    }

    public static function values(): array
    {
        return array_column(
            array: self::cases(),
            column_key: 'value',
        );
    }

    public static function comment(): string
    {
        $comment = [];

        foreach (self::cases() as $item) {
            $comment[] = implode(
                separator: ': ',
                array: [$item->value, $item->getLabel()],
            );
        }

        return implode(
            separator: ' | ',
            array: $comment,
        );
    }

    public static function options(bool $translated = false): array
    {
        if (! $translated) {
            return array_column(
                array: self::cases(),
                column_key: 'value',
                index_key: 'name',
            );
        }

        return Arr::mapWithKeys(
            array: self::cases(),
            callback: fn (self $case) => [$case->getLabel() => $case->value],
        );
    }

    public static function tryFromName(string $name): ?static
    {
        try {
            return constant('static::' . $name);
        } catch (Error $e) {
            return null;
        }
    }

    public static function fromName(string $name): static
    {
        return self::tryFromName($name) ?? throw new ValueError($name . ' is not a valid case for enum ' . self::class);
    }

    public function badgeClass(): string
    {
        return 'badge badge-';
    }

    public function getLabel(): string
    {
        $string = Str::of($this->name);

        if ($string->contains('_')) {
            $string = $string->lower();
        }

        return $string->snake()->replace('_', ' ')->apa()->toString();
    }

    public function toHtml(): string
    {
        if (! method_exists(self::class, method: 'getColor')) {
            return $this->getLabel();
        }

        return sprintf(
            "<span class='%s'>%s</span>",
            $this->badgeClass() . $this->getColor(),
            $this->getLabel(),
        );
    }

    public function is(null|string|int|UnitEnum $value): bool
    {
        if (! $value instanceof UnitEnum && $value !== null) {
            $value = self::tryFrom((int) $value);
        }

        return $this === $value;
    }

    public function isNot(null|string|int|UnitEnum $value): bool
    {
        return ! $this->is($value);
    }
}
