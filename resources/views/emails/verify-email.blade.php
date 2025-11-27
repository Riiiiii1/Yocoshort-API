<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifica tu email</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Hola {{ $user->name }}!</h1>
        <p>Gracias por registrarte en nuestra aplicación. Para completar tu registro, por favor verifica tu dirección de correo electrónico haciendo clic en el siguiente botón:</p>
        
        <p>
            <a href="{{ $verificationUrl }}" class="button">Verificar mi email</a>
        </p>

        <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
        <p><a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a></p>

        <div class="footer">
            <p>Si no creaste una cuenta, puedes ignorar este mensaje.</p>
        </div>
    </div>
</body>
</html>