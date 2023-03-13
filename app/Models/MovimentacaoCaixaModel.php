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

    public function buscaListaMovimentacoesCaixa(string $tipoFiltro, string|null $filtroInicio, string|null $filtroFinal, $empresaId)
    {

        switch ($tipoFiltro) {
            case 'periodo':
                $this->select("date_format(mcx_data, '%Y-%m-%d') as mcx_periodo_movimentacao");

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
                $periodoFiltro = "date_format(mcx_data, '%Y-%m-%d') between date_format('" . $filtroInicio . "', '%Y-%m-%d') and date_format('" . $filtroFinal . "', '%Y-%m-%d')";
                break;
            case 'mensal':
                $this->select("date_format(mcx_data, '%Y-%m') as mcx_periodo_movimentacao");
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
                $periodoFiltro = "date_format(mcx_data, '%Y-%m') between date_format('" . $filtroInicio . "', '%Y-%m') and date_format('" . $filtroFinal . "', '%Y-%m')";
                break;
            case 'anual':
                $this->select("date_format(mcx_data, '%Y') as mcx_periodo_movimentacao");
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
                $periodoFiltro = "date_format(mcx_data, '%Y') between {$filtroInicio} and {$filtroFinal}";
                break;
        }

        $this->where("emp_id", $empresaId);
        $this->where($periodoFiltro);
        $this->groupBy('mcx_periodo_movimentacao');
        $this->orderBy('mcx_periodo_movimentacao');

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
