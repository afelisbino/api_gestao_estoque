<?php

namespace App\Entities;

use App\Libraries\Uuid;
use App\Models\CodigoBarrasProdutoModel;
use App\Models\ProdutoModel;
use CodeIgniter\Config\Services;

class CodigoBarrasProdutoEntity
{
    private $validacao;

    public function __construct(
        private int|null $pcb_id = null,
        private string|null $pcb_token = null,
        private string|null $pcb_codigo = null,
        private ProdutoEntity $produto = new ProdutoEntity()
    ) {
    }

    public function __set(string $name, $value)
    {
        $this->{$name} = $value;
    }

    public function __get(string $name)
    {
        return $this->{$name};
    }

    public function cadastrarCodigoBarrasProduto(CodigoBarrasProdutoEntity $codigoBarrasProdutoEntity): array
    {
        if (!Uuid::v4($codigoBarrasProdutoEntity->__get('produto')->__get('pro_token'))) return ['status' => false, 'msg' => 'Token do produto inválido'];

        $this->validacao = Services::validation();

        $this->validacao->setRules([
            'pcb_codigo' => 'required',
            'pro_token' => 'required|max_length[36]'
        ], [
            'pcb_codigo' => [
                'required' => "Codigo de barras do produto não informado!"
            ],
            'pro_token' => [
                'required' => "Produto não informado!",
                'max_length[36]' => "Token do produto inválido!"
            ]
        ]);

        if (!$this->validacao->run([
            'pcb_codigo' => $codigoBarrasProdutoEntity->__get('pcb_codigo'),
            'pro_token' => $codigoBarrasProdutoEntity->__get('produto')->__get('pro_token')
        ])) {
            log_message("DEBUG", json_encode($this->validacao->getErrors()));
            return ['status' => false, 'msg' => "Erro ao processar o cadastro, necessita enviar as informações dos campos corretamente!"];
        };

        $produtoModel = new ProdutoModel();

        $dadosProduto = $produtoModel->buscarProdutoEstoquePorToken($codigoBarrasProdutoEntity->__get('produto')->__get('pro_token'));

        if (empty($dadosProduto)) return ['status' => false, 'msg' => 'Produto não encontrado!'];

        $produtoCodigoBarraExistente = $produtoModel->buscarProdutoEstoquePorCodigoBarras($codigoBarrasProdutoEntity->__get('pcb_codigo'), $codigoBarrasProdutoEntity->__get('produto')->__get('empresa')->__get('emp_id'));

        if (!empty($produtoCodigoBarraExistente)) return ['status' => false, 'msg' => "Codigo de barras já existe no produto {$produtoCodigoBarraExistente->pro_nome}"];

        $codigoBarrasProdutoModel = new CodigoBarrasProdutoModel();

        return ($codigoBarrasProdutoModel->save(
            ['pcb_token' => Uuid::v4(), 'pcb_codigo' => $codigoBarrasProdutoEntity->__get('pcb_codigo'), 'pro_id' => $dadosProduto->pro_id]
        )) ? ['status' => true, 'msg' => "Codigo de barra vinculado com sucesso"] : ['status' => false, 'msg' => "Falha ao vincular o codigo {$codigoBarrasProdutoEntity->__get('pcb_codigo')} neste produto!"];
    }

    public function listarCodigosBarrasProduto(CodigoBarrasProdutoEntity $codigoBarrasProdutoEntity): array
    {
        if (!Uuid::is_valid($codigoBarrasProdutoEntity->__get('produto')->__get('pro_token'))) return ['status' => false, 'msg' => "Token do produto inválido!"];

        $codigoBarrasProdutoModel = new CodigoBarrasProdutoModel();

        $listaCodigoBarras = $codigoBarrasProdutoModel->buscaListaCodigoBarrasProduto($codigoBarrasProdutoEntity->__get('produto')->__get('pro_token'));

        if (empty($listaCodigoBarras)) return [];

        $codigoBarrasProduto = [];
        $index = 0;

        foreach ($listaCodigoBarras as $codigoBarras) {

            $codigoBarrasProduto[$index]['pcb_id'] = $codigoBarras->pcb_token;
            $codigoBarrasProduto[$index]['pcb_codigo'] = $codigoBarras->pcb_codigo;

            $index++;
        }

        return $codigoBarrasProduto;
    }

    public function excluirCodigoBarrasProduto(CodigoBarrasProdutoEntity $codigoBarrasProdutoEntity): array
    {

        if (!Uuid::is_valid($codigoBarrasProdutoEntity->__get('pcb_token'))) return ['status' => false, 'msg' => "Token do codigo de barras inválido!"];

        $codigoBarrasProdutoModel = new CodigoBarrasProdutoModel();

        $dadoCodigoBarrasProduto = $codigoBarrasProdutoModel->buscarCodigoBarrasProdutoPorToken($codigoBarrasProdutoEntity->__get('pcb_token'));

        if (empty($dadoCodigoBarrasProduto)) return ['status' => false, 'msg' => "Codigo de barras não encontrado!"];

        if ($codigoBarrasProdutoModel->delete($dadoCodigoBarrasProduto->pcb_id)) {
            return ['status' => true, 'msg' => "Codigo de barra excluido com sucesso!"];
        } else {
            return ['status' => false, 'msg' => "Falha ao excluir codigo de barras do produto!"];
        }
    }
}
