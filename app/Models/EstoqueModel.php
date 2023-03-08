<?php

namespace App\Models;

use CodeIgniter\Model;

class EstoqueModel extends Model
{
    protected $table      = 'estoque';
    protected $primaryKey = 'est_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['est_qtd_atual', 'est_qtd_minimo', 'pro_id'];

    protected $useTimestamps = false;

    public function buscarEstoqueProdutoPorToken(string $tokenProduto)
    {
        $this->join('produto', 'estoque.pro_id = produto.pro_id');
        $this->where('produto.pro_token', $tokenProduto);

        return $this->get()->getRow();
    }

    public function buscarEstatisticasEstoqueEmpresa(int $empresaId)
    {
        $this->select("(SELECT COUNT(*) FROM produto where produto.pro_disponivel = 0 and produto.emp_id = {$empresaId}) as total_desativados");
        $this->select("(SELECT COUNT(*) FROM produto join estoque on produto.pro_id = estoque.pro_id where estoque.est_qtd_atual = 0 and produto.pro_disponivel = 1 and produto.emp_id = {$empresaId}) as total_zerado");
        $this->select("(SELECT COUNT(*) FROM produto join estoque on produto.pro_id = estoque.pro_id where estoque.est_qtd_atual <= estoque.est_qtd_minimo and produto.pro_disponivel = 1 and produto.emp_id = {$empresaId}) as total_minimo");
        $this->selectSum('est_qtd_atual', "total_produto_estoque");
        $this->join('produto', 'estoque.pro_id = produto.pro_id');
        $this->where("produto.emp_id", $empresaId);
        $this->where("produto.pro_disponivel", 1);

        return $this->get()->getRow();
    }
}
