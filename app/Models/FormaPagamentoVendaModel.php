<?php

namespace App\Models;

use CodeIgniter\Model;

class FormaPagamentoVendaModel extends Model
{
  protected $table = 'forma_pagamento_venda';
  protected $primaryKey = 'fpv_id';

  protected $useAutoIncrement = true;

  protected $returnType     = 'array';
  protected $useSoftDeletes = false;
  protected $allowedFields = ['ven_id', 'tpg_id', 'fpv_valor_pago'];

  protected $validationRules = [
    'ven_id' => "required",
    'tpg_id' => 'required',
    'fpv_valor_pago' => 'required'
  ];
  protected $validationMessages = [
    'ven_id' => [
      'required' => 'Venda não encontrado!',
    ],
    'tpg_id' => [
      'required' => 'O tipo de pagamento não informado!',
    ],
    'fpv_valor_pago' => [
      'required' => 'O valor pago não informado!',
    ]
  ];

  protected $useTimestamps = false;
}
