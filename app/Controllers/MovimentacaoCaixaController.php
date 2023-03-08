<?php

namespace App\Controllers;

use App\Entities\MovimentacaoCaixaEntity;
use App\Entities\SessaoUsuarioEntity;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class MovimentacaoCaixaController extends BaseController
{
    private SessaoUsuarioEntity $sessaoUsuarioEntity;
    private $validacao;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController(
            $request,
            $response,
            $logger
        );

        $this->sessaoUsuarioEntity = new SessaoUsuarioEntity();

        $this->sessaoUsuarioEntity = $this->sessaoUsuarioEntity->buscarDadosSessaoUsuario($this->request->getServer('HTTP_AUTHORIZATION'));
    }

    public function registraMovimentacao(): ResponseInterface
    {
        $dadosMovimentacao =  $this->request->getJSON();

        $this->validacao = Services::validation();

        $this->validacao->setRules([
            'mcx_tipo' => 'required|string',
            'mcx_valor' => 'required|numeric',
        ], [
            'mcx_tipo' => [
                'required' => "Tipo da movimentação não foi informado!"
            ],
            'mcx_valor' => [
                'required' => "Valor da movimentação não informado!",
                'numeric' => "Precisa ser um valor numerico!"
            ]
        ]);

        if (!$this->validacao->run([
            'mcx_tipo' => $dadosMovimentacao->mcxTipo,
            'mcx_valor' => $dadosMovimentacao->mcxValor
        ])) {
            log_message("DEBUG", json_encode($this->validacao->getErrors()));
            return $this->response->setStatusCode(200, "Sucesso")->setJSON(['status' => false, 'msg' => "Erro ao processar a movimentação, necessita enviar as informações dos campos corretamente!"]);
        }

        $movimentacaoCaixaEntity = new MovimentacaoCaixaEntity(
            mcx_tipo: $dadosMovimentacao->mcxTipo,
            mcx_comentario: $dadosMovimentacao->mcxComentario,
            mcx_valor: $dadosMovimentacao->mcxValor,
            empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON(
            $movimentacaoCaixaEntity->salvaMovimentacaoCaixaManual($movimentacaoCaixaEntity)
        );
    }
}
