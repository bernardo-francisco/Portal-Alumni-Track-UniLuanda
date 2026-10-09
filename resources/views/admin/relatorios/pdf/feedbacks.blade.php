@extends('layouts.pdf')

@section('title', 'Relatório de Feedbacks')
@section('report_name', 'Feedbacks dos Egressos')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $feedbacks->count();
    $aprovados = $feedbacks->where('status', 'aprovado')->count();
    $pendentes = $feedbacks->where('status', 'pendente')->count();
    $rejeitados = $feedbacks->where('status', 'rejeitado')->count();
    $mediaNotas = $total > 0 ? round($feedbacks->avg('nota'), 1) : 0;
@endphp

@section('content')

    <div class="doc-title">Feedbacks dos Egressos</div>
    <div class="doc-subtitle">
        Análise dos feedbacks submetidos
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $aprovados }}</span>
                <span class="stat-label">Aprovados</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $pendentes }}</span>
                <span class="stat-label">Pendentes</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $mediaNotas }}/5</span>
                <span class="stat-label">Média</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Lista de Feedbacks</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 20%;">
            <col style="width: 30%;">
            <col style="width: 8%;">
            <col style="width: 12%;">
            <col style="width: 12%;">
            <col style="width: 13%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Egresso</th>
                <th>Título</th>
                <th class="text-center">Nota</th>
                <th class="text-center">Data</th>
                <th class="text-center">Status</th>
                <th class="text-center">Curso</th>
            </tr>
        </thead>

        <tbody>
            @forelse($feedbacks as $f)

                @php
                    $foto = fotoBase64($f->egresso->foto_url ?? null);

                    $badgeClass = match ($f->status) {
                        'aprovado'  => 'badge-success',
                        'pendente'  => 'badge-warning',
                        'rejeitado' => 'badge-danger',
                        default     => 'badge-unknown',
                    };
                @endphp

                <tr>
                    <td class="text-center">
                        @if($foto)
                            <span class="foto-wrap">
                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($f->egresso->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($f->egresso->nome_completo ?? '-', 25, '…') }}</td>
                    <td>{{ Str::limit($f->titulo ?? '-', 40, '…') }}</td>
                    <td class="text-center">⭐ {{ $f->nota ?? '-' }}/5</td>
                    <td class="text-center nowrap">{{ $f->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($f->status) }}</span>
                    </td>
                    <td class="text-center">{{ Str::limit($f->curso->nome ?? '-', 18, '…') }}</td>
                </tr>

            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 15px;">
                        Nenhum feedback encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection