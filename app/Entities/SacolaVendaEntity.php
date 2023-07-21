<?php

namespace App\Entities;

use App\Libraries\Uuid;
use App\Models\SacolaModel;
use App\Models\SacolaVendaModel;
use App\Models\VendaModel;

class SacolaVendaEntity
{

    public function __construct(
        private null|int $scl_id = null,
        private float $scl_qtd = 0,
        private float $scl_sub_total = 0,
        private string | null $scl_token = null,
        private ProdutoEntity $produto = new ProdutoEntity(),
        private VendaEntity $venda = new VendaEntity()
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

    public function adicionaItemSacolaVenda($itensSacola = [], $descontoVenda = 0, int|null $vendaId = null)
    {
        if (empty($itensSacola)) return ['status' => false, 'msg' => 'Nenhum item informado na venda!'];

        if (empty($vendaId)) return ['status' => false, 'msg' => "Nenhuma venda salva, tente novamente!"];

        $produtoEntity = new ProdutoEntity();
        $vendaEntity = new VendaEntity(ven_id: $vendaId);
        $estoqueEntity = new EstoqueEntity();

        $vendaModel = new VendaModel();

        $valorLucroVenda = 0;
        $porcentagemLucroVenda = 0;

        foreach ($itensSacola as $item) {

            $produtoEntity->__set('pro_token', $item['pro_id']);

            $buscaIdProduto = $produtoEntity->buscaIdProduto($produtoEntity);

            if (isset($buscaIdProduto['status']) || $buscaIdProduto == 0) {

                return ['status' => false, 'msg' => "Produto do item não encontrado!"];
            } else {
                $produtoEntity->__set('pro_id', $buscaIdProduto);

                $this->produto = $produtoEntity;
                $this->venda = $vendaEntity;
                $this->scl_qtd = $item['scl_qtd'];
                $this->scl_sub_total = $item['scl_sub_total'];

                $buscaCustoProduto = $produtoEntity->buscarDadosProduto($produtoEntity);

                if (!empty($buscaCustoProduto)) {
                    $valorLucroVenda += $this->calculaLucroItemVenda(
                        $item['scl_sub_total'],
                        $descontoVenda,
                        $buscaCustoProduto['pro_preco_custo'],
                        $item['scl_qtd']
                    );

                    $porcentagemLucroVenda += $this->calculaPorcentagemLucroItemVenda(
                        $item['scl_sub_total'],
                        $descontoVenda,
                        $buscaCustoProduto['pro_preco_custo'],
                        $item['scl_qtd']
                    );
                }

                $idItemSacola = $this->salvaItemSacola($this);

                $estoqueEntity->__set("produto", $produtoEntity);
                $estoqueEntity->cadastraSaidaEstoqueProduto($estoqueEntity, $item['scl_qtd']);

                $this->scl_id = $idItemSacola;

                if (!$this->vinculaItemSacolaVenda($this)) return ['status' => false, 'msg' => "Falha ao vincular o item a venda, tente novamente!"];
            }

            $vendaModel->save([
                'ven_id' => $vendaEntity->__get('ven_id'),
                'ven_lucro' => $valorLucroVenda,
                'ven_margem_lucro' => ($porcentagemLucroVenda > 0) ? ($porcentagemLucroVenda) : 0
            ]);
        }

        return ['status' => true, 'msg' => "Venda registrada com sucesso!"];
    }

    private function calculaLucroItemVenda(float $descontoVenda = 0, float $subTotal = 0, float $custoItem = 0, float $qtdComprado = 0): float
    {
        return ($subTotal - $descontoVenda) - ($custoItem * $qtdComprado);
    }

    private function calculaPorcentagemLucroItemVenda(float $descontoVenda = 0, float $subTotal = 0, float $custoItem = 0, float $qtdComprado = 0): float
    {
        return ((($subTotal - $descontoVenda) - ($custoItem * $qtdComprado)) / $subTotal);
    }

    public function listaItensVenda(VendaEntity $vendaEntity)
    {

        if (!Uuid::is_valid($vendaEntity->__get('ven_token'))) return ['status' => false, 'msg' => "Token da venda inválido!"];

        $sacolaVendaModel = new SacolaVendaModel();

        $itensVenda = $sacolaVendaModel->buscaListaItensVenda($vendaEntity->__get('ven_token'), $vendaEntity->__get('empresa')->__get('emp_id'));

        $listaItensVenda = [];
        $index = 0;

        foreach ($itensVenda as $item) {
            $listaItensVenda[$index]['scl_id'] = $item->scl_token;
            $listaItensVenda[$index]['pro_nome'] = ucwords($item->pro_nome);
            $listaItensVenda[$index]['pro_valor'] = $item->pro_valor_venda;
            $listaItensVenda[$index]['scl_qtd'] = $item->scl_qtd;
            $listaItensVenda[$index]['scl_sub_total'] = $item->scl_sub_total;

            $index++;
        }

        return $listaItensVenda;
    }

    private function salvaItemSacola(SacolaVendaEntity $sacolaVendaEntity): int
    {
        $dadosItem = [
            'scl_token' => Uuid::v4(),
            'scl_qtd' => $sacolaVendaEntity->__get('scl_qtd'),
            'scl_sub_total' => $sacolaVendaEntity->__get('scl_sub_total'),
            'pro_id' => $sacolaVendaEntity->__get('produto')->__get('pro_id')
        ];

        $sacolaModel = new SacolaModel();
        if ($sacolaModel->save($dadosItem)) return $sacolaModel->getInsertID();

        return 0;
    }

    private function vinculaItemSacolaVenda(SacolaVendaEntity $sacolaVendaEntity)
    {
        $sacolaVendaModel = new SacolaVendaModel();

        return $sacolaVendaModel->save([
            'scl_id' => $sacolaVendaEntity->__get('scl_id'),
            'ven_id' => $sacolaVendaEntity->__get('venda')->__get('ven_id')
        ]);
    }
}
