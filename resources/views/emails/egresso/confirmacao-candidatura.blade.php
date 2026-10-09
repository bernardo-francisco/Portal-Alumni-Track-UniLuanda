@extends('emails.layouts.email')

@section('title', '✅ Candidatura Recebida — Alumni Track')
@section('header_title', '✅ Candidatura Recebida')
@section('header_subtitle', 'A tua candidatura foi submetida com sucesso')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, {{ $egresso->nome_completo }}
    </p>
    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        A tua candidatura para <strong style="color: #a67c00;">{{ $oportunidade->titulo }}</strong>
        foi <strong style="color: #16a34a;">submetida com sucesso</strong>.
        A empresa irá analisar o teu perfil e entrará em contacto.
    </p>

    {{-- Resumo --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border: 1px solid #e6e2d3; padding: 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 16px 0; font-weight: 600; color: #0a0a0a; font-size: 16px;">
                    📋 Detalhes da Candidatura
                </p>
                <table width="100%" cellpadding="8" cellspacing="0">
                    <tr>
                        <td style="color: #525252; font-size: 14px; width: 160px; font-weight: 600;">Oportunidade:</td>
                        <td style="color: #0a0a0a; font-size: 14px;"><strong>{{ $oportunidade->titulo }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Empresa:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">{{ $empresa->nome ?? 'Confidencial' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Data de Envio:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">{{ $candidatura->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Estado:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">
                            <span style="display: inline-block; background: #dcfce7; color: #16a34a; padding: 2px 12px; border-radius: 50px; font-size: 12px; font-weight: 600;">
                                {{ $candidatura->status_label }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Próximos passos --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fbf6e3; border-left: 4px solid #c9a227; border-radius: 8px; padding: 16px 20px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 6px 0; font-weight: 600; color: #0a0a0a; font-size: 14px;">
                    💡 Próximos passos
                </p>
                <p style="margin: 0; color: #525252; font-size: 13px; line-height: 1.7;">
                    • Acompanha o estado da tua candidatura em <strong>Minhas Candidaturas</strong>.<br>
                    • Receberás uma notificação quando a empresa atualizar o estado.<br>
                    • Mantém o teu perfil e CV sempre atualizados.
                </p>
            </td>
        </tr>
    </table>

    {{-- Botão --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="{{ url('/egresso/minhas-candidaturas') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    📋 Ver Minhas Candidaturas
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #8a8a8a; margin: 0; text-align: center;">
        🍀 Boa sorte! Entraremos em contacto assim que houver novidades.
    </p>

@endsection