<?php

namespace App\Controllers;

use App\Entities\CategoriaEntity;
use App\Entities\SessaoUsuarioEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CategoriaController extends BaseController
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
     * @param nomeCategoria string
     */
    public function cadastrarCategoria(): ResponseInterface
    {
        $nomeCategoria = $this->request?->getPost('nomeCategoria');

        if (!$this->validateData(['nomeCategoria' => $nomeCategoria], [
            'nomeCategoria' => 'required|string',
        ])) return $this->response->setStatusCode(200, 'Categoria não informado')->setJSON(['status' => false, 'msg' => "Nome da categoria não informado!"]);

        $categoriaEntity = new CategoriaEntity(cat_nome: $nomeCategoria, empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($categoriaEntity->cadastrarCategoria($categoriaEntity));
    }

    /**
     * @param tokenCategoria string
     * @param nomeCategoria string
     */
    public function editarCategoria(): ResponseInterface
    {
        $dados = $this->request?->getRawInput();

        if (!$this->validateData($dados, [
            'nomeCategoria' => 'required|string',
            'tokenCategoria' => 'required|string|max_length[36]'
        ])) return $this->response->setStatusCode(200, "Informações invalidas")->setJSON(['status' => false, 'msg' => "Informações da categorias invalidas"]);

        $categoriaEntity = new CategoriaEntity(
            cat_token: $dados['tokenCategoria'],
            cat_nome: $dados['nomeCategoria'],
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($categoriaEntity->alterarCategoria($categoriaEntity));
    }

    /**
     * @param tokenCategoria string
     */
    public function deletarCategoria(): ResponseInterface
    {
        $dados = $this->request?->getRawInput();

        if (!$this->validateData($dados, [
            'tokenCategoria' => 'required|string|max_length[36]'
        ])) return $this->response->setStatusCode(200, "Informações invalidas")->setJSON(['status' => false, 'msg' => "Informações da categoria invalidas"]);

        $categoriaEntity = new CategoriaEntity(
            cat_token: $dados['tokenCategoria']
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($categoriaEntity->excluirCategoria($categoriaEntity));
    }

    public function listarCategoria(): ResponseInterface
    {
        $categoriaEntity = new CategoriaEntity(
            empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($categoriaEntity->listarCategoriaProdutoEmpresa($categoriaEntity));
    }
}
