<?php

namespace App\Entities;

use App\Libraries\Uuid;
use App\Models\TipoPagamentoModel;

class TipoPagamentoEntity
{

  public function __construct(
    private int|null $tpg_id = null,
    private string|null $tpg_nome = null,
    private string|null $tpg_token = null,
    private int|bool $tpg_ativo = false,
    private string|null $tpg_categoria_pagamento = null,
    private EmpresaEntity $empresa = new EmpresaEntity()
  ) {
  }

  public function __set($name, $value)
  {
    $this->{$name} = $value;
  }

  public function __get($name)
  {
    return $this->{$name};
  }

  public function buscaTipoPagamentoId($tokenTipoPagamento, $empresaId)
  {
    $tipoPagamentoModel = new TipoPagamentoModel();

    return empty($tokenTipoPagamento) ?
      null :
      $tipoPagamentoModel->where('tpg_token', $tokenTipoPagamento)->where('emp_id', $empresaId)->findColumn('tpg_id');
  }

  public function cadastraNovoTipoPagamento(TipoPagamentoEntity $tipoPagamentoEntity)
  {
    $tipoPagamentoModel = new TipoPagamentoModel();

    $salvaTipoPagamento = $tipoPagamentoModel->save([
      'tpg_nome' => $tipoPagamentoEntity->__get('tpg_nome'),
      'tpg_token' => Uuid::v4(),
      'tpg_categoria_pagamento' => $tipoPagamentoEntity->__get('tpg_categoria_pagamento'),
      'tpg_ativo' => 1,
      'emp_id' => $tipoPagamentoEntity->__get('empresa')->__get('emp_id')
    ]);

    if (!$salvaTipoPagamento) {
      log_message('ERROR', json_encode($tipoPagamentoModel->errors()));
      return [
        'status' => false,
        'msg' => 'Não foi possível salvar o tipo de categoria'
      ];
    }

    return [
      'status' => true,
      'msg' => "Tipo de pagamento cadastrado com sucesso!"
    ];
  }

  public function atualizaTipoPagamento(TipoPagamentoEntity $tipoPagamentoEntity)
  {

    $tipoPagamentoModel = new TipoPagamentoModel();

    $tipoPagamentoId = $this->buscaTipoPagamentoId($tipoPagamentoEntity->__get('tpg_token'), $tipoPagamentoEntity->__get('empresa')->__get('emp_id'));

    if (empty($tipoPagamentoId)) return ['status' => false, 'msg' => 'Tipo de pagamento não encontrado!'];

    $salvaTipoPagamento = $tipoPagamentoModel->save([
      'tpg_id' => $tipoPagamentoId,
      'tpg_token' => $tipoPagamentoEntity->__get('tpg_token'),
      'tpg_categoria_pagamento' => $tipoPagamentoEntity->__get('tpg_categoria_pagamento'),
      'tpg_nome' => $tipoPagamentoEntity->__get('tpg_nome')
    ]);

    if (!$salvaTipoPagamento) {
      log_message('ERROR', json_encode($tipoPagamentoModel->errors()));
      return [
        'status' => false,
        'msg' => 'Não foi possível salvar o tipo de categoria'
      ];
    }

    return [
      'status' => true,
      'msg' => "Tipo de pagamento atualizado com sucesso!"
    ];
  }

  public function alteraDisponibilidadeTipoPagamento(TipoPagamentoEntity $tipoPagamentoEntity)
  {
    $tipoPagamentoModel = new TipoPagamentoModel();

    $tipoPagamentoId = $this->buscaTipoPagamentoId($tipoPagamentoEntity->__get('tpg_token'), $tipoPagamentoEntity->__get('empresa')->__get('emp_id'));

    if (empty($tipoPagamentoId)) return ['status' => false, 'msg' => 'Tipo de pagamento não encontrado!'];

    $salvaTipoPagamento = $tipoPagamentoModel->save([
      'tpg_id' => $tipoPagamentoId,
      'tpg_ativo' => $tipoPagamentoEntity->__get('tpg_ativo')
    ]);

    if (!$salvaTipoPagamento) {
      log_message('ERROR', json_encode($tipoPagamentoModel->errors()));
      return [
        'status' => false,
        'msg' => 'Não foi possível salvar o tipo de categoria'
      ];
    }

    return [
      'status' => true,
      'msg' => "Tipo de pagamento atualizado com sucesso!"
    ];
  }

  public function listaTipoPagamentoEmpresa(TipoPagamentoEntity $tipoPagamentoEntity)
  {
    $tipoPagamentoModel = new TipoPagamentoModel();

    $listaTipoPagamento = $tipoPagamentoModel->where('emp_id', $tipoPagamentoEntity->__get('empresa')->__get('emp_id'))->findAll();

    $retornaLista = [];
    $index = 0;

    foreach ($listaTipoPagamento as $tipoPagamento) {
      $retornaLista[$index]['token'] = $tipoPagamento['tpg_token'];
      $retornaLista[$index]['status'] = (int)$tipoPagamento['tpg_ativo'];
      $retornaLista[$index]['nome'] = ucfirst($tipoPagamento['tpg_nome']);
      $retornaLista[$index]['categoria'] = $tipoPagamento['tpg_categoria_pagamento'];

      $index++;
    }

    return $retornaLista;
  }

  public function listaCategoriaPagamento()
  {
    return [
      [
        'codigo' => '01',
        'nome' => 'Dinheiro',
      ],
      [
        'codigo' => '02',
        'nome' => 'Cheque',
      ],
      [
        'codigo' => '03',
        'nome' => 'Cartão de Crédito',
      ],
      [
        'codigo' => '04',
        'nome' => 'Cartão de Débito',
      ],
      [
        'codigo' => '05',
        'nome' => 'Crédito Loja',
      ],
      [
        'codigo' => '10',
        'nome' => 'Vale Alimentação',
      ],
      [
        'codigo' => '11',
        'nome' => 'Vale Refeição',
      ],
      [
        'codigo' => '12',
        'nome' => 'Vale Presente',
      ],
      [
        'codigo' => '13',
        'nome' => 'Vale Combustível',
      ],
      [
        'codigo' => '99',
        'nome' => 'Outros',
      ]
    ];
  }
}
