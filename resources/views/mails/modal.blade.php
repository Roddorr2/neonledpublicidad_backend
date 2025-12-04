<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEON LED PUBLICIDAD</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #eaeaea;
            border-radius: 12px;
            overflow: hidden;
        }
        .header {
            background-color: #0e1ca0;
            padding: 24px 0;
            text-align: center;
        }
        .header-title {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 40px 30px;
        }
        .featured-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .message-title {
            font-size: 22px;
            font-weight: 600;
            color: #0e1ca0;
            margin-bottom: 20px;
        }
        .message-content {
            margin-bottom: 30px;
            color: #444;
            font-size: 16px;
            line-height: 1.7;
        }
        .user-info {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 3px solid #0e1ca0;
        }
        .user-info-title {
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            font-size: 18px;
        }
        .user-detail {
            margin-bottom: 10px;
            font-size: 15px;
        }
        .user-label {
            font-weight: 600;
            color: #555;
            display: inline-block;
            width: 80px;
        }
        .cta-container {
            text-align: center;
            margin: 35px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: #0e1ca0;
            color: #fefefe;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .cta-button:hover {
            background-color: #0e1ca0;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 25px 20px;
            text-align: center;
            font-size: 14px;
            color: #777;
            border-top: 1px solid #eaeaea;
        }
        .social-links {
            margin-bottom: 20px;
        }
        .social-link {
            display: inline-block;
            margin: 0 10px;
        }
        .social-link img {
            width: 24px;
            height: 24px;
            opacity: 0.8;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                border-radius: 0;
                border: none;
            }
            .content {
                padding: 25px 20px;
            }
            .user-info {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($title))
            <h1 class="header-title">{!! $title !!}</h1>
            @endif
        </div>

        <div class="content">
            @if(isset($send_message))
            <div class="message-content">
                <p>¡Hola {{ $data["nombre"] }}! 👋🏼</p>
                {!! $send_message !!}
            </div>
            @endif

            @if(isset($image))
            <img src="{{ $image }}" alt="NEON LED PUBLICIDAD" class="featured-image">
            @endif

            <div class="extra-message">
                {!! $extra_message !!}
            </div>

            {{-- <div class="cta-container">
                <a href="https://ledneonpublicidad.com/" class="cta-button" style="color:white;">Visitar Nuestro Sitio</a>
            </div> --}}
        </div>

        <div class="footer">
            <div class="social-links">
                <a href="https://web.facebook.com/p/Neon-Led-store-100063474791455/?_rdc=1&_rdr" class="social-link">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook">
                </a>
                <a href="https://www.tiktok.com/@neonled.publicidad?is_from_webapp=1&sender_device=pc" class="social-link">
                    <img src="https://cdn-icons-png.flaticon.com/512/3938/3938055.png" alt="Tiktok ">
                </a>
                <a href="https://www.instagram.com/neonledpublicidad.peru" class="social-link">
                    <img src="https://cdn-icons-png.flaticon.com/512/2111/2111463.png" alt="Instagram">
                </a>
                <a href="https://www.linkedin.com/company/neonhouseled/" class="social-link">
                    <img src="https://cdn-icons-png.flaticon.com/512/3536/3536505.png" alt="LinkedIn">
                </a>
            </div>
            <p>&copy; {{ date('Y') }} Desde Lima, Perú a tu proyecto. <br>Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
