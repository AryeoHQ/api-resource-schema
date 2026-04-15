<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Console\Commands\MakeResource;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Console\Commands\MakeResource\Events\BuildingSchema;
use Support\Http\Resources\Schemas\Contracts;
use Support\Http\Resources\Schemas\Provides;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\GeneratorCommands\Testing\Concerns\GeneratesFileTestCases;
use Tooling\GeneratorCommands\Testing\Concerns\RetrievesNamespaceTestCases;

#[CoversClass(MakeResource::class)]
final class MakeResourceTest extends TestCase
{
    use GeneratesFileTestCases;
    use RetrievesNamespaceTestCases;

    public References\Schema $reference {
        get => new References\Schema(name: 'Post', baseNamespace: 'App');
    }

    private References\Schema $nestedReference {
        get => new References\Schema(name: 'Post', baseNamespace: 'App\\Nested\\Deeper');
    }

    private References\SchemaCollection $collectionReference {
        get => new References\SchemaCollection(name: 'Posts', baseNamespace: 'App');
    }

    /** @var array<string, mixed> */
    public array $baselineInput {
        get => ['name' => 'Post', '--namespace' => 'App\\'];
    }

    /** @var array<string, mixed> */
    public array $withNamespaceBackslashInput {
        get => $this->baselineInput;
    }

    /** @var array<string, mixed> */
    public array $withoutNamespaceBackslashInput {
        get => ['name' => 'Post', '--namespace' => 'App'];
    }

    /** @var array<string, mixed> */
    public array $withNestedNamespaceInput {
        get => ['name' => 'Post', '--namespace' => 'App\\Nested\\Deeper'];
    }

    protected string $expectedNestedFilePath {
        get => $this->nestedReference->filePath->toString();
    }

    #[Test]
    public function it_can_make_a_schema(): void
    {
        Composer::fake();

        $this->artisan($this->command, $this->baselineInput);

        $this->assertTrue(File::exists($this->expectedFilePath), 'The schema was not created');
        tap(
            File::get($this->expectedFilePath),
            function (string $schemaClass) {
                $this->assertStringContainsString('implements '.class_basename(Contracts\Schema::class), $schemaClass);
                $this->assertStringContainsString('use '.Provides\AsSchema::class.';', $schemaClass);
                $this->assertStringContainsString('use '.class_basename(Provides\AsSchema::class).';', $schemaClass);
            }
        );
    }

    #[Test]
    public function it_also_makes_a_collection(): void
    {
        Composer::fake();

        $this->artisan($this->command, [
            'name' => 'Post',
            '--namespace' => 'App\\',
        ]);

        $this->assertTrue(File::exists($this->collectionReference->filePath->toString()), 'The schema collection was not created');
        tap(
            File::get($this->collectionReference->filePath->toString()),
            function (string $collectionClass) {
                $this->assertStringContainsString('extends '.class_basename(ResourceCollection::class), $collectionClass);
                $this->assertStringContainsString('implements '.class_basename(Contracts\SchemaCollection::class), $collectionClass);
                $this->assertStringContainsString('use '.class_basename(Provides\AsSchemaCollection::class).';', $collectionClass);
            }
        );
    }

    #[Test]
    public function it_treats_name_ending_in_collection_as_collection(): void
    {
        Composer::fake();

        $this->artisan($this->command, [
            'name' => 'Posts',
            '--namespace' => 'App\\',
        ]);

        $this->assertTrue(File::exists($this->collectionReference->filePath->toString()), 'The schema collection was not created');
        tap(
            File::get($this->collectionReference->filePath->toString()),
            function (string $collectionClass) {
                $this->assertStringContainsString('extends '.class_basename(ResourceCollection::class), $collectionClass);
            }
        );
    }

    #[Test]
    public function it_injects_properties_from_event_listeners(): void
    {
        Composer::fake();

        Event::listen(BuildingSchema::class, function (BuildingSchema $event): void {
            $event->imports->push('App\Models\User');
            $event->properties->push('public User $id { get => $this->resource->getKey(); }');
        });

        $this->artisan($this->command, $this->baselineInput);

        $this->assertTrue(File::exists($this->expectedFilePath));
        tap(
            File::get($this->expectedFilePath),
            function (string $schemaClass) {
                $this->assertStringContainsString('use App\Models\User;', $schemaClass);
                $this->assertStringContainsString('public User $id { get => $this->resource->getKey(); }', $schemaClass);
            }
        );
    }

    #[Test]
    public function it_generates_clean_output_with_no_event_listeners(): void
    {
        Composer::fake();

        $this->artisan($this->command, $this->baselineInput);

        $this->assertTrue(File::exists($this->expectedFilePath));
        tap(
            File::get($this->expectedFilePath),
            function (string $schemaClass) {
                $this->assertStringNotContainsString('{{ imports }}', $schemaClass);
                $this->assertStringNotContainsString('{{ properties }}', $schemaClass);
            }
        );
    }
}
