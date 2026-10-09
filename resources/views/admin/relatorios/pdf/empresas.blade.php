@extends('layouts.pdf')

@section('title', 'Relatório de Empresas')
@section('report_name', 'Empresas Registadas')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $empresas->count();
    $aprovadas = $empresas->where('status_validacao', 'aprovado')->count();
    $pendentes = $empresas->where('status_validacao', 'pendente')->count();
    $reprovadas = $empresas->where('status_validacao', 'reprovado')->count();

    $taxaAprovacao = $total > 0 ? round(($aprovadas / $total) * 100, 1) : 0;

    $totalOportunidades = $empresas->sum('oportunidades_count');
    $totalCandidaturas  = $empresas->sum('candidaturas_count');
@endphp

@section('content')

    <div class="doc-title">Empresas Registadas</div>
    <div class="doc-subtitle">
        Análise consolidada das empresas na plataforma
    </div>

    {{-- ============================================================
         ESTATÍSTICAS PRINCIPAIS
    ============================================================ --}}
    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $aprovadas }}</span>
                <span class="stat-label">Aprovadas</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $pendentes }}</span>
                <span class="stat-label">Pendentes</span>
            </td>
            <td class="stat-box stat-box-danger">
                <span class="stat-value">{{ $reprovadas }}</span>
                <span class="stat-label">Reprovadas</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $taxaAprovacao }}%</span>
                <span class="stat-label">Taxa Aprovação</span>
            </td>
        </tr>
    </table>

    {{-- ============================================================
         DISTRIBUIÇÃO POR SECTOR
    ============================================================ --}}
    @if($porSector->count() > 0)
        <div class="section-title">Distribuição por Sector</div>

        <table class="report-table">
            <colgroup>
                <col style="width: 50%;">
                <col style="width: 15%;">
                <col style="width: 35%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Sector</th>
                    <th class="text-center">Total</th>
                    <th>Distribuição</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porSector as $sector => $qtd)
                    @php
                        $percentual = $total > 0 ? round(($qtd / $total) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $sector ?: 'Não especificado' }}</strong></td>
                        <td class="text-center">
                            <span class="badge badge-primary">{{ $qtd }}</span>
                        </td>
                        <td>
                            <div style="background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="background: #0d6efd; width: {{ $percentual }}%; height: 100%;"></div>
                            </div>
                            <span class="small text-muted">{{ $percentual }}%</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ============================================================
         DISTRIBUIÇÃO POR PROVÍNCIA
    ============================================================ --}}
    @if($porProvincia->count() > 0)
        <div class="section-title">Distribuição por Província</div>

        <table class="report-table">
            <colgroup>
                <col style="width: 50%;">
                <col style="width: 15%;">
                <col style="width: 35%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Província</th>
                    <th class="text-center">Total</th>
                    <th>Distribuição</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porProvincia as $provincia => $qtd)
                    @php
                        $percentual = $total > 0 ? round(($qtd / $total) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $provincia }}</strong></td>
                        <td class="text-center">
                            <span class="badge badge-info">{{ $qtd }}</span>
                        </td>
                        <td>
                            <div style="background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="background: #0dcaf0; width: {{ $percentual }}%; height: 100%;"></div>
                            </div>
                            <span class="small text-muted">{{ $percentual }}%</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ============================================================
         LISTA COMPLETA DE EMPRESAS
    ============================================================ --}}
    <div class="section-title">Lista de Empresas</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 4%;">
            <col style="width: 22%;">
            <col style="width: 12%;">
            <col style="width: 13%;">
            <col style="width: 15%;">
            <col style="width: 9%;">
            <col style="width: 9%;">
            <col style="width: 16%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Empresa</th>
                <th>NIF</th>
                <th>Sector</th>
                <th>Localização</th>
                <th class="text-center">Oport.</th>
                <th class="text-center">Cand.</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse($empresas as $index => $emp)
                @php
                    $cor = match($emp->status_validacao) {
                        'aprovado'  => 'badge-success',
                        'pendente'  => 'badge-warning',
                        'reprovado' => 'badge-danger',
                        default     => 'badge-unknown',
                    };
                @endphp

                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ Str::limit($emp->nome, 30, '…') }}</strong>
                        @if($emp->email)
                            <br><span class="small text-muted">{{ Str::limit($emp->email, 28, '…') }}</span>
                        @endif
                    </td>
                    <td>{{ $emp->nif ?? '—' }}</td>
                    <td>{{ Str::limit($emp->sector ?? '—', 15, '…') }}</td>
                    <td>
                        {{ Str::limit($emp->localizacao ?? '—', 18, '…') }}
                        @if($emp->provincia)
                            <br><span class="small text-muted">{{ $emp->provincia }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-primary">{{ $emp->oportunidades_count }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $emp->candidaturas_count }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $cor }}">{{ ucfirst($emp->status_validacao) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma empresa encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection