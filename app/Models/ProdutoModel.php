<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdutoModel extends Model
{
    protected $table      = 'produto';
    protected $primaryKey = 'pro_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['pro_nome', 'pro_valor_venda', 'pro_preco_custo', 'pro_token', 'pro_descricao', 'pro_disponivel', 'frn_id', 'cat_id', 'emp_id'];

    protected $useTimestamps = false;

    public function buscarProdutoEstoquePorToken(string $pro_token)
    {
        $this->join('estoque', 'produto.pro_id = estoque.pro_id');
        $this->join('fornecedor', 'produto.frn_id = fornecedor.frn_id');
        $this->join('categoria', 'produto.cat_id = categoria.cat_id');
        $this->where('pro_token', $pro_token);

        return $this->get()->getRow();
    }

    public function listarProdutosEstoqueEmpresa($emp_id)
    {

        $this->join('fornecedor', 'produto.frn_id = fornecedor.frn_id');
        $this->join('categoria', 'produto.cat_id = categoria.cat_id');
        $this->join('estoque', 'produto.pro_id = estoque.pro_id', 'left');
        $this->where('produto.emp_id', $emp_id);
        $this->orderBy('pro_nome', 'ASC');

        return $this->get()->getResult();
    }

    public function buscarProdutoEstoquePorCodigoBarras(string $pcb_codigo, int $emp_id)
    {
        $this->join('fornecedor', 'produto.frn_id = fornecedor.frn_id');
        $this->join('categoria', 'produto.cat_id = categoria.cat_id');
        $this->join('estoque', 'produto.pro_id = estoque.pro_id', 'left');
        $this->join('produto_codigo_barra', 'produto.pro_id = produto_codigo_barra.pro_id');

        $this->where('produto.emp_id', $emp_id);
        $this->where('produto_codigo_barra.pcb_codigo', $pcb_codigo);

        return $this->get()->getRow();
    }

    public function listaProdutoAtivoEmpresa(int $emp_id)
    {
        $this->join('fornecedor', 'produto.frn_id = fornecedor.frn_id');
        $this->join('categoria', 'produto.cat_id = categoria.cat_id');
        $this->join('estoque', 'produto.pro_id = estoque.pro_id', 'left');

        $this->where('produto.emp_id', $emp_id);
        $this->where('produto.pro_disponivel', 1);
        $this->where('estoque.est_qtd_atual > 0');
        $this->orderBy('pro_nome', 'ASC');

        return $this->get()->getResult();
    }
}
