<?php

namespace App\Models;

use CodeIgniter\Model;

class CardapioModel extends Model
{
    protected $table      = 'cardapio';
    protected $primaryKey = 'cdp_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['cdp_nome', 'cdp_token', 'cdp_descricao', 'cdp_valor', 'cdp_disponivel', 'cat_id', 'emp_id'];

    protected $useTimestamps = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function salvarProdutoCardapio(array $dados)
    {
        if ($this->save($dados)) {
            return ['status' => true, 'msg' => "Produto salvo com sucesso!"];
        } else {
            return ['status' => false, 'msg' => 'Falha ao salvar produto'];
        }
    }

    public function listarTodosProdutoCardapioEmpresa(int $idEmpresa)
    {
        $this->join('categoria', 'cardapio.cat_id = categoria.cat_id');
        $this->where('cardapio.emp_id', $idEmpresa);

        return $this->get()->getResult();
    }

    public function listarProdutoCardapioAtivosEmpresa(int $idEmpresa)
    {
        $this->join('categoria', 'cardapio.cat_id = categoria.cat_id');
        $this->where('cardapio.emp_id', $idEmpresa);
        $this->where('cardapio.cdp_disponivel', true);

        return $this->get()->getResult();
    }

    public function listarProdutoCardapioPorCategoria(string $tokenCategoria, int $idEmpresa)
    {
        $this->join('categoria', 'cardapio.cat_id = categoria.cat_id');
        $this->where('categoria.cat_token', $tokenCategoria);
        $this->where('cardapio.emp_id', $idEmpresa);

        return $this->get()->getResult();
    }

    public function buscarDadosProdutoCardapioPorToken(string $tokenProduto, int $idEmpresa)
    {
        $this->join('categoria', 'cardapio.cat_id = categoria.cat_id');
        $this->where('cardapio.cdp_token', $tokenProduto);
        $this->where('cardapio.emp_id', $idEmpresa);

        return $this->get()->getRow();
    }

    public function listarProdutoAtivoCardapioPorCategoria(string $tokenCategoria, int $idEmpresa)
    {
        $this->join('categoria', 'cardapio.cat_id = categoria.cat_id');
        $this->where('categoria.cat_token', $tokenCategoria);
        $this->where('cardapio.emp_id', $idEmpresa);
        $this->where('cardapio.cdp_disponivel', 1);

        return $this->get()->getResult();
    }
}
