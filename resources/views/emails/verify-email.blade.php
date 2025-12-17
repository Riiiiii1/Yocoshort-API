<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #333; text-decoration: none; }
        .content { color: #555; line-height: 1.6; font-size: 16px; }
        .btn-container { text-align: center; margin: 30px 0; }
        .btn { background-color: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; }
        .btn:hover { background-color: #1d4ed8; }
        .footer { margin-top: 40px; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
        .link-plain { color: #2563eb; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ env('FRONTEND_URL') }}" class="logo">YocoShort 🚀</a>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $user->name }}</strong>,</p>
            <p>¡Gracias por unirte! Estás a un solo paso de empezar a crear enlaces increíbles.</p>
            <p>Por favor, confirma que este es tu correo electrónico haciendo clic en el botón de abajo:</p>
            
            <div class="btn-container">
                <a href="{{ $url }}" class="btn">Verificar mi Email</a>
            </div>
            
            <p>Si no creaste esta cuenta, puedes ignorar este mensaje.</p>
        </div>

        <div class="footer">
            <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
            <a href="{{ $url }}" class="link-plain">{{ $url }}</a>
            <p>&copy; {{ date('Y') }} YocoShort. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html> 