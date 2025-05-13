@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tenants</h1>
    <a href="{{ route('tenants.create') }}" class="btn btn-primary mb-3">Crear Nuevo Tenant</a>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Dominio</th>
                <th>Servidor</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tenants as $tenant)
            <tr>
                <td>{{ $tenant->id }}</td>
                <td>
                    @foreach($tenant->domains as $domain)
                    <span class="badge bg-info">{{ $domain->domain }}</span>
                    @endforeach
                </td>
                <td>
                    @if($tenant->databaseServer)
                    <span class="badge bg-success">{{ $tenant->databaseServer->name }}</span>
                    @else
                    <span class="badge bg-danger">Sin servidor</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form method="POST" action="{{ route('tenants.destroy', $tenant) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
