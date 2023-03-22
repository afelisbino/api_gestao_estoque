<?php

namespace App\Controllers;

use App\Entities\CategoriaEntity;
use App\Entities\CodigoBarrasProdutoEntity;
use App\Entities\EmpresaEntity;
use App\Entities\EstoqueEntity;
use App\Entities\FornecedorEntity;
use App\Entities\ProdutoEntity;
use App\Entities\SessaoUsuarioEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ProdutoController extends BaseController
{

    private SessaoUsuarioEntity $sessaoUsuarioEntity;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->sessaoUsuarioEntity = new SessaoUsuarioEntity();

        $this->sessaoUsuarioEntity = $this->sessaoUsuarioEntity->buscarDadosSessaoUsuario($this->request->getServer('HTTP_AUTHORIZATION'));
    }

    /**
     * @param nomeProduto string
     * @param precoVendaProduto float
     * @param precoCompraProduto float
     * @param descricaoProduto string
     * @param tokenCategoria string
     * @param tokenFornecedor string
     * @param codigoBarrasProduto array
     * @param estoqueAtualProduto int
     * @param estoqueMinimoProduto int
     */
    public function cadastrarProduto(): ResponseInterface
    {
        $dadosProdutoEstoque = $this->request?->getJSON(assoc: true);

        $produtoEntity = new ProdutoEntity(
            pro_nome: $dadosProdutoEstoque['nomeProduto'],
            pro_valor_venda: $dadosProdutoEstoque['precoVendaProduto'],
            pro_preco_custo: $dadosProdutoEstoque['precoCompraProduto'],
            pro_descricao: $dadosProdutoEstoque['descricaoProduto'],
            categoria: new CategoriaEntity(cat_token: $dadosProdutoEstoque['tokenCategoria']),
            fornecedor: new FornecedorEntity(frn_token: $dadosProdutoEstoque['tokenFornecedor']),
            empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
        );

        $cadastraProduto = $produtoEntity->cadastrarProduto($produtoEntity);

        if (!$cadastraProduto['status']) {
            return $this->response->setJSON(200, "Falha")->setJSON($cadastraProduto);
        }

        $produtoEntity->__set('pro_id', $cadastraProduto['pro_id']);
        $produtoEntity->__set('pro_token', $cadastraProduto['pro_token']);

        $estoqueEntity = new EstoqueEntity(
            est_qtd_atual: $dadosProdutoEstoque['estoqueAtualProduto'],
            est_qtd_minimo: $dadosProdutoEstoque['estoqueMinimoProduto'],
            produto: $produtoEntity
        );

        $cadastraEstoque = $estoqueEntity->cadastrarEstoqueProduto($estoqueEntity);

        if (!$cadastraEstoque['status']) {
            return $this->response->setJSON(200, "Falha")->setJSON($cadastraEstoque);
        }

        $codigoBarraProdutoEntity = new CodigoBarrasProdutoEntity(produto: $produtoEntity);

        $erroCadastroCodigoBarras = [];

        foreach ($dadosProdutoEstoque['codigoBarrasProduto'] as $codigo) {
            $codigoBarraProdutoEntity->__set('pcb_codigo', $codigo["pcb_codigo"]);

            $cadastraCodigoBarrasProduto = $codigoBarraProdutoEntity->cadastrarCodigoBarrasProduto($codigoBarraProdutoEntity);

            if (!$cadastraCodigoBarrasProduto['status']) {
                array_push($erroCadastroCodigoBarras, $cadastraCodigoBarrasProduto);
            }
        }

        if (!empty($erroCadastroCodigoBarras)) {
            log_message("DEBUG", json_encode($erroCadastroCodigoBarras));
            return $this->response->setJSON(200, "Falha")->setJSON(['status' => false, 'msg' => "Falha ao cadastrar alguns codigos de barras"]);
        }

        return $this->response->setJSON(200, "Sucesso")->setJSON(['status' => true, 'msg' => "Produto cadastrado no estoque com sucesso!"]);
    }

    public function listarTodosProdutosEstoque(): ResponseInterface
    {
        $produtoEntity = new ProdutoEntity();

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($produtoEntity->buscarListaProdutosEstoque($this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')));
    }

    /**
     * @param nomeProduto string
     * @param precoVendaProduto float
     * @param precoCompraProduto float
     * @param descricaoProduto string
     * @param tokenCategoria string
     * @param tokenFornecedor string
     * @param estoqueAtualProduto int
     * @param estoqueMinimoProduto int
     */
    public function alterarDadosProduto(): ResponseInterface
    {
        $dadosProdutoEstoque = $this->request?->getJSON();

        $produtoEntity = new ProdutoEntity(
            pro_token: $dadosProdutoEstoque->tokenProduto,
            pro_nome: $dadosProdutoEstoque->nomeProduto,
            pro_valor_venda: $dadosProdutoEstoque->precoVendaProduto,
            pro_preco_custo: $dadosProdutoEstoque->precoCompraProduto,
            pro_descricao: $dadosProdutoEstoque->descricaoProduto,
            categoria: new CategoriaEntity(cat_token: $dadosProdutoEstoque->tokenCategoria),
            fornecedor: new FornecedorEntity(frn_token: $dadosProdutoEstoque->tokenFornecedor),
            empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
        );

        $alteraDadosProduto = $produtoEntity->alterarDadosProduto($produtoEntity);

        $estoqueEntity = new EstoqueEntity(est_qtd_atual: $dadosProdutoEstoque->estoqueAtualProduto, est_qtd_minimo: $dadosProdutoEstoque->estoqueMinimoProduto, produto: $produtoEntity);

        $alteraDadosEstoque = $estoqueEntity->alterarDadosEstoqueProduto($estoqueEntity);

        if ($alteraDadosProduto['status'] && $alteraDadosEstoque['status']) {
            return $this->response->setStatusCode(200, "Sucesso")->setJSON($alteraDadosProduto);
        } else {
            return $this->response->setStatusCode(200, "Sucesso")->setJSON(['status' => false, 'msg' => "Falha ao alterar dados do produto!"]);
        }
    }

    /**
     * @param tokenProduto string
     */
    public function buscarDadosProduto(): ResponseInterface
    {
        $tokenProduto = $this->request?->getGet('tokenProduto');

        $produtoEntity = new ProdutoEntity(pro_token: $tokenProduto);

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($produtoEntity->buscarDadosProduto($produtoEntity));
    }

    /**
     * @param tokenProduto string
     */
    public function ativarProduto()
    {
        $dadosProduto = $this->request?->getRawInput();

        $produtoEntity = new ProdutoEntity(pro_token: $dadosProduto['tokenProduto'], pro_disponivel: true);

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($produtoEntity->alterarStatusProduto($produtoEntity));
    }

    /**
     * @param tokenProduto string
     */
    public function desativarProduto()
    {
        $dadosProduto = $this->request?->getRawInput();

        $produtoEntity = new ProdutoEntity(pro_token: $dadosProduto['tokenProduto'], pro_disponivel: false);

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($produtoEntity->alterarStatusProduto($produtoEntity));
    }

    /**
     * @param tokenProduto string
     */
    public function listarTodosCodigosBarrasProduto(): ResponseInterface
    {
        $tokenProduto = $this->request?->getGet('tokenProduto');

        $codigoBarraProdutoEntity = new CodigoBarrasProdutoEntity(produto: new ProdutoEntity(pro_token: $tokenProduto));

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($codigoBarraProdutoEntity->listarCodigosBarrasProduto($codigoBarraProdutoEntity));
    }

    /**
     * @param codigoBarrasProduto string
     * @param tokenProduto string
     */
    public function adicionarCodigoBarrasProduto(): ResponseInterface
    {
        $tokenProduto = $this->request?->getJsonVar('tokenProduto');
        $codigoBarrasProduto = $this->request?->getJsonVar('codigoBarrasProduto');

        $codigoBarraProdutoEntity = new CodigoBarrasProdutoEntity(pcb_codigo: $codigoBarrasProduto, produto: new ProdutoEntity(pro_token: $tokenProduto, empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')));

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($codigoBarraProdutoEntity->cadastrarCodigoBarrasProduto($codigoBarraProdutoEntity));
    }

    /**
     * @param tokenCodigoBarras string
     */
    public function deletarCodigoBarras(): ResponseInterface
    {
        $tokenCodigo = $this->request?->getRawInput();

        $codigoBarraProdutoEntity = new CodigoBarrasProdutoEntity(pcb_token: $tokenCodigo['tokenCodigo']);

        return $this->response->setStatusCode(200, 'Sucesso')->setJSON($codigoBarraProdutoEntity->excluirCodigoBarrasProduto($codigoBarraProdutoEntity));
    }

    /**
     * @param tokenProduto string
     */
    public function listarHistoricoEstoqueProduto(): ResponseInterface
    {
        $tokenProduto = $this->request?->getGet('tokenProduto');

        $estoqueEntity = new EstoqueEntity(produto: new ProdutoEntity(pro_token: $tokenProduto));

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($estoqueEntity->buscaHistoricoEstoqueProduto($estoqueEntity->__get('produto')));
    }

    /**
     * @param dataInicio string
     * @param dataFim string
     */
    public function listarHistoricoEstoqueEmpresa(): ResponseInterface
    {
        $dataInicio = $this->request?->getGet('dataInicio');
        $dataFim = $this->request?->getGet('dataFim');

        $estoqueEntity = new EstoqueEntity();

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($estoqueEntity->buscarHistoricoEstoqueEmpresa($this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'), $dataInicio, $dataFim));
    }

    /**
     * @param tokenProduto string
     * @param quantidadeEntrada int
     */
    public function adicionarEstoqueProduto()
    {
        $dados = $this->request->getRawInput();

        $estoqueEntity = new EstoqueEntity(produto: new ProdutoEntity(pro_token: $dados['tokenProduto'], empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')));

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($estoqueEntity->cadastraEntradaEstoqueProduto($estoqueEntity, $dados['quantidadeEntrada']));
    }

    /**
     * @param tokenProduto string
     * @param quantidadeSaida int
     */
    public function retirarEstoqueProduto()
    {
        $dados = $this->request->getRawInput();

        $estoqueEntity = new EstoqueEntity(produto: new ProdutoEntity(pro_token: $dados['tokenProduto'], empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')));

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($estoqueEntity->cadastraSaidaEstoqueProduto($estoqueEntity, $dados['quantidadeSaida']));
    }

    public function listarProdutosAtivosEmpresa()
    {
        $produtoEntity = new ProdutoEntity();

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($produtoEntity->buscaListaProdutoAtivosEmpresa($this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')));
    }
}
