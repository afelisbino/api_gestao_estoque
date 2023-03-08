<?php

namespace App\Entities;

use App\Libraries\Uuid;
use App\Models\CardapioModel;
use App\Models\CategoriaModel;

class CardapioEntity
{

    public function __construct(
        private int | null $cdp_id = null,
        private string|null $cdp_nome = null,
        private string | null $cdp_token = null,
        private string | null $cdp_descricao = null,
        private float $cdp_valor = 0,
        private bool $cdp_disponivel = true,
        private CategoriaEntity $categoria = new CategoriaEntity(),
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

    public function cadastrarProdutoCardapio(CardapioEntity $cardapioEntity)
    {

        if (!Uuid::is_valid($cardapioEntity->__get('categoria')->__get('cat_token'))) {
            return ['status' => false, 'msg' => "Token categoria inválido!"];
        }

        $categoriaModel = new CategoriaModel();

        $dadosCategoria = $categoriaModel->buscarCategoriaPorToken($cardapioEntity->__get('categoria')->__get('cat_token'));

        if (empty($dadosCategoria)) return ['status' => false, 'msg' => "Categoria selecionada não encontrado!"];

        $cardapioModel = new CardapioModel();

        return $cardapioModel->salvarProdutoCardapio([
            'cdp_nome' => strtolower($cardapioEntity->__get('cdp_nome')),
            'cdp_descricao' => nl2br($cardapioEntity->__get('cdp_descricao')),
            'cdp_valor' => $cardapioEntity->__get('cdp_valor'),
            'cdp_disponivel' => $cardapioEntity->__get('cdp_disponivel'),
            'cdp_token' => Uuid::v4(),
            'cat_id' => $dadosCategoria->cat_id,
            'emp_id' => $cardapioEntity->__get("empresa")->__get('emp_id')
        ]);
    }

    public function alterarProdutoCardapio(CardapioEntity $cardapioEntity)
    {

        if (!Uuid::is_valid($cardapioEntity->__get('categoria')->__get('cat_token'))) {
            return ['status' => false, 'msg' => "Token categoria invalido!"];
        }

        if (!Uuid::is_valid($cardapioEntity->__get('cdp_token'))) {
            return ['status' => false, 'msg' => "Token do produto inválido!"];
        }

        $categoriaModel = new CategoriaModel();

        $dadosCategoria = $categoriaModel->buscarCategoriaPorToken($cardapioEntity->__get('categoria')->__get('cat_token'));

        if (empty($dadosCategoria)) return ['status' => false, 'msg' => "Categoria selecionada não encontrado!"];

        $cardapioModel = new CardapioModel();

        $dadosProdutoCardapio = $cardapioModel->buscarDadosProdutoCardapioPorToken($cardapioEntity->__get('cdp_token'), $cardapioEntity->__get("empresa")->__get('emp_id'));

        if (empty($dadosProdutoCardapio)) return ['status' => false, 'msg' => "Produto selecionado não encontrado!"];

        return $cardapioModel->salvarProdutoCardapio([
            'cdp_id' => $dadosProdutoCardapio->cdp_id,
            'cdp_nome' => strtolower($cardapioEntity->__get('cdp_nome')),
            'cdp_descricao' => $cardapioEntity->__get('cdp_descricao'),
            'cdp_valor' => $cardapioEntity->__get('cdp_valor'),
            'cdp_disponivel' => $cardapioEntity->__get('cdp_disponivel'),
            'cat_id' => $dadosCategoria->cat_id
        ]);
    }

    public function alterarStatusProdutoCardapio(CardapioEntity $cardapioEntity)
    {
        if (!Uuid::is_valid($cardapioEntity->__get('cdp_token'))) {
            return ['status' => false, 'msg' => "Token do produto inválido!"];
        }

        $cardapioModel = new CardapioModel();

        $dadosProdutoCardapio = $cardapioModel->buscarDadosProdutoCardapioPorToken($cardapioEntity->__get('cdp_token'), $cardapioEntity->__get("empresa")->__get('emp_id'));

        if (empty($dadosProdutoCardapio)) return ['status' => false, 'msg' => "Produto selecionado não encontrado!"];

        return $cardapioModel->salvarProdutoCardapio([
            'cdp_id' => $dadosProdutoCardapio->cdp_id,
            'cdp_disponivel' => $cardapioEntity->__get('cdp_disponivel')
        ]);
    }

    public function listarTodosProdutosCardapioEmpresa(CardapioEntity $cardapioEntity)
    {
        $cardapioModel = new CardapioModel();

        $listaProdutosCardapio = $cardapioModel->listarTodosProdutoCardapioEmpresa($cardapioEntity->__get('empresa')->__get('emp_id'));

        $cardapio = [];
        $i = 0;

        foreach ($listaProdutosCardapio as $produto) {
            $cardapio[$i]['cdp_id'] = $produto->cdp_token;
            $cardapio[$i]['cdp_nome'] = ucfirst($produto->cdp_nome);
            $cardapio[$i]['cdp_valor'] = $produto->cdp_valor;
            $cardapio[$i]['cdp_descricao'] = nl2br($produto->cdp_descricao);
            $cardapio[$i]['cdp_disponivel'] = (bool)$produto->cdp_disponivel;
            $cardapio[$i]['cat_id'] = $produto->cat_token;

            $i++;
        }

        return $cardapio;
    }

    public function listarProdutosCardapioAtivosEmpresa(CardapioEntity $cardapioEntity)
    {
        $cardapioModel = new CardapioModel();

        $listaProdutosCardapio = $cardapioModel->listarProdutoCardapioAtivosEmpresa($cardapioEntity->__get('empresa')->__get('emp_id'));

        $cardapio = [];
        $i = 0;

        foreach ($listaProdutosCardapio as $produto) {
            $cardapio[$i]['cdp_id'] = $produto->cdp_token;
            $cardapio[$i]['cdp_nome'] = ucfirst($produto->cdp_nome);
            $cardapio[$i]['cdp_valor'] = $produto->cdp_valor;
            $cardapio[$i]['cat_id'] = $produto->cat_token;

            $i++;
        }

        return $cardapio;
    }
}