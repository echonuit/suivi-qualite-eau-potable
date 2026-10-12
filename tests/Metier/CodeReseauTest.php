<?php

declare(strict_types=1);

namespace EauPotable\Tests\Metier;

use EauPotable\Metier\CodeReseau;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CodeReseauTest extends TestCase
{
    public function testUnCodeDeNeufChiffresEstAccepteEtGardeSonZeroInitial(): void
    {
        // Le réseau Aix-Couteron, cité dans le sujet
        self::assertSame('013000618', CodeReseau::valider('013000618'));
    }

    public function testLesEspacesAutourDuCodeSontIgnores(): void
    {
        self::assertSame('013000618', CodeReseau::valider(' 013000618 '));
    }

    #[DataProvider('codesInvalides')]
    public function testUnCodeInvalideEstRefuse(string $code): void
    {
        $this->expectException(InvalidArgumentException::class);

        CodeReseau::valider($code);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function codesInvalides(): iterable
    {
        yield 'vide' => [''];
        yield 'trop court (un code de commune)' => ['13001'];
        yield 'trop long' => ['0130006180'];
        yield 'avec des lettres' => ['01300061A'];
        yield 'une tentative d\'injection' => ['013000618; DROP TABLE'];
    }
}
