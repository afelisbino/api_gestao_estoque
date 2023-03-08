<?php

namespace App\Entities;

use App\Models\MovimentacaoCaixaModel;

class MovimentacaoCaixaEntity
{

    public function __construct(
        private int|null $mcx_id = null,
        private string|null $mcx_data = null,
        private string|null $mcx_tipo = null,
        private string|null $mcx_comentario = null,
        private float $mcx_valor = 0,
        private EmpresaEntity $empresa = new EmpresaEntity()
    ) {
    }

    public function __get($name)
    {
        return $this->{$name};
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    public function salvaMovimentacaoCaixaManual(MovimentacaoCaixaEntity $movimentacaoCaixaEntity)
    {
        $dadosMovimentacao = [
            'mcx_data' => date('Y-m-d H:i:s'),
            'mcx_valor' => $movimentacaoCaixaEntity->__get('mcx_valor'),
            'mcx_tipo' => $movimentacaoCaixaEntity->__get('mcx_tipo'),
            'mcx_comentario' => $movimentacaoCaixaEntity->__get('mcx_comentario'),
            'emp_id' => $movimentacaoCaixaEntity->__get('empresa')->__get('emp_id')
        ];

        $movimentacaoCaixaModel = new MovimentacaoCaixaModel();

        if ($movimentacaoCaixaModel->save($dadosMovimentacao)) return ['status' => true, 'msg' => "Movimentação salva com sucesso!"];

        return ['status' => false, 'msg' => "Falha ao salvar a movimentação, tente novamente!"];
    }
}
