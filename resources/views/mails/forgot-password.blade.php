<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecimiento de Contraseña - NEON LED PUBLICIDAD</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
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
            color: #FFFFFF;
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
            color: #0e1ca0;
        }

        .message {
            margin-bottom: 30px;
            color: #555555;
            font-size: 16px;
        }

        .reset-box {
            background-color: #F8F6FF;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 4px solid #0e1ca0;
            text-align: center;
        }

        .reset-icon {
            background-color: #0e1ca0;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 4px 12px rgba(57, 8, 156, 0.25);
        }

        .reset-icon svg {
            width: 40px;
            height: 40px;
            fill: #ffffff;
        }

        .reset-text {
            font-size: 16px;
            color: #555555;
            margin-bottom: 25px;
        }

        .cta-button {
            display: inline-block;
            background-color: #0e1ca0;
            color: #ffffff !important;
            text-decoration: none !important;
            padding: 14px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin: 10px 0;
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(71, 7, 199, 0.2);
        }

        .cta-button:hover {
            background-color: #0e1ca0;
        }

        .security-note {
            background-color: #fee2e2;
            border-radius: 12px;
            padding: 18px;
            border-left: 4px solid #ef4444;
            margin: 30px 0;
        }

        .security-note-title {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: #b91c1c;
            margin-bottom: 8px;
        }

        .security-note-title svg {
            width: 20px;
            height: 20px;
            fill: #ef4444;
            margin-right: 10px;
        }

        .security-note-content {
            color: #7f1d1d;
            font-size: 14px;
        }

        .email-footer {
            background-color: #F8F6FF;
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
            color: #94a3b8;
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
            <img src="https://i.ibb.co/tw50mj20/logo-azul-letra-Blanco.png" alt="NEON LED PUBLICIDAD" style="width: 100px">
            <h1>Restablecimiento de Contraseña</h1>
        </div>

        <div class="email-body">
            <div class="greeting">Hola {{ $nombre }},</div>

            <p class="message">
                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en NEON LED PUBLICIDAD. Si no has sido tú quien ha solicitado este cambio, puedes ignorar este mensaje.
            </p>

            <div class="reset-box">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto 25px;">
                    <tr>
                        <td style="background-color: #0e1ca0; width: 80px; height: 80px; border-radius: 50%; text-align: center; box-shadow: 0 4px 12px rgba(138, 79, 255, 0.25);">
                            <img src="https://cdn-icons-png.flaticon.com/128/2889/2889676.png" alt="Reset icon" width="40" height="40" style="width: 40px; height: 40px; filter: brightness(0) invert(1); display: block; margin: 0 auto;">
                        </td>
                    </tr>
                </table>

                <div class="reset-text">
                    Para restablecer tu contraseña, haz clic en el botón de abajo:
                </div>

                <a href="{{ url('https://ledneonpublicidad.com/login/res?token=' . $token) }}" class="cta-button">
                    Restablecer contraseña
                </a>
            </div>

            <div class="security-note">
                <div class="security-note-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                    Información importante
                </div>
                <div class="security-note-content">
                    Este enlace de restablecimiento expirará en 60 minutos por razones de seguridad. Si el enlace ha expirado, deberás solicitar un nuevo restablecimiento de contraseña.
                </div>
            </div>

            <p class="message">
                Si no solicitaste este cambio de contraseña, te recomendamos revisar la seguridad de tu cuenta o contactar a nuestro equipo de soporte inmediatamente.
            </p>

            <p class="message">
                Saludos,
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
                <a href="https://www.tiktok.com/@neonled.publicidad?is_from_webapp=1&sender_device=pc" class="social-link">
                    <img src="https://cdn-icons-png.flaticon.com/512/3938/3938055.png" alt="Tiktok ">
                </a>
                <a href="https://www.instagram.com/neonledpublicidad.peru" class="social-link">
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