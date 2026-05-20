<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Console\Concerns;

use Illuminate\Console\GeneratorCommand;
use Support\Http\Resources\Schemas\Contracts\Version;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\select;

/**
 * @mixin GeneratorCommand
 */
trait ResolvesSchemaVersion
{
    protected null|Version $version = null;

    protected function schemaVersionOptionName(): string
    {
        return 'schema-version';
    }

    protected function resolveVersion(): bool
    {
        /** @var class-string<\Support\Http\Resources\Schemas\Contracts\Version>|null $enumClass */
        $enumClass = config('api-resource-schema.version', null);

        if (! $enumClass) {
            $this->components->error(
                '`Version` enum not configured.'
            );

            return false;
        }

        $cases = collect($enumClass::cases());

        if ($input = $this->option($this->schemaVersionOptionName())) {
            $this->version = $cases->first(
                fn (Version $case) => $case->name === $input || (string) $case->value === $input,
                fn () => $this->components->error(
                    "Version [{$input}] not found. Available: ".$cases->map(fn (Version $case) => $case->value)->implode(', ')
                )
            );

            return (bool) $this->version;
        }

        $selected = select(
            label: 'Select a version.',
            options: $cases->mapWithKeys(fn (Version $case) => [$case->value => $case->value])->toArray(),
        );

        $this->version = $cases->first(fn (Version $case) => $case->value === $selected);

        return true;
    }

    /** @return array<int, InputOption> */
    protected function getSchemaVersionInputOptions(): array
    {
        return [
            new InputOption($this->schemaVersionOptionName(), null, InputOption::VALUE_OPTIONAL, 'The version for this schema (case name or value)'),
        ];
    }
}
