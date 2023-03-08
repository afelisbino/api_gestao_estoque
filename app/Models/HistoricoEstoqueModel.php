<?php

namespace App\Models;

use CodeIgniter\Model;

class HistoricoEstoqueModel extends Model
{
    protected $table      = 'historico_saida_produto';
    protected $primaryKey = 'hsp_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['hsp_tipo', 'hsp_data', 'hsp_qtd_registro', 'hsp_qtd_antigo', 'hsp_qtd_atual', 'est_id'];

    protected $useTimestamps = false;

    public function buscarListaHistoricoEstoqueEmpresa(int $emp_id, string|null $dataInicio = null, string|null $dataFim = null)
    {

        $this->join('estoque', 'historico_saida_produto.est_id = estoque.est_id');
        $this->join('produto', 'estoque.pro_id = produto.pro_id');

        $this->where('produto.emp_id', $emp_id);

        if (!empty($dataInicio) || !empty($dataFim)) {
            $periodoFiltro = "date_format(hsp_data, '%Y-%m-%d') between date_format('" . $dataInicio . "', '%Y-%m-%d') and date_format('" . $dataFim . "', '%Y-%m-%d')";
            $this->where($periodoFiltro);
        } else {
            $this->where("date_format(hsp_data, '%Y-%m-%d')", date('Y-m-d'));
        }

        $this->orderBy('hsp_data', 'DESC');

        return $this->get()->getResult();
    }

    public function buscarListaHistoricoProduto(string $tokenProduto)
    {
        $this->join('estoque', 'historico_saida_produto.est_id = estoque.est_id');
        $this->join('produto', 'estoque.pro_id = produto.pro_id');

        $this->where('produto.pro_token', $tokenProduto);

        $this->orderBy('hsp_data', 'DESC');
        $this->limit(50);

        return $this->get()->getResult();
    }

    public function buscarEstatisticasHistoricoEstoque(int $empresaId, string|null $dataInicio = null, string|null $dataFim = null)
    {
        $this->selectCount("*", "total_movimentacoes");
        $this->select('hsp_tipo as tipo_movimentacao');
        $this->join('estoque', 'historico_saida_produto.est_id = estoque.est_id');
        $this->join('produto', 'estoque.pro_id = produto.pro_id');
        $this->where('produto.emp_id', $empresaId);

        if (!empty($dataInicio) || !empty($dataFim)) {
            $periodoFiltro = "date_format(hsp_data, '%Y-%m-%d') between date_format('" . $dataInicio . "', '%Y-%m-%d') and date_format('" . $dataFim . "', '%Y-%m-%d')";
            $this->where($periodoFiltro);
        } else {
            $this->where("date_format(hsp_data, '%Y-%m-%d')", date('Y-m-d'));
        }

        $this->groupBy('hsp_tipo');

        return $this->get()->getResult();
    }
}
