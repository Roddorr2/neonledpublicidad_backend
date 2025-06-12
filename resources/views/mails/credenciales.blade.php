<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a NEON LED PUBLICIDAD</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #0e1ca0;
            --primary-dark: #062066;
            --primary-light: #F0EBFF;
            --text-on-primary: #FFFFFF;
            --text-primary: #333333;
            --text-secondary: #555555;
            --background-light: #F8F6FF;
            --warning-color: #F59E0B;
            --warning-dark: #B45309;
            --warning-light: #FFFBEB;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(138, 79, 255, 0.1);
        }

        .email-header {
            background-color: #0e1ca0;
            padding: 30px;
            text-align: center;
        }

        .email-header img {
            max-width: 220px;
            height: auto;
        }

        .email-header h1 {
            color: var(--text-on-primary);
            margin: 20px 0 0;
            font-weight: 600;
            font-size: 24px;
        }

        .email-body {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color:#0e1ca0;
        }

        .message {
            margin-bottom: 30px;
            color: var(--text-secondary);
            font-size: 16px;
        }

        .credentials-box {
            background-color: var(--background-light);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #0e1ca0;
        }

        .credential-item {
            margin-bottom: 15px;
            display: table;
            width: 100%;
        }

        .credential-item:last-child {
            margin-bottom: 0;
        }

        .credential-icon-container {
            display: table-cell;
            vertical-align: middle;
            width: 36px;
        }

        .credential-icon {
            background-color: #0e1ca0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: table;
            min-width: 36px;
            min-height: 36px;
            max-width: 36px;
            max-height: 36px;
            box-shadow: 0 2px 8px rgba(8, 4, 214, 0.3);
        }

        .icon-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            height: 36px;
        }

        .credential-icon img {
            width: 18px;
            height: 18px;
            display: inline-block;
            vertical-align: middle;
            filter: brightness(0) invert(1);
        }

        .credential-content {
            display: table-cell;
            vertical-align: middle;
            padding-left: 15px;
        }

        .credential-label {
            font-weight: 600;
            color: #0e1ca0;
            margin-bottom: 3px;
            font-size: 14px;
        }

        .credential-value {
            font-weight: 500;
            font-size: 16px;
            color: var(--text-primary);
            word-break: break-all;
        }

        .cta-button {
            display: block;
            background-color: #0e1ca0;
            color: #ffffff !important;
            text-decoration: none !important;
            padding: 14px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin: 30px 0;
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(3, 17, 143, 0.2);
        }

        .cta-button:hover {
            background-color: #0e1ca0;
        }

        .security-note {
            background-color: var(--warning-light);
            border-radius: 12px;
            padding: 15px;
            border-left: 4px solid var(--warning-color);
            margin-bottom: 30px;
        }

        .security-note-title {
            display: table;
            width: 100%;
            font-weight: 600;
            color: var(--warning-dark);
            margin-bottom: 5px;
        }

        .security-note-icon {
            display: table-cell;
            vertical-align: middle;
            width: 18px;
        }

        .security-note-text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 8px;
        }

        .security-note-title img {
            width: 18px;
            height: 18px;
            display: block;
        }

        .security-note-content {
            color: #78350f;
            font-size: 14px;
        }

        .email-footer {
            background-color: var(--background-light);
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #E9E4FF;
        }

        .company-info {
            font-size: 14px;
            color: #0e1ca0;
            margin-bottom: 15px;
        }

        .social-links {
            margin-bottom: 15px;
        }

        .social-link {
            display: inline-block;
            margin: 0 8px;
        }

        .social-link img {
            width: 24px;
            height: 24px;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .social-link:hover img {
            opacity: 1;
        }

        .copyright {
            font-size: 12px;
            color: #0e1ca0;
        }

        .nenpubli-signature {
            margin-top: 20px;
            font-weight: 600;
            color: #0e1ca0;
        }

        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }

            .email-header {
                padding: 20px;
            }

            .email-header h1 {
                font-size: 20px;
            }

            .greeting {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/logo-v2gYpX1AexbpAeLB3A5QD7xRprduZF.png" alt="NEON LED PUBLICIDAD" />
            <h1>¡Bienvenido a nuestra plataforma!</h1>
        </div>

        <div class="email-body">
            <div class="greeting">Hola {{ $nombre }},</div>

            <p class="message">
                Estamos encantados de darte la bienvenida a NEON LED PUBLICIDAD. Hemos creado tu cuenta y a continuación encontrarás tus credenciales de acceso:
            </p>

            <div class="credentials-box">
                <div class="credential-item">
                    <div class="credential-icon-container">
                        <div class="credential-icon">
                            <div class="icon-cell">
                                <img src="https://cdn-icons-png.flaticon.com/128/542/542638.png" alt="Email icon">
                            </div>
                        </div>
                    </div>
                    <div class="credential-content">
                        <div class="credential-label">Email</div>
                        <div class="credential-value">{{ $email }}</div>
                    </div>
                </div>

                <div class="credential-item">
                    <div class="credential-icon-container">
                        <div class="credential-icon">
                            <div class="icon-cell">
                                <img src="https://cdn-icons-png.flaticon.com/128/807/807292.png" alt="Password icon">
                            </div>
                        </div>
                    </div>
                    <div class="credential-content">
                        <div class="credential-label">Contraseña</div>
                        <div class="credential-value">{{ $password }}</div>
                    </div>
                </div>
            </div>

            <a href="{{ url('http://neonledmkt.com/login') }}" class="cta-button">
                Iniciar sesión ahora
            </a>

            <div class="security-note">
                <div class="security-note-title">
                    <div class="security-note-icon">
                        <img src="https://cdn-icons-png.flaticon.com/128/1161/1161388.png" alt="Security icon">
                    </div>
                    <div class="security-note-text">
                        Nota de seguridad
                    </div>
                </div>
                <div class="security-note-content">
                    Por tu seguridad, te recomendamos cambiar tu contraseña inmediatamente después de iniciar sesión por primera vez. Nunca compartas tus credenciales con otras personas.
                </div>
            </div>

            <p class="message">
                Si tienes alguna pregunta o necesitas ayuda, no dudes en contactar a nuestro equipo de soporte.
            </p>

            <p class="message">
                ¡Esperamos que disfrutes de nuestra plataforma!
                <div class="nenpubli-signature">El equipo de NEON LED PUBLICIDAD</div>
            </p>
        </div>

        <div class="email-footer">
            <div class="company-info">
                NEON LED PUBLICIDAD<br>
                Av. Principal #123, Ciudad<br>
                +52 (123) 456-7890
            </div>

            <div class="social-links">
                <a href="https://web.facebook.com/p/Neon-Led-store-100063474791455/?_rdc=1&_rdr#" class="social-link">
                    <img src="https://cdn-icons-png.flaticon.com/128/733/733547.png" alt="Facebook">
                </a>
                <a href="#" class="social-link">
                    <img src="https://cdn-icons-png.flaticon.com/128/733/733579.png" alt="Twitter">
                </a>
                <a href="#" class="social-link">
                    <img src="https://cdn-icons-png.flaticon.com/128/2111/2111463.png" alt="Instagram">
                </a>
                <a href="https://www.linkedin.com/company/neonhouseled/" class="social-link">
                    <img src="https://cdn-icons-png.flaticon.com/128/3536/3536505.png" alt="LinkedIn">
                </a>
            </div>

            <div class="copyright">
                &copy; {{ date('Y') }} NEON LED PUBLICIDAD. Todos los derechos reservados.
            </div>
        </div>
    </div>
</body>
</html>

