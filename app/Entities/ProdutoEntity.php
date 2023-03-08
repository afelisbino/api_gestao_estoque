<?php

namespace App\Entities;

use App\Libraries\Uuid;
use App\Models\CategoriaModel;
use App\Models\FornecedorModel;
use App\Models\ProdutoModel;
use CodeIgniter\Config\Services;

class ProdutoEntity
{
    private $validacao;

    public function __construct(
        private int|null $pro_id = null,
        private string |null $pro_token = null,
        private string|null $pro_nome = null,
        private float $pro_valor_venda = 0,
        private float $pro_preco_custo = 0,
        private string|null $pro_descricao = null,
        private bool $pro_disponivel = true,
        private CategoriaEntity $categoria = new CategoriaEntity(),
        private FornecedorEntity $fornecedor = new FornecedorEntity(),
        private EmpresaEntity $empresa = new EmpresaEntity()
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

    public function cadastrarProduto(ProdutoEntity $produtoEntity): array
    {
        $this->validacao = Services::validation();

        $this->validacao->setRules([
            'pro_nome' => 'required|string',
            'pro_valor_venda' => 'required|numeric',
            'pro_preco_custo' => 'numeric',
            'cat_token' => 'required|string|max_length[36]',
            'frn_token' => 'required|string|max_length[36]',
        ], [
            'pro_nome' => [
                'required' => "Nome do produto não informado!"
            ],
            'pro_valor_venda' => [
                'required' => "Preço de venda não informado!",
                'numeric' => "Precisa ser um valor numerico!"
            ],
            'pro_preco_custo' => [
                'numeric' => "Precisa ser um valor numerico!"
            ],
            'cat_token' => [
                'required' => "Categoria não informado!",
                'string' => "Token da categoria inválido!",
                'max_length[36]' => "Token da categoria inválido!"
            ],
            'frn_token' => [
                'required' => "Fornecedor não informado!",
                'string' => "Token da categoria inválido!",
                'max_length[36]' => "Token da categoria inválido!"
            ]
        ]);

        if (!$this->validacao->run([
            'pro_nome' => $produtoEntity->__get('pro_nome'),
            'pro_valor_venda' => $produtoEntity->__get('pro_valor_venda'),
            'pro_preco_custo' => $produtoEntity->__get('pro_preco_custo'),
            'cat_token' => $produtoEntity->__get('categoria')->__get('cat_token'),
            'frn_token' => $produtoEntity->__get('fornecedor')->__get('frn_token')
        ])) {
            log_message("DEBUG", json_encode($this->validacao->getErrors()));
            return ['status' => false, 'msg' => "Erro ao processar o cadastro, necessita enviar as informações dos campos corretamente!"];
        }

        $categoriaModel = new CategoriaModel();

        $dadosCategoria = $categoriaModel->buscarCategoriaPorToken($produtoEntity->__get('categoria')->__get('cat_token'));

        if (empty($dadosCategoria)) {
            return ['status' => false, 'msg' => "Categoria não encontrado!"];
        }

        $fornecedorModel = new FornecedorModel();

        $dadosFornecedor = $fornecedorModel->buscarFornecedorPorToken($produtoEntity->__get('fornecedor')->__get('frn_token'));

        if (empty($dadosFornecedor)) {
            return ['status' => false, 'msg' => "Fornecedor não encontrado"];
        }

        $dados = [
            'pro_token' => Uuid::v4(),
            'pro_nome' => $produtoEntity->__get('pro_nome'),
            'pro_descricao' => $produtoEntity->__get('pro_descricao'),
            'pro_valor_venda' => $produtoEntity->__get('pro_valor_venda'),
            'pro_preco_custo' => $produtoEntity->__get('pro_preco_custo'),
            'cat_id' => $dadosCategoria->cat_id,
            'frn_id' => $dadosFornecedor->frn_id,
            'emp_id' => $produtoEntity->__get('empresa')->__get('emp_id')
        ];

        $produtoModel = new ProdutoModel();

        return ($produtoModel->save($dados)) ? ['status' => true, 'pro_id' => $produtoModel->getInsertID(), 'pro_token' => $dados['pro_token']] : ['status' => false, 'msg' => "Falha ao cadastrar produto"];
    }

    public function buscarListaProdutosEstoque(EmpresaEntity $empresaEntity): array
    {

        $produtoModel = new ProdutoModel();

        $listaProdutos = $produtoModel->listarProdutosEstoqueEmpresa($empresaEntity->__get('emp_id'));

        $produtos = [];
        $index = 0;

        foreach ($listaProdutos as $produto) {
            $produtos[$index]['pro_id'] = $produto->pro_token;
            $produtos[$index]['pro_nome'] = $produto->pro_nome;
            $produtos[$index]['pro_descricao'] = $produto->pro_descricao;
            $produtos[$index]['pro_disponivel'] = (bool) $produto->pro_disponivel;
            $produtos[$index]['pro_valor_venda'] = $produto->pro_valor_venda;
            $produtos[$index]['pro_preco_custo'] = $produto->pro_preco_custo;
            $produtos[$index]['cat_token'] = $produto->cat_token;
            $produtos[$index]['frn_token'] = $produto->frn_token;
            $produtos[$index]['est_qtd_atual'] = $produto->est_qtd_atual;
            $produtos[$index]['est_qtd_minimo'] = $produto->est_qtd_minimo;

            $index++;
        }

        return $produtos;
    }

    public function alterarDadosProduto(ProdutoEntity $produtoEntity): array
    {

        if (!Uuid::v4($produtoEntity->__get('pro_token'))) return ['status' => false, 'msg' => 'Token do produto inválido'];

        $this->validacao = Services::validation();

        $this->validacao->setRules([
            'pro_token' => 'required|string|max_length[36]',
            'pro_nome' => 'required|string',
            'pro_valor_venda' => 'required|numeric',
            'pro_preco_custo' => 'numeric',
            'cat_token' => 'required|string|max_length[36]',
            'frn_token' => 'required|string|max_length[36]',
        ], [
            'pro_token' => [
                'required' => "Token do produto não informado!",
                'string' => "Token do produto não inválido!",
                'max_length[36]' => "Token do produto inválido!",
            ],
            'pro_nome' => [
                'required' => "Nome do produto não informado!"
            ],
            'pro_valor_venda' => [
                'required' => "Preço de venda não informado!",
                'numeric' => "Precisa ser um valor numerico!"
            ],
            'pro_preco_custo' => [
                'numeric' => "Precisa ser um valor numerico!"
            ],
            'cat_token' => [
                'required' => "Categoria não informado!",
                'string' => "Token da categoria inválido!",
                'max_length[36]' => "Token da categoria inválido!"
            ],
            'frn_token' => [
                'required' => "Fornecedor não informado!",
                'string' => "Token da categoria inválido!",
                'max_length[36]' => "Token da categoria inválido!"
            ]
        ]);

        if (!$this->validacao->run([
            'pro_token' => $produtoEntity->__get('pro_token'),
            'pro_nome' => $produtoEntity->__get('pro_nome'),
            'pro_valor_venda' => $produtoEntity->__get('pro_valor_venda'),
            'pro_preco_custo' => $produtoEntity->__get('pro_preco_custo'),
            'cat_token' => $produtoEntity->__get('categoria')->__get('cat_token'),
            'frn_token' => $produtoEntity->__get('fornecedor')->__get('frn_token')
        ])) {
            log_message("DEBUG", json_encode($this->validacao->getErrors()));
            return ['status' => false, 'msg' => "Erro ao processar o cadastro, necessita enviar as informações dos campos corretamente!"];
        }

        $categoriaModel = new CategoriaModel();

        $dadosCategoria = $categoriaModel->buscarCategoriaPorToken($produtoEntity->__get('categoria')->__get('cat_token'));

        if (empty($dadosCategoria)) {
            return ['status' => false, 'msg' => "Categoria não encontrado!"];
        }

        $fornecedorModel = new FornecedorModel();

        $dadosFornecedor = $fornecedorModel->buscarFornecedorPorToken($produtoEntity->__get('fornecedor')->__get('frn_token'));

        if (empty($dadosFornecedor)) {
            return ['status' => false, 'msg' => "Fornecedor não encontrado"];
        }

        $produtoModel = new ProdutoModel();

        $dadosProduto = $produtoModel->buscarProdutoEstoquePorToken($produtoEntity->__get('pro_token'));

        if (empty($dadosProduto)) return ['status' => false, 'msg' => "Produto não encontrado!"];

        $dados = [
            'pro_id' => $dadosProduto->pro_id,
            'pro_nome' => $produtoEntity->__get('pro_nome'),
            'pro_descricao' => $produtoEntity->__get('pro_descricao'),
            'pro_valor_venda' => $produtoEntity->__get('pro_valor_venda'),
            'pro_preco_custo' => $produtoEntity->__get('pro_preco_custo'),
            'cat_id' => $dadosCategoria->cat_id,
            'frn_id' => $dadosFornecedor->frn_id,
        ];

        return $produtoModel->save($dados) ? ['status' => true, 'msg' => "Produto alterado com sucesso!"] : ['status' => false, 'msg' => "Falha ao salvar alterações do produto!"];
    }

    public function buscarDadosProduto(ProdutoEntity $produtoEntity): array
    {

        if (!Uuid::is_valid($produtoEntity->__get('pro_token'))) return ['status' => false, 'msg' => "Token do produto inválido!"];

        $produtoModel = new ProdutoModel();

        $recuperaDadosProduto = $produtoModel->buscarProdutoEstoquePorToken($produtoEntity->__get('pro_token'));

        return empty($recuperaDadosProduto) ? [] : [
            'pro_id' => $recuperaDadosProduto->pro_token,
            'pro_nome' => $recuperaDadosProduto->pro_nome,
            'pro_descricao' => $recuperaDadosProduto->pro_descricao,
            'pro_disponivel' => (bool) $recuperaDadosProduto->pro_disponivel,
            'pro_valor_venda' => $recuperaDadosProduto->pro_valor_venda,
            'pro_preco_custo' => $recuperaDadosProduto->pro_preco_custo,
            'frn_token' => $recuperaDadosProduto->frn_token,
            'cat_token' => $recuperaDadosProduto->cat_token,
            'est_qtd_atual' => $recuperaDadosProduto->est_qtd_atual,
            'est_qtd_minimo' => $recuperaDadosProduto->est_qtd_minimo
        ];
    }

    public function alterarStatusProduto(ProdutoEntity $produtoEntity)
    {

        if (!Uuid::v4($produtoEntity->__get('pro_token'))) return ['status' => false, 'msg' => 'Token do produto inválido'];

        $produtoModel = new ProdutoModel();

        $dadosProduto = $produtoModel->buscarProdutoEstoquePorToken($produtoEntity->__get('pro_token'));

        if (empty($dadosProduto)) return ['status' => false, 'msg' => "Produto não encontrado!"];

        $dados = ['pro_id' => $dadosProduto->pro_id, 'pro_disponivel' => $produtoEntity->pro_disponivel];

        return $produtoModel->save($dados) ? ['status' => true, 'msg' => "Produto alterado com sucesso!"] : ['status' => false, 'msg' => "Falha ao salvar alterações do produto!"];
    }

    public function buscaListaProdutoAtivosEmpresa(EmpresaEntity $empresaEntity)
    {
        $produtoModel = new ProdutoModel();

        $listaProdutosAtivosEmpresa = $produtoModel->listaProdutoAtivoEmpresa($empresaEntity->__get('emp_id'));

        $codigoBarrasEntity = new CodigoBarrasProdutoEntity();

        $listaProdutos = [];
        $index = 0;

        foreach ($listaProdutosAtivosEmpresa as $produtos) {
            $listaProdutos[$index]['pro_id'] = $produtos->pro_token;
            $listaProdutos[$index]['pro_nome'] = $produtos->pro_nome;
            $listaProdutos[$index]['pro_valor'] = $produtos->pro_valor_venda;
            $listaProdutos[$index]['cat_token'] = $produtos->cat_token;
            $listaProdutos[$index]['frn_token'] = $produtos->frn_token;
            $listaProdutos[$index]['est_qtd_atual'] = $produtos->est_qtd_atual;

            $codigoBarrasEntity->__set('produto', new ProdutoEntity(pro_token: $produtos->pro_token));

            $listaCodigoBarrasProdutos = $codigoBarrasEntity->listarCodigosBarrasProduto($codigoBarrasEntity);

            $listaProdutos[$index]['pro_codigos'] = $listaCodigoBarrasProdutos;

            $index++;
        }

        return $listaProdutos;
    }

    public function buscaIdProduto(ProdutoEntity $produtoEntity)
    {
        if (!Uuid::is_valid($produtoEntity->__get('pro_token'))) return ['status' => false, 'msg' => "Token do produto inválido!"];

        $produtoModel = new ProdutoModel();

        $recuperaDadosProduto = $produtoModel->buscarProdutoEstoquePorToken($produtoEntity->__get('pro_token'));

        if (empty($recuperaDadosProduto)) return 0;

        return $recuperaDadosProduto->pro_id;
    }
}
