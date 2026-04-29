<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Extensions;

use PHPStan\Rules\Methods\ReturnTypeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<ReturnTypeRule>
 */
class SchemaCollectionReturnTypeTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ReturnTypeRule::class); // @phpstan-ignore phpstanApi.classConstant
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__.'/../../../../../tooling/phpstan/schema-collection-return-type.neon',
        ];
    }

    #[Test]
    public function it_accepts_schema_collection_return_types(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Controller.php')], []);
    }
}
