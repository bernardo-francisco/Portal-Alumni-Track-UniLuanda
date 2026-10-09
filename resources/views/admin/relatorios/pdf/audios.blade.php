@extends('layouts.pdf')

@section('title', 'Relatório de Áudios')
@section('report_name', 'Mensagens de Áudio')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $audios->count();
    $lidas = $audios->where('lida', true)->count();
    $duracaoTotal = $audios->sum('duracao_segundos');
@endphp

@section('content')

    <div class="doc-title">Mensagens de Áudio</div>
    <div class="doc-subtitle">
        Análise das mensagens de voz na plataforma
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total de Áudios</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $lidas }}</span>
                <span class="stat-label">Ouvidos</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $total - $lidas }}</span>
                <span class="stat-label">Não Ouvidos</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ gmdate('H:i', $duracaoTotal) }}</span>
                <span class="stat-label">Duração Total</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Lista de Mensagens de Áudio</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 22%;">
            <col style="width: 22%;">
            <col style="width: 12%;">
            <col style="width: 14%;">
            <col style="width: 13%;">
            <col style="width: 12%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Remetente</th>
                <th>Destinatário</th>
                <th class="text-center">Duração</th>
                <th class="text-center">Data</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Ouvido</th>
            </tr>
        </thead>

        <tbody>
            @forelse($audios as $audio)

                @php
                    $foto = fotoBase64($audio->remetente->foto_url ?? null);
                @endphp

                <tr>
                    <td class="text-center">
                        @if($foto)
                            <span class="foto-wrap">
                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($audio->remetente->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($audio->remetente->nome_completo ?? '-', 25, '…') }}</td>
                    <td>{{ Str::limit($audio->destinatario->nome_completo ?? '-', 25, '…') }}</td>
                    <td class="text-center nowrap">{{ gmdate('i:s', $audio->duracao_segundos ?? 0) }}</td>
                    <td class="text-center nowrap">{{ $audio->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        <span class="badge badge-info">🎤 Áudio</span>
                    </td>
                    <td class="text-center">
                        @if($audio->lida)
                            <span class="badge badge-success">Sim</span>
                        @else
                            <span class="badge badge-warning">Não</span>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 15px;">
                        Nenhum áudio encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection