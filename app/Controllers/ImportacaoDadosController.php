<?php


namespace App\Controllers;

ini_set('post_max_size', '900M');
ini_set('max_execution_time', 9000);

use App\Entities\CategoriaEntity;
use App\Entities\CodigoBarrasProdutoEntity;
use App\Entities\EstoqueEntity;
use App\Entities\FornecedorEntity;
use App\Entities\MovimentacaoCaixaEntity;
use App\Entities\ProdutoEntity;
use App\Entities\SessaoUsuarioEntity;
use App\Entities\VendaEntity;
use App\Models\CategoriaModel;
use App\Models\FornecedorModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ImportacaoDadosController extends BaseController
{

    private SessaoUsuarioEntity $sessaoUsuarioEntity;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController(
            $request,
            $response,
            $logger
        );

        $this->sessaoUsuarioEntity = new SessaoUsuarioEntity();

        $this->sessaoUsuarioEntity = $this->sessaoUsuarioEntity->buscarDadosSessaoUsuario($this->request->getServer('HTTP_AUTHORIZATION'));
    }

    /**
     * @param frn_nome string
     * @param frn_doc string
     */
    public function importaFornecedores(): ResponseInterface
    {
        $dadosFornecedores = $this->request?->getJSON(true);

        $fornecedorEntity = new FornecedorEntity();
        $fornecedorEntity->__set('empresa', $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $retornoFornecedor = [];

        foreach ($dadosFornecedores as $fornecedor) {

            $fornecedorEntity->__set('frn_nome', strtolower($fornecedor['frn_nome']));
            $fornecedorEntity->__set('frn_doc', $fornecedor['frn_doc']);


            $retornoFornecedor[] = $fornecedorEntity->cadastrarFornecedor($fornecedorEntity);
        }

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($retornoFornecedor);
    }

    /**
     * @param cat_nome string
     */
    public function importaCategorias(): ResponseInterface
    {
        $dadosCategoria = $this->request?->getJSON(true);

        $categoriaEntity = new CategoriaEntity();
        $categoriaEntity->__set('empresa', $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $retornoCategoria = [];

        foreach ($dadosCategoria as $categoria) {
            $categoriaEntity->__set('cat_nome', $categoria['cat_nome']);

            $retornoCategoria[] = $categoriaEntity->cadastrarCategoria($categoriaEntity);
        }

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($retornoCategoria);
    }

    /**
     * @param nomeProduto string
     * @param precoVendaProduto float
     * @param descricaoProduto string
     * @param nomeCategoria string
     * @param nomeFornecedor string
     * @param codigoBarrasProduto string
     * @param estoqueAtualProduto int
     * @param estoqueMinimoProduto int
     */
    public function importaProdutos(): ResponseInterface
    {

        $dadosProdutos = $this->request?->getJSON(true);

        $produtoEntity = new ProdutoEntity();
        $estoqueEntity = new EstoqueEntity();
        $codigoBarraProdutoEntity = new CodigoBarrasProdutoEntity();

        $categoriaModel = new CategoriaModel();

        $fornecedorModel = new FornecedorModel();

        $retornoProduto = [];

        foreach ($dadosProdutos as $produto) {

            $buscaDadosCategoria = $categoriaModel->buscaCategoriaProdutoEmpresaPorNome(strtolower($produto['cat_nome']), $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')->__get('emp_id'));

            if (empty($buscaDadosCategoria)) {
                $retornoProduto[] = array('status' => false, 'msg' => "Categoria não encontrada!");
            } else {
                $buscaDadosFornecedor = $fornecedorModel->buscaFornecedorProdutoEmpresa($this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')->__get('emp_id'));

                if (empty($buscaDadosFornecedor)) {
                    $retornoProduto[] = array('status' => false, 'msg' => "Fornecedor não encontrado!");
                } else {
                    $produtoEntity->__set('pro_nome', strtolower($produto['pro_nome']));
                    $produtoEntity->__set('pro_valor_venda', $produto['pro_valor_venda']);
                    $produtoEntity->__set('pro_preco_custo', 0);
                    $produtoEntity->__set('pro_descricao', $produto['pro_descricao']);
                    $produtoEntity->__set('pro_disponivel', $produto['pro_disponivel']);
                    $produtoEntity->__set('categoria', new CategoriaEntity(cat_token: $buscaDadosCategoria->cat_token));
                    $produtoEntity->__set('fornecedor', new FornecedorEntity(frn_token: $buscaDadosFornecedor->frn_token));
                    $produtoEntity->__set('empresa', $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

                    $cadastraProduto = $produtoEntity->cadastrarProduto($produtoEntity);

                    if (!$cadastraProduto['status']) {
                        $retornoProduto[] = $cadastraProduto;
                    } else {
                        $produtoEntity->__set('pro_id', $cadastraProduto['pro_id']);
                        $produtoEntity->__set('pro_token', $cadastraProduto['pro_token']);

                        $estoqueEntity->__set('est_qtd_atual', $produto['est_qtd_atual']);
                        $estoqueEntity->__set('est_qtd_minimo', $produto['est_qtd_minimo']);
                        $estoqueEntity->__set('produto', $produtoEntity);

                        $cadastraEstoque = $estoqueEntity->cadastrarEstoqueProduto($estoqueEntity);

                        if (!$cadastraEstoque['status']) {
                            $retornoProduto[] = $cadastraEstoque;
                        } else {
                            if (!empty($produto['pro_codigo'])) {
                                $codigoBarraProdutoEntity->__set('pcb_codigo', $produto['pro_codigo']);
                                $codigoBarraProdutoEntity->__set('produto', $produtoEntity);

                                $cadastraCodigoBarrasProduto = $codigoBarraProdutoEntity->cadastrarCodigoBarrasProduto($codigoBarraProdutoEntity);

                                if (!$cadastraCodigoBarrasProduto['status']) $retornoProduto[] = $cadastraCodigoBarrasProduto;
                            }
                        }
                    }
                }
            }
        }

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($retornoProduto);
    }

    public function importaVendas(): ResponseInterface
    {
        $dadosVenda = $this->request?->getJSON(true);

        $vendaEntity = new VendaEntity();

        $retornoVendas = [];

        foreach ($dadosVenda as $venda) {
            $vendaEntity->__set('ven_data', $venda['rgv_data']);
            $vendaEntity->__set('ven_tipo', "local");
            $vendaEntity->__set('ven_tipo_pagamento', $venda['rgv_forma_pag']);
            $vendaEntity->__set('ven_valor_compra', $venda['rgv_vlr_total']);
            $vendaEntity->__set('ven_desconto', $venda['rgv_desconto']);
            $vendaEntity->__set('ven_total', ($venda['rgv_vlr_total'] - $venda['rgv_desconto']));
            $vendaEntity->__set('ven_status', $venda['rgv_status']);
            $vendaEntity->__set('empresa', $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

            if ($venda['rgv_status'] == 'aberto' && $venda['rgv_fiado'] == "1" && $venda['rgv_vlr_total'] > 0) {

                $vendaEntity->__set('ven_cliente', empty($venda['pes_nome']) ? "--" : $venda['pes_nome']);
            }

            $retornoVendas[] = $vendaEntity->salvaVendaImportado($vendaEntity);
        }

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($retornoVendas);
    }

    public function importaMovimentacoesManuais(): ResponseInterface
    {
        $dadosMovimentacao = $this->request?->getJSON(true);

        $movimentacaoEntity = new MovimentacaoCaixaEntity();

        $retornoMovimentacao = [];

        foreach ($dadosMovimentacao as $movimentacao) {

            $movimentacaoEntity->__set('mcx_data', $movimentacao['hcx_data']);
            $movimentacaoEntity->__set('mcx_tipo', $movimentacao['hcx_tipo']);
            $movimentacaoEntity->__set('mcx_valor', $movimentacao['hcx_vlr']);
            $movimentacaoEntity->__set('mcx_comentario', $movimentacao['hcx_msg']);
            $movimentacaoEntity->__set('empresa', $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

            $retornoMovimentacao[] = $movimentacaoEntity->salvaMovimentacaoCaixaManual($movimentacaoEntity);
        }

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($retornoMovimentacao);
    }
}
