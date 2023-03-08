<?php

namespace App\Entities;

use App\Entities\EmpresaEntity;
use App\Libraries\Uuid;
use App\Models\EmpresaModel;
use App\Models\PessoaModel;

class PessoaEntity
{

    public function __construct(
        private int | null $pes_id = null,
        private string | null $pes_nome = null,
        private string | null $pes_token = null,
        private EmpresaEntity $empresa = new EmpresaEntity()
    ) {
    }

    public function __get($nomeParametro)
    {
        return $this->{$nomeParametro};
    }

    public function __set($nomeParametro, $valorParametro): void
    {
        $this->{$nomeParametro} = $valorParametro;
    }

    public function salvarPessoa(PessoaEntity $pessoaEntity): array
    {

        if (!Uuid::is_valid($pessoaEntity->__get('empresa')->__get('emp_token'))) {
            return ['status' => false, 'msg' => 'Token invalido'];
        }

        $pessoaModel = new PessoaModel();

        $empresaModel = new EmpresaModel();

        $dadosEmpresa = $empresaModel->buscarEmpresaPorToken($pessoaEntity->__get('empresa')->__get('emp_token'));

        if (empty($dadosEmpresa)) {
            return ['status' => false, 'msg' => 'Empresa não encontrada'];
        }

        return $pessoaModel->cadastrarPessoa([
            'pes_token' => Uuid::v4(),
            'pes_nome' => strtolower($pessoaEntity->__get('pes_nome')),
            'emp_id' => $dadosEmpresa->emp_id
        ]);
    }
}
