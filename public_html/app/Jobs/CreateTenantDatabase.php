<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CreateTenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle()
    {
        // Ya tenemos la conexión configurada por el tenant
        $server = $this->tenant->databaseServer;

        // Crear una conexión temporal para crear la base de datos
        config([
            'database.connections.landlord' => [
                'driver' => 'pgsql',
                'host' => $server->host,
                'port' => $server->port,
                'database' => $server->database,
                'username' => $server->username,
                'password' => $server->password,
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ],
        ]);

        // Crear la base de datos
        $database = $this->tenant->getDatabaseName();

        try {
            DB::connection('landlord')->statement("CREATE DATABASE \"{$database}\"");
        } catch (\Exception $e) {
            // Si la base de datos ya existe, no hacemos nada
            if (!str_contains($e->getMessage(), 'already exists')) {
                throw $e;
            }
        }

        // Limpiar la conexión temporal
        DB::purge('landlord');
    }
}
