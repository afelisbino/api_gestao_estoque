<?php

namespace App\Controllers;

ini_set('post_max_size', '900M');
ini_set('max_execution_time', 9000);

use App\Controllers\BaseController;
use App\Entities\EmpresaEntity;
use App\Entities\TipoPagamentoEntity;
use App\Entities\VendaEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ServicoController extends BaseController
{
  public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
  {
    parent::initController(
      $request,
      $response,
      $logger
    );
  }

  public function criarFormasPagamentoPadraoEmpresas()
  {
    $empresaEntity = new EmpresaEntity();
    $tipoPagamentoEntity = new TipoPagamentoEntity();

    $log = [];
    $index = 0;

    foreach ($empresaEntity->listarEmpresasAtivas() as $empresa) {
      $empresaEntity->__set('emp_id', $empresa['emp_id']);

      $log[$index]['empresa'] = $empresa['emp_id'];

      $log[$index]['cadastro'] = $tipoPagamentoEntity->cadastraFormasPagamentoPadrao($empresaEntity);

      $index++;
    }

    return $this->response->setStatusCode(200, "Sucesso")->setJSON($log);
  }

  public function corrigiFormasPagamentoVendasExistente()
  {
    $vendaEntity = new VendaEntity();
    $empresaEntity = new EmpresaEntity();

    $listaEmpresasAtivas = $empresaEntity->listarEmpresasAtivas();

    $log = [];
    $index = 0;

    foreach ($listaEmpresasAtivas as $empresa) {
      $log[$index]['empresa'] = $empresa['emp_id'];

      $listaVenda = $vendaEntity->buscaListaVendaEmpresaTipoPagamento(
        new EmpresaEntity(emp_id: $empresa['emp_id'])
      );

      $indexVenda = 0;

      foreach ($listaVenda as $venda) {
        $log[$index][$indexVenda]['venda'] = $venda->ven_id;

        if ($vendaEntity->atualizaFormasPagamentoVendas(
          $venda->ven_id,
          empty($venda->ven_tipo_pagamento) ? 'dinheiro' : $venda->ven_tipo_pagamento,
          $venda->ven_total,
          new EmpresaEntity(emp_id: $empresa['emp_id'])
        )) {

          $log[$index][$indexVenda]['formaPagamento'] = 'Forma de pagamento salvo com sucesso!';

          if (
            $vendaEntity->alteraTipoPagamentoVendaParaDesabilitado(
              $venda->ven_id
            )
          ) {
            $log[$index][$indexVenda]['alteraStatus'] = 'Tipo de pagamento alterado para desabilitado!';
          } else {
            $log[$index][$indexVenda]['alteraStatus'] = 'Falha ao alterar tipo de pagamento da venda';
          }
        } else {
          $log[$index][$indexVenda]['formaPagamento'] = 'Falha ao salvar forma de pagamento!';
        }

        $indexVenda++;
      }
      $index++;
    }

    return $this->response->setStatusCode(200, 'Sucesso')->setJSON($log);
  }
}
