<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseServer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'port',
        'database',
        'username',
        'password',
        'capacity',
        'active',
        'priority',
        'tenant_count'
    ];

    // Relación con los tenants
    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'server_id');
    }

    // Método para seleccionar el servidor óptimo
    public static function getOptimalServer()
    {
        return self::where('active', true)
            ->where(function($query) {
                $query->where('capacity', 0)
                    ->orWhereRaw('tenant_count < capacity');
            })
            ->orderBy('priority') // Menor número = mayor prioridad
            ->orderBy('tenant_count')
            ->first();
    }
}
