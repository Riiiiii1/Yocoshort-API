<!DOCTYPE html>
<html>
<head>
    <title>Prueba Google Auth</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            text-align: center;
        }
        .btn { 
            background: #4285F4; 
            color: white; 
            padding: 15px 30px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            font-size: 16px;
        }
        .btn:hover {
            background: #357ae8;
        }
        .result { 
            background: #f5f5f5; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 5px;
            white-space: pre-wrap;
            font-family: monospace;
            text-align: left;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .success {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>🔐 Prueba de Autenticación con Google</h1>
    
    <a href="/auth/google/redirect-web" class="btn">
        🚀 Iniciar sesión con Google
    </a>

    <div id="results">
        @if(session('error'))
            <div class="error">
                <strong>❌ Error:</strong> {{ session('error') }}
            </div>
        @endif

        @if(session('token'))
            <div class="success">
                <h3>✅ ¡Autenticación Exitosa!</h3>
                <p><strong>Usuario:</strong> {{ session('user_name') }}</p>
                <p><strong>Email:</strong> {{ session('user_email') }}</p>
            </div>
            
            <div class="result">
                <strong>Token de acceso:</strong><br>
                {{ session('token') }}
            </div>

            <button onclick="testProtectedRoute()" class="btn" style="background: #4CAF50;">
                🔒 Probar Ruta Protegida
            </button>

            <button onclick="copyToken()" class="btn" style="background: #FF9800;">
                📋 Copiar Token
            </button>
        @endif
    </div>

    <script>
        async function testProtectedRoute() {
            try {
                const token = '{{ session('token') }}';
                const response = await fetch('/api/user', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    alert('✅ Ruta protegida funciona correctamente!\n\nUsuario: ' + 
                          JSON.stringify(data, null, 2));
                } else {
                    alert('❌ Error: ' + JSON.stringify(data, null, 2));
                }
            } catch (error) {
                alert('❌ Error de conexión: ' + error.message);
            }
        }

        function copyToken() {
            const token = '{{ session('token') }}';
            navigator.clipboard.writeText(token).then(function() {
                alert('✅ Token copiado al portapapeles');
            }, function(err) {
                alert('❌ Error al copiar: ' + err);
            });
        }

        // Mostrar mensajes temporales
        @if(session('token') || session('error'))
            setTimeout(() => {
                window.scrollTo(0, document.body.scrollHeight);
            }, 100);
        @endif
    </script>
</body>
</html>