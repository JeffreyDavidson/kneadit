<?php

namespace Tests\Support\PHPStan;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Tests\TestCase;

final class TestFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'test';
    }

    public function getTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        Scope $scope,
    ): Type {
        if ($functionCall->getArgs() === []) {
            return new ObjectType(TestCase::class);
        }

        return ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $functionCall->getArgs(),
            $functionReflection->getVariants(),
        )->getReturnType();
    }
}
