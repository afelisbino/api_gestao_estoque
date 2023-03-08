<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table      = 'usuario_funcionario';
    protected $primaryKey = 'fun_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['fun_usuario', 'fun_senha', 'fun_ativo', 'fun_adm', 'fun_token', 'emp_id', 'pes_id'];

    protected $useTimestamps = false;

    public function cadastrarUsuario(array $dados): array
    {
        if ($this->save($dados)) {
            return ['status' => true, 'msg' => 'Usuario cadastrado com sucesso!'];
        } else {
            return ['status' => false, 'msg' => 'Falha ao cadastrar usuario', 'error' => $this->errors()];
        }
    }

    public function alterarDadosUsuario(array $dados): array
    {
        if ($this->save($dados)) {
            return ['status' => true, 'msg' => 'Usuario alterado com sucesso!'];
        } else {
            return ['status' => false, 'msg' => 'Falha ao alterar usuario', 'error' => $this->errors()];
        }
    }

    public function buscarPorUsuarioSenha(string $usuario, string $senha)
    {
        $this->join('empresa', 'usuario_funcionario.emp_id = empresa.emp_id');
        $this->join('pessoa', 'usuario_funcionario.pes_id = pessoa.pes_id');
        $this->where('fun_usuario', $usuario);
        $this->where('fun_senha', md5($senha));

        return $this->get()->getRow();
    }

    public function buscarUsuarioPorToken(string $tokenUsuario)
    {
        $this->where('fun_token', $tokenUsuario);

        return $this->get()->getRow();
    }

    public function buscarListaUsuariosEmpresa(string $tokenEmpresa)
    {
        $this->join('empresa', 'usuario_funcionario.emp_id = empresa.emp_id');
        $this->join('pessoa', 'usuario_funcionario.pes_id = pessoa.pes_id');
        $this->where('emp_token', $tokenEmpresa);

        return $this->get()->getResult();
    }
}
