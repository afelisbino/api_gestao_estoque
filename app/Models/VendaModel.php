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

    protected $allowedFields = ['ven_data', 'ven_total', 'ven_desconto', 'ven_valor_compra', 'ven_fiado', 'ven_status', 'ven_cliente', 'ven_token', 'ven_tipo', 'ven_tipo_pagamento', 'ven_lucro', 'ven_margem_lucro', 'emp_id'];

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
            "ven_lucro",
            "ven_lucro"
        );
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');
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
            "ven_lucro",
            "ven_lucro"
        );
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');
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
        $this->selectSum('ven_lucro', "ven_valor_lucro");
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');

        $this->where('emp_id', $empresaId);
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->orderBy('ven_data', 'DESC');
        $this->groupBy("date_format(ven_data, '%Y-%m-%d')");
        $this->limit(7);

        return $this->get()->getResult();
    }

    public function buscaEstatisticasVendasLocalPeriodo($dataInicio, $dataFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') between date_format('" . $dataInicio . "','%Y-%m-%d') and date_format('" . $dataFim . "','%Y-%m-%d')";

        $this->select("date_format(ven_data, '%Y-%m-%d') as data_venda");
        $this->selectSum('ven_total', "ven_valor_total");
        $this->selectSum('ven_lucro', "ven_valor_lucro");
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');

        $this->where('emp_id', $empresaId);
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->where($periodoFiltro);

        $this->orderBy('ven_data', 'ASC');
        $this->groupBy("data_venda");

        return $this->get()->getResult();
    }

    public function buscaEstatisticasVendaFiado($empresaId, $data)
    {
        $this->selectCount('*', "ven_qtd_fiado");
        $this->where('emp_id', $empresaId);
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->where('ven_fiado', '1');
        $this->where("date_format(ven_data, '%Y-%m-%d')", $data);

        return $this->get()->getRow();
    }

    public function buscaEstatisticasVendaNormal($empresaId, $data)
    {
        $this->selectCount('*', "ven_qtd_normal");
        $this->where('emp_id', $empresaId);
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->where('ven_fiado', '0');
        $this->where("date_format(ven_data, '%Y-%m-%d')", $data);

        return $this->get()->getRow();
    }

    public function buscaEstatisticasFormaPagamentos($empresaId, $dataVenda)
    {
        $this->select('tipo_pagamento.tpg_nome');
        $this->selectSum('venda.ven_total', "ven_valor");
        $this->selectCount('venda.ven_id', "ven_qtd");
        $this->join('forma_pagamento_venda', 'venda.ven_id = forma_pagamento_venda.ven_id', 'inner');
        $this->join('tipo_pagamento', 'forma_pagamento_venda.tpg_id = tipo_pagamento.tpg_id');
        $this->where('venda.emp_id', $empresaId);
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->where("date_format(ven_data, '%Y-%m-%d')", $dataVenda);
        $this->groupBy('tpg_nome');

        return $this->get()->getResult();
    }

    public function buscaListaVendasLocalFinalizadaEmpresaPorPeriodo($dataInicio, $dataFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') between date_format('" . $dataInicio . "','%Y-%m-%d') and date_format('" . $dataFim . "','%Y-%m-%d')";

        $this->select();
        $this->selectSum(
            "ven_lucro",
            "ven_lucro"
        );
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');
        $this->join('forma_pagamento_venda', 'venda.ven_id = forma_pagamento_venda.ven_id', 'inner');
        $this->join('tipo_pagamento', 'forma_pagamento_venda.tpg_id = tipo_pagamento.tpg_id');
        $this->where('ven_status', 'finalizado');
        $this->where('ven_tipo', 'local');
        $this->where('venda.emp_id', $empresaId);
        $this->where($periodoFiltro);
        $this->groupBy('venda.ven_id');
        $this->orderBy('ven_data', 'ASC');

        return $this->get()->getResult();
    }

    public function buscaListaValoresLucroReceitaVendasFinalizadasMensal($mesAnoInicio, $mesAnoFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y-%m') between date_format('" . $mesAnoInicio . "', '%Y-%m') and date_format('" . $mesAnoFim . "', '%Y-%m')";

        $this->select("date_format(ven_data, '%Y-%m') as ven_mes");
        $this->selectSum(
            "ven_lucro",
            "ven_lucro"
        );
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');
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
        $this->selectSum(
            "ven_lucro",
            "ven_lucro"
        );
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');
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
            $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') between date_format('" . $dataInicio . "','%Y-%m-%d') and date_format('" . $dataFim . "','%Y-%m-%d')";
        }

        $this->select("date_format(ven_data, '%Y-%m-%d') as ven_data_periodo");
        $this->selectSum(
            "ven_lucro",
            "ven_lucro"
        );
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');
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

        $this->selectSum(
            "ven_lucro",
            "ven_lucro"
        );
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');
        $this->selectSum("venda.ven_total", 'ven_receita');

        $this->where('ven_status', 'finalizado');
        $this->where('emp_id', $empresaId);
        $this->where($periodoFiltro);

        return $this->get()->getRow();
    }

    public function buscaValoresLucroReceitaVendasFinalizadasAnual($anoInicio, $anoFim, $empresaId)
    {
        $periodoFiltro = "date_format(venda.ven_data, '%Y') between " . $anoInicio . " and " . $anoFim;

        $this->selectSum(
            "ven_lucro",
            "ven_lucro"
        );
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');

        $this->selectSum("venda.ven_total", 'ven_receita');

        $this->where('ven_status', 'finalizado');
        $this->where('emp_id', $empresaId);
        $this->where($periodoFiltro);

        return $this->get()->getRow();
    }

    public function buscaValoresLucroReceitaVendasFinalizadasPeriodo($dataInicio, $dataFim, $empresaId)
    {
        $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') = " . date('Y-m-d');

        if (!empty($dataInicio) && !empty($dataFim)) {
            $periodoFiltro = "date_format(ven_data, '%Y-%m-%d') between date_format('" . $dataInicio . "','%Y-%m-%d') and date_format('" . $dataFim . "','%Y-%m-%d')";
        }

        $this->selectSum(
            "ven_lucro",
            "ven_lucro"
        );
        $this->selectSum('ven_margem_lucro', 'ven_porcentagem_lucro');
        $this->selectSum("venda.ven_total", 'ven_receita');

        $this->where('ven_status', 'finalizado');
        $this->where('emp_id', $empresaId);
        $this->where($periodoFiltro);

        return $this->get()->getRow();
    }

    public function verificaVendaExistenteEmpresa(string $datHoraVenda, int $empresaId)
    {
        $this->where('ven_data', $datHoraVenda);
        $this->where('emp_id', $empresaId);

        return $this->get()->getRow();
    }

    public function buscaListaVendasEmpresa(int $empresaId)
    {
        $this->select('ven_id, ven_total, ven_tipo_pagamento');
        $this->where('emp_id', $empresaId);
        $this->whereNotIn('ven_tipo_pagamento', ['desabilitado']);

        return $this->get()->getResult();
    }
}
