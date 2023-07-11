<?php

namespace App\Controllers;

use App\Entities\EmpresaEntity;
use App\Entities\SessaoUsuarioEntity;
use App\Entities\TipoPagamentoEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class TipoPagamentoController extends BaseController
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
   * @param nomeTipoPagamento string
   * @param categoriaTipoPagamento string
   */
  public function novoTipoPagamento()
  {
    $dadosRequisicao = $this->request->getJSON(true);

    $tipoPagamentoEntity = new TipoPagamentoEntity(
      tpg_nome: $dadosRequisicao['nomeTipoPagamento'],
      tpg_categoria_pagamento: $dadosRequisicao['categoriaTipoPagamento'],
      empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
    );

    return $this->response->setStatusCode(200)->setJSON($tipoPagamentoEntity->cadastraNovoTipoPagamento($tipoPagamentoEntity));
  }

  /**
   * @param nomeTipoPagamento string
   * @param categoriaTipoPagamento string
   * @param tokenTipoPagamento string
   */
  public function alterarTipoPagamento()
  {
    $dadosRequisicao = $this->request->getJSON(true);

    $tipoPagamentoEntity = new TipoPagamentoEntity(
      tpg_token: $dadosRequisicao['tokenTipoPagamento'],
      tpg_nome: $dadosRequisicao['nomeTipoPagamento'],
      tpg_categoria_pagamento: $dadosRequisicao['categoriaTipoPagamento'],
      empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
    );

    return $this->response->setStatusCode(200)->setJSON($tipoPagamentoEntity->atualizaTipoPagamento($tipoPagamentoEntity));
  }

  /**
   * @param ativoTipoPagamento int (0|1)
   * @param tokenTipoPagamento string
   */
  public function alterarStatusTipoPagamento()
  {
    $dadosRequisicao = $this->request->getJSON(true);

    $tipoPagamentoEntity = new TipoPagamentoEntity(
      tpg_token: $dadosRequisicao['tokenTipoPagamento'],
      tpg_ativo: $dadosRequisicao['ativoTipoPagamento'],
      empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
    );

    return $this->response->setStatusCode(200)->setJSON($tipoPagamentoEntity->alteraDisponibilidadeTipoPagamento($tipoPagamentoEntity));
  }

  public function buscaListaTipoPagamento()
  {
    $tipoPagamentoEntity = new TipoPagamentoEntity(
      empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
    );

    return $this->response->setStatusCode(200)->setJSON($tipoPagamentoEntity->listaTipoPagamentoEmpresa($tipoPagamentoEntity));
  }

  public function buscaListaCategoriaTipoPgamento()
  {
    $tipoPagamentoEntity = new TipoPagamentoEntity();

    return $this->response->setStatusCode(200)->setJSON($tipoPagamentoEntity->listaCategoriaPagamento());
  }
}
