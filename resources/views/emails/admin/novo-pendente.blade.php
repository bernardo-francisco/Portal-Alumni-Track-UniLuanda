@extends('emails.layouts.email')

@section('title', '📋 Novo Egresso Pendente - Alumni Track')
@section('header_title', '🆕 Novo Cadastro Pendente')
@section('header_subtitle', 'Aguarda validação manual')

@section('content')

    <p style="font-size: 18px; color: #1a1a1a; margin: 0 0 8px 0;">
        Olá, Administrador(a)
    </p>
    <p style="color: #525252; font-size: 15px; margin: 0 0 24px 0;">
        Um novo egresso acabou de se cadastrar e <strong style="color: #d97706;">aguarda validação manual</strong>.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background: #faf9f5; border-radius: 12px; border: 1px solid #e6e2d3; padding: 24px; margin-bottom: 28px;">
        <tr>
            <td>
                <p style="margin: 0 0 16px 0; font-weight: 600; color: #0a0a0a; font-size: 16px;">
                    📋 Detalhes do Egresso
                </p>
                <table width="100%" cellpadding="8" cellspacing="0">
                    <tr>
                        <td style="color: #525252; font-size: 14px; width: 120px; font-weight: 600;">Nome:</td>
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
                                {{ $egresso->unidade->sigla ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Nº Processo:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">
                            <code style="background: #e6e2d3; padding: 2px 8px; border-radius: 4px; font-size: 13px;">{{ $egresso->numero_processo }}</code>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #525252; font-size: 14px; font-weight: 600;">Data Cadastro:</td>
                        <td style="color: #0a0a0a; font-size: 14px;">{{ $egresso->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Botão Validar --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center">
                <a href="{{ route('admin.validacao.index') }}"
                   style="display: inline-block; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; padding: 14px 48px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 16px rgba(201,162,39,0.35);">
                    🔍 Ir para Validação
                </a>
            </td>
        </tr>
    </table>

    <p style="font-size: 13px; color: #8a8a8a; margin: 0; text-align: center;">
        ⏳ Este egresso aguarda sua análise. Acesse o painel para aprovar ou reprovar.
    </p>

@endsection