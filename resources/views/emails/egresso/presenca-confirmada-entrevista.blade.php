@extends('emails.layouts.email')

@section('title', '✅ Presença Confirmada — Alumni Track')
@section('header_title', '✅ Presença Confirmada')
@section('header_subtitle', 'Contamos contigo na entrevista!')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, <strong style="color: #a67c00;">{{ $egresso->nome_completo }}</strong>
    </p>
    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        Confirmaste a tua presença na entrevista da oportunidade abaixo.
        A empresa foi notificada. 💼
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border: 1px solid #e6e2d3; padding: 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 16px 0; font-weight: 600; color: #0a0a0a; font-size: 16px;">
                    📋 Detalhes
                </p>
                <table width="100%" cellpadding="8" cellspacing="0">
                    <tr>
                        <td style="color: #525252; font-size: 14px; width: 160px; font-weight: 600;">Oportunidade:</td>
                        <td style="color: #0a0a0a; font-size: 14px;"><strong>{{ $oportunidade->titulo }}</strong></td>
                    </tr>
                    @if($candidatura->data_entrevista)
                        <tr>
                            <td style="color: #525252; font-size: 14px; font-weight: 600;">Data da Entrevista:</td>
                            <td style="color: #0a0a0a; font-size: 14px;">
                                <strong>{{ $candidatura->data_entrevista->format('d/m/Y \à\s H:i') }}</strong>
                            </td>
                        </tr>
                    @endif
                    @if($candidatura->local_entrevista)
                        <tr>
                            <td style="color: #525252; font-size: 14px; font-weight: 600;">Local:</td>
                            <td style="color: #0a0a0a; font-size: 14px;">{{ $candidatura->local_entrevista }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

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
        Prepara-te e boa sorte! 🍀
    </p>

@endsection