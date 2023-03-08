<?php

namespace App\Models;

use CodeIgniter\Model;

class SacolaVendaModel extends Model
{
    protected $table      = 'sacola_venda';

    protected $returnType     = 'array';

    protected $allowedFields = ['ven_id', 'scl_id'];

    public function buscaListaItensVenda($tokenVenda, $empresaId)
    {
        $this->join('venda', 'sacola_venda.ven_id = venda.ven_id');
        $this->join('sacola', 'sacola_venda.scl_id = sacola.scl_id');
        $this->join('produto', 'sacola.pro_id = produto.pro_id');

        $this->where('venda.ven_token', $tokenVenda);
        $this->where('venda.emp_id', $empresaId);

        return $this->get()->getResult();
    }

    public function buscarProdutosMaisVendidos(int $empresaId, string|null $dataInicio = null, string|null $dataFim = null)
    {

        $this->select("produto.pro_nome as nome_produto");
        $this->selectSum('sacola.scl_qtd', 'quantidade_vendido');
        $this->join('venda', 'sacola_venda.ven_id = venda.ven_id');
        $this->join('sacola', 'sacola_venda.scl_id = sacola.scl_id');
        $this->join('produto', 'sacola.pro_id = produto.pro_id');
        $this->where('venda.emp_id', $empresaId);
        $this->where('venda.ven_status', 'finalizado');

        if (!empty($dataInicio) || !empty($dataFim)) {
            $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') between date_format('" . $dataInicio . "', '%Y-%m-%d') and date_format('" . $dataFim . "', '%Y-%m-%d')";
            $this->where($periodoFiltro);
        } else {
            $this->where("date_format(ven_data, '%Y-%m-%d')", date('Y-m-d'));
        }
        $this->groupBy('produto.pro_id');
        $this->orderBy('quantidade_vendido', 'DESC');
        $this->limit(3);

        return $this->get()->getResult();
    }
}
