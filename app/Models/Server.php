<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    protected $fillable = ['name', 'url', 'api_key', 'active'];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}
