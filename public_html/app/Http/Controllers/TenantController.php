<?php

namespace App\Http\Controllers;

use App\Models\DatabaseServer;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('domains', 'databaseServer')->get();
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        $servers = DatabaseServer::where('active', true)->get();
        return view('tenants.create', compact('servers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:domains,domain',
            'server_id' => 'nullable|exists:database_servers,id',
        ]);

        // Generar un ID basado en el nombre
        $id = Str::slug($validated['name']) . '-' . Str::random(6);

        // Usar servidor automático si no se especifica
        $serverId = $validated['server_id'] ?? Tenant::assignOptimalServer();

        // Crear el tenant
        $tenant = Tenant::create([
            'id' => $id,
            'server_id' => $serverId,
            'name' => $validated['name'],
        ]);

        // Crear el dominio
        $tenant->domains()->create([
            'domain' => $validated['domain']
        ]);

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant creado correctamente.');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('domains', 'databaseServer');
        return view('tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        $servers = DatabaseServer::where('active', true)->get();
        $tenant->load('domains');
        return view('tenants.edit', compact('tenant', 'servers'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'server_id' => 'required|exists:database_servers,id',
        ]);

        $oldServerId = $tenant->server_id;

        $tenant->update([
            'name' => $validated['name'],
            'server_id' => $validated['server_id'],
        ]);

        // Actualizar contadores si cambió el servidor
        if ($oldServerId != $validated['server_id']) {
            DatabaseServer::find($oldServerId)->decrement('tenant_count');
            DatabaseServer::find($validated['server_id'])->increment('tenant_count');
        }

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant actualizado correctamente.');
    }

    public function destroy(Tenant $tenant)
    {
        $serverId = $tenant->server_id;

        // Eliminar el tenant
        $tenant->delete();

        // Actualizar contador
        if ($serverId) {
            DatabaseServer::find($serverId)->decrement('tenant_count');
        }

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant eliminado correctamente.');
    }
}
