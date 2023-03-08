<?php

namespace App\Models;

use CodeIgniter\Model;

class SacolaModel extends Model
{
    protected $table      = 'sacola';
    protected $primaryKey = 'scl_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['scl_qtd', 'scl_sub_total', 'scl_token', 'pro_id'];

    protected $useTimestamps = false;
}
