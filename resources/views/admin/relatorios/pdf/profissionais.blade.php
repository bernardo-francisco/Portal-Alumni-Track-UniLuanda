@extends('layouts.pdf')

@section('title', 'Relatório de Profissionais')
@section('report_name', 'Relatório de Profissionais')
@section('footer_right', 'Documento gerado automaticamente')

@php
    $total = $profissionais->count();
    $atuais = $profissionais->where('is_current', true)->count();
    $fullTime = $profissionais->where('tipo_emprego', 'full_time')->count();
    $freelance = $profissionais->where('tipo_emprego', 'freelance')->count();
@endphp

@section('content')

    <div class="doc-title">Relatório de Profissionais</div>
    <div class="doc-subtitle">
        Documento emitido automaticamente pelo sistema UniLuanda Alumni Track
    </div>

    <table class="stats-table">
        <tr>
            <td class="stat-box">
                <span class="stat-value">{{ $total }}</span>
                <span class="stat-label">Total de Registos</span>
            </td>
            <td class="stat-box stat-box-info">
                <span class="stat-value">{{ $atuais }}</span>
                <span class="stat-label">Atuais</span>
            </td>
            <td class="stat-box stat-box-success">
                <span class="stat-value">{{ $fullTime }}</span>
                <span class="stat-label">Tempo Inteiro</span>
            </td>
            <td class="stat-box stat-box-warning">
                <span class="stat-value">{{ $freelance }}</span>
                <span class="stat-label">Freelance</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Registos Profissionais</div>

    <table class="report-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 18%;">
            <col style="width: 10%;">
            <col style="width: 15%;">
            <col style="width: 17%;">
            <col style="width: 10%;">
            <col style="width: 12%;">
            <col style="width: 8%;">
            <col style="width: 5%;">
        </colgroup>

        <thead>
            <tr>
                <th class="text-center">Foto</th>
                <th>Egresso</th>
                <th>Nº Processo</th>
                <th>Cargo</th>
                <th>Empregador</th>
                <th>Tipo</th>
                <th>Sector</th>
                <th class="text-center">Início</th>
                <th class="text-center">Atual</th>
            </tr>
        </thead>

        <tbody>
            @forelse($profissionais as $prof)

                @php
                    $egresso = $prof->egresso;
                    $foto = fotoBase64($egresso->foto_url ?? null);

                    $tipoLabels = [
                        'full_time'     => 'Tempo Inteiro',
                        'part_time'     => 'Tempo Parcial',
                        'freelance'     => 'Freelance',
                        'self_employed' => 'Autónomo',
                        'unemployed'    => 'Desempregado',
                        'student'       => 'A Estudar',
                        'unknown'       => 'Desconhecido',
                    ];

                    $tipoClass = match($prof->tipo_emprego) {
                        'full_time'     => 'badge-success',
                        'part_time'     => 'badge-warning',
                        'freelance'     => 'badge-info',
                        'self_employed' => 'badge-primary',
                        'unemployed'    => 'badge-danger',
                        'student'       => 'badge-secondary',
                        default         => 'badge-unknown',
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
                                {{ strtoupper(mb_substr($egresso->nome_completo ?? 'E', 0, 1)) }}
                            </span>
                        @endif
                    </td>

                    <td>{{ Str::limit($egresso->nome_completo ?? '-', 25, '…') }}</td>
                    <td class="fw-bold nowrap">{{ $egresso->numero_processo ?? '-' }}</td>
                    <td>{{ Str::limit($prof->cargo ?? '-', 20, '…') }}</td>
                    <td>{{ Str::limit($prof->empregador ?? '-', 25, '…') }}</td>
                    <td>
                        <span class="badge {{ $tipoClass }}">
                            {{ $tipoLabels[$prof->tipo_emprego] ?? '—' }}
                        </span>
                    </td>
                    <td>{{ Str::limit($prof->sector ?? '-', 15, '…') }}</td>
                    <td class="text-center nowrap">
                        {{ $prof->data_inicio ? date('d/m/Y', strtotime($prof->data_inicio)) : '-' }}
                    </td>
                    <td class="text-center">
                        @if($prof->is_current)
                            <span class="badge badge-success">Sim</span>
                        @else
                            <span class="badge badge-secondary">Não</span>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted" style="padding: 15px;">
                        Nenhum registo profissional encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

@endsection