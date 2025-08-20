@extends('layouts.app')

@section('title', 'Servicios y Productos Innovadores - Colsertrans')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/index.js') }}" defer></script>
@endpush

@section('content')
    <header>
        <div class="logo-container">
            <img src="{{ asset('public/images/Logo.png') }}" alt="Logo Ipocoldigitaltechonology" class="logo-image">
        </div>
        <nav>
            <ul class="nav-links">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="{{ route('certificates.form') }}">Certificados</a></li>
                <li><a href="#ayuda">Ayuda</a></li>
                @guest
                    <li><a href="{{ route('login') }}">Login</a></li>
                    <li><a href="{{ route('register') }}">Registro</a></li>
                @else
                    @if (auth()->check() && auth()->user()->role === 'admin')
                        <li><a href="{{ route('admin.dashboard') }}">Panel Admin</a></li>
                    @else
                        <li><a href="{{ route('user.dashboard') }}">Mi Panel</a></li>
                    @endif
                    <li><a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar Sesión</a>
                    </li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endguest
            </ul>
        </nav>
    </header>

    <section class="hero">
        <div class="split-images">
            <div class="image-container left">
                <div class="image-content">
                    <h2>Innovación Digital Sin Límites</h2>
                    <p>Transformamos ideas en soluciones tecnológicas de vanguardia.</p>
                    <!--<a href="{{ route('user.shop') }}#servicios"><button class="cta-button">Más información</button></a>-->
                </div>
            </div>
            <div class="image-container right">
                <div class="image-content">
                    <h2>Soluciones a medidas</h2>
                    <p>Desarrollamos tecnología personalizada para empresas y emprendedores.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="inicio" class="section">
        <h2 class="section-title">Transformamos el futuro digital</h2>
        <div class="section-content">
            <p>En IPOCOL Digital Technology es una empresa que ofrece soluciones tecnológicas
                integrales enfocadas en impulsar el crecimiento, la seguridad y la eficiencia
                de negocios. Su misión es brindar servicios digitales a medida para empresas
                y emprendedores, incluyendo telecomunicaciones, marketing digital, seguridad
                electrónica, automatización de procesos y gestión de datos. Con una visión
                clara de ser líderes en servicios digitales en Colombia, IPOCOL se destaca
                por su innovación, adaptación a nuevas tecnologías y compromiso con la
                excelencia. Ofrecen soporte personalizado y tecnología de última generación
                para mejorar la comunicación, seguridad, gestión de información y presencia
                digital de sus clientes.</p>
            <div class="features">
                <div class="feature">
                    <svg class="feature-icon" viewBox="0 0 24 24" width="40" height="40">
                        <circle cx="12" cy="8" r="5" fill="#007BFF" />
                        <rect x="9" y="14" width="6" height="4" rx="1" fill="#007BFF" />
                        <rect x="9" y="19" width="6" height="2" rx="1" fill="#007BFF" />
                    </svg>
                    <h3> Innovación Constante</h3>
                    <p>Adoptamos las últimas tecnologías para ofrecer soluciones digitales modernas y efectivas.</p>
                </div>
                <div class="feature">
                    <svg class="feature-icon" viewBox="0 0 24 24" width="40" height="40">
                        <path d="M12 3 L19 6 V11 C19 16 15.5 20 12 21 C8.5 20 5 16 5 11 V6 Z" fill="#007BFF" />
                    </svg>
                    <h3>Compromiso Total</h3>
                    <p>Trabajamos de la mano con cada cliente para garantizar resultados confiables y seguros.</p>
                </div>
                <div class="feature">
                    <svg class="feature-icon" viewBox="0 0 24 24" width="40" height="40">
                        <rect x="6" y="6" width="12" height="12" rx="2" fill="none" stroke="#007BFF"
                            stroke-width="2" />
                        <rect x="9" y="9" width="6" height="6" rx="1" fill="#007BFF" />
                        <rect x="3" y="9.5" width="3" height="1" fill="#007BFF" />
                        <rect x="3" y="13.5" width="3" height="1" fill="#007BFF" />
                        <rect x="18" y="9.5" width="3" height="1" fill="#007BFF" />
                        <rect x="18" y="13.5" width="3" height="1" fill="#007BFF" />
                        <rect x="9.5" y="3" width="1" height="3" fill="#007BFF" />
                        <rect x="13.5" y="3" width="1" height="3" fill="#007BFF" />
                        <rect x="9.5" y="18" width="1" height="3" fill="#007BFF" />
                        <rect x="13.5" y="18" width="1" height="3" fill="#007BFF" />
                    </svg>
                    <h3>Excelencia Técnica</h3>
                    <p>Brindamos soporte especializado y herramientas de calidad para impulsar tu negocio.</p>
                </div>
            </div>
        </div>
    </section>


    <section id="servicios" class="section">
        <h2 class="section-title">Nuestros Servicios</h2>

        <div class="services-slider-container">
            <div class="services-slider">

                <!-- 1) Mercado y Ventas -->
                <div class="service-card">
                    <div class="card-inner">

                        <!-- Frente -->
                        <div class="card-face card-front">
                            <div class="service-icon">
                                <svg viewBox="0 0 100 100" width="60" height="60" aria-hidden="true">
                                    <circle cx="50" cy="50" r="45" fill="var(--primary-color)"
                                        opacity=".12" />
                                    <!-- Barras -->
                                    <rect x="24" y="58" width="10" height="18" rx="2" fill="none"
                                        stroke="var(--primary-color)" stroke-width="5" />
                                    <rect x="40" y="48" width="10" height="28" rx="2" fill="none"
                                        stroke="var(--primary-color)" stroke-width="5" />
                                    <rect x="56" y="38" width="10" height="38" rx="2" fill="none"
                                        stroke="var(--primary-color)" stroke-width="5" />
                                </svg>
                            </div>
                            <h3>Mercado y Ventas</h3>
                            <p>Impulsamos tu crecimiento con estrategias efectivas para alcanzar tus metas.</p>
                        </div>

                        <!-- Reverso -->
                        <div class="card-face card-back">
                            <ul>
                                <li>Estudios de mercado y estrategias</li>
                                <li>Campañas de mercadeo directo</li>
                                <li>Encuestas y sondeos</li>
                                <li>Telemarketing y ventas telefónicas</li>
                                <li>Validación de datos de clientes</li>
                            </ul>
                        </div>

                    </div>
                </div>

                <div class="service-card">
                    <div class="card-inner">

                        <div class="card-face card-front">
                            <div class="service-icon">
                                <svg viewBox="0 0 100 100" width="60" height="60" aria-hidden="true">
                                    <circle cx="50" cy="50" r="45" fill="var(--primary-color)"
                                        opacity=".12" />
                                    <!-- enlaces -->
                                    <line x1="30" y1="35" x2="70" y2="35"
                                        stroke="var(--primary-color)" stroke-width="5" stroke-linecap="round" />
                                    <line x1="30" y1="35" x2="50" y2="65"
                                        stroke="var(--primary-color)" stroke-width="5" stroke-linecap="round" />
                                    <line x1="70" y1="35" x2="50" y2="65"
                                        stroke="var(--primary-color)" stroke-width="5" stroke-linecap="round" />
                                    <!-- nodos -->
                                    <circle cx="30" cy="35" r="6" fill="var(--primary-color)" />
                                    <circle cx="70" cy="35" r="6" fill="var(--primary-color)" />
                                    <circle cx="50" cy="65" r="6" fill="var(--primary-color)" />

                                </svg>
                            </div>
                            <h3>Telecomunicaciones</h3>
                            <p>Conectamos tu negocio con los mejores servicios de telecomunicaciones, garantizando calidad y
                                tecnología avanzada.</p>
                        </div>

                        <div class="card-face card-back">
                            <ul>
                                <li>Gestión de llamadas y consultas</li>
                                <li>Plataforma tecnológica en alquiler</li>
                                <li>Llamadas para pedidos (entrantes/salientes)</li>
                                <li>Facturación y nómina electrónica</li>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- 3) Estatutos -->
                <div class="service-card">
                    <div class="card-inner">

                        <div class="card-face card-front">
                            <div class="service-icon">
                                <svg viewBox="0 0 100 100" width="60" height="60" aria-hidden="true">
                                    <circle cx="50" cy="50" r="45" fill="var(--primary-color)"
                                        opacity=".12" />
                                    <!-- monitor -->
                                    <rect x="20" y="32" width="38" height="26" rx="3" fill="none"
                                        stroke="var(--primary-color)" stroke-width="5" />
                                    <path d="M32,62 H46" stroke="var(--primary-color)" stroke-width="5"
                                        stroke-linecap="round" />
                                    <rect x="42" y="62" width="12" height="4" rx="2"
                                        fill="var(--primary-color)" />
                                    <!-- chevrons dentro del monitor -->
                                    <path d="M36,38 L30,45 L36,52" fill="none" stroke="var(--primary-color)"
                                        stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M44,38 L50,45 L44,52" fill="none" stroke="var(--primary-color)"
                                        stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                                    <!-- móvil -->
                                    <rect x="62" y="30" width="16" height="30" rx="3" fill="none"
                                        stroke="var(--primary-color)" stroke-width="5" />
                                    <rect x="66" y="33" width="8" height="2" rx="1"
                                        fill="var(--primary-color)" />
                                    <circle cx="70" cy="56" r="2" fill="var(--primary-color)" />
                                </svg>
                            </div>
                            <h3>Desarrollo Web y Apps</h3>
                            <p>Transformamos tu idea en una experiencia digital única con el desarrollo de páginas web y
                                aplicaciones a la medida.</p>
                        </div>

                        <div class="card-face card-back">
                            <ul>
                                <li>Sitios web responsivos</li>
                                <li>E-commerce</li>
                                <li>Aplicaciones móviles</li>
                                <li>Sistemas a medida</li>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- 4) Creación ante Entidades Parafiscales -->
                <div class="service-card">
                    <div class="card-inner">

                        <div class="card-face card-front">
                            <div class="service-icon">
                                <svg viewBox="0 0 100 100" width="60" height="60" aria-hidden="true">
                                    <circle cx="50" cy="50" r="45" fill="var(--primary-color)"
                                        opacity=".12" />
                                    <!-- regla -->
                                    <g transform="rotate(-25 50 50)">
                                        <rect x="26" y="44" width="48" height="10" rx="2" fill="none"
                                            stroke="var(--primary-color)" stroke-width="5" />
                                        <line x1="32" y1="44" x2="32" y2="54"
                                            stroke="var(--primary-color)" stroke-width="3" />
                                        <line x1="38" y1="44" x2="38" y2="54"
                                            stroke="var(--primary-color)" stroke-width="3" />
                                        <line x1="44" y1="44" x2="44" y2="54"
                                            stroke="var(--primary-color)" stroke-width="3" />
                                        <line x1="50" y1="44" x2="50" y2="54"
                                            stroke="var(--primary-color)" stroke-width="3" />
                                        <line x1="56" y1="44" x2="56" y2="54"
                                            stroke="var(--primary-color)" stroke-width="3" />
                                        <line x1="62" y1="44" x2="62" y2="54"
                                            stroke="var(--primary-color)" stroke-width="3" />
                                        <line x1="68" y1="44" x2="68" y2="54"
                                            stroke="var(--primary-color)" stroke-width="3" />
                                    </g>
                                    <!-- lápiz -->
                                    <g transform="rotate(25 50 50)">
                                        <rect x="28" y="60" width="38" height="8" rx="2" fill="none"
                                            stroke="var(--primary-color)" stroke-width="5" />
                                        <polygon points="66,60 78,64 66,68" fill="none" stroke="var(--primary-color)"
                                            stroke-width="5" stroke-linejoin="round" />
                                        <rect x="28" y="60" width="6" height="8" rx="2"
                                            fill="var(--primary-color)" />
                                    </g>
                                </svg>
                            </div>
                            <h3>Diseño Gráfico y Publicidad</h3>
                            <p>Propuestas visuales creativas y estrategias publicitarias que marcan la diferencia y capturan
                                la atención.</p>
                        </div>

                        <div class="card-face card-back">
                            <ul>
                                <li>Creación de logos</li>
                                <li>Material publicitario</li>
                                <li>Animaciones de logos</li>
                                <li>Experiencia de usuario</li>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- 5) Liquidar Planillas de Pago (PILA) -->
                <div class="service-card">
                    <div class="card-inner">

                        <div class="card-face card-front">
                            <div class="service-icon">
                                <svg viewBox="0 0 100 100" width="60" height="60" aria-hidden="true">
                                    <circle cx="50" cy="50" r="45" fill="var(--primary-color)"
                                        opacity=".12" />
                                    <!-- escudo -->
                                    <path d="M50,20 L75,28 V45 C75,60 62,70 50,75 C38,70 25,60 25,45 V28 Z" fill="none"
                                        stroke="var(--primary-color)" stroke-width="5" stroke-linejoin="round" />
                                    <!-- check -->
                                    <path d="M40,52 L48,60 L62,46" fill="none" stroke="var(--primary-color)"
                                        stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <h3>Seguridad Electrónica</h3>
                            <p>Protegemos lo que más importa con soluciones avanzadas que garantizan tu tranquilidad
                                empresarial.</p>
                        </div>

                        <div class="card-face card-back">
                            <ul>
                                <li>Sistemas CCTV</li>
                                <li>Control de acceso</li>
                                <li>Alarmas inteligentes</li>
                                <li>Monitoreo remoto</li>
                            </ul>
                        </div>

                    </div>
                </div>
                <!-- 6) Liquidar Planillas de Pago (PILA) -->
                <div class="service-card">
                    <div class="card-inner">

                        <div class="card-face card-front">
                            <div class="service-icon">
                                <svg viewBox="0 0 100 100" width="60" height="60" aria-hidden="true">
                                    <circle cx="50" cy="50" r="45" fill="var(--primary-color)"
                                        opacity=".12" />
                                    <!-- arco de la diadema -->
                                    <path d="M30,40 C30,26 70,26 70,40" fill="none" stroke="var(--primary-color)"
                                        stroke-width="5" stroke-linecap="round" />
                                    <!-- copas -->
                                    <rect x="26" y="40" width="10" height="18" rx="3" fill="none"
                                        stroke="var(--primary-color)" stroke-width="5" />
                                    <rect x="64" y="40" width="10" height="18" rx="3" fill="none"
                                        stroke="var(--primary-color)" stroke-width="5" />
                                    <!-- micrófono -->
                                    <path d="M70,50 C70,60 60,66 50,66 H44" fill="none" stroke="var(--primary-color)"
                                        stroke-width="5" stroke-linecap="round" />
                                    <circle cx="42" cy="66" r="2" fill="var(--primary-color)" />
                                </svg>
                            </div>
                            <h3>Mantenimiento y Soporte</h3>
                            <p>Aseguramos el óptimo funcionamiento de tus sistemas con servicios confiables y respuesta
                                rápida.</p>
                        </div>

                        <div class="card-face card-back">
                            <ul>
                                <li>Soporte 24/7</li>
                                <li>Mantenimiento preventivo</li>
                                <li>Resolución de incidencias</li>
                                <li>Optimización de sistemas</li>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- 7) Liquidar Planillas de Pago (PILA) -->
                <div class="service-card">
                    <div class="card-inner">

                        <div class="card-face card-front">
                            <div class="service-icon">
                                <svg viewBox="0 0 100 100" width="60" height="60" aria-hidden="true">
                                    <circle cx="50" cy="50" r="45" fill="var(--primary-color)"
                                        opacity=".12" />
                                    <!-- birrete -->
                                    <path d="M20,45 L50,30 L80,45 L50,60 Z" fill="none" stroke="var(--primary-color)"
                                        stroke-width="5" stroke-linejoin="round" />
                                    <!-- borla -->
                                    <path d="M80,45 L80,58" fill="none" stroke="var(--primary-color)" stroke-width="5"
                                        stroke-linecap="round" />
                                    <circle cx="80" cy="58" r="3" fill="var(--primary-color)" />
                                    <!-- base (cinta) -->
                                    <rect x="38" y="60" width="24" height="8" rx="2" fill="none"
                                        stroke="var(--primary-color)" stroke-width="5" />
                                </svg>
                            </div>
                            <h3>Educación y Capacitación</h3>
                            <p>Programas enfocados en el desarrollo profesional para mantener a tu equipo a la vanguardia
                                tecnológica.</p>
                        </div>

                        <div class="card-face card-back">
                            <ul>
                                <li>Capacitación técnica</li>
                                <li>Talleres de innovación</li>
                                <li>Certificaciones profesionales</li>
                                <li>Transformación digital</li>
                            </ul>
                        </div>

                    </div>
                </div>

            </div>

            <button class="slider-control prev">❮</button>
            <button class="slider-control next">❯</button>
        </div>
    </section>



    <section id="certificados" class="section">
        <h2 class="section-title">Capacitación Con Certificado</h2>

        <div class="certificates">
            <p>
                En <strong>IPOCOL Digital Technology</strong> formamos a emprendedores y equipos con programas
                prácticos y actualizados. Modalidad virtual o presencial, material descargable y
                <strong>certificación oficial IPOCOL</strong>.
            </p>

            <!-- 1) Cámara de comercio -->
            <div class="certificate">
                <svg class="certificate-icon" viewBox="0 0 100 100" width="80" height="80" aria-hidden="true">
                    <rect x="10" y="10" width="80" height="80" rx="5" fill="#ffffff"
                        stroke="currentColor" stroke-width="3" />
                    <circle cx="50" cy="40" r="20" fill="currentColor" />
                    <path d="M30,70 L70,70" stroke="currentColor" stroke-width="3" />
                    <path d="M30,80 L70,80" stroke="currentColor" stroke-width="3" />
                </svg>
                <h3>Capacitación de Cámara de Comercio</h3>
                <p>
                    Paso a paso para matrícula mercantil, homonimia y requisitos. Duración:
                    <strong>8 horas</strong>. Incluye <em>checklist</em> y
                    <strong>certificado IPOCOL</strong>.
                </p>
            </div>

            <!-- 2) Estatutos societarios -->
            <div class="certificate">
                <svg class="certificate-icon" viewBox="0 0 100 100" width="80" height="80" aria-hidden="true">
                    <rect x="10" y="10" width="80" height="80" rx="5" fill="#ffffff"
                        stroke="currentColor" stroke-width="3" />
                    <path d="M30,30 L70,30 L70,70 L30,70 Z" fill="currentColor" />
                    <path d="M40,40 L60,40" stroke="#ffffff" stroke-width="2" />
                    <path d="M40,50 L60,50" stroke="#ffffff" stroke-width="2" />
                    <path d="M40,60 L60,60" stroke="#ffffff" stroke-width="2" />
                </svg>
                <h3>Capacitación en Creación de Estatutos</h3>
                <p>
                    Redacción de estatutos y buenas prácticas de gobierno corporativo. Duración:
                    <strong>4 horas</strong>. Plantillas IPOCOL + <strong>certificado</strong>.
                </p>
            </div>

            <!-- 3) Afiliaciones parafiscales -->
            <div class="certificate">
                <svg class="certificate-icon" viewBox="0 0 100 100" width="80" height="80" aria-hidden="true">
                    <rect x="10" y="10" width="80" height="80" rx="5" fill="#ffffff"
                        stroke="currentColor" stroke-width="3" />
                    <circle cx="50" cy="50" r="25" fill="currentColor" />
                    <path d="M35,50 L45,60 L65,40" stroke="#ffffff" stroke-width="3" fill="none" />
                </svg>
                <h3>Afiliación a Entidades Parafiscales</h3>
                <p>
                    EPS, ARL y Caja de Compensación: requisitos, flujo y alta en plataformas.
                    Duración: <strong>2 horas</strong>. Incluye guías y <strong>certificado IPOCOL</strong>.
                </p>
            </div>

            <!-- 4) Planillas de parafiscales (PILA) -->
            <div class="certificate">
                <svg class="certificate-icon" viewBox="0 0 100 100" width="80" height="80" aria-hidden="true">
                    <rect x="20" y="15" width="50" height="65" rx="5" fill="#ffffff"
                        stroke="currentColor" stroke-width="3" />
                    <path d="M70 20 L70 35 L55 35" fill="none" stroke="currentColor" stroke-width="3" />
                    <circle cx="45" cy="60" r="10" fill="currentColor" stroke="#ffffff"
                        stroke-width="2" />
                    <polygon points="40,70 45,80 50,70" fill="currentColor" stroke="#ffffff" stroke-width="2" />
                    <polygon points="42,70 45,75 48,70" fill="#ffffff" stroke="#ffffff" stroke-width="1" />
                    <circle cx="75" cy="25" r="10" fill="currentColor" stroke="#ffffff"
                        stroke-width="2" />
                    <path d="M70,25 L74,30 L80,20" stroke="#ffffff" stroke-width="2" fill="none" />
                </svg>
                <h3>Creación y Montaje de Planillas (PILA)</h3>
                <p>
                    Liquidación de aportes, novedades y cierres mensuales en operadores. Duración:
                    <strong>4 horas</strong>. Material práctico + <strong>certificado IPOCOL</strong>.
                </p>
            </div>
        </div>
    </section>

    <section id="nosotros" class="section">
        <h2 class="section-title">Nosotros</h2>
        <div class="about-container">
            <div class="about-tabs">
                <button class="tab-button active" data-tab="mision">Misión</button>
                <button class="tab-button" data-tab="vision">Visión</button>
                <button class="tab-button" data-tab="valores">Valores</button>
            </div>
            <div class="tab-content">
                <div class="tab-panel active" id="mision">
                    <h3>Nuestra Misión</h3>
                    <p>Impulsar a empresas y emprendedores con soluciones tecnológicas integrales —telecomunicaciones,
                        desarrollo web y apps, marketing digital, seguridad electrónica y gestión de datos— entregadas a la
                        medida, con innovación continua, soporte cercano y resultados medibles.</p>
                </div>
                <div class="tab-panel" id="vision">
                    <h3>Nuestra Visión</h3>
                    <p>Ser referentes en Colombia (y proyectarnos en LATAM) por transformar negocios en organizaciones
                        digitales seguras, conectadas y escalables, reconocidos por nuestra excelencia técnica, adaptación a
                        nuevas tecnologías y compromiso con la calidad.</p>
                </div>
                <div class="tab-panel" id="valores">
                    <h3>Nuestros Valores</h3>
                    <ul class="valores-list">
                        <li><span>Innovación constante</span> Exploramos y adoptamos tecnologías que generen ventajas reales
                            para el cliente.</li>
                        <li><span>Enfoque al cliente</span> Diseñamos soluciones a medida, con escucha activa y
                            acompañamiento cercano.</li>
                        <li><span>Excelencia técnica</span> Estándares altos de calidad, pruebas rigurosas y documentación
                            clara.</li>
                        <li><span>Servicio y compromiso</span> Soporte oportuno (SLA), resolución efectiva y postventa
                            responsable.</li>
                        <li><span>Trabajo colaborativo</span> Co-creación con clientes y aliados para maximizar el impacto.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="ayuda" class="section">
        <h2 class="section-title">Centro de Ayuda</h2>

        <div class="help-content">
            <!-- FAQ -->
            <div class="faq">
                <h3>Preguntas Frecuentes</h3>

                <div class="accordion" role="tablist">
                    <!-- 1 -->
                    <div class="accordion-item">
                        <button class="accordion-header" role="tab" aria-expanded="true" aria-controls="faq1"
                            id="tab-faq1">
                            ¿Cómo solicito un servicio o cotización en IPOCOL Digital Technology?
                        </button>
                        <div class="accordion-content" role="region" id="faq1" aria-labelledby="tab-faq1">
                            <p>
                                Diligencia el <strong>formulario de contacto</strong> de esta sección o escríbenos por
                                <a href="https://wa.me/573229675194" target="_blank" rel="noopener">WhatsApp</a>.
                                También puedes enviarnos un correo a
                                <a
                                    href="mailto:contacto@ipocoldigitaltechnology.com">contacto@ipocoldigitaltechnology.com</a>.
                                Nuestro equipo te responderá con la cotización y los pasos a seguir.
                            </p>
                        </div>
                    </div>

                    <!-- 2 -->
                    <div class="accordion-item">
                        <button class="accordion-header" role="tab" aria-expanded="false" aria-controls="faq2"
                            id="tab-faq2">
                            ¿Cuáles son los tiempos de ejecución o entrega?
                        </button>
                        <div class="accordion-content" role="region" id="faq2" aria-labelledby="tab-faq2">
                            <p>
                                Varían según el alcance: para <em>desarrollo web y apps</em> entregamos un cronograma con
                                hitos;
                                en <em>telecomunicaciones y seguridad electrónica</em> definimos fecha de
                                visita/instalación;
                                en <em>mantenimiento</em> acordamos ventana de atención. Te confirmamos tiempos al recibir
                                tu solicitud.
                            </p>
                        </div>
                    </div>

                    <!-- 3 -->
                    <div class="accordion-item">
                        <button class="accordion-header" role="tab" aria-expanded="false" aria-controls="faq3"
                            id="tab-faq3">
                            ¿Qué garantía y soporte ofrecen?
                        </button>
                        <div class="accordion-content" role="region" id="faq3" aria-labelledby="tab-faq3">
                            <p>
                                Incluimos acompañamiento durante la implementación y pruebas de funcionamiento.
                                Ofrecemos planes de <strong>mantenimiento y soporte</strong> (remoto y/o en sitio) con
                                tiempos de respuesta
                                acordados (SLA). Para equipos físicos aplican las garantías del fabricante.
                            </p>
                        </div>
                    </div>

                    <!-- 4 -->
                    <div class="accordion-item">
                        <button class="accordion-header" role="tab" aria-expanded="false" aria-controls="faq4"
                            id="tab-faq4">
                            ¿Realizan capacitaciones con certificado?
                        </button>
                        <div class="accordion-content" role="region" id="faq4" aria-labelledby="tab-faq4">
                            <p>
                                Sí. Dictamos programas prácticos (virtuales o presenciales) en creación y gestión
                                empresarial,
                                herramientas digitales y seguridad electrónica. Al finalizar emitimos
                                <strong>certificación IPOCOL Digital Technology</strong>.
                            </p>
                        </div>
                    </div>

                    <!-- 5 -->
                    <div class="accordion-item">
                        <button class="accordion-header" role="tab" aria-expanded="false" aria-controls="faq5"
                            id="tab-faq5">
                            ¿Cómo solicito soporte técnico?
                        </button>
                        <div class="accordion-content" role="region" id="faq5" aria-labelledby="tab-faq5">
                            <p>
                                Selecciona <em>Soporte técnico</em> en el formulario, cuéntanos el incidente y adjunta datos
                                del equipo/servicio.
                                También puedes escribir a
                                <a href="https://wa.me/573229675194" target="_blank" rel="noopener">WhatsApp</a>
                                para atención prioritaria.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTACTO -->
            <div class="contact-form">
                <h3>Contáctenos</h3>

                <form action="https://formspree.io/f/mwplyena" method="POST">
                    <input type="hidden" name="_subject" value="Centro de Ayuda - IPOCOL Digital Technology">
                    <input type="text" name="_gotcha" style="display:none">

                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="text" id="name" name="name" required autocomplete="name">
                    </div>

                    <div class="form-group">
                        <label for="reason">Motivo:</label>
                        <select id="reason" name="reason" required>
                            <option value="">Seleccione un motivo</option>
                            <option value="Cotización de servicio">Cotización de servicio</option>
                            <option value="Asesoría/Diagnóstico">Asesoría/Diagnóstico</option>
                            <option value="Soporte técnico">Soporte técnico</option>
                            <option value="Capacitación con certificado">Capacitación con certificado</option>
                            <option value="Consulta general">Consulta general</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Número de WhatsApp o contacto</label>
                        <input type="tel" id="contact_number" name="number" inputmode="tel"
                            placeholder="+57 xxx xxx xxxx" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="message">Mensaje:</label>
                        <textarea id="message" name="message" rows="4" required placeholder="Cuéntanos qué necesitas…"></textarea>
                    </div>

                    <button type="submit" class="submit-button">Enviar</button>
                </form>

                <p style="margin-top:.75rem;font-size:.9rem;">
                    También puedes escribirnos directamente a
                    <a href="mailto:contacto@ipocoldigitaltechnology.com">contacto@ipocoldigitaltechnology.com</a>
                    o a nuestro <a href="https://wa.me/573229675194 " target="_blank" rel="noopener">WhatsApp</a>.
                </p>
            </div>
        </div>
    </section>

    <script>
        // Acordeón accesible (toggle + cerrar otros)
        document.querySelectorAll('.accordion-header').forEach(btn => {
            btn.addEventListener('click', () => {
                const expanded = btn.getAttribute('aria-expanded') === 'true';
                document.querySelectorAll('.accordion-header').forEach(b => b.setAttribute('aria-expanded',
                    'false'));
                btn.setAttribute('aria-expanded', String(!expanded));
            });
        });
    </script>

    <script>
        // Acordeón accesible (toggle + cerrar otros)
        document.querySelectorAll('.accordion-header').forEach(btn => {
            btn.addEventListener('click', () => {
                const expanded = btn.getAttribute('aria-expanded') === 'true';
                // cerrar todos
                document.querySelectorAll('.accordion-header').forEach(b => b.setAttribute('aria-expanded',
                    'false'));
                // abrir/cerrar el actual
                btn.setAttribute('aria-expanded', String(!expanded));
            });
        });
    </script>

    <button class="back-to-top" id="backToTopBtn" aria-label="Volver arriba">&#8679;</button>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <div class="logo-container">
                    <img src="{{ asset('public/images/Logo.png') }}" alt="Logo Logo Ipocoldigitaltechonology" class="logo-image">
                </div>
            </div>
            <div class="footer-links">
                <h4>Enlaces Rápidos</h4>
                <ul>
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="{{ route('certificates.form') }}">Certificados</a></li>
                    <li><a href="#ayuda">Ayuda</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contacto</h4>
                <p>Email: ipocoldigitaltechnologysas@gmail.com</p>
                <p>Teléfono: +57 322 9675194</p>
                <p>Dirección: Cra 12A No. 9-26, Piso 2 Colombia, Funza Cundinamarca</p>
            </div>
            <div class="footer-map">
                <h4>Nuestra Ubicación</h4>
                <div class="map-container">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d63621.11988165525!2d-74.214894!3d4.714369!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e3f82abd2f94933%3A0x6ed1ef89fa1b248e!2sCra%2012A%20%239-26%2C%20Funza%2C%20Cundinamarca!5e0!3m2!1ses-419!2sco!4v1755645684261!5m2!1ses-419!2sco"
                        width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 Colsertrans. Todos los derechos reservados.</p>
        </div>
    </footer>


    <script>
        // Tocar una tarjeta alterna el giro en móviles
        document.addEventListener('click', function(e) {
            const card = e.target.closest('.service-card');
            if (!card) return;
            if (e.target.closest('.slider-control')) return; // ignora botones del slider
            card.classList.toggle('flip');
        }, {
            passive: true
        });
    </script>

@endsection
