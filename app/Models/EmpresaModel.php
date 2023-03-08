<?php

namespace App\Models;

use CodeIgniter\Model;

class EmpresaModel extends Model
{
    protected $table      = 'empresa';
    protected $primaryKey = 'emp_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['emp_doc', 'emp_nome', 'emp_ativo', 'emp_token'];

    protected $useTimestamps = false;

    public function salvarNovaEmpresa(array $dados = [])
    {
        if ($this->save($dados)) {
            return ['status' => true, 'msg' => "Empresa salvo com sucesso!"];
        } else {
            return ['status' => false, 'msg' => "Falha ao salvar nova empresa", 'error' => $this->errors()];
        }
    }

    public function salvarAlteracoesEmpresa(array $dados = [])
    {
        if ($this->save($dados)) {
            return ['status' => true, 'msg' => "Alterações da empresa salvo com sucesso!"];
        } else {
            return ['status' => false, 'msg' => "Falha ao salvar alterações da empresa", 'error' => $this->errors()];
        }
    }

    public function buscarEmpresaPorDocumento(string $emp_doc)
    {
        $this->where('emp_doc', $emp_doc);

        return $this->get()->getRow();
    }

    public function buscarEmpresaPorToken(string $emp_token)
    {
        $this->where('emp_token', $emp_token);

        return $this->get()->getRow();
    }
}
