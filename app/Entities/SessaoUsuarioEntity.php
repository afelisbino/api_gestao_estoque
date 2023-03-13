<?php

namespace App\Entities;

use App\Libraries\JwtToken;
use App\Libraries\Uuid;
use App\Models\UsuarioModel;

class SessaoUsuarioEntity
{
    public function __construct(
        private UsuarioEntity $usuario = new UsuarioEntity()
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

    public function realizarLoginUsuario(SessaoUsuarioEntity $sessaoEntity): array
    {
        $usuarioModel = new UsuarioModel();

        $verificaCredenciaisUsuario = $usuarioModel->buscarPorUsuarioSenha(
            $sessaoEntity->__get('usuario')->__get('fun_usuario'),
            $sessaoEntity->__get('usuario')->__get('fun_senha')
        );

        if (empty($verificaCredenciaisUsuario)) return ['status' => false, 'msg' => 'Usuario ou senha invalido'];

        if (!$verificaCredenciaisUsuario->fun_ativo) return ['status' => false, 'msg' => 'Usuario se encontra desativado'];

        return ['status' => true, 'msg' => "Sessão iniciado com sucesso!", 'token' => JwtToken::encodeTokenJwt([
            'tokenUsuario' => $verificaCredenciaisUsuario->fun_token,
            'usuarioNome' => $verificaCredenciaisUsuario->fun_usuario
        ]), 'admin' => (bool)$verificaCredenciaisUsuario->fun_adm];
    }

    public function buscarDadosSessaoUsuario(string $tokenJwt): SessaoUsuarioEntity
    {

        $tokenSessao = JwtToken::decodeTokenJwt($tokenJwt);

        if (empty($tokenSessao) || !Uuid::is_valid($tokenSessao['tokenUsuario'])) return new SessaoUsuarioEntity();

        $usuarioModel = new UsuarioModel();
        $dadosUsuario = $usuarioModel->buscarUsuarioPorToken($tokenSessao['tokenUsuario']);

        if (empty($dadosUsuario)) return new SessaoUsuarioEntity();

        return new SessaoUsuarioEntity(
            usuario: new UsuarioEntity(
                fun_id: $dadosUsuario->fun_id,
                fun_usuario: $dadosUsuario->fun_usuario,
                fun_token: $dadosUsuario->fun_token,
                empresa: new EmpresaEntity(emp_id: $dadosUsuario->emp_id)
            )
        );
    }
}
