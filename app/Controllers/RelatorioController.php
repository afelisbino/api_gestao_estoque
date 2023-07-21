<?php

namespace App\Controllers;

use App\Entities\CaixaEntity;
use App\Entities\EstoqueEntity;
use App\Entities\SessaoUsuarioEntity;
use App\Entities\VendaEntity;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class RelatorioController extends BaseController
{
    private SessaoUsuarioEntity $sessaoUsuarioEntity;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController(
            $request,
            $response,
            $logger
        );

        $this->sessaoUsuarioEntity = new SessaoUsuarioEntity();

        $this->sessaoUsuarioEntity = $this->sessaoUsuarioEntity->buscarDadosSessaoUsuario($this->request->getServer('HTTP_AUTHORIZATION'));
    }

    public function buscaQuantidadeVendasLocal()
    {
        $vendaEntity = new VendaEntity(empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $dataInicio = $this->request?->getGet('dataInicio');
        $dataFim = $this->request?->getGet("dataFim");

        $dadosEstatisticasVenda = null;

        if (empty($dataInicio) || empty($dataFim)) {
            $dadosEstatisticasVenda = $vendaEntity->recuperaQuantidadeVendasLocalDataAtual($vendaEntity->__get('empresa'));
        } else {
            $dadosEstatisticasVenda = $vendaEntity->recuperaQuantidadeVendasLocalPorPeriodo($vendaEntity->__get('empresa'), $dataInicio, $dataFim);
        }

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($dadosEstatisticasVenda);
    }

    public function listaVendasLocalFinalizadaPeriodo()
    {
        $vendaEntity = new VendaEntity(empresa: $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $dataInicio = $this->request?->getGet('dataInicio');
        $dataFim = $this->request?->getGet("dataFim");

        $listaDadosVendasFinalizada = [];

        if (!empty($dataInicio) || !empty($dataFim)) {
            $listaDadosVendasFinalizada = $vendaEntity->listaVendasLocalEmpresaRealizadaPeriodo($vendaEntity->__get('empresa'), $dataInicio, $dataFim);
        }

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($listaDadosVendasFinalizada);
    }

    public function buscaEstatisticasVendasUltimosSeteDias()
    {
        $vendaEntity = new VendaEntity();

        return $this->response->setStatusCode("200", "Sucesso")->setJSON($vendaEntity->recuperaQuantidadeVendasLocalPorPeriodo($this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'), DATA_SETE_DIAS_ATRAS, date('Y-m-d')));
    }

    public function buscaListaEstoqueEmpresa()
    {
        $estoqueEntity = new EstoqueEntity();

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($estoqueEntity->listaEstoqueEmpresa($this->sessaoUsuarioEntity->__get('usuario')->__get('empresa')));
    }

    /**
     * @param dataInicio string
     * @param dataFim string
     */
    public function buscaEstatisticasEstoqueEmpresa()
    {
        $dataInicio = $this->request?->getGet('dataInicio');
        $dataFim = $this->request?->getGet('dataFim');

        $estoqueEntity = new EstoqueEntity();

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($estoqueEntity->buscaEstatisticasEstoqueEmpresa($this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'), $dataInicio, $dataFim));
    }

    /**
     * @param dataInicio string
     * @param dataFim string
     */
    public function buscaEstatisticasCaixaEmpresaPeriodo(): ResponseInterface
    {

        $dataInicio = $this->request?->getGet('dataInicio');
        $dataFim = $this->request?->getGet('dataFim');

        $caixaEntity = new CaixaEntity();

        $resumoEstatisticasCaixa = $caixaEntity->buscaEstatisticasResumidoCaixa('periodo', $dataInicio, $dataFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $estatisticasFechamentoCaixa = $caixaEntity->buscaFechamentoCaixaEmpresa('periodo', $dataInicio, $dataFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $estatisticasReceitaLucro = $caixaEntity->buscaReceitaLucroEmpresa('periodo', $dataInicio, $dataFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $estatisticasMovimentacaoCaixa = $caixaEntity->buscaMovimentacoesCaixaEmpresa('periodo', $dataInicio, $dataFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $responseEstatisticas = [
            'resumo' => $resumoEstatisticasCaixa,
            'fechamento' => $estatisticasFechamentoCaixa,
            'valoresReceitaLucro' => $estatisticasReceitaLucro,
            'valoresMovimentacoes' => $estatisticasMovimentacaoCaixa
        ];

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($responseEstatisticas);
    }

    /**
     * @param mesInicio string
     * @param mesFim string
     */
    public function buscaEstatisticasCaixaEmpresaMensal(): ResponseInterface
    {

        $mesInicio = $this->request?->getGet('mesInicio');
        $mesFim = $this->request?->getGet('mesFim');

        $caixaEntity = new CaixaEntity();

        $resumoEstatisticasCaixa = $caixaEntity->buscaEstatisticasResumidoCaixa('mensal', $mesInicio, $mesFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $estatisticasFechamentoCaixa = $caixaEntity->buscaFechamentoCaixaEmpresa('mensal', $mesInicio, $mesFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $estatisticasReceitaLucro = $caixaEntity->buscaReceitaLucroEmpresa('mensal', $mesInicio, $mesFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $estatisticasMovimentacaoCaixa = $caixaEntity->buscaMovimentacoesCaixaEmpresa('mensal', $mesInicio, $mesFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $responseEstatisticas = [
            'resumo' => $resumoEstatisticasCaixa,
            'fechamento' => $estatisticasFechamentoCaixa,
            'valoresReceitaLucro' => $estatisticasReceitaLucro,
            'valoresMovimentacoes' => $estatisticasMovimentacaoCaixa
        ];

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($responseEstatisticas);
    }

    /**
     * @param anoInicio string
     * @param anoFim string
     */
    public function buscaEstatisticasCaixaEmpresaAnual(): ResponseInterface
    {

        $anoInicio = $this->request?->getGet('anoInicio');
        $anoFim = $this->request?->getGet('anoFim');

        $caixaEntity = new CaixaEntity();

        $resumoEstatisticasCaixa = $caixaEntity->buscaEstatisticasResumidoCaixa('anual', $anoInicio, $anoFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $estatisticasFechamentoCaixa = $caixaEntity->buscaFechamentoCaixaEmpresa('anual', $anoInicio, $anoFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $estatisticasReceitaLucro = $caixaEntity->buscaReceitaLucroEmpresa('anual', $anoInicio, $anoFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $estatisticasMovimentacaoCaixa = $caixaEntity->buscaMovimentacoesCaixaEmpresa('anual', $anoInicio, $anoFim, $this->sessaoUsuarioEntity->__get('usuario')->__get('empresa'));

        $responseEstatisticas = [
            'resumo' => $resumoEstatisticasCaixa,
            'fechamento' => $estatisticasFechamentoCaixa,
            'valoresReceitaLucro' => $estatisticasReceitaLucro,
            'valoresMovimentacoes' => $estatisticasMovimentacaoCaixa
        ];

        return $this->response->setStatusCode(200, "Sucesso")->setJSON($responseEstatisticas);
    }
}
