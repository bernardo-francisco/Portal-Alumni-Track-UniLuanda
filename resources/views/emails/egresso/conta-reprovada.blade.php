@extends('emails.layouts.email')

@section('title', '❌ Conta Reprovada - Alumni Track')
@section('header_title', '❌ Cadastro Reprovado')
@section('header_subtitle', 'Saiba o que aconteceu')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, <strong style="color: #dc2626;">{{ $egresso->nome_completo }}</strong>
    </p>
    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        Lamentamos informar que seu cadastro no <strong style="color: #a67c00;">Alumni Track</strong> foi <strong style="color: #dc2626;">reprovado</strong>.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background: #fef2f2; border-radius: 12px; border-left: 4px solid #dc2626; padding: 20px 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 6px 0; font-weight: 600; color: #991b1b; font-size: 14px;">
                    📋 Motivo da Reprovação:
                </p>
                <p style="margin: 0; font-size: 14px; color: #7f1d1d; background: #fee2e2; padding: 12px 16px; border-radius: 8px;">
                    {{ $motivo }}
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border-left: 4px solid #0a0a0a; padding: 20px 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 6px 0; font-weight: 600; color: #0a0a0a; font-size: 14px;">
                    💡 O que fazer agora?
                </p>
                <p style="margin: 0; font-size: 14px; color: #525252;">
                    Para mais informações ou recorrer da decisão, entre em contacto com a administração da UNILUANDA.
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="mailto:{{ config('mail.from.address') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    📧 Falar com a Administração
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 14px; color: #525252; margin: 0; text-align: center;">
        Agradecemos o seu interesse em fazer parte da nossa comunidade. 🎓
    </p>

@endsection