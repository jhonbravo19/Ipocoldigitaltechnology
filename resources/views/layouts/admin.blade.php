<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    @stack('styles')
</head>

<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="sidebar" id="sidebar">
        <div class="company-logo">
            <div class="company-brand">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('public/images/Logo.png') }}" alt="Logo de la empresa" style="height:100px;">
                </a>
            </div>
        </div>

        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <i class="bi bi-shield-check"></i>
                <span>Admin Panel</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('admin.profile.show') }}" class="nav-link">
                    <i class="bi bi-person-circle"></i>
                    <span>PROFILE</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.certificates.index') }}" class="nav-link">
                    <i class="bi bi-award"></i>
                    <span>CERTIFICATES</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.courses.index') }}" class="nav-link">
                    <i class="bi bi-book me-2"></i>
                    <span>COURSES</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.certificates.create') }}" class="nav-link">
                    <i class="bi bi-plus-circle"></i>
                    <span>NEW CERTIFICATE</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.template.config') }}" class="nav-link">
                    <i class="bi bi-gear"></i>
                    <span>CONFIG TEMPLATES</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.statistics') }}" class="nav-link">
                    <i class="bi bi-graph-up me-2"></i>
                    <span>STATISTICS</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
                <div>
                    <div style="font-size: 0.9rem; font-weight: 500;">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </div>
                    <div style="font-size: 0.8rem; opacity: 0.7;">
                        Administrador
                    </div>
                </div>
            </div>

            <div class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>LOGOUT</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('js/admin.js') }}"></script>

    @stack('scripts')
    @yield('scripts')

    <div id="admin-chatbot" class="admin-bot hidden">
        <div class="admin-bot-header">
            <div class="admin-bot-brand">
                <i class="fas fa-shield-alt"></i>
                <span>Asistente Admin</span>
            </div>
            <button id="admin-bot-close" aria-label="Cerrar">&times;</button>
        </div>

        <div id="admin-bot-body" class="admin-bot-body">
            <div class="bot-message">
                <div class="bubble">
                    ¡Hola Admin! ¿Qué necesitas gestionar hoy?
                </div>
            </div>
            <div id="admin-quick" class="quick-replies"></div>
        </div>

        <div class="admin-bot-footer">
            <input id="admin-input" type="text" placeholder="Ej: crear certificado, reportes..." />
            <button id="admin-send" aria-label="Enviar">
                <i class="fa fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <button id="admin-bot-toggle" class="admin-bot-fab" aria-label="Asistente Admin">
        <i class="fa fa-robot"></i>
    </button>

    <style>
        :root {
            --admin-primary: #eb0909ff;
            --admin-secondary: #fd4040ff;
            --admin-dark: #1f1f1f;
            --admin-light: #ffffff;
        }

        #admin-bot-toggle.admin-bot-fab {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 9999;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
            color: #fff;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        #admin-bot-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .25);
        }

        #admin-bot-toggle i {
            font-size: 22px;
        }

        #admin-chatbot.admin-bot {
            position: fixed;
            right: 18px;
            bottom: 82px;
            z-index: 9999;
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

        #admin-chatbot.admin-bot.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        #admin-chatbot.hidden {
            display: block;
        }

        .admin-bot-header {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
            color: #fff;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .admin-bot-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        #admin-bot-close {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
        }

        .admin-bot-body {
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
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
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
            border-color: var(--admin-primary);
            color: var(--admin-primary);
        }

        .admin-bot-footer {
            display: flex;
            gap: 8px;
            padding: 10px;
            background: #fff;
            border-top: 1px solid #eee;
        }

        .admin-bot-footer input {
            flex: 1;
            border: 1px solid #e6e6e6;
            border-radius: 12px;
            padding: 10px 12px;
            outline: none;
        }

        .admin-bot-footer button {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 10px 12px;
            cursor: pointer;
        }

        @media (max-width: 480px) {
            #admin-chatbot.admin-bot {
                left: 12px;
                right: 12px;
                width: auto;
                height: 64vh;
                bottom: 74px;
            }

            #admin-bot-toggle.admin-bot-fab {
                left: 12px;
                bottom: 12px;
            }
        }
    </style>

    <script>
        (function () {
            const adminRoutes = {
                dashboard: "{{ route('admin.dashboard') }}",
                certificates: "{{ route('admin.certificates.index') }}",
                certificatesCreate: "{{ route('admin.certificates.create') }}",
                courses: "{{ route('admin.courses.index') }}",
                template: "{{ route('admin.template.config') }}",
                statistics: "{{ route('admin.statistics') }}",
                expiring: "{{ route('admin.certificates.expiring-soon') }}",
                profile: "{{ route('admin.profile.show') }}"
            };

            const adminBot = {
                welcomeMessage: "¡Hola Admin! ¿Qué necesitas gestionar: Certificados, Cursos, Plantilla o Reportes?",
                quickReplies: ["Certificados", "Cursos", "Plantilla", "Reportes"],
                responses: {
                    certificados: "📋 Gestión completa: crear, editar, activar/inactivar, exportar, ver próximos a vencer.",
                    cursos: "📚 Administrar cursos y contenido educativo.",
                    plantilla: "🎨 Configurar plantillas de certificados: logos, textos, firmas.",
                    reportes: "📊 Ver estadísticas y reportes del sistema.",
                    crear: "✅ Te llevo al formulario para crear un nuevo certificado.",
                    vencer: "⏰ Mostrando certificados próximos a vencer en los próximos 30 días.",
                    perfil: "👤 Gestionar tu perfil de administrador.",
                    default: "No encontré esa opción. Puedo ayudarte con: Certificados, Cursos, Plantilla o Reportes."
                },
                actions: {
                    certificados: () => window.location.assign(adminRoutes.certificates),
                    crear: () => window.location.assign(adminRoutes.certificatesCreate),
                    cursos: () => window.location.assign(adminRoutes.courses),
                    plantilla: () => window.location.assign(adminRoutes.template),
                    reportes: () => window.location.assign(adminRoutes.statistics),
                    vencer: () => window.location.assign(adminRoutes.expiring),
                    perfil: () => window.location.assign(adminRoutes.profile)
                }
            };

            const container = document.getElementById('admin-chatbot');
            const fab = document.getElementById('admin-bot-toggle');
            const closeBtn = document.getElementById('admin-bot-close');
            const body = document.getElementById('admin-bot-body');
            const input = document.getElementById('admin-input');
            const send = document.getElementById('admin-send');
            const quick = document.getElementById('admin-quick');

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
                    b.onclick = () => handleAdminCommand(label);
                    quick.appendChild(b);
                });
            }

            function handleAdminCommand(cmd) {
                if (!cmd) return;
                const normalized = cmd.toLowerCase().trim();

                appendMessage(cmd, 'user');

                if (normalized.includes('certificado')) {
                    appendMessage(adminBot.responses.certificados);
                    setTimeout(() => {
                        addActionButtons(['Ver todos', 'Crear nuevo', 'Por vencer']);
                    }, 500);
                } else if (normalized.includes('crear')) {
                    appendMessage(adminBot.responses.crear);
                    adminBot.actions.crear();
                } else if (normalized.includes('curso')) {
                    appendMessage(adminBot.responses.cursos);
                    adminBot.actions.cursos();
                } else if (normalized.includes('plantilla')) {
                    appendMessage(adminBot.responses.plantilla);
                    adminBot.actions.plantilla();
                } else if (normalized.includes('reporte') || normalized.includes('estadistica')) {
                    appendMessage(adminBot.responses.reportes);
                    adminBot.actions.reportes();
                } else if (normalized.includes('perfil')) {
                    appendMessage(adminBot.responses.perfil);
                    adminBot.actions.perfil();
                } else if (normalized.includes('vencer') || normalized.includes('expir')) {
                    appendMessage(adminBot.responses.vencer);
                    adminBot.actions.vencer();
                } else {
                    appendMessage(adminBot.responses.default);
                }
            }


            function addActionButtons(actions) {
                const buttonContainer = document.createElement('div');
                buttonContainer.className = 'action-buttons';
                buttonContainer.style.cssText = 'display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;';

                actions.forEach(action => {
                    const btn = document.createElement('button');
                    btn.textContent = action;
                    btn.style.cssText = 'background:#eb0909;color:white;border:none;padding:6px 12px;border-radius:12px;cursor:pointer;font-size:12px;';
                    btn.onclick = () => executeAction(action);
                    buttonContainer.appendChild(btn);
                });

                body.appendChild(buttonContainer);
                body.scrollTop = body.scrollHeight;
            }

            function executeAction(action) {
                if (action === 'Ver todos') adminBot.actions.certificados();
                if (action === 'Crear nuevo') adminBot.actions.crear();
                if (action === 'Por vencer') {
                    appendMessage(adminBot.responses.vencer);
                    adminBot.actions.vencer();
                }
            }

            fab.addEventListener('click', () => container.classList.toggle('show'));
            closeBtn.addEventListener('click', () => container.classList.remove('show'));
            send.addEventListener('click', () => { handleAdminCommand(input.value); input.value = ''; });
            input.addEventListener('keydown', (e) => { if (e.key === 'Enter') { handleAdminCommand(input.value); input.value = ''; } });

            appendMessage(adminBot.welcomeMessage);
            setQuickReplies(adminBot.quickReplies);
        })();
    </script>
</body>

</html>