<?php

namespace App\Controllers;

use App\Entities\FornecedorEntity;
use App\Entities\SessaoUsuarioEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class FornecedorController extends BaseController
{

    private SessaoUsuarioEntity $sessaoUsuarioEntity;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->sessaoUsuarioEntity = new SessaoUsuarioEntity();

        $this->sessaoUsuarioEntity = $this->sessaoUsuarioEntity->buscarDadosSessaoUsuario($this->request->getServer('HTTP_AUTHORIZATION'));
    }

    /**
     * @param nomeFornecedor string
     * @param documentoFornecedor string
     */
    public function cadastrarFornecedor(): ResponseInterface
    {
        $nomeFornecedor = $this->request?->getJsonVar('nomeFornecedor');
        $docFornecedor = $this->request?->getJsonVar('documentoFornecedor');

        if (!$this->validateData(
            [
                'nomeFornecedor' => $nomeFornecedor
            ],
            [
                'nomeFornecedor' => 'required|string',
                'documentoFornecedor' => 'string|max_length[18]|permit_empty'
            ]
        )) {
            return $this->response->setStatusCode(200, "Dados não informado")->setJSON(['status' => false, 'msg' => "Nome do fornecedor não informado!"]);
        }

        $fornecedorEntity = new FornecedorEntity(frn_nome: $nomeFornecedor, frn_doc: $docFornecedor, empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($fornecedorEntity->cadastrarFornecedor($fornecedorEntity));
    }

    /**
     * @param nomeFornecedor string
     * @param documentoFornecedor string
     * @param tokenFornecedor string
     */
    public function editarFornecedor(): ResponseInterface
    {
        $dados = $this->request?->getRawInput();

        if (!$this->validateData(
            $dados,
            [
                'nomeFornecedor' => 'required|string',
                'tokenFornecedor' => 'required|string|max_length[36]',
                'documentoFornecedor' => 'string|max_length[18]'
            ]
        )) {
            return $this->response->setStatusCode(200, "Dados não informado")->setJSON(['status' => false, 'msg' => "Dados do fornecedor não informado!"]);
        }

        $fornecedorEntity = new FornecedorEntity(frn_nome: $dados['nomeFornecedor'], frn_doc: $dados['documentoFornecedor'], frn_token: $dados['tokenFornecedor'], empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($fornecedorEntity->alterarFornecedor($fornecedorEntity));
    }

    /**
     * @param tokenFornecedor string
     */
    public function deletarFornecedor(): ResponseInterface
    {
        $dados = $this->request?->getRawInput();

        if (!$this->validateData(
            $dados,
            [
                'tokenFornecedor' => 'required|string|max_length[36]'
            ]
        )) {
            return $this->response->setStatusCode(200, "Dados não informado")->setJSON(['status' => false, 'msg' => "Dados do fornecedor não informado!"]);
        }

        $fornecedorEntity = new FornecedorEntity(empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'), frn_token: $dados['tokenFornecedor']);

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($fornecedorEntity->excluirFornecedor($fornecedorEntity));
    }

    /**
     * @param tokenFornecedor string
     */
    public function buscarFornecedor(): ResponseInterface
    {
        $dados = $this->request?->getGet();

        if (!$this->validateData(
            $dados,
            [
                'tokenFornecedor' => 'required|string|max_length[36]'
            ]
        )) {
            return $this->response->setStatusCode(200, "Dados não informado")->setJSON(['status' => false, 'msg' => "Dados do fornecedor não informado!"]);
        }

        $fornecedorEntity = new FornecedorEntity(empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'), frn_token: $dados['tokenFornecedor']);

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($fornecedorEntity->buscarDadosFornecedor($fornecedorEntity));
    }

    public function listarFornecedores(): ResponseInterface
    {

        $fornecedorEntity = new FornecedorEntity(empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($fornecedorEntity->listarFornecedores($fornecedorEntity));
    }
}
