@extends('emails.layouts.email')

@section('title', '🎯 Nova Candidatura — Alumni Track')
@section('header_title', '🎯 Nova Candidatura')
@section('header_subtitle', 'Um egresso candidatou-se a uma das suas oportunidades')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, {{ $empresa->nome ?? 'Equipa' }}
    </p>
    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        Recebeu uma <strong style="color: #a67c00;">nova candidatura</strong> para a oportunidade
        <strong>{{ $oportunidade->titulo }}</strong>.
    </p>

    {{-- Dados do Candidato --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border: 1px solid #e6e2d3; padding: 24px; margin-bottom: 20px;">
        <tr>
            <td>
                <p style="margin: 0 0 16px 0; font-weight: 600; color: #0a0a0a; font-size: 16px;">
                    👤 Dados do Candidato
                </p>
                <table width="100%" cellpadding="8" cellspacing="0">
                    <tr>
                        <td style="color: #525252; font-size: 14px; width: 140px; font-weight: 600;">Nome:</td>
                        <td style="color: #0a0a0a; font-size: 14px;"><strong>{{ $egresso->nome_completo }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Email:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">
                            <a href="mailto:{{ $egresso->email }}" style="color: #a67c00; text-decoration: none;">{{ $egresso->email }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Telefone:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">{{ $egresso->telefone ?? 'Não informado' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Curso:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">{{ $egresso->curso->nome ?? 'Não informado' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Unidade:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">
                            <span style="display: inline-block; background: #fbf6e3; color: #a67c00; padding: 2px 12px; border-radius: 50px; font-size: 12px; font-weight: 600;">
                                {{ $egresso->curso->unidade->sigla ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Dados da Oportunidade --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border: 1px solid #e6e2d3; padding: 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 16px 0; font-weight: 600; color: #0a0a0a; font-size: 16px;">
                    💼 Oportunidade
                </p>
                <table width="100%" cellpadding="8" cellspacing="0">
                    <tr>
                        <td style="color: #525252; font-size: 14px; width: 140px; font-weight: 600;">Título:</td>
                        <td style="color: #0a0a0a; font-size: 14px;"><strong>{{ $oportunidade->titulo }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Tipo:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">{{ ucfirst($oportunidade->tipo ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Data Candidatura:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">{{ $candidatura->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Botão --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="{{ url('/empresa/candidaturas') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    🎯 Ver Candidatura
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #8a8a8a; margin: 0; text-align: center;">
        ⏳ Aceda ao painel para analisar o perfil completo do candidato.
    </p>

@endsection