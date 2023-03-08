<?php

namespace App\Entities;

use App\Libraries\Uuid;
use App\Models\EmpresaModel;
use App\Models\PessoaModel;
use App\Models\UsuarioModel;

class UsuarioEntity
{

    public function __construct(
        private int | null $fun_id = null,
        private string | null $fun_usuario = null,
        private string | null $fun_senha = null,
        private int | bool $fun_ativo = false,
        private string | null $fun_token = null,
        private int | bool $fun_adm = false,
        private EmpresaEntity $empresa = new EmpresaEntity(),
        private PessoaEntity $pessoa = new PessoaEntity()
    ) {
    }

    public function __get($nomeParametro)
    {
        return $this->{$nomeParametro};
    }

    public function __set($nomeParametro, $valorParametro): void
    {
        $this->{$nomeParametro} = $valorParametro;
    }

    public function salvarUsuario(UsuarioEntity $usuarioEntity)
    {
        if (!Uuid::is_valid($usuarioEntity->__get('empresa')->__get('emp_token'))) {
            return ['status' => false, 'msg' => 'Token invalido'];
        }

        $usuarioModel = new UsuarioModel();

        $empresaModel = new EmpresaModel();

        $dadosEmpresa = $empresaModel->buscarEmpresaPorToken($usuarioEntity->__get('empresa')->__get('emp_token'));

        if (empty($dadosEmpresa)) {
            return ['status' => false, 'msg' => 'Empresa não encontrada'];
        }

        $pessoaModel = new PessoaModel();

        $dadosPessoa = $pessoaModel->buscarPessoaPorToken($usuarioEntity->__get('pessoa')->__get('pes_token'));

        return $usuarioModel->cadastrarUsuario([
            'fun_usuario' => $usuarioEntity->__get('fun_usuario'),
            'fun_senha' => md5($usuarioEntity->__get('fun_senha')),
            'fun_ativo' => 1,
            'fun_token' => Uuid::v4(),
            'fun_adm' => $usuarioEntity->__get('fun_adm'),
            'emp_id' => $dadosEmpresa->emp_id,
            'pes_id' => $dadosPessoa->pes_id
        ]);
    }

    public function validarUsuario(UsuarioEntity $usuarioEntity): array
    {
        $usuarioModel = new UsuarioModel();

        $dadosUsuario = $usuarioModel->buscarPorUsuarioSenha($usuarioEntity->__get('fun_usuario'), md5($usuarioEntity->__get('fun_senha')));

        if (empty($dadosUsuario)) {
            return [
                'status' => false,
                'msg' => "Usuário ou senha inválido!"
            ];
        } else if (!$dadosUsuario->fun_usuario) {
            return [
                'status' => false,
                'msg' => 'Usuario não está ativo!'
            ];
        } else {
            return [
                'status' => true,
                'msg' => 'Usuario autenticado com sucesso!',
                'adm' => $dadosUsuario->fun_adm,
            ];
        }
    }
}
