<?php

namespace App\Entities;

use App\Libraries\Uuid;
use App\Models\CodigoBarrasProdutoModel;
use App\Models\EstoqueModel;
use App\Models\HistoricoEstoqueModel;
use App\Models\ProdutoModel;
use App\Models\SacolaVendaModel;
use CodeIgniter\I18n\Time;

class EstoqueEntity
{

    public function __construct(
        private int|null $est_id = null,
        private int $est_qtd_atual = 0,
        private int $est_qtd_minimo = 0,
        private ProdutoEntity $produto = new ProdutoEntity()
    ) {
    }

    public function __set(string $name, $value)
    {
        $this->{$name} = $value;
    }

    public function __get(string $name)
    {
        return $this->{$name};
    }

    public function cadastrarEstoqueProduto(EstoqueEntity $estoqueEntity): array
    {

        $estoqueModel = new EstoqueModel();

        if ($estoqueModel->save([
            'est_qtd_atual' => $estoqueEntity->__get('est_qtd_atual'),
            'est_qtd_minimo' => $estoqueEntity->__get('est_qtd_minimo'),
            'pro_id' => $estoqueEntity->__get('produto')->__get('pro_id')
        ])) {
            $historicoEstoqueModel = new HistoricoEstoqueModel();

            $historicoEstoqueModel->save([
                'hsp_tipo' => 'entrada',
                'hsp_data' => date('Y-m-d H:i:s'),
                'hsp_qtd_registro' => $estoqueEntity->__get('est_qtd_atual'),
                'hsp_qtd_antigo' => 0,
                'hsp_qtd_atual' => $estoqueEntity->__get('est_qtd_atual'),
                'est_id' => $estoqueModel->getInsertID()
            ]);

            return ['status' => true, 'msg' => "Produto cadastrado com sucesso"];
        } else {
            return ['status' => false, 'msg' => "Falha ao cadastrar produto, tente novamente!"];
        }
    }

    public function alterarDadosEstoqueProduto(EstoqueEntity $estoqueEntity)
    {
        if (!Uuid::is_valid($estoqueEntity->__get('produto')->__get('pro_token'))) return ['status' => false, 'msg' => 'Token do produto inválido'];

        $estoqueModel = new EstoqueModel();

        $recuperaDadosEstoque = $estoqueModel->buscarEstoqueProdutoPorToken($estoqueEntity->__get('produto')->__get('pro_token'));

        if (empty($recuperaDadosEstoque)) return ['status' => false, 'msg' => "Produto não encontrado!"];

        $estoqueAtualProduto = $recuperaDadosEstoque->est_qtd_atual;
        $qtdAdicionado = 0;
        $qtdRetirado = 0;

        if ($estoqueAtualProduto < $estoqueEntity->__get('est_qtd_atual')) {
            $qtdAdicionado = (int) ($estoqueEntity->__get('est_qtd_atual') - $estoqueAtualProduto);
        } else {
            $qtdRetirado = (int) ($estoqueAtualProduto - $estoqueEntity->__get('est_qtd_atual'));
        }

        $historicoEstoqueModel = new HistoricoEstoqueModel();

        $historicoEstoqueModel->save([
            'hsp_tipo' => $qtdAdicionado === 0 ? "saida" : "entrada",
            'hsp_data' => date('Y-m-d H:i:s'),
            'hsp_qtd_registro' => $qtdAdicionado === 0 ? $qtdRetirado : $qtdAdicionado,
            'hsp_qtd_antigo' => $estoqueAtualProduto,
            'hsp_qtd_atual' => $estoqueEntity->__get('est_qtd_atual'),
            'est_id' => $recuperaDadosEstoque->est_id
        ]);

        return ($estoqueModel->save([
            'est_qtd_atual' => $estoqueEntity->__get('est_qtd_atual'),
            'est_qtd_minimo' => $estoqueEntity->__get('est_qtd_minimo'),
            'est_id' => $recuperaDadosEstoque->est_id
        ])) ? ['status' => true, 'msg' => "Estoque atualizado com sucesso!"] : ['status' => false, 'msg' => "Falha ao atualizar os dados do estoque!"];
    }

    public function buscarHistoricoEstoqueEmpresa(EmpresaEntity $empresaEntity, $dataInicio = null, $dataFim = null): array
    {
        $historicoEstoqueModel = new HistoricoEstoqueModel();

        $listaHistoricoEstoque = $historicoEstoqueModel->buscarListaHistoricoEstoqueEmpresa($empresaEntity->__get('emp_id'), $dataInicio, $dataFim);

        $historicoEstoque = [];
        $index = 0;

        foreach ($listaHistoricoEstoque as $historico) {

            $dateTime = Time::parse($historico->hsp_data, "America/Sao_Paulo");

            $historicoEstoque[$index]['hsp_tipo'] = ucfirst($historico->hsp_tipo);
            $historicoEstoque[$index]['pro_nome'] = ucwords($historico->pro_nome);
            $historicoEstoque[$index]['hsp_data'] = $dateTime->toLocalizedString('dd/MM/YYYY HH:mm');
            $historicoEstoque[$index]['hsp_antigo'] = $historico->hsp_qtd_antigo;
            $historicoEstoque[$index]['hsp_movimentado'] = $historico->hsp_qtd_registro;
            $historicoEstoque[$index]['hsp_total'] = $historico->hsp_qtd_atual;

            $index++;
        }

        return $historicoEstoque;
    }

    public function buscaHistoricoEstoqueProduto(ProdutoEntity $produtoEntity): array
    {
        $historicoEstoqueModel = new HistoricoEstoqueModel();

        $listaHistoricoEstoque = $historicoEstoqueModel->buscarListaHistoricoProduto($produtoEntity->__get('pro_token'));

        $historicoEstoque = [];
        $index = 0;

        foreach ($listaHistoricoEstoque as $historico) {

            $dateTime = Time::parse($historico->hsp_data, "America/Sao_Paulo");

            $historicoEstoque[$index]['hsp_tipo'] = ucfirst($historico->hsp_tipo);
            $historicoEstoque[$index]['hsp_data'] = $dateTime->toLocalizedString('dd/MM/YYYY HH:mm');
            $historicoEstoque[$index]['hsp_antigo'] = $historico->hsp_qtd_antigo;
            $historicoEstoque[$index]['hsp_movimentado'] = $historico->hsp_qtd_registro;
            $historicoEstoque[$index]['hsp_total'] = $historico->hsp_qtd_atual;

            $index++;
        }

        return $historicoEstoque;
    }

    public function cadastraEntradaEstoqueProduto(EstoqueEntity $estoqueEntity, int $quantidadeAdicionado): array
    {
        if (!Uuid::is_valid($estoqueEntity->__get('produto')->__get('pro_token'))) return ['status' => false, 'msg' => 'Token do produto inválido'];

        if ($quantidadeAdicionado <= 0) return ['status' => false, 'msg' => "A quantidade precisa ser maior que zero para salvar a entrada!"];

        $estoqueModel = new EstoqueModel();

        $recuperaDadosEstoque = $estoqueModel->buscarEstoqueProdutoPorToken($estoqueEntity->__get('produto')->__get('pro_token'));

        if (empty($recuperaDadosEstoque)) return ['status' => false, 'msg' => "Produto não encontrado!"];

        $historicoEstoqueModel = new HistoricoEstoqueModel();

        $historicoEstoqueModel->save([
            'hsp_tipo' => "entrada",
            'hsp_data' => date('Y-m-d H:i:s'),
            'hsp_qtd_registro' => $quantidadeAdicionado,
            'hsp_qtd_antigo' => $recuperaDadosEstoque->est_qtd_atual,
            'hsp_qtd_atual' => ($recuperaDadosEstoque->est_qtd_atual + $quantidadeAdicionado),
            'est_id' => $recuperaDadosEstoque->est_id
        ]);

        $salvaDadosEstoque = $estoqueModel->save([
            'est_qtd_atual' => ($recuperaDadosEstoque->est_qtd_atual + $quantidadeAdicionado),
            'est_id' => $recuperaDadosEstoque->est_id
        ]);

        return ($salvaDadosEstoque) ? ['status' => true, 'msg' => "Estoque alterado com sucesso!"] : ['status' => false, 'msg' => "Falha ao alterar dados do estoque"];
    }

    public function cadastraSaidaEstoqueProduto(EstoqueEntity $estoqueEntity, int $quantidadeRetirado): array
    {
        if (!Uuid::is_valid($estoqueEntity->__get('produto')->__get('pro_token'))) return ['status' => false, 'msg' => 'Token do produto inválido'];

        if ($quantidadeRetirado <= 0) return ['status' => false, 'msg' => "A quantidade precisa ser maior que zero para salvar a entrada!"];

        $estoqueModel = new EstoqueModel();

        $recuperaDadosEstoque = $estoqueModel->buscarEstoqueProdutoPorToken($estoqueEntity->__get('produto')->__get('pro_token'));

        if (empty($recuperaDadosEstoque)) return ['status' => false, 'msg' => "Produto não encontrado!"];

        $historicoEstoqueModel = new HistoricoEstoqueModel();

        $historicoEstoqueModel->save([
            'hsp_tipo' => "saida",
            'hsp_data' => date('Y-m-d H:i:s'),
            'hsp_qtd_registro' => $quantidadeRetirado,
            'hsp_qtd_antigo' => $recuperaDadosEstoque->est_qtd_atual,
            'hsp_qtd_atual' => ($recuperaDadosEstoque->est_qtd_atual > 0) ? ($recuperaDadosEstoque->est_qtd_atual - $quantidadeRetirado) : 0,
            'est_id' => $recuperaDadosEstoque->est_id
        ]);

        $salvaDadosEstoque = $estoqueModel->save([
            'est_qtd_atual' => ($recuperaDadosEstoque->est_qtd_atual > 0) ? ($recuperaDadosEstoque->est_qtd_atual - $quantidadeRetirado) : 0,
            'est_id' => $recuperaDadosEstoque->est_id
        ]);

        return ($salvaDadosEstoque) ? ['status' => true, 'msg' => "Estoque alterado com sucesso!"] : ['status' => false, 'msg' => "Falha ao alterar dados do estoque"];
    }

    public function listaEstoqueEmpresa(EmpresaEntity $empresaEntity)
    {
        $produtoModel = new ProdutoModel();

        $dadosEstoque = $produtoModel->listarProdutosEstoqueEmpresa($empresaEntity->__get('emp_id'));

        $estoqueEmpresa = [];
        $index = 0;

        $codigosBarrasProdutoModel = new CodigoBarrasProdutoModel();

        foreach ($dadosEstoque as $produto) {
            $estoqueEmpresa[$index]['pro_id'] = $produto->pro_token;
            $estoqueEmpresa[$index]['pro_nome'] = ucwords($produto->pro_nome);
            $estoqueEmpresa[$index]['pro_qtd_atual'] = (int) $produto->est_qtd_atual;
            $estoqueEmpresa[$index]['pro_qtd_minimo'] = (int) $produto->est_qtd_minimo;
            $estoqueEmpresa[$index]['pro_disponivel'] = (bool) $produto->pro_disponivel;

            $codigosBarrasProduto = $codigosBarrasProdutoModel->buscaListaCodigoBarrasProduto($produto->pro_token);

            $listaCodigoBarras = [];

            foreach ($codigosBarrasProduto as $codigoBarras) {
                $listaCodigoBarras[] = $codigoBarras->pcb_codigo;
            }

            $estoqueEmpresa[$index]['pro_codigos'] = $listaCodigoBarras;

            $index++;
        }

        return $estoqueEmpresa;
    }

    public function buscaEstatisticasEstoqueEmpresa(EmpresaEntity $empresaEntity, $dataInicio = null, $dataFim = null)
    {
        $estoqueModel = new EstoqueModel();

        $estatisticasEstoque =  $estoqueModel->buscarEstatisticasEstoqueEmpresa($empresaEntity->__get('emp_id'));

        $sacolaVendaModel = new SacolaVendaModel();

        $estatisticasProdutosMaisVendido = $sacolaVendaModel->buscarProdutosMaisVendidos($empresaEntity->__get('emp_id'), $dataInicio, $dataFim);

        $historicoEstoqueModel = new HistoricoEstoqueModel();

        $estatisticasMovimentacaoEstoque = $historicoEstoqueModel->buscarEstatisticasHistoricoEstoque($empresaEntity->__get('emp_id'), $dataInicio, $dataFim);

        return [
            'quantidade_estoque' => $estatisticasEstoque?->total_produto_estoque,
            'quantidade_estoque_zerado' => $estatisticasEstoque?->total_zerado,
            'quantidade_estoque_minimo' => $estatisticasEstoque?->total_minimo,
            'quantidade_estoque_desativado' => $estatisticasEstoque?->total_desativados,
            'produtos_vendidos' => $estatisticasProdutosMaisVendido,
            'quantidade_movimentacoes' => $estatisticasMovimentacaoEstoque
        ];
    }
}
