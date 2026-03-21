<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Console\Commands\MakeResource;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Console\ResourceMakeCommand;
use Illuminate\Support\Stringable;
use Support\Http\Resources\Schemas\Console\Commands\MakeResource\Events\BuildingSchema;
use Support\Http\Resources\Schemas\Console\Commands\MakeResource\References\Schema;
use Support\Http\Resources\Schemas\Console\Commands\MakeResource\References\SchemaCollection;
use Symfony\Component\Console\Input\InputOption;
use Tooling\GeneratorCommands\Concerns\CreatesColocatedTests;
use Tooling\GeneratorCommands\Concerns\GeneratorCommandCompatibility;
use Tooling\GeneratorCommands\Concerns\RetrievesNamespace;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;
use Tooling\GeneratorCommands\References\Contracts\Reference;

class MakeResource extends ResourceMakeCommand implements GeneratesFile
{
    use CreatesColocatedTests;
    use GeneratorCommandCompatibility;
    use RetrievesNamespace;

    public string $stub {
        get => $this->shouldBeCollection()
            ? __DIR__.'/stubs/schema-collection.stub'
            : __DIR__.'/stubs/schema.stub';
    }

    public Stringable $nameInput {
        get => $this->nameInput ??= str($this->argument('name'));
    }

    public Reference $reference {
        get => $this->reference ??= $this->shouldBeCollection()
            ? resolve(SchemaCollection::class, [
                'name' => $this->nameInput,
                'baseNamespace' => $this->baseNamespace,
            ])
            : resolve(Schema::class, [
                'name' => $this->nameInput,
                'baseNamespace' => $this->baseNamespace,
            ]);
    }

    public function handle(): null|bool
    {
        $this->resolveNamespace();

        if ($this->shouldBeCollection()) {
            $this->type = 'Resource collection';
        }

        return GeneratorCommand::handle();
    }

    protected function buildClass($name): string
    {
        $stub = str(GeneratorCommand::buildClass($name));

        if ($this->shouldBeCollection()) {
            return $stub->value();
        }

        $event = tap(new BuildingSchema($name), event(...));

        $stub = $this->replaceImports($stub, $event->imports);
        $stub = $this->replaceProperties($stub, $event->properties);

        return $stub->value();
    }

    /**
     * @param  array<int, string>  $imports
     */
    protected function replaceImports(Stringable $stub, array $imports): Stringable
    {
        $replacement = str(collect($imports)
            ->map(fn (string $import) => str($import)->trim('\\')->wrap('use ', ';'))
            ->implode("\n"));

        return $stub->replace(
            '{{ imports }}',
            $replacement->whenNotEmpty(fn (Stringable $s) => $s->prepend("\n"))->toString()
        );
    }

    /**
     * @param  array<int, string>  $properties
     */
    protected function replaceProperties(Stringable $stub, array $properties): Stringable
    {
        $replacement = str(collect($properties)
            ->map(fn (string $property) => str($property)->prepend('    '))
            ->implode("\n\n"));

        return $stub->replace(
            '{{ properties }}',
            $replacement->whenNotEmpty(fn (Stringable $s) => $s->prepend("\n\n"))->toString()
        );
    }

    protected function shouldBeCollection(): bool
    {
        return $this->option('collection') ||
               str_ends_with($this->argument('name'), 'Collection');
    }

    /**
     * @return array<int, InputOption>
     */
    protected function getOptions(): array
    {
        return [
            new InputOption('force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'),
            new InputOption('collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection'),
            ...$this->getNamespaceInputOptions(),
        ];
    }
}
