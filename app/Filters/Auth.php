<?php

namespace App\Filters;

use App\Entities\SessaoUsuarioEntity;
use App\Libraries\JwtToken;
use App\Libraries\Uuid;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');

        $jwt = $request->getServer('HTTP_AUTHORIZATION');

        if (empty($jwt)) return $response->setStatusCode(401, "Token não informado")->setJSON(['status' => false, 'msg' => "Token da sessão não informado!"]);

        $token = JwtToken::decodeTokenJwt($jwt);

        if (empty($token)) return $response->setStatusCode(401, "Falha ao validar token")->setJSON(['status' => false, 'msg' => "Falha ao validar token da sessão!"]);

        if (!Uuid::is_valid($token['tokenUsuario'])) return $response->setStatusCode('401', 'Token inválido!')->setJSON(['status' => false, 'msg' => "Token do usuario inválido!"]);

        $sessaoUsuario = new SessaoUsuarioEntity();

        $sessaoUsuario = $sessaoUsuario->buscarDadosSessaoUsuario($jwt);

        if (empty($sessaoUsuario->__get('usuario')->__get('empresa')->__get('emp_id'))) {
            return $response->setStatusCode(401, 'Não Autorizado')->setJSON(['status' => false, 'msg' => "Usuario não encontrado!"]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
