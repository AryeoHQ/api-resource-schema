<?php

declare(strict_types=1);

namespace Support\Http\Resources\Schemas\Console\Commands\MakeResource;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\Attributes\WithConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Console\Commands\MakeResource\Events\BuildingSchema;
use Support\Http\Resources\Schemas\Contracts;
use Support\Http\Resources\Schemas\Provides;
use Tests\Fixtures\Support\Schemas\ApiVersion;
use Tests\TestCase;
use Tooling\Composer\Composer;
use Tooling\GeneratorCommands\Testing\Concerns\GeneratesFileTestCases;
use Tooling\GeneratorCommands\Testing\Concerns\RetrievesNamespaceTestCases;

#[CoversClass(MakeResource::class)]
#[WithConfig('api-resource-schema.version', ApiVersion::class)]
final class MakeResourceTest extends TestCase
{
    use GeneratesFileTestCases;
    use RetrievesNamespaceTestCases;

    public References\Schema $reference {
        get => new References\Schema(name: 'Post', baseNamespace: 'App\\V1');
    }

    private References\Schema $nestedReference {
        get => new References\Schema(name: 'Post', baseNamespace: 'App\\Nested\\Deeper\\V1');
    }

    private References\SchemaCollection $collectionReference {
        get => new References\SchemaCollection(name: 'Posts', baseNamespace: 'App\\V1');
    }

    /** @var array<string, mixed> */
    public array $baselineInput {
        get => ['name' => 'Post', '--namespace' => 'App\\', '--schema-version' => 'V1'];
    }

    /** @var array<string, mixed> */
    public array $withNamespaceBackslashInput {
        get => $this->baselineInput;
    }

    /** @var array<string, mixed> */
    public array $withoutNamespaceBackslashInput {
        get => ['name' => 'Post', '--namespace' => 'App', '--schema-version' => 'V1'];
    }

    /** @var array<string, mixed> */
    public array $withNestedNamespaceInput {
        get => ['name' => 'Post', '--namespace' => 'App\\Nested\\Deeper', '--schema-version' => 'V1'];
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
                $this->assertStringContainsString('#[Version('.class_basename(ApiVersion::class).'::V1)]', $schemaClass);
                $this->assertStringContainsString('use '.ApiVersion::class.';', $schemaClass);
                $this->assertStringContainsString('#[CollectedBy(Posts::class)]', $schemaClass);
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
            '--schema-version' => 'V1',
        ]);

        $this->assertTrue(File::exists($this->collectionReference->filePath->toString()), 'The schema collection was not created');
        tap(
            File::get($this->collectionReference->filePath->toString()),
            function (string $collectionClass) {
                $this->assertStringContainsString('extends '.class_basename(ResourceCollection::class), $collectionClass);
                $this->assertStringContainsString('implements '.class_basename(Contracts\SchemaCollection::class), $collectionClass);
                $this->assertStringContainsString('use '.class_basename(Provides\AsSchemaCollection::class).';', $collectionClass);
                $this->assertStringContainsString('#[Collects(Post::class)]', $collectionClass);
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
            '--schema-version' => 'V1',
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
    public function it_strips_collection_suffix_from_name(): void
    {
        Composer::fake();

        $this->artisan($this->command, [
            'name' => 'PostCollection',
            '--namespace' => 'App\\',
            '--schema-version' => 'V1',
        ]);

        $this->assertTrue(File::exists($this->expectedFilePath), 'The schema was not created');
        $this->assertTrue(File::exists($this->collectionReference->filePath->toString()), 'The schema collection was not created');
    }

    #[Test]
    public function it_does_not_duplicate_version_in_namespace(): void
    {
        Composer::fake();

        $this->artisan($this->command, [
            'name' => 'Post',
            '--namespace' => 'App\\V1',
            '--schema-version' => 'V1',
        ]);

        $this->assertTrue(File::exists($this->expectedFilePath), 'The schema was not created');
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
                $this->assertStringNotContainsString('{{ version }}', $schemaClass);
                $this->assertStringNotContainsString('{{ versionImport }}', $schemaClass);
            }
        );
    }

    #[Test]
    public function it_accepts_version_by_value(): void
    {
        Composer::fake();

        $this->artisan($this->command, [
            'name' => 'Post',
            '--namespace' => 'App\\',
            '--schema-version' => 'v1',
        ]);

        $this->assertTrue(File::exists($this->expectedFilePath));
        tap(
            File::get($this->expectedFilePath),
            function (string $schemaClass) {
                $this->assertStringContainsString('#[Version('.class_basename(ApiVersion::class).'::V1)]', $schemaClass);
            }
        );
    }

    #[Test]
    public function it_prompts_for_version_when_not_provided(): void
    {
        Composer::fake();

        $this->artisan($this->command, ['name' => 'Post', '--namespace' => 'App\\'])
            ->expectsQuestion('Select a version.', 'v1');

        $this->assertTrue(File::exists($this->expectedFilePath));
        tap(
            File::get($this->expectedFilePath),
            function (string $schemaClass) {
                $this->assertStringContainsString('#[Version('.class_basename(ApiVersion::class).'::V1)]', $schemaClass);
            }
        );
    }

    #[Test]
    public function it_fails_with_invalid_version(): void
    {
        Composer::fake();

        $this->artisan($this->command, [
            'name' => 'Post',
            '--namespace' => 'App\\',
            '--schema-version' => 'INVALID',
        ]);

        $this->assertFalse(File::exists($this->expectedFilePath));
    }

    #[Test]
    public function it_fails_when_version_is_not_bound(): void
    {
        config()->set('api-resource-schema.version', null);
        Composer::fake();

        $this->artisan($this->command, [
            'name' => 'Post',
            '--namespace' => 'App\\',
            '--schema-version' => 'V1',
        ]);

        $this->assertFalse(File::exists($this->expectedFilePath));
    }
}
