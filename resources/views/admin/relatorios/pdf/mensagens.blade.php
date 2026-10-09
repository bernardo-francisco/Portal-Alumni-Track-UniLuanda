@extends('layouts.pdf')

@section('title', 'Relatório de Mensagens')
@section('report_name', 'Mensagens Trocadas')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $mensagens->count();
    $lidas = $mensagens->where('lida', true)->count();
    $naoLidas = $mensagens->where('lida', false)->count();
    $comAnexo = $mensagens->whereNotNull('ficheiro')->count();
@endphp

@section('content')

    <div class="doc-title">Mensagens Trocadas</div>
    <div class="doc-subtitle">
        Análise das mensagens na plataforma
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $lidas }}</span>
                <span class="stat-label">Lidas</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $naoLidas }}</span>
                <span class="stat-label">Não Lidas</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $comAnexo }}</span>
                <span class="stat-label">Com Anexo</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Lista de Mensagens</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 18%;">
            <col style="width: 18%;">
            <col style="width: 35%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Remetente</th>
                <th>Destinatário</th>
                <th>Mensagem</th>
                <th class="text-center">Data</th>
                <th class="text-center">Lida</th>
                <th class="text-center">Anexo</th>
            </tr>
        </thead>

        <tbody>
            @forelse($mensagens as $msg)

                @php
                    $foto = fotoBase64($msg->remetente->foto_url ?? null);
                @endphp

                <tr>
                    <td class="text-center">
                        @if($foto)
                            <span class="foto-wrap">
                                <img src="{{ $foto }}" class="foto-img" alt="Foto">
                            </span>
                        @else
                            <span class="foto-wrap">
                                {{ strtoupper(mb_substr($msg->remetente->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($msg->remetente->nome_completo ?? '-', 22, '…') }}</td>
                    <td>{{ Str::limit($msg->destinatario->nome_completo ?? '-', 22, '…') }}</td>
                    <td>{{ Str::limit($msg->mensagem ?? '-', 60, '…') }}</td>
                    <td class="text-center nowrap">{{ $msg->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        @if($msg->lida)
                            <span class="badge badge-success">Sim</span>
                        @else
                            <span class="badge badge-warning">Não</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($msg->ficheiro)
                            <span class="badge badge-info">Sim</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 15px;">
                        Nenhuma mensagem encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection