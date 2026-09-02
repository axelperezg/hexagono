{{-- Plain HTML notification sent to the internal team (MAIL_TO_ADDRESS)
     whenever a visitor submits the public contact form. Kept as simple,
     inline-styled HTML so it renders consistently across mail clients. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo mensaje de contacto</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: -apple-system, Segoe UI, Roboto, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td style="background:#0a0a0a; padding:20px 28px;">
                            <span style="color:#ffffff; font-size:14px; letter-spacing:0.1em; text-transform:uppercase;">Hexágono Research</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 16px; font-size:18px; color:#111111;">Nuevo mensaje de contacto</h1>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#27272a;">
                                <tr>
                                    <td style="padding:6px 0; width:160px; color:#71717a;">Nombre</td>
                                    <td style="padding:6px 0;">{{ $contactMessage->name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#71717a;">Institución</td>
                                    <td style="padding:6px 0;">{{ $contactMessage->institution ?? 'No especificada' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#71717a;">Correo</td>
                                    <td style="padding:6px 0;">{{ $contactMessage->email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#71717a;">Teléfono</td>
                                    <td style="padding:6px 0;">{{ $contactMessage->phone ?? 'No especificado' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#71717a;">Tipo de estudio</td>
                                    <td style="padding:6px 0;">{{ $contactMessage->study_type->label() }}</td>
                                </tr>
                            </table>
                            <p style="margin:20px 0 6px; color:#71717a; font-size:14px;">Mensaje</p>
                            <p style="margin:0; padding:14px; background:#fafafa; border-radius:6px; font-size:14px; color:#27272a; white-space:pre-line;">{{ $contactMessage->message }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
