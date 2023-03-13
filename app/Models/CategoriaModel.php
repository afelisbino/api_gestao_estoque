<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriaModel extends Model
{
    protected $table      = 'categoria';
    protected $primaryKey = 'cat_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['cat_nome', 'cat_token', 'emp_id'];

    protected $useTimestamps = false;

    public function salvarCategoria(array $dados)
    {
        if ($this->save($dados)) {
            return ['status' => true, 'msg' => 'Categoria salvo com sucesso'];
        } else {
            return ['status' => false, 'msg' => 'Falha ao salvar a categoria'];
        }
    }

    public function salvarAlteracoesCategoria(array $dados)
    {
        if ($this->save($dados)) {
            return ['status' => true, 'msg' => 'Categoria alterado com sucesso'];
        } else {
            return ['status' => false, 'msg' => 'Falha ao alterar a categoria'];
        }
    }

    public function deletarCategoria(int $cat_id)
    {
        if ($this->delete($cat_id)) {
            return ['status' => true, 'msg' => "Categoria excluido com sucesso"];
        } else {
            return ['status' => false, 'msg' => "Falha ao excluir categoria!"];
        }
    }

    public function buscarCategoriaPorToken(string $cat_token)
    {
        $this->where('cat_token', $cat_token);

        return $this->get()->getRow();
    }

    public function listarCategoria(int $emp_id)
    {
        $this->where('emp_id', $emp_id);
        $this->orderBy('cat_nome', 'ASC');

        return $this->get()->getResult();
    }

    public function buscaCategoriaProdutoEmpresaPorNome(string $nomeCategoria, int $empresaId){
        $this->where('cat_nome', $nomeCategoria);
        $this->where('emp_id', $empresaId);

        return $this->get()->getRow();
    }
}
