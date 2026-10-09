<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Alumni Track')</title>
</head>
<body style="font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #1a1a1a; background: #f6f4ee; margin: 0; padding: 0;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f6f4ee; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 16px; box-shadow: 0 8px 40px rgba(10,10,10,0.08); overflow: hidden; max-width: 600px;">

                    {{-- HEADER --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #0a0a0a 0%, #262626 100%); padding: 40px 40px 30px 40px; text-align: center; border-bottom: 3px solid #c9a227;">
                            <div style="margin-bottom: 12px;">
                                <span style="display: inline-block; background: rgba(201,162,39,0.15); padding: 8px 20px; border-radius: 50px; font-size: 14px; color: #c9a227; letter-spacing: 1px; font-weight: 600;">
                                    🎓 ALUMNI TRACK
                                </span>
                            </div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;">
                                @yield('header_title', 'Notificação')
                            </h1>
                            <p style="color: rgba(255,255,255,0.75); margin: 8px 0 0 0; font-size: 16px;">
                                @yield('header_subtitle', '')
                            </p>
                        </td>
                    </tr>

                    {{-- CORPO --}}
                    <tr>
                        <td style="padding: 40px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="background: #0a0a0a; padding: 24px 40px; text-align: center; border-top: 3px solid #c9a227;">
                            <p style="font-size: 12px; color: rgba(255,255,255,0.75); margin: 0 0 4px 0;">
                                <strong style="color: #c9a227;">Alumni Track</strong> — Sistema de Controlo e Localização de Ex-Estudantes
                            </p>
                            <p style="font-size: 11px; color: rgba(255,255,255,0.6); margin: 0;">
                                © {{ date('Y') }} Universidade de Luanda. Todos os direitos reservados.
                            </p>
                            <p style="font-size: 11px; color: rgba(255,255,255,0.6); margin: 8px 0 0 0;">
                                📧 {{ config('mail.from.address') }}
                            </p>
                            <p style="font-size: 10px; color: rgba(255,255,255,0.4); margin: 8px 0 0 0;">
                                ⚠️ Este é um email automático. Por favor, não responda.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>