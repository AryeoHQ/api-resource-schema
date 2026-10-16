<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Support\Http\Resources\Schemas\Attributes\ContributesTag\ContributesTag;
use Support\Http\Resources\Schemas\Attributes\Untagged\Untagged;
use Support\Http\Resources\Schemas\Contracts\Schema;
use Tests\Tooling\Concerns\GetsFixtures;

/**
 * @extends RuleTestCase<ConditionalFieldMustBeCoveredByTag>
 */
class ConditionalFieldMustBeCoveredByTagTest extends RuleTestCase
{
    use GetsFixtures;

    protected function getRule(): Rule
    {
        return new ConditionalFieldMustBeCoveredByTag;
    }

    #[Test]
    public function it_passes_when_every_conditional_field_is_covered(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Tags/ValidTaggedSchema.php')], []);
    }

    #[Test]
    public function it_fails_when_a_relation_has_no_contribution(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Tags/MissingContribution.php')], [
            [
                '['.class_basename(Schema::class).'] field [comments] calls [whenLoaded] for relation [comments] and needs a ['.class_basename(ContributesTag::class).'] attribute for that relation.',
                23,
            ],
            [
                '['.class_basename(Schema::class).'] field [teamMembershipId] calls [whenPivotLoaded] for relation [team_user] and needs a ['.class_basename(ContributesTag::class).'] attribute for that relation.',
                25,
            ],
            [
                '['.class_basename(Schema::class).'] field [rating] calls [whenAggregated] with a non-literal relation, which no ['.class_basename(ContributesTag::class).'] attribute can cover.',
                27,
            ],
        ]);
    }

    #[Test]
    public function it_fails_when_a_field_reads_the_caller_without_untagged(): void
    {
        $this->analyse([$this->getFixturePath('PhpStan/Tags/MissingUntagged.php')], [
            [
                '['.class_basename(Schema::class).'] field [canEdit] reads the authenticated user and needs the ['.class_basename(Untagged::class).'] attribute.',
                19,
            ],
            [
                '['.class_basename(Schema::class).'] field [viewerId] reads the authenticated user and needs the ['.class_basename(Untagged::class).'] attribute.',
                21,
            ],
            [
                '['.class_basename(Schema::class).'] field [owner] reads the authenticated user and needs the ['.class_basename(Untagged::class).'] attribute.',
                23,
            ],
        ]);
    }
}
