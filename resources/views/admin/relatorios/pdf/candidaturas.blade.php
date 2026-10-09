@extends('layouts.pdf')

@section('title', 'Relatório de Candidaturas')
@section('report_name', 'Relatório de Candidaturas')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $candidaturas->count();
    $pendentes = $candidaturas->where('status', 'pendente')->count();
    $emAnalise = $candidaturas->where('status', 'em_analise')->count();
    $entrevistas = $candidaturas->where('status', 'entrevista')->count();
    $aprovadas = $candidaturas->where('status', 'aprovado')->count();
    $rejeitadas = $candidaturas->where('status', 'rejeitado')->count();
@endphp

@section('content')

    <div class="doc-title">Relatório de Candidaturas</div>
    <div class="doc-subtitle">
        Documento emitido automaticamente pelo sistema UniLuanda Alumni Track
    </div>

    {{-- RESUMO --}}
    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $pendentes }}</span>
                <span class="stat-label">Pendentes</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $emAnalise + $entrevistas }}</span>
                <span class="stat-label">Em Avaliação</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $aprovadas }}</span>
                <span class="stat-label">Aprovadas</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Lista de Candidaturas</div>

    {{-- TABELA --}}
    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 18%;">
            <col style="width: 22%;">
            <col style="width: 15%;">
            <col style="width: 10%;">
            <col style="width: 12%;">
            <col style="width: 10%;">
            <col style="width: 8%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Candidato</th>
                <th>Oportunidade</th>
                <th>Empresa</th>
                <th class="text-center">Data</th>
                <th class="text-center">Status</th>
                <th class="text-center">Avaliado em</th>
                <th class="text-center">CV</th>
            </tr>
        </thead>

        <tbody>
            @forelse($candidaturas as $c)

                @php
                    $foto = fotoBase64($c->egresso->foto_url ?? null);

                    $status = $c->status ?? 'pendente';
                    $badgeClass = match ($status) {
                        'pendente'   => 'badge-warning',
                        'em_analise' => 'badge-info',
                        'entrevista' => 'badge-primary',
                        'aprovado'   => 'badge-success',
                        'rejeitado'  => 'badge-danger',
                        default      => 'badge-unknown',
                    };
                    $statusLabel = getStatusLabel($status);
                @endphp

                <tr>
                    <td class="text-center">
                        @if($foto)
                            <span class="foto-wrap">
                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($c->egresso->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>

                    <td>{{ Str::limit($c->egresso->nome_completo ?? '-', 25, '…') }}</td>

                    <td>
                        {{ Str::limit($c->oportunidade->titulo ?? '-', 32, '…') }}
                    </td>

                    <td>
                        {{ Str::limit($c->oportunidade->empresa ?? '-', 20, '…') }}
                    </td>

                    <td class="text-center nowrap">
                        {{ $c->created_at ? $c->created_at->format('d/m/Y') : '-' }}
                    </td>

                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </td>

                    <td class="text-center nowrap">
                        {{ $c->avaliado_em ? $c->avaliado_em->format('d/m/Y') : '-' }}
                    </td>

                    <td class="text-center">
                        @if($c->cv_anexo)
                            <span class="badge badge-success">Sim</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma candidatura encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection