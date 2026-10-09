@extends('layouts.pdf')

@section('title', 'Relatório de Eventos')
@section('report_name', 'Eventos Organizados')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $eventos->count();
    $ativos = $eventos->where('is_active', true)->count();
    $passados = $eventos->filter(fn($e) => $e->data_inicio && $e->data_inicio->isPast())->count();
    $futuros = $total - $passados;
@endphp

@section('content')

    <div class="doc-title">Eventos Organizados</div>
    <div class="doc-subtitle">
        Análise dos eventos realizados e agendados
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $ativos }}</span>
                <span class="stat-label">Ativos</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $futuros }}</span>
                <span class="stat-label">Futuros</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $passados }}</span>
                <span class="stat-label">Passados</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Lista de Eventos</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 30%;">
            <col style="width: 12%;">
            <col style="width: 12%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
        </colgroup>

        <thead>
            <tr>
                <th>Título</th>
                <th class="text-center">Tipo</th>
                <th class="text-center">Categoria</th>
                <th class="text-center">Data Início</th>
                <th>Local</th>
                <th class="text-center">Vagas</th>
                <th class="text-center">Inscritos</th>
            </tr>
        </thead>

        <tbody>
            @forelse($eventos as $e)

                @php
                    $tipo = $e->tipo ?? 'presencial';
                    $tipoClass = match ($tipo) {
                        'presencial' => 'badge-success',
                        'online'     => 'badge-primary',
                        'hibrido'    => 'badge-warning',
                        default      => 'badge-unknown',
                    };
                @endphp

                <tr>
                    <td>{{ Str::limit($e->titulo ?? '-', 35, '…') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $tipoClass }}">{{ ucfirst($tipo) }}</span>
                    </td>
                    <td class="text-center">{{ ucfirst($e->categoria ?? '-') }}</td>
                    <td class="text-center nowrap">
                        {{ $e->data_inicio ? $e->data_inicio->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td>{{ Str::limit($e->local ?? '-', 20, '…') }}</td>
                    <td class="text-center">{{ $e->max_participantes ?? '∞' }}</td>
                    <td class="text-center"><strong>{{ $e->inscricoes_count ?? 0 }}</strong></td>
                </tr>

            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 15px;">
                        Nenhum evento encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection