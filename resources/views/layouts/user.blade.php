<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title') - Panel Usuario</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <link rel="stylesheet" href="{{ asset('css/user.css') }}">

  @stack('styles')
</head>

<body>
  <button class="mobile-toggle" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
  </button>

  <div class="mobile-header">
    <span class="mobile-brand">Mi Dashboard</span>
    <span class="text-muted">{{ auth()->user()->name ?? 'Usuario' }}</span>
  </div>

  <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

  <div class="sidebar" id="sidebar">
    <div class="company-logo">
      <div class="company-brand">
        <a href="{{ url('/') }}">
          <img src="{{ asset('public/images/Logo.png') }}" alt="Logo de la empresa" style="height:100px;">
        </a>
      </div>
    </div>

    <nav class="sidebar-nav">

      <div class="nav-item">
        <a href="{{ route('user.dashboard') }}"
          class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
          <i class="bi bi-person-circle"></i>
          <span>Dashboard</span>
        </a>
      </div>

      <div class="nav-item">
        <a href="{{ route('user.profile') }}" class="nav-link {{ request()->routeIs('user.profile') ? 'active' : '' }}">
          <i class="bi bi-person-circle"></i>
          <span>Profile</span>
        </a>
      </div>

      <div class="nav-item">
        <a href="{{ route('user.certificates') }}"
          class="nav-link {{ request()->routeIs('user.certificates*') ? 'active' : '' }}">
          <i class="bi bi-award"></i>
          <span>Certificates</span>
        </a>
      </div>

    </nav>

    <div class="sidebar-footer">
      <div class="user-info">
        <div class="user-avatar">
          {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
        </div>
        <div class="user-details">
          <div class="user-name">
            {{ auth()->user()->name ?? 'Usuario' }}
          </div>
          <div class="user-role">
            {{ auth()->user()->role ?? 'Rol no definido' }}
          </div>
        </div>
      </div>

      <div class="nav-item">
        <a href="{{ route('logout') }}" class="nav-link"
          onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <i class="bi bi-box-arrow-right"></i>
          <span>Logout</span>
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

      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-circle me-2"></i>
      {{ session('error') }}
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

  <script src="{{ asset('js/user.js') }}"></script>

  @stack('scripts')
  @yield('scripts')
  <div id="user-chatbot" class="user-bot hidden">
    <div class="user-bot-header">
      <div class="user-bot-brand">
        <i class="fas fa-user-circle"></i>
        <span>Mi Asistente</span>
      </div>
      <button id="user-bot-close" aria-label="Cerrar">&times;</button>
    </div>

    <div id="user-bot-body" class="user-bot-body">
      <div class="bot-message">
        <div class="bubble">
          ¡Hola! ¿En qué puedo ayudarte con tu cuenta?
        </div>
      </div>
      <div id="user-quick" class="quick-replies"></div>
    </div>

    <div class="user-bot-footer">
      <input id="user-input" type="text" placeholder="Ej: mis certificados, perfil..." />
      <button id="user-send" aria-label="Enviar">
        <i class="fa fa-paper-plane"></i>
      </button>
    </div>
  </div>

  <button id="user-bot-toggle" class="user-bot-fab" aria-label="Mi Asistente">
    <i class="fa fa-comments"></i>
  </button>

  <style>
    :root {
      --user-primary: #2d08b4e0;
      --user-secondary: #1028ff;
    }

    #user-bot-toggle.user-bot-fab {
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
      background: linear-gradient(135deg, var(--user-primary), var(--user-secondary));
      color: #fff;
      border: none;
      box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
      cursor: pointer;
      transition: transform .2s ease, box-shadow .2s ease;
    }

    #user-bot-toggle:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(0, 0, 0, .25);
    }

    #user-bot-toggle i {
      font-size: 22px;
    }

    #user-chatbot.user-bot {
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

    #user-chatbot.user-bot.show {
      opacity: 1;
      pointer-events: auto;
      transform: translateY(0) scale(1);
    }

    #user-chatbot.hidden {
      display: block;
    }

    .user-bot-header {
      background: linear-gradient(135deg, var(--user-primary), var(--user-secondary));
      color: #fff;
      padding: 12px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .user-bot-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
    }

    #user-bot-close {
      background: transparent;
      border: none;
      color: #fff;
      font-size: 22px;
      cursor: pointer;
    }

    .user-bot-body {
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
      background: linear-gradient(135deg, var(--user-primary), var(--user-secondary));
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
      border-color: var(--user-primary);
      color: var(--user-primary);
    }

    .user-bot-footer {
      display: flex;
      gap: 8px;
      padding: 10px;
      background: #fff;
      border-top: 1px solid #eee;
    }

    .user-bot-footer input {
      flex: 1;
      border: 1px solid #e6e6e6;
      border-radius: 12px;
      padding: 10px 12px;
      outline: none;
    }

    .user-bot-footer button {
      background: linear-gradient(135deg, var(--user-primary), var(--user-secondary));
      color: #fff;
      border: none;
      border-radius: 12px;
      padding: 10px 12px;
      cursor: pointer;
    }

    @media (max-width: 480px) {
      #user-chatbot.user-bot {
        left: 12px;
        right: 12px;
        width: auto;
        height: 64vh;
        bottom: 74px;
      }

      #user-bot-toggle.user-bot-fab {
        left: 12px;
        bottom: 12px;
      }
    }
  </style>

  <script>
    (function () {
      const userRoutes = {
        dashboard: "{{ route('user.dashboard') }}",
        certificates: "{{ route('user.certificates') }}",
        profile: "{{ route('user.profile') }}",
        whatsapp: "https://wa.me/573229675194?text=Hola%20IpocoldigithalTechnology%2C%20soy%20usuario%20registrado%20y%20necesito%20ayuda"
      };

      const userBot = {
        welcomeMessage: "¡Hola! ¿En qué puedo ayudarte con tu cuenta?",
        quickReplies: ["Certificados", "Perfil", "Tienda", "Ayuda"],
        responses: {
          certificados: "📋 Aquí puedes ver todos tus certificados emitidos y descargarlos.",
          perfil: "👤 Gestiona tu información personal y configuración de cuenta.",
          tienda: "🛒 Explora nuestros productos y servicios disponibles.",
          ayuda: "📞 Te conecto con nuestro WhatsApp para ayuda personalizada.",
          dashboard: "🏠 Tu panel principal con resumen de toda tu información.",
          default: "Puedo ayudarte con: Certificados, Perfil, Tienda o conectarte con Ayuda."
        },
        actions: {
          certificados: () => window.location.assign(userRoutes.certificates),
          perfil: () => window.location.assign(userRoutes.profile),
          dashboard: () => window.location.assign(userRoutes.dashboard),
          ayuda: () => window.open(userRoutes.whatsapp, "_blank")
        }
      };

      const container = document.getElementById('user-chatbot');
      const fab = document.getElementById('user-bot-toggle');
      const closeBtn = document.getElementById('user-bot-close');
      const body = document.getElementById('user-bot-body');
      const input = document.getElementById('user-input');
      const send = document.getElementById('user-send');
      const quick = document.getElementById('user-quick');

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
          b.onclick = () => handleUserCommand(label);
          quick.appendChild(b);
        });
      }

      function handleUserCommand(cmd) {
        if (!cmd) return;
        const normalized = cmd.toLowerCase().trim();

        appendMessage(cmd, 'user');

        if (normalized.includes('certificado')) {
          appendMessage(userBot.responses.certificados);
          userBot.actions.certificados();
        } else if (normalized.includes('perfil')) {
          appendMessage(userBot.responses.perfil);
          userBot.actions.perfil();
        } else if (normalized.includes('ayuda') || normalized.includes('contacto')) {
          appendMessage(userBot.responses.ayuda);
          userBot.actions.ayuda();
        } else if (normalized.includes('dashboard') || normalized.includes('inicio')) {
          appendMessage(userBot.responses.dashboard);
          userBot.actions.dashboard();
        } else {
          appendMessage(userBot.responses.default);
        }
      }

      fab.addEventListener('click', () => container.classList.toggle('show'));
      closeBtn.addEventListener('click', () => container.classList.remove('show'));
      send.addEventListener('click', () => { handleUserCommand(input.value); input.value = ''; });
      input.addEventListener('keydown', (e) => { if (e.key === 'Enter') { handleUserCommand(input.value); input.value = ''; } });

      appendMessage(userBot.welcomeMessage);
      setQuickReplies(userBot.quickReplies);
    })();
  </script>
</body>

</html>