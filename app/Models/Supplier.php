<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'country', 'email', 'phone'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
