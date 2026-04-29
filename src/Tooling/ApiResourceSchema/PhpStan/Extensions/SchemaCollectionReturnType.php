<?php

declare(strict_types=1);

namespace Tooling\ApiResourceSchema\PhpStan\Extensions;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Support\Http\Resources\Schemas\Attributes\CollectedBy;
use Support\Http\Resources\Schemas\Contracts\Schema;

final class SchemaCollectionReturnType implements DynamicStaticMethodReturnTypeExtension
{
    private readonly ReflectionProvider $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function getClass(): string
    {
        return Schema::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'collection';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): null|Type
    {
        if (! $methodCall->class instanceof Name) {
            return null;
        }

        $calledOnType = $scope->resolveTypeByName($methodCall->class);

        $classNames = $calledOnType->getObjectClassNames();

        if (count($classNames) !== 1) {
            return null;
        }

        $className = $classNames[0];

        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        $attributes = $classReflection->getNativeReflection()->getAttributes(CollectedBy::class);

        if ($attributes === []) {
            return null;
        }

        $expressions = $attributes[0]->getArgumentsExpressions();

        if ($expressions === []) {
            return null;
        }

        $expr = $expressions[0];

        if ($expr instanceof \PhpParser\Node\Expr\ClassConstFetch && $expr->class instanceof Name) {
            return new ObjectType($expr->class->toString());
        }

        return null;
    }
}
