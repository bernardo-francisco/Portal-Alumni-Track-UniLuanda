@extends('layouts.pdf')

@section('title', 'Relatório de Egressos')
@section('report_name', 'Relatório de Egressos')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total       = $egressos->count();
    $verificados = $egressos->where('verificado', true)->count();
    $activos     = $egressos->where('status', 'active')->count();
    $inactivos   = $egressos->where('status', 'inactive')->count();
@endphp


@section('content')

    {{-- ============================================================
         TÍTULO
    ============================================================ --}}
    <div class="doc-title">Relatório de Egressos</div>
    <div class="doc-subtitle">
        Documento emitido automaticamente pelo sistema UniLuanda Alumni Track
    </div>


    {{-- ============================================================
         RESUMO
    ============================================================ --}}
    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total de Egressos</span>
            </td>

            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $verificados }}</span>
                <span class="stat-label">Verificados</span>
            </td>

            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $activos }}</span>
                <span class="stat-label">Ativos</span>
            </td>

            <td class="stat-box stat-box-danger">
                <span class="stat-value">{{ $inactivos }}</span>
                <span class="stat-label">Inativos</span>
            </td>
        </tr>
    </table>


    {{-- ============================================================
         TÍTULO DA SECÇÃO
    ============================================================ --}}
    <div class="section-title">Lista de Egressos</div>


    {{-- ============================================================
         TABELA
    ============================================================ --}}
    <table class="report-table">

        <colgroup>
            <col style="width: 5%;">
            <col style="width: 12%;">
            <col style="width: 17%;">
            <col style="width: 20%;">
            <col style="width: 15%;">
            <col style="width: 7%;">
            <col style="width: 5%;">
            <col style="width: 9%;">
            <col style="width: 10%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Nº Processo</th>
                <th>Nome Completo</th>
                <th>Email</th>
                <th>Curso</th>
                <th class="text-center">Unid.</th>
                <th class="text-center">Ano</th>
                <th class="text-center">Status</th>
                <th>Localização</th>
            </tr>
        </thead>

        <tbody>
            @forelse($egressos as $egresso)

                @php
                    $status = $egresso->status ?? 'unknown';

                    $badgeClass = match ($status) {
                        'active'   => 'badge-active',
                        'inactive' => 'badge-inactive',
                        'blocked'  => 'badge-warning',
                        default    => 'badge-unknown',
                    };

                    $statusLabel = match ($status) {
                        'active'   => 'Ativo',
                        'inactive' => 'Inativo',
                        'blocked'  => 'Bloqueado',
                        default    => '—',
                    };

                    // ✅ Foto em base64 (função global do helpers.php)
                    $foto = fotoBase64($egresso->foto_url);

                    // Truncamentos
                    $nomeCompleto = Str::limit($egresso->nome_completo ?? '-', 30, '…');
                    $email        = Str::limit($egresso->email ?? '-', 32, '…');
                    $cursoNome    = Str::limit($egresso->curso?->nome ?? '-', 24, '…');

                    // Localização
                    $localizacaoTexto = null;
                    if ($egresso->localizacaoAtual) {
                        $localizacaoTexto = $egresso->localizacaoAtual->cidade ?? '-';
                        if ($egresso->localizacaoAtual->pais) {
                            $localizacaoTexto .= ', ' . $egresso->localizacaoAtual->pais;
                        }
                        $localizacaoTexto = Str::limit($localizacaoTexto, 16, '…');
                    }
                @endphp

                <tr>
                    {{-- FOTO --}}
                    <td class="text-center">
                        @if($foto)
                            <span class="foto-wrap">
                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($egresso->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>

                    <td class="fw-bold nowrap">{{ $egresso->numero_processo ?? '-' }}</td>
                    <td>{{ $nomeCompleto }}</td>
                    <td>{{ $email }}</td>
                    <td>{{ $cursoNome }}</td>
                    <td class="text-center nowrap">{{ $egresso->curso?->unidade?->sigla ?? '-' }}</td>
                    <td class="text-center nowrap">{{ $egresso->ano_formatura ?? '-' }}</td>

                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </td>

                    <td>
                        @if($localizacaoTexto)
                            {{ $localizacaoTexto }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted" style="padding: 15px;">
                        Nenhum egresso encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>

    </table>

@endsection