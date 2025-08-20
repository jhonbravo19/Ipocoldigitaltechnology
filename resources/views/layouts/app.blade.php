<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }

        .btn-purple {
            background-color: #6f42c1;
            border: none;
        }

        .btn-purple:hover {
            background-color: #5936a2;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div id="app">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
    <div class="whatsapp-float">
        <a href="https://wa.me/573229675194?text=Hola%20Ipocoldigitaltechnology,%20necesito%20información%20sobre%20sus%20servicios"
            target="_blank" class="whatsapp-button" title="Contáctanos por WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <style>
        .whatsapp-float {
            position: fixed;
            bottom: 100px;
            right: 80px;
            z-index: 1000;
            animation: pulse 3s infinite;
            /* si ya tienes @keyframes, se mantiene */
        }

        /* Botón redondo (solo ícono) */
        .whatsapp-button {
            width: 56px;
            height: 56px;
            background: #25d366;
            /* verde WhatsApp */
            color: #fff !important;
            border-radius: 50%;
            /* círculo perfecto */
            display: flex;
            align-items: center;
            justify-content: center;
            /* centra el ícono */
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(37, 211, 102, .4);
            transition: transform .3s ease, box-shadow .3s ease, background .3s ease;
        }

        .whatsapp-button:hover {
            background: #128c7e;
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(37, 211, 102, .6);
        }

        /* Asegura centrado con cualquier tipo de ícono (font, svg o imagen) */
        .whatsapp-button i,
        .whatsapp-button svg,
        .whatsapp-button img {
            display: block;
            margin: 0;
            /* quita empujes laterales */
            line-height: 1;
            font-size: 28px;
            /* tamaño del ícono si es fuente */
            width: 28px;
            /* si es svg/img, se centra igual */
            height: 28px;
        }

        /* Si alguna vez quieres versión con texto, usa una clase extra */
        .whatsapp-button.has-text {
            width: auto;
            height: 48px;
            padding: 0 16px;
            border-radius: 999px;
            /* píldora */
            gap: 10px;
            /* espacio entre ícono y texto */
        }


        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        @media (max-width: 768px) {
            .whatsapp-float {
                bottom: 15px;
                right: 15px;
            }

            .whatsapp-button {
                padding: 12px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                justify-content: center;
            }

            .whatsapp-text {
                display: none;
            }

            .whatsapp-button i {
                margin-right: 0;
                font-size: 24px;
            }
        }
    </style>

    <div id="general-chatbot" class="general-bot hidden">
        <div class="general-bot-header">
            <div class="general-bot-brand">
                <img src="{{ asset('public/images/Logo.png') }}" alt="Ipocoldigitaltechnology"
                    style="width:24px;height:24px;object-fit:contain;" />
                <span>Asistente Colsertrans</span>
            </div>
            <button id="general-bot-close" aria-label="Cerrar">&times;</button>
        </div>

        <div id="general-bot-body" class="general-bot-body">
            <div class="bot-message">
                <div class="bubble">
                    ¡Hola! Soy el asistente de Ipocoldigitaltechnology. ¿En qué puedo ayudarte?
                </div>
            </div>
            <div id="general-quick" class="quick-replies"></div>
        </div>

        <div class="general-bot-footer">
            <input id="general-input" type="text" placeholder="Ej: servicios, certificados..." />
            <button id="general-send" aria-label="Enviar">
                <i class="fa fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <button id="general-bot-toggle" class="general-bot-fab" aria-label="Asistente Colsertrans">
        <i class="fa fa-comments"></i>
    </button>

    <style>
        :root {
            --general-primary: rgb(50, 47, 238);
            --general-secondary: rgb(0, 4, 235);
        }

        #general-bot-toggle.general-bot-fab {
            position: fixed;
            right: 18px;
            bottom: 100px;
            z-index: 9998;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--general-primary), var(--general-secondary));
            color: #fff;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        #general-bot-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .25);
        }

        #general-bot-toggle i {
            font-size: 22px;
        }

        #general-chatbot.general-bot {
            position: fixed;
            right: 18px;
            bottom: 164px;
            z-index: 9998;
            width: 360px;
            max-width: 92vw;
            height: 520px;
            max-height: 70vh;
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 16px 40px rgba(0, 0, 0, .2);
            display: flex;
            flex-direction: column;
            opacity: 0;
            pointer-events: none;
            transform: translateY(12px) scale(.98);
            transition: all .25s ease;
        }

        #general-chatbot.general-bot.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        #general-chatbot.hidden {
            display: block;
        }

        .general-bot-header {
            background: linear-gradient(135deg, var(--general-primary), var(--general-secondary));
            color: #fff;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .general-bot-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 14px;
        }

        #general-bot-close {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
        }

        .general-bot-body {
            flex: 1;
            padding: 14px;
            overflow: auto;
            background: #fafafa;
        }

        .bot-message,
        .user-message {
            display: flex;
            margin-bottom: 10px;
        }

        .bot-message .bubble {
            background: #fff;
            color: #222;
            border-radius: 14px;
            padding: 10px 12px;
            max-width: 85%;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
        }

        .user-message {
            justify-content: flex-end;
        }

        .user-message .bubble {
            background: linear-gradient(135deg, var(--general-primary), var(--general-secondary));
            color: #fff;
            border-radius: 14px;
            padding: 10px 12px;
            max-width: 85%;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
        }

        .quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .quick-replies button {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 999px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all .15s ease;
            font-size: 13px;
        }

        .quick-replies button:hover {
            border-color: var(--general-primary);
            color: var(--general-primary);
        }

        .general-bot-footer {
            display: flex;
            gap: 8px;
            padding: 10px;
            background: #fff;
            border-top: 1px solid #eee;
        }

        .general-bot-footer input {
            flex: 1;
            border: 1px solid #e6e6e6;
            border-radius: 12px;
            padding: 10px 12px;
            outline: none;
        }

        .general-bot-footer button {
            background: linear-gradient(135deg, var(--general-primary), var(--general-secondary));
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 10px 12px;
            cursor: pointer;
        }

        @media (max-width: 480px) {
            #general-chatbot.general-bot {
                right: 12px;
                left: 12px;
                width: auto;
                height: 64vh;
                bottom: 154px;
            }

            #general-bot-toggle.general-bot-fab {
                right: 12px;
                bottom: 90px;
            }
        }
    </style>

    <script>
        (function() {
                const generalRoutes = {
                    home: "{{ route('home') }}",
                    certificatesPublic: "{{ route('certificates.form') }}",
                    @auth
                    certificatesUser: "{{ route('user.certificates') }}",
                    userDashboard: "{{ route('user.dashboard') }}",
                    userProfile: "{{ route('user.profile') }}",
                @else
                    login: "{{ route('login') }}",
                    register: "{{ route('register') }}",
                @endauth
                whatsapp:
                    "https://wa.me/573229675194?text=Hola%20Ipocoldigitaltechnology%2C%20necesito%20informaci%C3%B3n%20sobre%20sus%20servicios"
            };

            const generalBot = {
                @auth
                welcomeMessage: "¡Hola {{ auth()->user()->first_name ?? auth()->user()->name }}! ¿En qué puedo ayudarte con nuestros servicios?",
                quickReplies: ["Mis Certificados", "Mi Perfil", "Tienda", "Servicios"],
            @else
                welcomeMessage: "¡Hola! Soy el asistente de Colsertrans. Te ayudo con formalización empresarial, certificados y capacitaciones. ¿Qué necesitas?",
                quickReplies: ["Servicios", "Certificados", "Registro", "Contacto"],
            @endauth
            responses: {
                servicios: "🏢 Ofrecemos: afiliación a entidades parafiscales, creación de estatutos, registro en Cámara de Comercio, planillas de pago y capacitaciones certificables.",
                certificados: @auth "📋 Aquí puedes ver todos tus certificados emitidos, descargarlos y verificar su estado."
            @else
                "📋 Puedes buscar y verificar certificados por cédula o número de serie. También regístrate para ver tus certificados personales."
            @endauth ,
            @guest registro:
            "📝 Regístrate en nuestra plataforma para acceder a tu panel personal y gestionar tus certificados.",
            login: "🔐 Inicia sesión para acceder a tu panel y ver todos tus certificados.",
        @endguest
        @auth
        perfil: "👤 Gestiona tu información personal, cambia tu contraseña y actualiza tus datos de contacto.",
        @endauth
        tienda:
            "🛒 Contamos con productos de seguridad: extintores, kits de control de derrames, botiquines y más. La tienda virtual está en construcción.",
            contacto: "📞 Te conecto con nuestro WhatsApp para atención personalizada con uno de nuestros asesores.",
            precios:
            "💰 Para cotizaciones personalizadas, escríbenos por WhatsApp. Los precios varían según el servicio requerido.",
            proceso:
            "📋 Nuestro proceso: 1) Consulta inicial, 2) Cotización, 3) Capacitación, 4) Emisión de certificado, 5) Verificación online.",
            ayuda: "❓ Puedo ayudarte con: servicios, certificados, registro o conectarte con nuestro equipo por WhatsApp.",
            default:
            "No entendí bien tu consulta. Puedo ayudarte con: Servicios, Certificados, @guest Registro, @endguest @auth Mi Perfil, @endauth Tienda o Contacto."
        },
        actions: {
            servicios: () => window.location.assign(generalRoutes.home + "#servicios"),
            @auth
            certificados: () => window.location.assign(generalRoutes.certificatesUser),
            perfil: () => window.location.assign(generalRoutes.userProfile),
            dashboard: () => window.location.assign(generalRoutes.userDashboard),
        @else
            certificados: () => window.location.assign(generalRoutes.certificatesPublic),
            registro: () => window.location.assign(generalRoutes.register),
            login: () => window.location.assign(generalRoutes.login),
        @endauth
        contacto: () => window.open(generalRoutes.whatsapp, "_blank"),
            ayuda: () => window.open(generalRoutes.whatsapp, "_blank")
        }
        };

        const container = document.getElementById('general-chatbot');
        const fab = document.getElementById('general-bot-toggle');
        const closeBtn = document.getElementById('general-bot-close');
        const body = document.getElementById('general-bot-body');
        const input = document.getElementById('general-input');
        const send = document.getElementById('general-send');
        const quick = document.getElementById('general-quick');

        function appendMessage(text, from = 'bot') {
            const wrap = document.createElement('div');
            wrap.className = from === 'bot' ? 'bot-message' : 'user-message';
            const bubble = document.createElement('div');
            bubble.className = 'bubble';
            bubble.textContent = text;
            wrap.appendChild(bubble);
            body.appendChild(wrap);
            body.scrollTop = body.scrollHeight;
        }

        function setQuickReplies(list) {
            quick.innerHTML = '';
            list.forEach(label => {
                const b = document.createElement('button');
                b.textContent = label;
                b.onclick = () => handleGeneralCommand(label);
                quick.appendChild(b);
            });
        }

        function handleGeneralCommand(cmd) {
            if (!cmd) return;
            const normalized = cmd.toLowerCase().trim();

            appendMessage(cmd, 'user');

            if (normalized.includes('servicio')) {
                appendMessage(generalBot.responses.servicios);
                generalBot.actions.servicios();
            } else if (normalized.includes('certificado')) {
                appendMessage(generalBot.responses.certificados);
                generalBot.actions.certificados();
                @guest
            } else if (normalized.includes('registro') || normalized.includes('registrar')) {
                appendMessage(generalBot.responses.registro);
                generalBot.actions.registro();
            } else if (normalized.includes('login') || normalized.includes('ingresar')) {
                appendMessage(generalBot.responses.login);
                generalBot.actions.login();
            @endguest
            @auth
        } else if (normalized.includes('perfil')) {
            appendMessage(generalBot.responses.perfil);
            generalBot.actions.perfil();
        } else if (normalized.includes('dashboard') || normalized.includes('panel')) {
            generalBot.actions.dashboard();
        @endauth
        } else if (normalized.includes('tienda') || normalized.includes('producto')) {
            appendMessage(generalBot.responses.tienda);
            generalBot.actions.tienda();
        } else if (normalized.includes('contacto') || normalized.includes('whatsapp') || normalized.includes('ayuda')) {
            appendMessage(generalBot.responses.contacto);
            generalBot.actions.contacto();
        } else if (normalized.includes('precio') || normalized.includes('cotizacion')) {
            appendMessage(generalBot.responses.precios);
            generalBot.actions.contacto();
        } else if (normalized.includes('proceso') || normalized.includes('como funciona')) {
            appendMessage(generalBot.responses.proceso);
        } else {
            appendMessage(generalBot.responses.default);
        }
        }

        fab?.addEventListener('click', () => container?.classList.toggle('show'));
        closeBtn?.addEventListener('click', () => container?.classList.remove('show'));
        send?.addEventListener('click', () => {
            handleGeneralCommand(input.value);
            input.value = '';
        });
        input?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                handleGeneralCommand(input.value);
                input.value = '';
            }
        });

        if (container && fab) {
            appendMessage(generalBot.welcomeMessage);
            setQuickReplies(generalBot.quickReplies);
        }
        })();
    </script>

</body>

</html>
