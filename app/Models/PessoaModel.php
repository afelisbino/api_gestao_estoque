<?php

namespace App\Models;

use CodeIgniter\Model;

class PessoaModel extends Model
{
    protected $table      = 'pessoa';
    protected $primaryKey = 'pes_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['pes_nome', 'emp_id', 'pes_token'];

    protected $useTimestamps = false;

    public function cadastrarPessoa(array $dados): array
    {
        if ($this->save($dados)) {
            return ['status' => true, 'msg' => 'Pessoa salvo com sucesso', 'token' => $dados['pes_token']];
        } else {
            return ['status' => false, 'msg' => "Falha ao salvar a pessoa", 'error' => $this->errors()];
        }
    }

    public function alterarDadosPessoa(array $dados): array
    {
        if ($this->save($dados)) {
            return ['status' => true, 'msg' => 'Pessoa alterado com sucesso'];
        } else {
            return ['status' => false, 'msg' => "Falha ao alterar a pessoa", 'error' => $this->errors()];
        }
    }

    public function buscarPessoaPorToken(string $token){
        $this->where('pes_token', $token);

        return $this->get()->getRow();
    }
}
