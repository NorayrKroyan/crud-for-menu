<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingMenu extends Model
{
    protected $table = 'accounting_menus';

    protected $fillable = [
        'name',
        'parent_id',
        'url',
        'component',
        'sort_order',
        'is_active',
        'icon',
        'permission',
    ];

    public $timestamps = false;

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
