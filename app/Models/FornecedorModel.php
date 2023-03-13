<?php

namespace App\Models;

use CodeIgniter\Model;

class FornecedorModel extends Model
{
    protected $table      = 'fornecedor';
    protected $primaryKey = 'frn_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['frn_nome', 'frn_token', 'frn_doc', 'emp_id'];

    protected $useTimestamps = false;

    public function salvarFornecedor(array $dados)
    {
        if ($this->save($dados)) {
            return ['status' => true, 'msg' => "Fornecedor salvo com sucesso!"];
        } else {
            return ['status' => false, 'msg' => "Falha ao salvar fornecedor!"];
        }
    }

    public function deletarFornecedor(int $frn_id)
    {
        if ($this->delete($frn_id)) {
            return ['status' => true, 'msg' => "Fornecedor excluido com sucesso"];
        } else {
            return ['status' => false, 'msg' => "Falha ao excluir fornecedor!"];
        }
    }

    public function buscarFornecedorPorToken(string $frn_token)
    {
        $this->where('frn_token', $frn_token);

        return $this->get()->getRow();
    }

    public function buscarListaFornecedores(string $emp_id)
    {
        $this->where('emp_id', $emp_id);
        $this->orderBy('frn_nome', 'ASC');

        return $this->get()->getResult();
    }

    public function buscaFornecedorProdutoEmpresa($empresaId)
    {
        $this->where('emp_id', $empresaId);

        return $this->get()->getRow();
    }
}
