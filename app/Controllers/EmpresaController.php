<?php

namespace App\Controllers;

use App\Entities\EmpresaEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class EmpresaController extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController(
            $request,
            $response,
            $logger
        );
    }

    /**
     * @param nomeEmpresa string
     * @param documentoEmpresa string
     */
    public function cadastrarNovaEmpresa(): ResponseInterface
    {

        $nomeEmpresa = $this->request?->getPost('nomeEmpresa');
        $documentoEmpresa = $this->request?->getPost('documentoEmpresa');

        if (empty($nomeEmpresa)) {
            return $this->response->setStatusCode(200, "Informação não enviado!")->setJSON(['status' => false, 'msg' => "Nome da empresa não informado!"]);
        }

        if (empty($documentoEmpresa)) {
            return $this->response->setStatusCode(
                200,
                "Informação não enviado!"
            )->setJSON(['status' => false, 'msg' => "Documento não informado!"]);
        }

        $empresaEntity = new EmpresaEntity(emp_doc: $documentoEmpresa, emp_nome: $nomeEmpresa);

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($empresaEntity->cadastrarEmpresa($empresaEntity));
    }
}
