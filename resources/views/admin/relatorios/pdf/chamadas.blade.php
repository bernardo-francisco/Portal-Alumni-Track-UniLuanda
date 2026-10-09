@extends('layouts.pdf')

@section('title', 'Relatório de Chamadas')
@section('report_name', 'Chamadas de Voz e Vídeo')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $chamadas->count();
    $video = $chamadas->where('tipo', 'video')->count();
    $voz = $chamadas->where('tipo', 'audio')->count();
    $duracaoTotal = $chamadas->sum('duracao_segundos');
@endphp

@section('content')

    <div class="doc-title">Chamadas de Voz e Vídeo</div>
    <div class="doc-subtitle">
        Análise das chamadas efetuadas na plataforma
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $video }}</span>
                <span class="stat-label">Vídeo</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $voz }}</span>
                <span class="stat-label">Voz</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ gmdate('H:i', $duracaoTotal) }}</span>
                <span class="stat-label">Duração Total</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Lista de Chamadas</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 20%;">
            <col style="width: 20%;">
            <col style="width: 12%;">
            <col style="width: 13%;">
            <col style="width: 15%;">
            <col style="width: 15%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Chamador</th>
                <th>Destinatário</th>
                <th class="text-center">Tipo</th>
                <th class="text-center">Duração</th>
                <th class="text-center">Data</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse($chamadas as $ch)

                @php
                    $foto = fotoBase64($ch->chamador->foto_url ?? null);
                    $tipo = $ch->tipo ?? 'voz';
                    $badgeClass = $tipo === 'video' ? 'badge-info' : 'badge-success';

                    $estado = $ch->estado ?? 'terminada';
                    $estadoClass = match ($estado) {
                        'terminada' => 'badge-success',
                        'recusada'  => 'badge-danger',
                        'perdida'   => 'badge-warning',
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
                                {{ strtoupper(mb_substr($ch->chamador->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($ch->chamador->nome_completo ?? '-', 25, '…') }}</td>
                    <td>{{ Str::limit($ch->destinatario->nome_completo ?? '-', 25, '…') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">
                            {{ $tipo === 'video' ? '📹 Vídeo' : '📞 Voz' }}
                        </span>
                    </td>
                    <td class="text-center nowrap">
                        {{ gmdate('i:s', $ch->duracao_segundos ?? 0) }}
                    </td>
                    <td class="text-center nowrap">{{ $ch->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $estadoClass }}">{{ ucfirst($estado) }}</span>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma chamada registada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection