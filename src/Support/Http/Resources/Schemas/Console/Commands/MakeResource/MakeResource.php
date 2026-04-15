<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Console\Commands\MakeResource;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Console\ResourceMakeCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Support\Http\Resources\Schemas\Console\Commands\MakeResource\Events\BuildingSchema;
use Support\Http\Resources\Schemas\Console\Commands\MakeResource\References\Schema;
use Support\Http\Resources\Schemas\Console\Commands\MakeResource\References\SchemaCollection;
use Symfony\Component\Console\Input\InputOption;
use Tooling\GeneratorCommands\Concerns\CreatesColocatedTests;
use Tooling\GeneratorCommands\Concerns\GeneratorCommandCompatibility;
use Tooling\GeneratorCommands\Concerns\RetrievesNamespace;
use Tooling\GeneratorCommands\Contracts\GeneratesFile;

class MakeResource extends ResourceMakeCommand implements GeneratesFile
{
    use CreatesColocatedTests;
    use GeneratorCommandCompatibility;
    use RetrievesNamespace;

    public Stringable $nameInput {
        get => $this->nameInput ??= str($this->argument('name'))->singular();
    }

    public Schema|SchemaCollection $reference {
        get => $this->reference ??= $this->isCollection()
            ? resolve(SchemaCollection::class, [
                'name' => $this->nameInput,
                'baseNamespace' => $this->baseNamespace,
            ])
            : resolve(Schema::class, [
                'name' => $this->nameInput,
                'baseNamespace' => $this->baseNamespace,
            ]);
    }

    public function handle(): bool
    {
        $this->resolveNamespace();

        if ($this->isCollection()) {
            $this->generateSchemaCollection();
        } else {
            $this->generateSchema();
            $this->generateSchemaCollection();
        }

        return (bool) Command::SUCCESS;
    }

    protected function isCollection(): bool
    {
        return $this->option('collection') || $this->nameInput->endsWith('Collection');
    }

    protected function generateSchema(): void
    {
        GeneratorCommand::handle();
    }

    protected function generateSchemaCollection(): void
    {
        if (! ($this->reference instanceof SchemaCollection)) {
            $this->reference = $this->reference->collection;
            $this->nameInput = $this->reference->name;
        }

        $this->type = 'Resource collection';

        GeneratorCommand::handle();
    }

    protected function buildClass($name): string
    {
        $stub = str(GeneratorCommand::buildClass($name));

        return match ($this->reference instanceof SchemaCollection) {
            true => $this->prepareSchemaCollectionStub($name, $stub)->value(),
            false => $this->prepareSchemaStub($name, $stub)->value(),
        };
    }

    protected function prepareSchemaStub(string $name, Stringable $stub): Stringable
    {
        $event = tap(new BuildingSchema($name), event(...));

        $stub = $stub->replace('{{ collection }}', $this->reference->collection->name->value());
        $stub = $this->replaceImports($stub, $event->imports);
        $stub = $this->replaceProperties($stub, $event->properties);

        return $stub;
    }

    protected function prepareSchemaCollectionStub(string $name, Stringable $stub): Stringable
    {
        $stub = $stub->replace('{{ schema }}', $this->reference->schema->name->value());

        return $stub;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $imports
     */
    protected function replaceImports(Stringable $stub, Collection $imports): Stringable
    {
        $replacement = str($imports
            ->map(fn (string $import) => str($import)->trim('\\')->wrap('use ', ';'))
            ->implode("\n"));

        return $stub->replace(
            '{{ imports }}',
            $replacement->whenNotEmpty(fn (Stringable $s) => $s->prepend("\n"))->toString()
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $properties
     */
    protected function replaceProperties(Stringable $stub, Collection $properties): Stringable
    {
        $replacement = str($properties
            ->map(fn (string $property) => str($property)->prepend('    '))
            ->implode("\n\n"));

        return $stub->replace(
            '{{ properties }}',
            $replacement->whenNotEmpty(fn (Stringable $s) => $s->prepend("\n\n"))->toString()
        );
    }

    /**
     * @return array<int, InputOption>
     */
    protected function getOptions(): array
    {
        return [
            new InputOption('collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection'),
            new InputOption('force', 'f', InputOption::VALUE_NONE, 'Create the class even if it already exists'),
            ...$this->getNamespaceInputOptions(),
        ];
    }
}
