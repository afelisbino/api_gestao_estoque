<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\SessaoUsuarioEntity;
use App\Entities\UsuarioEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AutenticacaoController extends BaseController
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
     * @param usuarioNome string
     * @param usuarioSenha string
     */
    public function autenticarUsuario(): ResponseInterface
    {
        $dadosLogin = $this->request?->getJSON(true);

        if (empty($dadosLogin['usuarioNome'])) return $this->response->setStatusCode(200, 'Usuario não informado')->setJSON(['status' => false, 'msg' => 'Usuario não informado!']);

        if (empty($dadosLogin['usuarioSenha'])) return $this->response->setStatusCode(200, 'Senha não informado')->setJSON(['status' => false, 'msg' => 'Senha do usuario não informado!']);

        $sessaoEntity = new SessaoUsuarioEntity(
            new UsuarioEntity(fun_usuario: $dadosLogin['usuarioNome'], fun_senha: $dadosLogin['usuarioSenha'])
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($sessaoEntity->realizarLoginUsuario($sessaoEntity));
    }
}
