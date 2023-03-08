<?php

namespace App\Models;

use CodeIgniter\Model;

class VendaModel extends Model
{
    protected $table      = 'venda';
    protected $primaryKey = 'ven_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['ven_data', 'ven_total', 'ven_desconto', 'ven_valor_compra', 'ven_fiado', 'ven_status', 'ven_cliente', 'ven_token', 'ven_tipo', 'ven_tipo_pagamento', 'emp_id'];

    protected $useTimestamps = false;

    public function buscaListaVendasFiadoAbertaEmpresa($emp_id)
    {
        $this->where('ven_status', 'aberto');
        $this->where('ven_tipo', 'local');
        $this->where('ven_fiado', 1);
        $this->where('emp_id', $emp_id);
        $this->orderBy('ven_data', 'ASC');
        $this->orderBy('ven_cliente', 'ASC');


        return $this->get()->getResult();
    }

    public function buscaVendaPorToken($tokenVenda, $empresaId)
    {
        $this->where('ven_token', $tokenVenda);
        $this->where('emp_id', $empresaId);

        return $this->get()->getRow();
    }

    public function buscaQuantidadeVendasLocalEmpresaPeriodo($dataInicio, $dataFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') between date_format('" . $dataInicio . "', '%Y-%m-%d') and date_format('" . $dataFim . "', '%Y-%m-%d')";

        $this->selectCount('*', 'ven_quantidade');
        $this->where('emp_id', $empresaId);
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->where($periodoFiltro);

        return $this->get()->getRow();
    }

    public function buscaQuantidadeVendasLocalEmpresaDataAtual($empresaId)
    {
        $this->selectCount('*', 'ven_quantidade');
        $this->where('emp_id', $empresaId);
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->where("date_format(ven_data, '%Y-%m-%d')", date('Y-m-d'));

        return $this->get()->getRow();
    }

    public function buscaValoresVendasLocalEmpresaDataAtual($empresaId)
    {

        $this->selectSum('ven_total', "ven_valor_total");
        $this->selectSum(
            "venda.ven_total - (
                SELECT SUM(produto.pro_preco_custo) 
                FROM sacola_venda 
                inner join sacola on sacola_venda.scl_id = sacola.scl_id 
                join produto on sacola.pro_id = produto.pro_id
                where sacola_venda.ven_id = venda.ven_id
            )",
            "ven_lucro"
        );
        $this->where('emp_id', $empresaId);
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->where("date_format(ven_data, '%Y-%m-%d')", date('Y-m-d'));

        return $this->get()->getRow();
    }

    public function buscaValoresVendasLocalEmpresaPeriodo($dataInicio, $dataFim, $empresaId)
    {

        $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') between date_format('" . $dataInicio . "', '%Y-%m-%d') and date_format('" . $dataFim . "', '%Y-%m-%d')";

        $this->selectSum('ven_total', "ven_valor_total");
        $this->selectSum(
            "venda.ven_total - (
                SELECT SUM(produto.pro_preco_custo) 
                FROM sacola_venda 
                inner join sacola on sacola_venda.scl_id = sacola.scl_id 
                join produto on sacola.pro_id = produto.pro_id
                where sacola_venda.ven_id = venda.ven_id
            )",
            "ven_lucro"
        );
        $this->where('emp_id', $empresaId);
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->where($periodoFiltro);

        return $this->get()->getRow();
    }

    public function buscaEstatisticasVendasLocalUltimosSeteDias($empresaId)
    {
        $this->select("date_format(ven_data, '%Y-%m-%d') as data_venda");
        $this->selectSum('ven_total', "ven_valor_total");
        $this->select("
        (
            SUM(venda.ven_total) - (
                SELECT SUM(produto.pro_preco_custo) 
                FROM sacola_venda
                join sacola on sacola_venda.scl_id = sacola.scl_id
                inner join venda as v on sacola_venda.ven_id = v.ven_id
                join produto on sacola.pro_id = produto.pro_id 
                where date_format(v.ven_data, '%Y-%m-%d') = date_format(venda.ven_data, '%Y-%m-%d')
                and v.emp_id = venda.emp_id
                and v.ven_status = 'finalizado'
                and v.ven_tipo = 'local'
             ) 
        ) as ven_valor_lucro
        ");
        $this->select("
        (
            SELECT SUM(ven_total) 
            FROM venda as v 
            where date_format(v.ven_data, '%Y-%m-%d') = date_format(venda.ven_data, '%Y-%m-%d') 
            and v.emp_id = venda.emp_id
            and v.ven_status = 'finalizado'
            and v.ven_tipo = 'local'
            and v.ven_tipo_pagamento = 'cartao'
        ) as ven_valor_cartao");
        $this->select("
        (
            SELECT SUM(ven_total) 
            FROM venda as v 
            where date_format(v.ven_data, '%Y-%m-%d') = date_format(venda.ven_data, '%Y-%m-%d') 
            and v.emp_id = venda.emp_id
            and v.ven_status = 'finalizado'
            and v.ven_tipo = 'local'
            and v.ven_tipo_pagamento = 'dinheiro'
        ) as ven_valor_dinheiro");

        $this->select("(
            SELECT COUNT(*) 
            FROM venda as v 
            where date_format(v.ven_data, '%Y-%m-%d') = date_format(venda.ven_data, '%Y-%m-%d') 
            and v.emp_id = venda.emp_id
            and v.ven_status = 'finalizado'
            and v.ven_tipo = 'local'
            and v.ven_fiado = 1
        ) as ven_qtd_fiado");

        $this->select("(
            SELECT COUNT(*) 
            FROM venda as v 
            where date_format(v.ven_data, '%Y-%m-%d') = date_format(venda.ven_data, '%Y-%m-%d') 
            and v.emp_id = venda.emp_id
            and v.ven_status = 'finalizado'
            and v.ven_tipo = 'local'
            and v.ven_fiado = 0
        ) as ven_qtd_normal");
        $this->select("(
            SELECT COUNT(*) 
            FROM venda as v 
            where date_format(v.ven_data, '%Y-%m-%d') = date_format(venda.ven_data, '%Y-%m-%d') 
            and v.emp_id = venda.emp_id
            and v.ven_status = 'finalizado'
            and v.ven_tipo = 'local'
            and v.ven_tipo_pagamento = 'cartao'
        ) as ven_qtd_cartao");
        $this->select("(
            SELECT COUNT(*) 
            FROM venda as v 
            where date_format(v.ven_data, '%Y-%m-%d') = date_format(venda.ven_data, '%Y-%m-%d') 
            and v.emp_id = venda.emp_id
            and v.ven_status = 'finalizado'
            and v.ven_tipo = 'local'
            and v.ven_tipo_pagamento = 'dinheiro'
        ) as ven_qtd_dinheiro");

        $this->where('emp_id', $empresaId);
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->orderBy('ven_data', 'ASC');
        $this->groupBy("date_format(ven_data, '%Y-%m-%d')");
        $this->limit(7);

        return $this->get()->getResult();
    }

    public function buscaListaVendasLocalFinalizadaEmpresaPorPeriodo($dataInicio, $dataFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') between date_format('" . $dataInicio . "', '%Y-%m-%d') and date_format('" . $dataFim . "', '%Y-%m-%d')";

        $this->select();
        $this->select(
            "venda.ven_total - (
                SELECT SUM(produto.pro_preco_custo) 
                FROM sacola_venda 
                inner join sacola on sacola_venda.scl_id = sacola.scl_id 
                join produto on sacola.pro_id = produto.pro_id
                where sacola_venda.ven_id = venda.ven_id
            ) as ven_lucro"
        );
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->where('emp_id', $empresaId);
        $this->where($periodoFiltro);
        $this->groupBy('venda.ven_id');
        $this->orderBy('ven_data', 'ASC');

        return $this->get()->getResult();
    }

    public function buscaListaValoresLucroReceitaVendasFinalizadasMensal($mesAnoInicio, $mesAnoFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y-%m') between date_format('" . $mesAnoInicio . "', '%Y-%m') and date_format('" . $mesAnoFim . "', '%Y-%m')";

        $this->select("date_format(ven_data, '%Y-%m') as ven_mes");
        $this->select(
            "venda.ven_total - (
                SELECT SUM(produto.pro_preco_custo) 
                FROM sacola_venda 
                inner join sacola on sacola_venda.scl_id = sacola.scl_id 
                join produto on sacola.pro_id = produto.pro_id 
                inner join venda as v on sacola_venda.ven_id = v.ven_id 
                where date_format(v.ven_data, '%Y-%m') = date_format(venda.ven_data, '%Y-%m')
            ) as ven_lucro"
        );
        $this->selectSum("venda.ven_total", 'ven_receita');

        $this->where('ven_status', 'finalizado');
        $this->where('emp_id', $empresaId);
        $this->where($periodoFiltro);

        $this->groupBy('ven_mes');
        $this->orderBy('ven_mes', 'ASC');

        return $this->get()->getResult();
    }

    public function buscaListaValoresLucroReceitaVendasFinalizadasAnual($anoInicio, $anoFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y') between " . $anoInicio . " and " . $anoFim;

        $this->select("date_format(ven_data, '%Y') as ven_ano");
        $this->select(
            "venda.ven_total - (
                SELECT SUM(produto.pro_preco_custo) 
                FROM sacola_venda 
                inner join sacola on sacola_venda.scl_id = sacola.scl_id 
                join produto on sacola.pro_id = produto.pro_id 
                inner join venda as v on sacola_venda.ven_id = v.ven_id 
                where date_format(v.ven_data, '%Y') = date_format(venda.ven_data, '%Y')
                and v.emp_id = {$empresaId}
            ) as ven_lucro"
        );
        $this->selectSum("venda.ven_total", 'ven_receita');

        $this->where('ven_status', 'finalizado');
        $this->where('emp_id', $empresaId);
        $this->where($periodoFiltro);

        $this->groupBy('ven_ano');
        $this->orderBy('ven_ano', 'ASC');

        return $this->get()->getResult();
    }

    public function buscaListaValoresLucroReceitaVendasFinalizadasPeriodo($dataInicio, $dataFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y-%m-%d) = " . date('Y-m-d');

        if (!empty($dataInicio) && !empty($dataFim)) {
            $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') between date_format('" . $dataInicio . "', '%Y-%m-%d') and date_format('" . $dataFim . "', '%Y-%m-%d')";
        }

        $this->select("date_format(ven_data, '%Y-%m-%d') as ven_data_periodo");
        $this->select(
            "venda.ven_total - (
                SELECT SUM(produto.pro_preco_custo) 
                FROM sacola_venda 
                inner join sacola on sacola_venda.scl_id = sacola.scl_id 
                join produto on sacola.pro_id = produto.pro_id 
                inner join venda as v on sacola_venda.ven_id = v.ven_id 
                where date_format(v.ven_data, '%Y-%m-%d') = date_format(venda.ven_data, '%Y-%m-%d')
                and v.emp_id = {$empresaId}
            ) as ven_lucro"
        );
        $this->selectSum("venda.ven_total", 'ven_receita');

        $this->where('ven_status', 'finalizado');
        $this->where('emp_id', $empresaId);
        $this->where($periodoFiltro);

        $this->groupBy("ven_data_periodo");
        $this->orderBy("ven_data_periodo", "ASC");

        return $this->get()->getResult();
    }

    public function buscaValoresLucroReceitaVendasFinalizadasMensal($mesAnoInicio, $mesAnoFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y-%m') between date_format('" . $mesAnoInicio . "', '%Y-%m') and date_format('" . $mesAnoFim . "', '%Y-%m')";

        $this->select(
            "venda.ven_total - (
                SELECT SUM(produto.pro_preco_custo) 
                FROM sacola_venda 
                inner join sacola on sacola_venda.scl_id = sacola.scl_id 
                join produto on sacola.pro_id = produto.pro_id 
                inner join venda as v on sacola_venda.ven_id = v.ven_id 
                where date_format(v.ven_data, '%Y-%m') = date_format(venda.ven_data, '%Y-%m')
                and v.emp_id = {$empresaId}
            ) as ven_lucro"
        );
        $this->selectSum("venda.ven_total", 'ven_receita');

        $this->where('ven_status', 'finalizado');
        $this->where('emp_id', $empresaId);
        $this->where($periodoFiltro);

        return $this->get()->getRow();
    }

    public function buscaValoresLucroReceitaVendasFinalizadasAnual($anoInicio, $anoFim, $empresaId)
    {
        $periodoFiltro = "date_format(venda.ven_data, '%Y') between " . $anoInicio . " and " . $anoFim;

        $this->select(
            "venda.ven_total - (SELECT SUM(produto.pro_preco_custo) FROM sacola_venda inner join sacola on sacola_venda.scl_id = sacola.scl_id join produto on sacola.pro_id = produto.pro_id inner join venda as v on sacola_venda.ven_id = v.ven_id where date_format(v.ven_data, '%Y') = date_format(venda.ven_data, '%Y') and v.emp_id = {$empresaId}) as ven_lucro"
        );

        $this->selectSum("venda.ven_total", 'ven_receita');

        $this->where('ven_status', 'finalizado');
        $this->where('emp_id', $empresaId);
        $this->where($periodoFiltro);

        return $this->get()->getRow();
    }

    public function buscaValoresLucroReceitaVendasFinalizadasPeriodo($dataInicio, $dataFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y-%m-%d) = " . date('Y-m-d');

        if (!empty($dataInicio) && !empty($dataFim)) {
            $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') between date_format('" . $dataInicio . "', '%Y-%m-%d') and date_format('" . $dataFim . "', '%Y-%m-%d')";
        }

        $this->select(
            "venda.ven_total - (
                SELECT SUM(produto.pro_preco_custo) 
                FROM sacola_venda 
                inner join sacola on sacola_venda.scl_id = sacola.scl_id 
                join produto on sacola.pro_id = produto.pro_id 
                inner join venda as v on sacola_venda.ven_id = v.ven_id 
                where date_format(v.ven_data, '%Y-%m-%d') = date_format(venda.ven_data, '%Y-%m-%d')
                and v.emp_id = {$empresaId}
            ) as ven_lucro"
        );
        $this->selectSum("venda.ven_total", 'ven_receita');

        $this->where('ven_status', 'finalizado');
        $this->where('emp_id', $empresaId);
        $this->where($periodoFiltro);

        return $this->get()->getRow();
    }
}
