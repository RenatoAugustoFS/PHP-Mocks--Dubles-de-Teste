<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorEmail;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase
{
    private Encerrador $encerrador;
    private $hornet;
    private $cbmil;
    private $leilaoDao;
    private $enviadorEmail;

    protected function setUp(): void
    {
        $this->hornet = new Leilao(
            'Hornet',
            new \DateTimeImmutable('8 days ago')
        );

        $this->cbmil = new Leilao(
            'cbmil',
            new \DateTimeImmutable('10 days ago')
        );

        //$this->leilaoDao = $this->createMock(LeilaoDao::class);
        $this->leilaoDao = $this->getMockBuilder(LeilaoDao::class)
            ->setConstructorArgs([new \PDO('sqlite::memory:')])
            ->getMock();

        $this->leilaoDao->method('recuperarNaoFinalizados')->willReturn([$this->hornet, $this->cbmil]);
        $this->leilaoDao->method('recuperarFinalizados')->willReturn([$this->hornet, $this->cbmil]);

        $this->leilaoDao
            ->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive(
                [$this->hornet],
                [$this->cbmil]
            );

        $this->enviadorEmail = $this->createMock(EnviadorEmail::class);
        $this->enviadorEmail->method('notificarTerminoLeilao')->willReturn(false);

        $this->encerrador = new Encerrador($this->leilaoDao, $this->enviadorEmail);
    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $this->encerrador->encerra();
        $leiloes = $this->leilaoDao->recuperarFinalizados();

        self::assertCount(2, $leiloes);
        self::assertTrue($leiloes[0]->estaFinalizado());
        self::assertTrue($leiloes[1]->estaFinalizado());
    }

    public function testDeveContinuarOProcessamentoAoEncontrarErroAoEnviarEmail()
    {
            $error = new \DomainException('Erro ao enviar email');
            $this->enviadorEmail
                ->expects($this->exactly(2))
                ->method('notificarTerminoLeilao')
                ->willThrowException($error);

            $this->encerrador->encerra();
    }
}