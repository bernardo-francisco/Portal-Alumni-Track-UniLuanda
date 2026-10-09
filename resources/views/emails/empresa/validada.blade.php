@extends('emails.layouts.email')

@section('title', $aprovada ? 'Empresa Aprovada' : 'Empresa Não Aprovada')
@section('header_title', $aprovada ? '✅ Empresa Aprovada!' : '❌ Empresa Não Aprovada')
@section('header_subtitle', 'UniLuanda Alumni Track')

@section('content')

    <p style="font-size: 15px; line-height: 1.6; color: #334155; margin: 0 0 16px;">
        Olá, <strong style="color: #a67c00;">{{ $empresa->nome }}</strong>,
    </p>

    @if($aprovada)

        <p style="font-size: 15px; line-height: 1.6; color: #525252; margin: 0 0 20px;">
            Temos o prazer de informar que a sua empresa foi
            <strong style="color: #16a34a;">aprovada</strong> na plataforma
            <strong>UniLuanda Alumni Track</strong>.
        </p>

        <table width="100%" cellpadding="0" cellspacing="0" style="background: #ecfdf5; border-left: 4px solid #16a34a; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <tr>
                <td style="font-size: 14px; line-height: 1.6; color: #065f46;">
                    <strong>🎉 O que pode fazer agora:</strong>
                    <ul style="margin: 8px 0 0; padding-left: 20px;">
                        <li>Aceder ao dashboard da empresa</li>
                        <li>Publicar oportunidades de emprego e estágio</li>
                        <li>Receber e gerir candidaturas</li>
                    </ul>
                </td>
            </tr>
        </table>

        <table width="100%" cellpadding="0" cellspacing="0" style="text-align: center; margin: 28px 0;">
            <tr>
                <td align="center">
                    <a href="{{ url('/empresa/dashboard') }}"
                       style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #a67c00 0%, #c9a227 55%, #e0b93d 100%); color: #0a0a0a; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 14px; box-shadow: 0 6px 16px rgba(201,162,39,0.4);">
                        Aceder ao Dashboard →
                    </a>
                </td>
            </tr>
        </table>

    @else

        <p style="font-size: 15px; line-height: 1.6; color: #525252; margin: 0 0 20px;">
            Informamos que a sua empresa <strong>não foi aprovada</strong> na plataforma
            <strong>UniLuanda Alumni Track</strong> neste momento.
        </p>

        @if($motivo)
            <table width="100%" cellpadding="0" cellspacing="0" style="background: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                <tr>
                    <td>
                        <p style="margin: 0 0 6px; font-size: 13px; font-weight: 700; color: #991b1b;">
                            Motivo da Reprovação
                        </p>
                        <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #7f1d1d;">
                            {{ $motivo }}
                        </p>
                    </td>
                </tr>
            </table>
        @endif

        <p style="font-size: 14px; line-height: 1.6; color: #64748b; margin: 0;">
            Se acredita que houve um erro ou pretende mais informações, contacte a administração da plataforma.
        </p>

    @endif

@endsection