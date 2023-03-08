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
        $usuarioNome = $this->request?->getPost('usuarioNome');
        $usuarioSenha = $this->request?->getPost('usuarioSenha');

        if (empty($usuarioNome)) return $this->response->setStatusCode(200, 'Usuario não informado')->setJSON(['status' => false, 'msg' => 'Usuario não informado!']);

        if (empty($usuarioSenha)) return $this->response->setStatusCode(200, 'Senha não informado')->setJSON(['status' => false, 'msg' => 'Senha do usuario não informado!']);

        $sessaoEntity = new SessaoUsuarioEntity(
            new UsuarioEntity(fun_usuario: $usuarioNome, fun_senha: $usuarioSenha)
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($sessaoEntity->realizarLoginUsuario($sessaoEntity));
    }

    /**
     * @param tokenSessao string
     */
    public function desautenticarUsuario(): ResponseInterface
    {
        $tokenSessao = $this->request?->getRawInput();

        if (!isset($tokenSessao['tokenSessao']) || empty($tokenSessao['tokenSessao'])) return $this->response->setStatusCode(200, 'Token não informado!')->setJSON(['status' => false, 'msg' => "Token da sessão não informado!"]);

        $sessaoEntity = new SessaoUsuarioEntity(
            sus_token: $tokenSessao['tokenSessao']
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($sessaoEntity->realizarLogoffUsuario($sessaoEntity));
    }
}
