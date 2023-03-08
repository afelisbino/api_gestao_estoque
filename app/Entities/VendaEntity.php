<?php

namespace App\Entities;

use App\Libraries\Uuid;
use App\Models\VendaModel;
use CodeIgniter\I18n\Time;

class VendaEntity
{

    public function __construct(
        private int|null $ven_id = null,
        private string|null $ven_data = null,
        private string|null $ven_status = null,
        private string|null $ven_cliente = null,
        private bool|int $ven_fiado = 0,
        private string|null $ven_token = null,
        private string|null $ven_tipo = null,
        private float $ven_total = 0,
        private float $ven_desconto = 0,
        private float $ven_valor_compra = 0,
        private string|null $ven_tipo_pagamento = null,
        private EmpresaEntity $empresa = new EmpresaEntity()
    ) {
    }

    public function __get($name)
    {
        return $this->{$name};
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    public function registraVendaLocalNormal(array $dadosVenda, EmpresaEntity $empresa)
    {
        if (empty($dadosVenda)) return ['status' => false, 'msg' => "Nenhum informação de venda foi enviado!"];

        $this->ven_valor_compra = $dadosVenda['vendaValorCompra'];
        $this->ven_desconto = $dadosVenda['vendaValorDesconto'];
        $this->ven_total = $this->calculaTotalVenda($dadosVenda['vendaValorCompra'], $dadosVenda['vendaValorDesconto']);
        $this->ven_tipo = "local";
        $this->ven_status = "finalizado";
        $this->ven_tipo_pagamento = $dadosVenda['vendaTipoPagamento'];
        $this->empresa = $empresa;

        $idVenda = $this->salvaVenda($this);

        if ($idVenda === 0) return ['status' => false, 'msg' => "Falha ao salvar a venda, tente novamente!"];

        $sacolaVendaEntity = new SacolaVendaEntity();

        $adicionaItensSacolaVenda = $sacolaVendaEntity->adicionaItemSacolaVenda($dadosVenda['itens'], $idVenda);

        return $adicionaItensSacolaVenda;
    }

    public function registraVendaLocalFiado(array $dadosVenda, EmpresaEntity $empresa)
    {
        if (empty($dadosVenda)) return ['status' => false, 'msg' => "Nenhum informação de venda foi enviado!"];

        $this->ven_valor_compra = $dadosVenda['vendaValorCompra'];
        $this->ven_desconto = 0;
        $this->ven_total = $dadosVenda['vendaValorCompra'];
        $this->ven_fiado = true;
        $this->ven_cliente = $dadosVenda['nomeCliente'];
        $this->ven_tipo = "local";
        $this->ven_status = "aberto";
        $this->ven_tipo_pagamento = "dinheiro";
        $this->empresa = $empresa;

        $idVenda = $this->salvaVenda($this);

        if ($idVenda === 0) return ['status' => false, 'msg' => "Falha ao salvar a venda, tente novamente!"];

        $sacolaVendaEntity = new SacolaVendaEntity();

        $adicionaItensSacolaVenda = $sacolaVendaEntity->adicionaItemSacolaVenda($dadosVenda['itens'], $idVenda);

        return $adicionaItensSacolaVenda;
    }

    public function listaVendaFiadoAberto(EmpresaEntity $empresa)
    {
        $vendaModel = new VendaModel();

        $listaVendasFiado = $vendaModel->buscaListaVendasFiadoAbertaEmpresa($empresa->__get('emp_id'));

        $vendasFiadoAbertas = [];
        $index = 0;

        foreach ($listaVendasFiado as $venda) {
            $dateTime = Time::parse($venda->ven_data, "America/Sao_Paulo");

            $vendasFiadoAbertas[$index]['ven_id'] = $venda->ven_token;
            $vendasFiadoAbertas[$index]['ven_cliente'] = $venda->ven_cliente;
            $vendasFiadoAbertas[$index]['ven_data'] = $dateTime->toLocalizedString('dd/MM/YYYY HH:mm');
            $vendasFiadoAbertas[$index]['ven_total'] = $venda->ven_total;

            $index++;
        }

        return $vendasFiadoAbertas;
    }

    public function alteraStatusVendaFiadoParaPago(VendaEntity $vendaEntity)
    {
        if (!Uuid::is_valid($vendaEntity->__get('ven_token'))) return ['status' => false, 'msg' => "Token da venda inválido!"];

        $vendaModel = new VendaModel();

        $dadosVenda = $vendaModel->buscaVendaPorToken($vendaEntity->__get('ven_token'), $vendaEntity->__get('empresa')->__get('emp_id'));

        if (empty($dadosVenda)) return ['status' => false, 'msg' => "Venda não encontrado!"];

        if ($vendaModel->save([
            'ven_status' => 'finalizado',
            'ven_data' => date('Y-m-d H:i:s'),
            'ven_id' => $dadosVenda->ven_id
        ])) return ['status' => true, 'msg' => "Venda fiado pago com sucesso!"];

        return ['status' => false, 'msg' => "Falha ao pagar venda, tente novamente"];
    }

    public function recuperaQuantidadeVendasLocalDataAtual(EmpresaEntity $empresaEntity)
    {
        $vendaModel = new VendaModel();

        $valoresEstatisticasVendas = $vendaModel->buscaValoresVendasLocalEmpresaDataAtual($empresaEntity->__get('emp_id'));

        $quantidadeVendas = $vendaModel->buscaQuantidadeVendasLocalEmpresaDataAtual($empresaEntity->__get('emp_id'));

        return [
            'qtdTotalVendas' => (int) $quantidadeVendas->ven_quantidade,
            'valorTotalVendas' => (float) $valoresEstatisticasVendas->ven_valor_total,
            'valorTotalLucro' => (float) $valoresEstatisticasVendas->ven_lucro,
        ];
    }

    public function recuperaQuantidadeVendasLocalPorPeriodo(EmpresaEntity $empresaEntity, $dataInicio, $dataFim)
    {
        $vendaModel = new VendaModel();

        $valoresEstatisticasVendas = $vendaModel->buscaValoresVendasLocalEmpresaPeriodo($dataInicio, $dataFim, $empresaEntity->__get('emp_id'));

        $quantidadeVendas = $vendaModel->buscaQuantidadeVendasLocalEmpresaPeriodo($dataInicio, $dataFim, $empresaEntity->__get('emp_id'));

        return [
            'qtdTotalVendas' => (int) $quantidadeVendas->ven_quantidade,
            'valorTotalVendas' => (float) $valoresEstatisticasVendas->ven_valor_total,
            'valorTotalLucro' => (float) $valoresEstatisticasVendas->ven_lucro,
        ];
    }

    public function recuperaEstatisticasVendasLocalEmpresa(EmpresaEntity $empresaEntity)
    {
        $vendaModel = new VendaModel();

        $dadosEstatisticasVendas = $vendaModel->buscaEstatisticasVendasLocalUltimosSeteDias($empresaEntity->__get('emp_id'));

        $estatisticaVendas = [];
        $index = 0;

        foreach ($dadosEstatisticasVendas as $valoresVendas) {
            $dateTime = Time::parse($valoresVendas->data_venda, "America/Sao_Paulo");

            $estatisticaVendas[$index]['dataLabel'] = $dateTime->toLocalizedString('dd/MM/YYYY');
            $estatisticaVendas[$index]['valorTotalVendas'] = $valoresVendas->ven_valor_total;
            $estatisticaVendas[$index]['valorTotalGanhos'] = $valoresVendas->ven_valor_lucro;
            $estatisticaVendas[$index]['valorTotalCartao'] = $valoresVendas->ven_valor_cartao;
            $estatisticaVendas[$index]['valorTotalDinheiro'] = $valoresVendas->ven_valor_dinheiro;
            $estatisticaVendas[$index]['totalCartao'] = $valoresVendas->ven_qtd_cartao;
            $estatisticaVendas[$index]['totalDinheiro'] = $valoresVendas->ven_qtd_dinheiro;
            $estatisticaVendas[$index]['totalFiado'] = $valoresVendas->ven_qtd_fiado;
            $estatisticaVendas[$index]['totalNormal'] = $valoresVendas->ven_qtd_normal;

            $index++;
        }

        return $estatisticaVendas;
    }

    public function listaVendasLocalEmpresaRealizadaPeriodo(EmpresaEntity $empresaEntity, $dataInicio, $dataFim)
    {
        $vendaModel = new VendaModel();

        $vendasFinalizadas = $vendaModel->buscaListaVendasLocalFinalizadaEmpresaPorPeriodo($dataInicio, $dataFim, $empresaEntity->__get('emp_id'));

        $listaVendas = [];
        $index = 0;

        foreach ($vendasFinalizadas as $venda) {
            $dateTime = Time::parse($venda->ven_data, "America/Sao_Paulo");

            $listaVendas[$index]['ven_id'] = $venda->ven_token;
            $listaVendas[$index]['ven_data'] = $dateTime->toLocalizedString('dd/MM/YYYY HH:mm');
            $listaVendas[$index]['ven_tipo'] = $venda->ven_fiado == 0 ? "Normal" : "Fiado";
            $listaVendas[$index]['ven_pagamento'] = ucfirst($venda->ven_tipo_pagamento);
            $listaVendas[$index]['ven_valor_compra'] = $venda->ven_valor_compra;
            $listaVendas[$index]['ven_desconto'] = $venda->ven_desconto;
            $listaVendas[$index]['ven_total'] = $venda->ven_total;
            $listaVendas[$index]['ven_lucro'] = $venda->ven_lucro;

            $index++;
        }

        return $listaVendas;
    }

    private function salvaVenda(VendaEntity $vendaEntity): int
    {
        $dadosVenda = [
            'ven_token' => Uuid::v4(),
            'ven_data' => date('Y-m-d H:i:s'),
            'ven_status' => $vendaEntity->__get('ven_status'),
            'ven_cliente' => $vendaEntity->__get('ven_cliente'),
            'ven_total' => $vendaEntity->__get('ven_total'),
            'ven_valor_compra' => $vendaEntity->__get('ven_valor_compra'),
            'ven_desconto' => $vendaEntity->__get('ven_desconto'),
            'ven_fiado' => $vendaEntity->__get('ven_fiado'),
            'ven_tipo_pagamento' => $vendaEntity->__get('ven_tipo_pagamento'),
            'ven_tipo' => $vendaEntity->__get('ven_tipo'),
            'emp_id' => $vendaEntity->__get('empresa')->__get('emp_id')
        ];

        $vendaModel = new VendaModel();

        if ($vendaModel->save($dadosVenda)) return $vendaModel->getInsertID();

        return 0;
    }

    private function calculaTotalVenda(float $valorCompra, float $valorDesconto): float
    {
        return $valorCompra - $valorDesconto;
    }
}
