<?php

namespace App\Controllers;

use App\Entities\PessoaEntity;
use App\Entities\EmpresaEntity;
use App\Entities\UsuarioEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class UsuarioController extends BaseController
{

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * @param pessoaNome string
     * @param usuarioNome string
     * @param usuarioSenha string
     * @param empresaToken string
     */
    public function cadastrarPrimeiroUsuarioEmpresa(): ResponseInterface
    {

        $pessoaNome = $this->request?->getPost('pessoaNome');
        $usuarioNome = $this->request?->getPost('usuarioNome');
        $usuarioSenha = $this->request?->getPost('usuarioSenha');
        $empresaToken = $this->request?->getPost('empresaToken');

        if (empty($pessoaNome)) {
            return $this->response->setStatusCode(200, "Informação não enviado!")->setJSON(['status' => false, 'msg' => "Nome da pessoa não informado!"]);
        }

        if (empty($usuarioNome)) {
            return $this->response->setStatusCode(200, "Informação não enviado!")->setJSON(['status' => false, 'msg' => "Nome do usuario não informado!"]);
        }

        if (empty($usuarioSenha)) {
            return $this->response->setStatusCode(200, "Informação não enviado!")->setJSON(['status' => false, 'msg' => "Senha do usuario não informado!"]);
        }

        if (empty($empresaToken)) {
            return $this->response->setStatusCode(200, "Informação não enviado!")->setJSON(['status' => false, 'msg' => "Token empresa não informado!"]);
        }

        $pessoaEntity = new PessoaEntity(
            pes_nome: $pessoaNome,
            empresa: new EmpresaEntity(emp_token: $empresaToken)
        );

        $salvaPessoaUsuario = $pessoaEntity->salvarPessoa($pessoaEntity);

        if (!$salvaPessoaUsuario['status']) return $this->response->setStatusCode(200, "Falha ao salvar")->setJSON($salvaPessoaUsuario);

        $usuarioEntity = new UsuarioEntity(
            fun_usuario: $usuarioNome,
            fun_senha: $usuarioSenha,
            fun_adm: true,
            empresa: new EmpresaEntity(emp_token: $empresaToken),
            pessoa: new PessoaEntity(pes_token: $salvaPessoaUsuario['token'])
        );

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($usuarioEntity->salvarUsuario($usuarioEntity));
    }
}
