@extends('emails.layouts.email')

@section('title', '✅ Conta Aprovada - Alumni Track')
@section('header_title', '✅ Conta Aprovada!')
@section('header_subtitle', 'Bem-vindo(a) à nossa comunidade')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, <strong style="color: #a67c00;">{{ $egresso->nome_completo }}</strong>
    </p>

    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        🎉 <strong>Boas notícias!</strong> Sua conta no <strong style="color: #a67c00;">Alumni Track</strong> foi <strong style="color: #16a34a;">aprovada</strong> com sucesso!
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f0fdf4; border-radius: 12px; border-left: 4px solid #16a34a; padding: 20px 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0; font-size: 15px; color: #14532d;">
                    ✅ <strong>Agora você tem acesso total ao sistema</strong> e pode aproveitar todas as funcionalidades da nossa rede de ex-estudantes.
                </p>
            </td>
        </tr>
    </table>

    {{-- Botão Login --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="{{ route('login') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    🔑 Acessar Minha Conta
                </a>
            </td>
        </tr>
    </table>

    {{-- Credenciais --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border: 1px solid #e6e2d3; padding: 20px 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 12px 0; font-weight: 600; color: #0a0a0a; font-size: 14px;">
                    🔐 Suas Credenciais
                </p>
                <table width="100%" cellpadding="4" cellspacing="0">
                    <tr>
                        <td style="color: #525252; font-size: 14px; width: 80px;"><strong>Email:</strong></td>
                        <td style="color: #0a0a0a; font-size: 14px; font-weight: 500;">{{ $egresso->email }}</td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; width: 80px;"><strong>Senha:</strong></td>
                        <td style="color: #0a0a0a; font-size: 14px;">A que criou no cadastro</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="font-size: 14px; color: #525252; margin: 0 0 8px 0; text-align: center;">
        Seja bem-vindo(a) à comunidade de ex-estudantes da <strong>UNILUANDA</strong>! 🎓
    </p>
    <p style="font-size: 13px; color: #8a8a8a; margin: 0; text-align: center;">
        Conecte-se, compartilhe e cresça com a gente.
    </p>

@endsection