<?php

namespace App\Entities;

use App\Libraries\Uuid;
use App\Models\CategoriaModel;

class CategoriaEntity
{

    public function __construct(
        private int | null $cat_id = null,
        private string | null $cat_token = null,
        private string | null $cat_nome = null,
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

    public function cadastrarCategoria(CategoriaEntity $categoriaEntity): array
    {
        $categoriaModel = new CategoriaModel();

        return $categoriaModel->salvarCategoria([
            'cat_nome' => strtolower($categoriaEntity->__get('cat_nome')),
            'emp_id' => $categoriaEntity->__get('empresa')->__get('emp_id'),
            'cat_token' => Uuid::v4()
        ]);
    }

    public function alterarCategoria(CategoriaEntity $categoriaEntity): array
    {
        if (!Uuid::is_valid($categoriaEntity->__get('cat_token'))) {
            return ['status' => false, 'msg' => "Token categoria invalido"];
        }

        $categoriaModel = new CategoriaModel();

        $recuperaDadosCategoria = $categoriaModel->buscarCategoriaPorToken($categoriaEntity->__get('cat_token'));

        return $categoriaModel->salvarAlteracoesCategoria([
            'cat_id' => $recuperaDadosCategoria->cat_id,
            'cat_nome' => strtolower(
                $categoriaEntity->__get('cat_nome')
            )
        ]);
    }

    public function excluirCategoria(CategoriaEntity $categoriaEntity): array
    {
        if (!Uuid::is_valid($categoriaEntity->__get('cat_token'))) {
            return ['status' => false, 'msg' => "Token categoria invalido"];
        }

        $categoriaModel = new CategoriaModel();

        $recuperaDadosCategoria = $categoriaModel->buscarCategoriaPorToken($categoriaEntity->__get('cat_token'));

        return $categoriaModel->deletarCategoria($recuperaDadosCategoria->cat_id);
    }

    public function listarCategoriaProdutoEmpresa(CategoriaEntity $categoriaEntity): array
    {
        $categoriaModel = new CategoriaModel();

        $listaCategorias = $categoriaModel->listarCategoria($categoriaEntity->__get('empresa')->__get('emp_id'));

        $categorias = [];
        $i = 0;

        foreach ($listaCategorias as $categoria) {
            $categorias[$i]['cat_id'] = $categoria->cat_token;
            $categorias[$i]['cat_nome'] = ucfirst($categoria->cat_nome);

            $i++;
        }

        return $categorias;
    }

    public function buscarDadosCategoria(CategoriaEntity $categoriaEntity): array
    {
        if (!Uuid::is_valid($categoriaEntity->__get('cat_token'))) {
            return ['status' => false, 'msg' => "Token categoria invalido"];
        }

        $categoriaModel = new CategoriaModel();

        $dadoCategoria = $categoriaModel->buscarCategoriaPorToken($categoriaEntity->__get('cat_token'));

        if (empty($dadoCategoria)) return [];

        return [
            'cat_id' => $dadoCategoria->cat_token,
            'cat_nome' => $dadoCategoria->cat_nome
        ];
    }
}
