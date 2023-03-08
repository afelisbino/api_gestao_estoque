<?php

namespace App\Models;

use CodeIgniter\Model;

class MovimentacaoCaixaModel extends Model
{
    protected $table      = 'movimentacao_caixa';
    protected $primaryKey = 'mcx_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['mcx_data', 'mcx_valor', 'mcx_tipo', 'mcx_comentario', 'emp_id'];

    protected $useTimestamps = false;

    public function buscaMovimentacoesCaixaMensal($mesAnoInicio, $mesAnoFim, $empresaId)
    {

        $periodoFiltro = "date_format(mcx_data, '%Y-%m') between date_format('" . $mesAnoInicio . "', '%Y-%m') and date_format('" . $mesAnoFim . "', '%Y-%m')";

        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'entrada' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m') = date_format(movimentacao_caixa.mcx_data, '%Y-%m')
            ) as mcx_entrada"
        );
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'saida' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m') = date_format(movimentacao_caixa.mcx_data, '%Y-%m')
            ) as mcx_saida"
        );
        $this->where($periodoFiltro);
        $this->where("emp_id", $empresaId);

        return $this->get()->getRow();
    }

    public function buscaMovimentacoesCaixaAnual($anoInicio, $anoFim, $empresaId)
    {

        $periodoFiltro = "date_format(movimentacao_caixa.mcx_data, '%Y') between " . $anoInicio . " and " . $anoFim;

        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'entrada' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y') = date_format(movimentacao_caixa.mcx_data, '%Y')
            ) as mcx_entrada"
        );
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'saida' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y') = date_format(movimentacao_caixa.mcx_data, '%Y')
            ) as mcx_saida"
        );
        $this->where($periodoFiltro);
        $this->where("emp_id", $empresaId);

        return $this->get()->getRow();
    }

    public function buscaMovimentacoesCaixaPeriodo($dataInicio, $dataFim, $empresaId)
    {

        $periodoFiltro = "date_format(mcx_data, '%Y-%m-%d) = " . date('Y-m-d');

        if (!empty($dataInicio) && !empty($dataFim)) {
            $periodoFiltro = "date_format(mcx_data, '%Y-%m-%d') between date_format('" . $dataInicio . "', '%Y-%m-%d') and date_format('" . $dataFim . "', '%Y-%m-%d')";
        }

        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'entrada' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m-%d') = date_format(movimentacao_caixa.mcx_data, '%Y-%m-%d')
            ) as mcx_entrada"
        );
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'saida' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m-%d') = date_format(movimentacao_caixa.mcx_data, '%Y-%m-%d')
            ) as mcx_saida"
        );
        $this->where($periodoFiltro);
        $this->where("emp_id", $empresaId);

        return $this->get()->getRow();
    }

    public function buscaListaMovimentacoesCaixaMensal($mesAnoInicio, $mesAnoFim, $empresaId)
    {

        $periodoFiltro = "date_format(mcx_data, '%Y-%m') between date_format('" . $mesAnoInicio . "', '%Y-%m') and date_format('" . $mesAnoFim . "', '%Y-%m')";

        $this->select("date_format(mcx_data, '%Y-%m') as mcx_mes");
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'entrada' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m') = date_format(movimentacao_caixa.mcx_data, '%Y-%m')
            ) as mcx_entrada"
        );
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'saida' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m') = date_format(movimentacao_caixa.mcx_data, '%Y-%m')
            ) as mcx_saida"
        );
        $this->where($periodoFiltro);
        $this->where("emp_id", $empresaId);
        $this->orderBy("mcx_mes", "ASC");
        $this->groupBy("mcx_tipo");

        return $this->get()->getResult();
    }

    public function buscaListaMovimentacoesCaixaAnual($anoInicio, $anoFim, $empresaId)
    {

        $periodoFiltro = "date_format(mcx_data, '%Y') between " . $anoInicio . " and " . $anoFim;

        $this->select("date_format(mcx_data, '%Y') as mcx_ano");
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'entrada' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y') = date_format(movimentacao_caixa.mcx_data, '%Y')
            ) as mcx_entrada"
        );
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'saida' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y') = date_format(movimentacao_caixa.mcx_data, '%Y')
            ) as mcx_saida"
        );
        $this->where($periodoFiltro);
        $this->where("emp_id", $empresaId);
        $this->orderBy("mcx_ano", "ASC");
        $this->groupBy("mcx_tipo");

        return $this->get()->getResult();
    }

    public function buscaListaMovimentacoesCaixaPeriodo($dataInicio, $dataFim, $empresaId)
    {

        $periodoFiltro = "date_format(mcx_data, '%Y-%m-%d) = " . date('Y-m-d');

        if (!empty($dataInicio) && !empty($dataFim)) {
            $periodoFiltro = "date_format(mcx_data, '%Y-%m-%d') between date_format('" . $dataInicio . "', '%Y-%m-%d') and date_format('" . $dataFim . "', '%Y-%m-%d')";
        }

        $this->select("date_format(mcx_data, '%Y-%m-%d') as mcx_data");
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'entrada' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m-%d') = date_format(movimentacao_caixa.mcx_data, '%Y-%m-%d')
            ) as mcx_entrada"
        );
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'saida' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m-%d') = date_format(movimentacao_caixa.mcx_data, '%Y-%m-%d')
            ) as mcx_saida"
        );
        $this->where($periodoFiltro);
        $this->where("emp_id", $empresaId);
        $this->groupBy("mcx_tipo");
        $this->orderBy("mcx_data", "ASC");

        return $this->get()->getResult();
    }

    public function buscaMovimentacoesCaixaDia($dataMovimentacao, $empresaId)
    {

        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'entrada' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m-%d') = date_format(movimentacao_caixa.mcx_data, '%Y-%m-%d')
                group by date_format(movimentacao_caixa.mcx_data, '%Y-%m-%d')
            ) as mcx_entrada"
        );
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'saida' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m-%d') = date_format(movimentacao_caixa.mcx_data, '%Y-%m-%d')
                group by date_format(movimentacao_caixa.mcx_data, '%Y-%m-%d')
            ) as mcx_saida"
        );
        $this->where("date_format(mcx_data, '%Y-%m-%d') = date_format('" . $dataMovimentacao . "', '%Y-%m-%d')");
        $this->where("emp_id", $empresaId);

        return $this->get()->getRow();
    }

    public function buscaMovimentacoesCaixaMes($mesMovimentacao, $empresaId)
    {

        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'entrada' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m') = date_format(movimentacao_caixa.mcx_data, '%Y-%m')
                group by date_format(movimentacao_caixa.mcx_data, '%Y-%m')
            ) as mcx_entrada"
        );
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'saida' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y-%m') = date_format(movimentacao_caixa.mcx_data, '%Y-%m')
                group by date_format(movimentacao_caixa.mcx_data, '%Y-%m')
            ) as mcx_saida"
        );
        $this->where("date_format(mcx_data, '%Y-%m') = date_format('" . $mesMovimentacao . "', '%Y-%m')");
        $this->where("emp_id", $empresaId);

        return $this->get()->getRow();
    }

    public function buscaMovimentacoesCaixaAno($anoMovimentacao, $empresaId)
    {

        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'entrada' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y') = date_format(movimentacao_caixa.mcx_data, '%Y')
                group by date_format(movimentacao_caixa.mcx_data, '%Y')
            ) as mcx_entrada"
        );
        $this->select(
            "(
                SELECT SUM(mcx.mcx_valor)
                from movimentacao_caixa as mcx 
                where mcx_tipo = 'saida' 
                and mcx.emp_id = {$empresaId} 
                and date_format(mcx.mcx_data, '%Y') = date_format(movimentacao_caixa.mcx_data, '%Y')
                group by date_format(movimentacao_caixa.mcx_data, '%Y')
            ) as mcx_saida"
        );
        $this->where("date_format(mcx_data, '%Y') = " . $anoMovimentacao);
        $this->where("emp_id", $empresaId);

        return $this->get()->getRow();
    }
}
