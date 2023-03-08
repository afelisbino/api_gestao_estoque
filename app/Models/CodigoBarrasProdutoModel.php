<?php

namespace App\Models;

use CodeIgniter\Model;

class CodigoBarrasProdutoModel extends Model
{
    protected $table      = 'produto_codigo_barra';
    protected $primaryKey = 'pcb_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['pcb_token', 'pcb_codigo', 'pro_id'];

    protected $useTimestamps = false;

    public function buscaListaCodigoBarrasProduto(string $pro_token)
    {
        $this->join('produto', 'produto_codigo_barra.pro_id = produto.pro_id');
        $this->where('produto.pro_token', $pro_token);

        return $this->get()->getResult();
    }

    public function buscarCodigoBarrasProdutoPorToken(string $pcb_token)
    {
        $this->where('pcb_token', $pcb_token);

        return $this->get()->getRow();
    }
}
