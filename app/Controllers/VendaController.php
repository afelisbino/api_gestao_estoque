<?php

namespace App\Controllers;

use App\Entities\SacolaVendaEntity;
use App\Entities\SessaoUsuarioEntity;
use App\Entities\VendaEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class VendaController extends BaseController
{

    private SessaoUsuarioEntity $sessaoUsuarioEntity;

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

    /**
     * @param vendaValorCompra float
     * @param vendaValorDesconto float
     * @param vendaTipoPagamento string
     * @param itens array
     * @param pro_id string
     * @param scl_qtd int
     * @param scl_sub_total float
     */
    public function cadastrarVendaLocalNormal()
    {
        $dadosVenda = $this->request->getJSON(true);

        $vendaEntity = new VendaEntity();

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($vendaEntity->registraVendaLocalNormal($dadosVenda, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')));
    }

    /**
     * @param vendaValorCompra float
     * @param nomeCliente string
     * @param itens array
     * @param pro_id string
     * @param scl_qtd int
     * @param scl_sub_total float
     */
    public function cadastrarVendaLocalFiado()
    {
        $dadosVenda = $this->request->getJSON(true);

        $vendaEntity = new VendaEntity();

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($vendaEntity->registraVendaLocalFiado($dadosVenda, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')));
    }

    public function listarVendaFiadoAberto()
    {
        $vendaEntity = new VendaEntity();

        return $this->response->setStatusCode(200, "Sucesso")->setJSON(
            $vendaEntity->listaVendaFiadoAberto($this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'))
        );
    }

    /**
     * @param tokenVenda string
     */
    public function listarItensVenda()
    {
        $tokenVenda = $this->request->getGet('tokenVenda');

        $sacolaVendaEntity = new SacolaVendaEntity(
            venda: new VendaEntity(
                ven_token: $tokenVenda,
                empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
            )
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON(
            $sacolaVendaEntity->listaItensVenda($sacolaVendaEntity->__get('venda'))
        );
    }
    /**
     * @param tokenVenda string
     * @param tipoPagamento string
     */
    public function pagaVendaFiado()
    {
        $dados = $this->request->getRawInput();

        $vendaEntity = new VendaEntity(
            empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
        );

        $statusPagamentoVenda = false;

        foreach ($dados['tokenVenda'] as $venda) {
            $vendaEntity->__set('ven_token', $venda);
            $pagaVenda = $vendaEntity->alteraStatusVendaFiadoParaPago($vendaEntity, $dados['formaPagamento']);

            $statusPagamentoVenda = $pagaVenda['status'];
        }

        return $this->response->setStatusCode(200, "Sucesso")->setJSON(
            $statusPagamentoVenda ? array(
                'status' => $statusPagamentoVenda,
                'msg' => "Vendas pago com sucesso!"
            ) : array(
                'status' => $statusPagamentoVenda,
                'msg' => 'Falha ao pagar vendas fiado'
            )
        );
    }
}
