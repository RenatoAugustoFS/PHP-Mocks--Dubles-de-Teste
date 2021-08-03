<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EnviadorEmail
{
    public function notificarTerminoLeilao(Leilao $leilao): void
    {
        $sucesso = mail(
            'renatoaugusto.ads@gmail.com',
            'Leilão finalizado',
            'Este leilão foi finalizado' . $leilao->recuperarDescricao()
        );

        if (!$sucesso) {
            throw new \DomainException('Erro ao enviar email');
        }
    }
}