@extends('layouts.pdf')

@section('title', 'Relatório do Mural de Notícias')
@section('report_name', 'Relatório do Mural')
@section('footer_right', 'Documento gerado automaticamente')

@section('content')

    <div class="doc-title">Relatório do Mural de Notícias</div>
    <div class="doc-subtitle">Documento emitido automaticamente pelo sistema UniLuanda Alumni Track</div>

    {{-- RESUMO --}}
    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $totalPublicacoes }}</span>
                <span class="stat-label">Total de Publicações</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $publicadas }}</span>
                <span class="stat-label">Publicadas</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $totalCurtidas }}</span>
                <span class="stat-label">Curtidas</span>
            </td>
            <td class="stat-box stat-box-danger">
                <span class="stat-value">{{ $totalComentarios }}</span>
                <span class="stat-label">Comentários</span>
            </td>
        </tr>
    </table>

    {{-- DISTRIBUIÇÃO --}}
    <div class="section-title">Distribuição por Tipo</div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Tipo</th>
                <th class="text-center">Total</th>
                <th class="text-center">Percentagem</th>
            </tr>
        </thead>
        <tbody>
            @foreach($porTipo as $tipo => $total)
                <tr>
                    <td><strong>{{ ucfirst($tipo) }}</strong></td>
                    <td class="text-center">{{ $total }}</td>
                    <td class="text-center fw-bold">
                        {{ $totalPublicacoes > 0 ? round(($total / $totalPublicacoes) * 100) : 0 }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOP 5 --}}
    <div class="section-title">Top 5 Publicações (Curtidas + Comentários)</div>
    <table class="report-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">#</th>
                <th>Título</th>
                <th class="text-center" style="width: 15%;">Autor</th>
                <th class="text-center" style="width: 10%;">Curtidas</th>
                <th class="text-center" style="width: 10%;">Comentários</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topPublicacoes as $i => $p)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ Str::limit($p->titulo, 60) }}</td>
                    <td class="text-center">{{ $p->egresso->nome_completo ?? ($p->admin->name ?? 'Admin') }}</td>
                    <td class="text-center">{{ $p->curtidas_count }}</td>
                    <td class="text-center">{{ $p->comentarios_count }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted" style="padding: 15px;">Sem dados.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- LISTA COMPLETA --}}
    <div class="section-title">Todas as Publicações</div>
    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 30%;">
            <col style="width: 10%;">
            <col style="width: 15%;">
            <col style="width: 10%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 6%;">
        </colgroup>
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Título</th>
                <th class="text-center">Tipo</th>
                <th class="text-center">Autor</th>
                <th class="text-center">Data</th>
                <th class="text-center">Curtidas</th>
                <th class="text-center">Comentários</th>
                <th class="text-center">Publicado</th>
                <th class="text-center">Destaque</th>
            </tr>
        </thead>
        <tbody>
            @forelse($publicacoes as $i => $p)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ Str::limit($p->titulo, 50) }}</td>
                    <td class="text-center">{{ ucfirst($p->tipo) }}</td>
                    <td class="text-center">{{ $p->egresso->nome_completo ?? ($p->admin->name ?? 'Admin') }}</td>
                    <td class="text-center">{{ $p->created_at?->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $p->curtidas_count }}</td>
                    <td class="text-center">{{ $p->comentarios_count }}</td>
                    <td class="text-center">
                        <span class="badge {{ $p->publicado ? 'badge-active' : 'badge-inactive' }}">
                            {{ $p->publicado ? 'Sim' : 'Não' }}
                        </span>
                    </td>
                    <td class="text-center">{{ $p->destaque ? '⭐' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted" style="padding: 15px;">Nenhuma publicação.</td></tr>
            @endforelse
        </tbody>
    </table>

@endsection