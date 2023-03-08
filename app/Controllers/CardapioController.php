<?php

namespace App\Controllers;

use App\Entities\CardapioEntity;
use App\Entities\CategoriaEntity;
use App\Entities\SessaoUsuarioEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CardapioController extends BaseController
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
     * @param nomeProduto string
     * @param descricaoProduto string
     * @param valorProduto float
     * @param tokenCategoria string
     */
    public function cadastrarProdutoCardapio(): ResponseInterface
    {
        $nomeProduto = $this->request?->getPost('nomeProduto');
        $descricaoProduto = $this->request?->getPost('descricaoProduto');
        $valorProduto = $this->request?->getPost('valorProduto');
        $tokenCategoria = $this->request?->getPost('tokenCategoria');

        $cardapioEntity = new CardapioEntity(
            cdp_nome: $nomeProduto,
            cdp_valor: (float) $valorProduto,
            cdp_descricao: $descricaoProduto,
            categoria: new CategoriaEntity(cat_token: $tokenCategoria),
            empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($cardapioEntity->cadastrarProdutoCardapio($cardapioEntity));
    }

    /**
     * @param tokenProduto string
     * @param nomeProduto string
     * @param descricaoProduto string
     * @param valorProduto float
     * @param tokenCategoria string
     */
    public function alterarProdutoCardapio(): ResponseInterface
    {
        $dados = $this->request?->getRawInput();

        $cardapioEntity = new CardapioEntity(
            cdp_token: $dados['tokenProduto'],
            cdp_nome: $dados['nomeProduto'],
            cdp_descricao: $dados['descricaoProduto'],
            cdp_valor: (float) $dados['valorProduto'],
            categoria: new CategoriaEntity(cat_token: $dados['tokenCategoria']),
            empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($cardapioEntity->alterarProdutoCardapio($cardapioEntity));
    }

    /**
     * @param tokenProduto string
     */
    public function ativarProdutoCardapio(): ResponseInterface
    {
        $dados = $this->request?->getRawInput();

        $cardapioEntity = new CardapioEntity(
            cdp_token: $dados['tokenProduto'],
            cdp_disponivel: true,
            empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($cardapioEntity->alterarStatusProdutoCardapio($cardapioEntity));
    }

    /**
     * @param tokenProduto string
     */
    public function desativarProdutoCardapio(): ResponseInterface
    {
        $dados = $this->request?->getRawInput();

        $cardapioEntity = new CardapioEntity(
            cdp_token: $dados['tokenProduto'],
            cdp_disponivel: false,
            empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($cardapioEntity->alterarStatusProdutoCardapio($cardapioEntity));
    }

    public function listarTodosProdutosCardapioEmpresa(): ResponseInterface
    {
        $cardapioEntity = new CardapioEntity(empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        return $this->response->setStatusCode(200, 'Sucesso')->setJSON($cardapioEntity->listarTodosProdutosCardapioEmpresa($cardapioEntity));
    }

    public function listarProdutosAtivosCardapioEmpresa(): ResponseInterface
    {
        $cardapioEntity = new CardapioEntity(empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        return $this->response->setStatusCode(200, 'Sucesso')->setJSON($cardapioEntity->listarProdutosCardapioAtivosEmpresa($cardapioEntity));
    }
}
