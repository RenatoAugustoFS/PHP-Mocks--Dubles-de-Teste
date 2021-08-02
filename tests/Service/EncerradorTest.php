<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase
{
    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $hornet = new Leilao(
            'Hornet',
            new \DateTimeImmutable('8 days ago')
        );

        $cbmil = new Leilao(
            'cbmil',
            new \DateTimeImmutable('10 days ago')
        );

        //$leilaoDao = $this->createMock(LeilaoDao::class);
        $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
            ->setConstructorArgs([new \PDO('sqlite::memory:')])
            ->getMock();

        $leilaoDao->method('recuperarNaoFinalizados')->willReturn([$hornet, $cbmil]);
        $leilaoDao->method('recuperarFinalizados')->willReturn([$hornet, $cbmil]);

        $leilaoDao
            ->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive(
                [$hornet],
                [$cbmil]
            );

        $encerrador = new Encerrador($leilaoDao);
        $encerrador->encerra();

        $leiloes = $leilaoDao->recuperarFinalizados();

        self::assertCount(2, $leiloes);
        self::assertTrue($leiloes[0]->estaFinalizado());
        self::assertTrue($leiloes[1]->estaFinalizado());
    }
}