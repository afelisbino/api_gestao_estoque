<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoPagamentoModel extends Model
{

  protected $table      = 'tipo_pagamento';
  protected $primaryKey = 'tpg_id';

  protected $useAutoIncrement = true;

  protected $returnType     = 'array';
  protected $useSoftDeletes = false;

  protected $validationRules = [
    'tpg_nome' => "required_with[tpg_token]|string|",
    'tpg_token' => 'required|string|is_valid',
    'tpg_categoria_pagamento' => "required_with[tpg_token]|in_list[01, 02, 03, 04, 05, 10, 11, 12, 13, 99]",
    'emp_id' => 'required_with[tpg_nome, tpg_categoria_pagamento, tpg_token]|required_without[tpg_id]'
  ];
  protected $validationMessages = [
    'tpg_nome' => [
      'required_with' => 'O nome do tipo de pagamento é obrigatório!',
    ],
    'tpg_token' => [
      'required' => 'O token do tipo de pagamento é obrigatório!',
      'is_valid' => 'O token não é válido!'
    ],
    'tpg_categoria_pagamento' => [
      'required_with' => 'Categoria do pagamento é obrigatório!',
      'in_list' => 'Categoria do tipo de pagamento não se encaixa nos tipos válidos'
    ],
    'emp_id' => [
      'required_with' => 'Empresa não vinculada!',
      'required_without' => 'Empresa não vinculada!',
    ]
  ];

  protected $allowedFields = ['tpg_nome', 'tpg_token', 'tpg_ativo', 'tpg_categoria_pagamento', 'emp_id'];

  protected $useTimestamps = false;

  public function existeCadastroTipoPagamentoEmpresa(string $categoriaPagamento, int $empresaId): bool
  {
    $this->where('tpg_categoria_pagamento', $categoriaPagamento);
    $this->where('emp_id', $empresaId);

    $existeTipoPagamento = $this->get()->getRow();

    return empty($existeTipoPagamento) ? false : true;
  }
}
