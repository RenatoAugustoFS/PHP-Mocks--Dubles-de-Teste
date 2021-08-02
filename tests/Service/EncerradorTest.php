<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;

class LeilaoDaoMock extends LeilaoDao
{
    private $leiloes = [];

    public function salva(Leilao $leilao): void
    {
        $this->leiloes[] = $leilao;
    }

    public function recuperarNaoFinalizados(): array
    {
        return array_filter($this->leiloes, function (Leilao $leilao) {
            return !$leilao->estaFinalizado();
        });
    }

    public function recuperarFinalizados(): array
    {
        return array_filter($this->leiloes, function (Leilao $leilao) {
            return $leilao->estaFinalizado();
        });
    }

    public function atualiza(Leilao $leilao)
    {

    }
}

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

        $leilaoDao = new LeilaoDaoMock();
        $leilaoDao->salva($hornet);
        $leilaoDao->salva($cbmil);

        $encerrador = new Encerrador($leilaoDao);
        $encerrador->encerra();

        $leiloes = $leilaoDao->recuperarFinalizados();

        self::assertCount(2, $leiloes);
        self::assertEquals($leiloes[0]->recuperarDescricao(), 'Hornet');
        self::assertEquals($leiloes[1]->recuperarDescricao(), 'cbmil');
    }
}