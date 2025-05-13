<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'server_id',
        // Otros campos que ya tengas...
    ];

    // Relación con el servidor
    public function databaseServer(): BelongsTo
    {
        return $this->belongsTo(DatabaseServer::class, 'server_id');
    }

    // Este método es clave - sobreescribe el comportamiento por defecto
    public function getConnectionName(): string
    {
        return 'tenant';
    }

    // Método que se usa para obtener la información de la base de datos
    public function getDatabaseName(): string
    {
        return $this->id;
    }

    /**
     * Sobreescribir el método para configurar la conexión con el servidor correcto
     */
    public function configure(): void
    {
        $server = $this->databaseServer;

        if (!$server) {
            throw new \Exception("El tenant {$this->id} no tiene un servidor asignado.");
        }

        // Configurar la conexión tenant con los datos del servidor asignado
        config([
            'database.connections.tenant' => [
                'driver' => 'pgsql',
                'host' => $server->host,
                'port' => $server->port,
                'database' => $this->getDatabaseName(),
                'username' => $server->username,
                'password' => $server->password,
                'charset' => 'utf8',
                'prefix' => '',
                'prefix_indexes' => true,
                'schema' => 'public',
                'sslmode' => 'prefer',
            ],
            // También necesitamos configurar la conexión para crear la base de datos
            'tenancy.database.central_connection' => [
                'driver' => 'pgsql',
                'host' => $server->host,
                'port' => $server->port,
                'database' => $server->database,
                'username' => $server->username,
                'password' => $server->password,
                'charset' => 'utf8',
                'prefix' => '',
                'prefix_indexes' => true,
                'schema' => 'public',
                'sslmode' => 'prefer',
            ],
        ]);
    }

    // Método estático para asignar un servidor óptimo
    public static function assignOptimalServer()
    {
        $server = DatabaseServer::getOptimalServer();

        if (!$server) {
            throw new \Exception('No hay servidores disponibles con capacidad.');
        }

        return $server->id;
    }

    // Bootstrap para actualizar contadores cuando se crea/elimina un tenant
    protected static function booted()
    {
        parent::booted();

        static::created(function (Tenant $tenant) {
            if ($tenant->server_id) {
                DatabaseServer::find($tenant->server_id)->increment('tenant_count');
            }
        });

        static::deleted(function (Tenant $tenant) {
            if ($tenant->server_id) {
                DatabaseServer::find($tenant->server_id)->decrement('tenant_count');
            }
        });
    }
}
