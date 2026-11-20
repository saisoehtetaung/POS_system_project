<?php

namespace App\Domain\Concerns;

use Illuminate\Database\Eloquent\Model;

abstract class PosModel extends Model
{
    use HasUlidPrimaryKey;

    protected $guarded = ['id'];
}
