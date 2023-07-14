<?php

namespace App\Entities;

use App\Libraries\Encryption;
use App\Libraries\Uuid;
use App\Models\EmpresaModel;

class EmpresaEntity
{
    public function __construct(
        private int | null $emp_id = null,
        private string | null $emp_doc = null,
        private string | null $emp_nome = null,
        private int | bool $emp_ativo = true,
        private string | null $emp_token = null
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

    public function cadastrarEmpresa(EmpresaEntity $empresaEntity)
    {
        $empresaModel = new EmpresaModel();

        $dadosEmpresa = [
            'emp_token' => Uuid::v4(),
            'emp_nome' => Encryption::encrypt($empresaEntity->__get('emp_nome'), Encryption::lerChaveArquivo()),
            'emp_doc' => Encryption::encrypt($empresaEntity->__get('emp_doc'), Encryption::lerChaveArquivo()),
            'emp_ativo' => $empresaEntity->__get('emp_ativo')
        ];

        if ($empresaModel->save($dadosEmpresa)) {
            $empresaEntity->__set('emp_id', $empresaModel->getInsertID());

            $tipoPagamentoEntity = new TipoPagamentoEntity(empresa: $empresaEntity);

            $tipoPagamentoEntity->cadastraFormasPagamentoPadrao($tipoPagamentoEntity->__get('empresa'));

            return ['status' => true, 'msg' => "Empresa salvo com sucesso!"];
        }

        return ['status' => false, 'msg' => "Falha ao salvar nova empresa", 'error' => $empresaModel->errors()];
    }

    public function alterarDadosEmpresa(EmpresaEntity $empresaEntity)
    {
        $empresaModel = new EmpresaModel();

        $dadosEmpresa = $empresaModel->buscarEmpresaPorToken($empresaEntity->__get('emp_token'));

        $empNome = (empty($empresaEntity->__get('emp_nome'))) ? $dadosEmpresa->emp_nome : Encryption::encrypt($empresaEntity->__get('emp_nome'), Encryption::lerChaveArquivo());
        $empDoc = (empty($empresaEntity->__get('emp_doc'))) ? $dadosEmpresa->emp_doc : Encryption::encrypt($empresaEntity->__get('emp_doc'), Encryption::lerChaveArquivo());

        $dados = [
            'emp_id' => $dadosEmpresa->emp_id,
            'emp_nome' => $empNome,
            'emp_doc' => $empDoc,
        ];

        return $empresaModel->salvarAlteracoesEmpresa($dados);
    }

    public function listarEmpresasAtivas()
    {
        $empresaModel = new EmpresaModel();

        $listaEmpresaAtivo = $empresaModel->where('emp_ativo', 1)->find();

        return $listaEmpresaAtivo;
    }
}
