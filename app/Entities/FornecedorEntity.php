<?php

namespace App\Entities;

use App\Libraries\Encryption;
use App\Libraries\Uuid;
use App\Models\FornecedorModel;

class FornecedorEntity
{
    public function __construct(
        private int | null $frn_id = null,
        private string | null $frn_nome = null,
        private string | null $frn_doc = null,
        private string | null $frn_token = null,
        private EmpresaEntity $empresa = new EmpresaEntity()
    ) {
        helper('documento');
    }

    public function __get($nomeParametro)
    {
        return $this->{$nomeParametro};
    }

    public function __set($nomeParametro, $valorParametro): void
    {
        $this->{$nomeParametro} = $valorParametro;
    }

    public function cadastrarFornecedor(FornecedorEntity $fornecedorEntity): array
    {
        $fornecedorModel = new FornecedorModel();

        $documento = empty($fornecedorEntity->__get('frn_doc')) ? null : Encryption::encrypt(removerCaracteres($fornecedorEntity->__get('frn_doc')), Encryption::lerChaveArquivo());

        return $fornecedorModel->salvarFornecedor([
            'frn_nome' => Encryption::encrypt(strtolower($fornecedorEntity->__get('frn_nome')), Encryption::lerChaveArquivo()),
            'frn_doc' => $documento,
            'frn_token' => Uuid::v4(),
            'emp_id' => $fornecedorEntity->__get('empresa')->__get('emp_id')
        ]);
    }

    public function alterarFornecedor(FornecedorEntity $fornecedorEntity): array
    {
        if (!Uuid::is_valid($fornecedorEntity->__get('frn_token'))) {
            return ['status' => false, 'msg' => "Token do fornecedor inválido"];
        }

        $fornecedorModel = new FornecedorModel();

        $recuperaDadosFornecedor = $fornecedorModel->buscarFornecedorPorToken($fornecedorEntity->__get('frn_token'));

        if (empty($recuperaDadosFornecedor)) return ['status' => false, 'msg' => "Nenhum fornecedor encontrado"];

        $documento = empty($fornecedorEntity->__get('frn_doc')) ? null : Encryption::encrypt(removerCaracteres($fornecedorEntity->__get('frn_doc')), Encryption::lerChaveArquivo());

        return $fornecedorModel->salvarFornecedor([
            'frn_id' => $recuperaDadosFornecedor->frn_id,
            'frn_nome' => Encryption::encrypt(strtolower($fornecedorEntity->__get('frn_nome')), Encryption::lerChaveArquivo()),
            'frn_doc' => $documento
        ]);
    }

    public function excluirFornecedor(FornecedorEntity $fornecedorEntity): array
    {
        if (!Uuid::is_valid($fornecedorEntity->__get('frn_token'))) {
            return ['status' => false, 'msg' => "Token do fornecedor inválido"];
        }

        $fornecedorModel = new FornecedorModel();

        $recuperaDadosFornecedor = $fornecedorModel->buscarFornecedorPorToken($fornecedorEntity->__get('frn_token'));

        if (empty($recuperaDadosFornecedor)) return ['status' => false, 'msg' => "Nenhum fornecedor encontrado"];

        return $fornecedorModel->deletarFornecedor($recuperaDadosFornecedor->frn_id);
    }

    public function listarFornecedores(FornecedorEntity $fornecedorEntity): array
    {
        $fornecedorModel = new FornecedorModel();

        $listaFornecedores = $fornecedorModel->buscarListaFornecedores($fornecedorEntity->__get('empresa')->__get('emp_id'));

        helper('documento');

        $fornecedores = [];
        $i = 0;

        foreach ($listaFornecedores as $fornecedor) {
            $fornecedores[$i]['frn_id'] = $fornecedor->frn_token;
            $fornecedores[$i]['frn_nome'] = ucfirst(Encryption::decrypt($fornecedor->frn_nome, Encryption::lerChaveArquivo()));
            $fornecedores[$i]['frn_doc'] = empty($fornecedor->frn_doc) ? "--" : mascararCnpj(Encryption::decrypt($fornecedor->frn_doc, Encryption::lerChaveArquivo()));

            $i++;
        }

        return $fornecedores;
    }

    public function buscarDadosFornecedor(FornecedorEntity $fornecedorEntity): array
    {
        $fornecedorModel = new FornecedorModel();

        $dadosFornecedor = $fornecedorModel->buscarFornecedorPorToken($fornecedorEntity->__get('frn_token'));

        if (empty($dadosFornecedor)) return [];

        return [
            'frn_id' => $dadosFornecedor->frn_token,
            'frn_nome' => strtolower(Encryption::decrypt($dadosFornecedor->frn_nome, Encryption::lerChaveArquivo())),
            'frn_doc' => empty($dadosFornecedor->frn_doc) ? null : Encryption::decrypt($dadosFornecedor->frn_doc, Encryption::lerChaveArquivo())
        ];
    }
}
