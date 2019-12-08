<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\PedidoRecSenha;

class RecuperacaoSenha extends Mailable
{
    use Queueable, SerializesModels;

    private $pedidoRecuperacao;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(PedidoRecSenha $pedido)
    {
        $this->pedidoRecuperacao = $pedido;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->view('emails.recuperar_senha')
        ->text('emails.recuperar_senha_plain')
        ->subject('Recuperação de senha')
        ->with([ 
            'url' => config('app.base_site_url'),
            'token' => $this->pedidoRecuperacao->token 
        ]);
    }
}
