<?php

namespace App\Entities;

use App\Entities\EmpresaEntity;
use App\Models\MovimentacaoCaixaModel;
use App\Models\VendaModel;
use CodeIgniter\I18n\Time;

class CaixaEntity
{

    public function buscaFechamentoCaixaEmpresa(string $tipoFiltro, string|null $filtroInicio = null, string|null $filtroFim = null, EmpresaEntity $empresaEntity): array
    {
        $vendaModel = new VendaModel();

        $dadosReceitaEmpresa = null;

        switch ($tipoFiltro) {
            case 'periodo':
                $dadosReceitaEmpresa = $vendaModel->buscaListaValoresLucroReceitaVendasFinalizadasPeriodo($filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));
                break;
            case 'mensal':
                $dadosReceitaEmpresa = $vendaModel->buscaListaValoresLucroReceitaVendasFinalizadasMensal($filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));
                break;
            case 'anual':
                $dadosReceitaEmpresa = $vendaModel->buscaListaValoresLucroReceitaVendasFinalizadasAnual($filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));
                break;
        }

        if (empty($dadosReceitaEmpresa)) return [];

        return $this->retornaValoresFechamentoCaixaEmpresa(
            tipoFiltro: $tipoFiltro,
            empresaId: $empresaEntity->__get('emp_id'),
            dadosReceitaEmpresa: $dadosReceitaEmpresa
        );
    }

    private function retornaValoresFechamentoCaixaEmpresa($dadosReceitaEmpresa, $tipoFiltro, $empresaId): array
    {
        $movimentacaoCaixaModel = new MovimentacaoCaixaModel();

        $estatisticasCaixa = [];
        $index = 0;

        switch ($tipoFiltro) {
            case 'periodo':
                foreach ($dadosReceitaEmpresa as $receita) {
                    $dateTime = Time::parse($receita->ven_data_periodo, "America/Sao_Paulo");

                    $estatisticasCaixa[$index]['data'] = $dateTime->toLocalizedString('dd/MM/YYYY');

                    $dadosMovimentacoesCaixaEmpresa = $movimentacaoCaixaModel->buscaMovimentacoesCaixaDia($receita->ven_data_periodo, $empresaId);

                    $valorEntradaCaixa = empty($dadosMovimentacoesCaixaEmpresa->mcx_entrada) ? 0 : $dadosMovimentacoesCaixaEmpresa->mcx_entrada;
                    $valorSaidaCaixa = empty($dadosMovimentacoesCaixaEmpresa->mcx_saida) ? 0 : $dadosMovimentacoesCaixaEmpresa->mcx_saida;

                    $valorFechamento = (($receita->ven_receita + $valorEntradaCaixa) - $valorSaidaCaixa);

                    $estatisticasCaixa[$index]['valorFechamento'] = $valorFechamento;

                    $index++;
                }

                break;
            case 'mensal':
                foreach ($dadosReceitaEmpresa as $receita) {
                    $dateTime = Time::parse($receita->ven_mes, "America/Sao_Paulo");

                    $estatisticasCaixa[$index]['data'] = $dateTime->toLocalizedString('MM/YYYY');

                    $dadosMovimentacoesCaixaEmpresa = $movimentacaoCaixaModel->buscaMovimentacoesCaixaMes($receita->ven_mes, $empresaId);

                    $valorEntradaCaixa = empty($dadosMovimentacoesCaixaEmpresa->mcx_entrada) ? 0 : $dadosMovimentacoesCaixaEmpresa->mcx_entrada;
                    $valorSaidaCaixa = empty($dadosMovimentacoesCaixaEmpresa->mcx_saida) ? 0 : $dadosMovimentacoesCaixaEmpresa->mcx_saida;

                    $valorFechamento = (($receita->ven_receita + $valorEntradaCaixa) - $valorSaidaCaixa);

                    $estatisticasCaixa[$index]['valorFechamento'] = $valorFechamento;

                    $index++;
                }
                break;
            case 'anual':
                foreach ($dadosReceitaEmpresa as $receita) {
                    $dateTime = Time::parse($receita->ven_ano, "America/Sao_Paulo");

                    $estatisticasCaixa[$index]['data'] = $dateTime->toLocalizedString('YYYY');

                    $dadosMovimentacoesCaixaEmpresa = $movimentacaoCaixaModel->buscaMovimentacoesCaixaAno($receita->ven_ano, $empresaId);

                    $valorEntradaCaixa = empty($dadosMovimentacoesCaixaEmpresa->mcx_entrada) ? 0 : $dadosMovimentacoesCaixaEmpresa->mcx_entrada;
                    $valorSaidaCaixa = empty($dadosMovimentacoesCaixaEmpresa->mcx_saida) ? 0 : $dadosMovimentacoesCaixaEmpresa->mcx_saida;

                    $valorFechamento = (($receita->ven_receita + $valorEntradaCaixa) - $valorSaidaCaixa);

                    $estatisticasCaixa[$index]['valorFechamento'] = $valorFechamento;

                    $index++;
                }
                break;
        }

        return $estatisticasCaixa;
    }

    public function buscaEstatisticasResumidoCaixa(string $tipoFiltro, string|null $filtroInicio = null, string|null $filtroFim = null, EmpresaEntity $empresaEntity): array
    {

        $vendaModel = new VendaModel();
        $movimentacaoCaixaModel = new MovimentacaoCaixaModel();

        $dadosReceitaEmpresa = null;

        switch ($tipoFiltro) {
            case 'periodo':
                $dadosReceitaEmpresa = $vendaModel->buscaValoresLucroReceitaVendasFinalizadasPeriodo($filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));
                break;
            case 'mensal':
                $dadosReceitaEmpresa = $vendaModel->buscaValoresLucroReceitaVendasFinalizadasMensal($filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));
                break;
            case 'anual':
                $dadosReceitaEmpresa = $vendaModel->buscaValoresLucroReceitaVendasFinalizadasAnual($filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));
                break;
        }

        $dadosMovimentacoesCaixaEmpresa = $movimentacaoCaixaModel->buscaListaMovimentacoesCaixa($tipoFiltro, $filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));

        if (empty($dadosMovimentacoesCaixaEmpresa) && empty($dadosReceitaEmpresa)) return [];

        $valorEntradaCaixa = 0;
        $valorSaidaCaixa = 0;

        foreach ($dadosMovimentacoesCaixaEmpresa as $movimentacao) {
            $valorEntradaCaixa += $movimentacao->mcx_entrada;
            $valorSaidaCaixa += $movimentacao->mcx_saida;
        }

        return [
            'valorTotalReceita' => empty($dadosReceitaEmpresa->ven_receita) ? 0 : $dadosReceitaEmpresa->ven_receita,
            'valorTotalLucro' => empty($dadosReceitaEmpresa->ven_lucro) ? 0 : $dadosReceitaEmpresa->ven_lucro,
            'valorTotalEntrada' => $valorEntradaCaixa,
            'valorTotalSaida' => $valorSaidaCaixa
        ];
    }

    public function buscaReceitaLucroEmpresa(string $tipoFiltro, string|null $filtroInicio = null, string|null $filtroFim = null, EmpresaEntity $empresaEntity): array
    {
        $vendaModel = new VendaModel();

        $dadosReceitaEmpresa = null;
        $estatisticasReceitaLucro = [];
        $index = 0;

        switch ($tipoFiltro) {
            case 'periodo':
                $dadosReceitaEmpresa = $vendaModel->buscaListaValoresLucroReceitaVendasFinalizadasPeriodo($filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));

                if (empty($dadosReceitaEmpresa)) return [];

                foreach ($dadosReceitaEmpresa as $valores) {
                    $dateTime = Time::parse($valores->ven_data_periodo, "America/Sao_Paulo");

                    $estatisticasReceitaLucro[$index]['data'] = $dateTime->toLocalizedString('dd/MM/YYYY');
                    $estatisticasReceitaLucro[$index]['valorReceita'] = $valores->ven_receita;
                    $estatisticasReceitaLucro[$index]['valorLucro'] = $valores->ven_lucro;

                    $index++;
                }

                break;
            case 'mensal':
                $dadosReceitaEmpresa = $vendaModel->buscaListaValoresLucroReceitaVendasFinalizadasMensal($filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));

                if (empty($dadosReceitaEmpresa)) return [];

                foreach ($dadosReceitaEmpresa as $valores) {
                    $dateTime = Time::parse($valores->ven_mes, "America/Sao_Paulo");

                    $estatisticasReceitaLucro[$index]['data'] = $dateTime->toLocalizedString('MM/YYYY');
                    $estatisticasReceitaLucro[$index]['valorReceita'] = $valores->ven_receita;
                    $estatisticasReceitaLucro[$index]['valorLucro'] = $valores->ven_lucro;

                    $index++;
                }
                break;
            case 'anual':
                $dadosReceitaEmpresa = $vendaModel->buscaListaValoresLucroReceitaVendasFinalizadasAnual($filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));

                if (empty($dadosReceitaEmpresa)) return [];

                foreach ($dadosReceitaEmpresa as $valores) {
                    $dateTime = Time::parse($valores->ven_ano, "America/Sao_Paulo");

                    $estatisticasReceitaLucro[$index]['data'] = $dateTime->toLocalizedString('YYYY');
                    $estatisticasReceitaLucro[$index]['valorReceita'] = $valores->ven_receita;
                    $estatisticasReceitaLucro[$index]['valorLucro'] = $valores->ven_lucro;

                    $index++;
                }
                break;
        }

        return $estatisticasReceitaLucro;
    }

    public function buscaMovimentacoesCaixaEmpresa(string $tipoFiltro, string|null $filtroInicio = null, string|null $filtroFim = null, EmpresaEntity $empresaEntity): array
    {
        $movimentacaoCaixaModel = new MovimentacaoCaixaModel();

        $dadosMovimentacoesCaixaEmpresa = null;
        $estatisticasMovimentacao = [];
        $index = 0;

        $dadosMovimentacoesCaixaEmpresa = $movimentacaoCaixaModel->buscaListaMovimentacoesCaixa($tipoFiltro, $filtroInicio, $filtroFim, $empresaEntity->__get('emp_id'));

        switch ($tipoFiltro) {
            case 'periodo':


                foreach ($dadosMovimentacoesCaixaEmpresa as $movimentacao) {
                    $dateTime = Time::parse($movimentacao->mcx_periodo_movimentacao, "America/Sao_Paulo");

                    $estatisticasMovimentacao[$index]['data'] = $dateTime->toLocalizedString('dd/MM/YYYY');

                    $estatisticasMovimentacao[$index]['valorEntrada'] = $movimentacao->mcx_entrada;
                    $estatisticasMovimentacao[$index]['valorSaida'] = $movimentacao->mcx_saida;


                    $index++;
                }

                break;
            case 'mensal':

                foreach ($dadosMovimentacoesCaixaEmpresa as $movimentacao) {
                    $dateTime = Time::parse($movimentacao->mcx_periodo_movimentacao, "America/Sao_Paulo");

                    $estatisticasMovimentacao[$index]['data'] = $dateTime->toLocalizedString('MM/YYYY');

                    $estatisticasMovimentacao[$index]['valorEntrada'] = $movimentacao->mcx_entrada;
                    $estatisticasMovimentacao[$index]['valorSaida'] = $movimentacao->mcx_saida;

                    $index++;
                }
                break;
            case 'anual':

                foreach ($dadosMovimentacoesCaixaEmpresa as $movimentacao) {
                    $dateTime = Time::parse($movimentacao->mcx_periodo_movimentacao, "America/Sao_Paulo");

                    $estatisticasMovimentacao[$index]['data'] = $dateTime->toLocalizedString('YYYY');
                    $estatisticasMovimentacao[$index]['valorEntrada'] = $movimentacao->mcx_entrada;
                    $estatisticasMovimentacao[$index]['valorSaida'] = $movimentacao->mcx_saida;

                    $index++;
                }
                break;
        }

        return $estatisticasMovimentacao;
    }
}
