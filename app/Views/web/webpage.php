<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yupana Estudio Contable</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #A20A09;
            --primary-dark: #7A0706;
            --primary-light: #E51417;
            --accent: #E51417;
            --white: #FFFFFF;
            --gray-100: #F7F7F7;
            --gray-200: #E5E5E5;
            --gray-400: #A5A5A5;
            --gray-600: #666666;
            --gray-800: #333333;
            --gray-900: #1A1A1A;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--gray-800);
            background: var(--white);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* --- NAV --- */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            z-index: 1000;
            transition: box-shadow 0.3s;
        }

        nav.scrolled {
            box-shadow: var(--shadow);
        }

        nav .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 72px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
        }

        .logo img {
            width: 148px;
            height: auto;
            display: block;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 32px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--gray-600);
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a.active {
            color: var(--primary);
        }

        .nav-links a.active::after {
            width: 100%;
        }

        .nav-cta {
            background: var(--primary);
            color: var(--white) !important;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 600 !important;
            transition: background 0.3s !important;
        }

        .nav-cta::after {
            display: none !important;
        }

        .nav-cta:hover {
            background: var(--primary-dark) !important;
            color: var(--white) !important;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            background: none;
            border: none;
            padding: 4px;
        }

        .hamburger span {
            width: 26px;
            height: 2.5px;
            background: var(--gray-800);
            border-radius: 2px;
            transition: 0.3s;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        /* --- HERO --- */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 120px 0 80px;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .hero-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(10, 5, 5, 0.85) 0%, rgba(162, 10, 9, 0.55) 50%, rgba(30, 10, 10, 0.7) 100%);
        }

        .hero .container {
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .hero-content {
            max-width: 680px;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            padding: 5px 14px 5px 5px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 28px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .hero-tag span {
            background: rgba(255, 255, 255, 0.15);
            padding: 2px 10px;
            border-radius: 50px;
            font-size: 0.72rem;
            margin-right: 4px;
        }

        .hero-content h1 {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.05;
            margin-bottom: 4px;
            color: var(--white);
            letter-spacing: -0.03em;
        }

        .hero-content .slogan {
            display: block;
            font-size: 3.6rem;
            font-weight: 300;
            color: var(--white);
            margin-bottom: 28px;
            letter-spacing: 0.04em;
            font-style: italic;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.15);
        }

        .hero-content p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.92);
            max-width: 540px;
            line-height: 1.8;
            margin-bottom: 36px;
            text-shadow: 0 1px 12px rgba(0, 0, 0, 0.12);
        }

        .hero-divider {
            width: 60px;
            height: 3px;
            background: var(--primary-light);
            border-radius: 2px;
            margin-bottom: 24px;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--white);
            color: var(--primary);
        }

        .btn-primary:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--white);
            border: 1.5px solid rgba(255, 255, 255, 0.3);
        }

        .btn-outline:hover {
            border-color: var(--white);
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }



        /* --- SECTIONS --- */
        section {
            padding: 90px 0;
        }

        .section-tag {
            display: inline-block;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 16px;
        }

        .section-sub {
            max-width: 600px;
            color: var(--gray-600);
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 48px;
        }

        .text-center {
            text-align: center;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        /* --- ABOUT --- */
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-image {
            background: var(--gray-100);
            border-radius: var(--radius);
            height: 420px;
            position: relative;
            overflow: hidden;
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .about-image .badge {
            position: absolute;
            bottom: 24px;
            left: 24px;
            background: var(--primary);
            color: var(--white);
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .about-text h3 {
            font-size: 1.5rem;
            color: var(--gray-900);
            margin-bottom: 16px;
        }

        .about-text p {
            color: var(--gray-600);
            margin-bottom: 20px;
            line-height: 1.8;
        }

        .about-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 24px;
        }

        .about-feature {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .about-feature i {
            color: var(--primary);
            font-size: 1rem;
        }

        /* --- SERVICES --- */
        #servicios {
            background: var(--gray-100);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        .service-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 36px 28px;
            box-shadow: var(--shadow);
            transition: all 0.3s;
            border: 1px solid transparent;
        }

        .service-card:hover {
            transform: translateY(-6px);
            border-color: var(--primary);
            box-shadow: 0 12px 40px rgba(162, 10, 9, 0.12);
        }

        .service-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: rgba(162, 10, 9, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .service-icon i {
            font-size: 1.4rem;
            color: var(--primary);
        }

        .service-card h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 10px;
        }

        .service-card p {
            color: var(--gray-600);
            font-size: 0.92rem;
            line-height: 1.7;
        }

        /* --- CONTACT --- */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
        }

        .contact-info h3 {
            font-size: 1.3rem;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .contact-info>p {
            color: var(--gray-600);
            margin-bottom: 32px;
        }

        .contact-item {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }

        .contact-item i {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(162, 10, 9, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .contact-item h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 2px;
        }

        .contact-item p {
            color: var(--gray-600);
            font-size: 0.92rem;
        }

        .contact-form {
            background: var(--gray-100);
            border-radius: var(--radius);
            padding: 36px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--gray-800);
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            outline: none;
            background: var(--white);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--primary);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #10B981;
            color: var(--white);
            padding: 16px 28px;
            border-radius: 8px;
            font-weight: 500;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s;
            pointer-events: none;
            z-index: 2000;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* --- FOOTER --- */
        footer {
            background: var(--gray-900);
            color: rgba(255, 255, 255, 0.7);
            padding: 60px 0 30px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 40px;
        }

        .footer-brand h3 {
            color: var(--white);
            font-size: 1.3rem;
            margin-bottom: 12px;
        }

        .footer-brand p {
            font-size: 0.9rem;
            line-height: 1.7;
            max-width: 300px;
        }

        .footer-social {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .footer-social a {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-social a:hover {
            background: var(--primary);
            color: var(--white);
        }

        .footer-col h4 {
            color: var(--white);
            font-size: 1rem;
            margin-bottom: 16px;
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col ul li {
            margin-bottom: 10px;
        }

        .footer-col ul li a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .footer-col ul li a:hover {
            color: var(--primary-light);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 968px) {
            .hero {
                padding: 110px 0 50px;
                min-height: auto;
            }

            .hero .container {
                text-align: center;
            }

            .hero-content {
                max-width: 100%;
            }

            .hero-divider {
                margin-left: auto;
                margin-right: auto;
            }

            .hero-content p {
                margin: 0 auto 10px;
            }

            .hero-buttons {
                justify-content: center;
            }


            .about-grid {
                grid-template-columns: 1fr;
            }

            .services-grid {
                grid-template-columns: 1fr 1fr;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .nav-links {
                position: fixed;
                top: 72px;
                left: 0;
                width: 100%;
                background: var(--white);
                flex-direction: column;
                padding: 24px;
                gap: 16px;
                box-shadow: var(--shadow);
                transform: translateY(-120%);
                transition: transform 0.3s;
            }

            .nav-links.open {
                transform: translateY(0);
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .hero-content .slogan {
                font-size: 2rem;
            }

            .about-image {
                height: 260px;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .about-features {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }

        /* --- WHATSAPP FLOAT --- */
        .whatsapp-float {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 999;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #25D366;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.6rem;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
            transition: all 0.3s;
        }

        .whatsapp-float:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 28px rgba(37, 211, 102, 0.5);
            color: var(--white);
        }

        .whatsapp-float .tooltip {
            position: absolute;
            right: 64px;
            background: var(--gray-900);
            color: var(--white);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .whatsapp-float:hover .tooltip {
            opacity: 1;
        }
    </style>
</head>

<body>

    <!-- NAV -->
    <nav id="navbar">
        <div class="container">
            <a href="#" class="logo">
                <img src="<?= base_url('img/yu.png') ?>" alt="Yupana Estudio Contable">
            </a>
            <button class="hamburger" id="hamburger" aria-label="Menú">
                <span></span><span></span><span></span>
            </button>
            <ul class="nav-links" id="navLinks">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#nosotros">Nosotros</a></li>
                <li><a href="#servicios">Servicios</a></li>
                <li><a href="#contacto" class="nav-cta">Agendar Cita</a></li>
            </ul>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero" id="inicio">
        <div class="hero-bg">
            <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1920&q=80&auto=format&fit=crop" alt="Asesoría contable profesional" loading="lazy">
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-tag">
                    <i class="fas fa-check-circle"></i> Estudio Contable en SUNAT
                </div>
                <h1>Asesoría Contable<br>para tu Empresa</h1>
                <span class="slogan">Crecemos Juntos</span>
                <div class="hero-divider"></div>
                <p>Acompañamos tu negocio con soluciones contables, tributarias y laborales. Transformamos la complejidad en resultados para que tú puedas crecer con confianza.</p>
                <div class="hero-buttons">
                    <a href="#contacto" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Conversemos
                    </a>
                    <a href="#servicios" class="btn btn-outline">
                        Ver Servicios
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT -->
    <section id="nosotros">
        <div class="container">
            <div class="about-grid">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?w=700&q=80&auto=format&fit=crop" alt="Equipo de contadores profesionales" loading="lazy">
                    <div class="badge"><i class="fas fa-check-circle"></i> Estudio Contable Registrado</div>
                </div>
                <div class="about-text">
                    <span class="section-tag">Nosotros</span>
                    <h3>Más que contadores, tus aliados estratégicos</h3>
                    <p>En <strong>Yupana Estudio Contable</strong> entendemos que cada negocio es único. Por eso ofrecemos un servicio personalizado, combinando la experiencia tradicional con herramientas digitales modernas para optimizar tu gestión financiera.</p>
                    <p>Nuestro equipo está conformado por contadores públicos colegiados con amplia experiencia en los sectores comercial, servicios y construcción. Estamos comprometidos con la excelencia y la transparencia.</p>
                    <div class="about-features">
                        <div class="about-feature"><i class="fas fa-check-circle"></i> Contadores Públicos Colegiados</div>
                        <div class="about-feature"><i class="fas fa-check-circle"></i> Atención Personalizada</div>
                        <div class="about-feature"><i class="fas fa-check-circle"></i> Tecnología en la Nube</div>
                        <div class="about-feature"><i class="fas fa-check-circle"></i> Resultados Garantizados</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICES -->
    <section id="servicios">
        <div class="container">
            <div class="text-center">
                <span class="section-tag">Servicios</span>
                <h2 class="section-title">Soluciones Contables Integrales</h2>
                <p class="section-sub mx-auto">Cubrimos todas las áreas de tu negocio para que puedas enfocarte en crecer mientras nosotros nos encargamos de los números.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-file-invoice"></i></div>
                    <h3>Contabilidad General</h3>
                    <p>Registro contable completo, estados financieros, balances y reportes gerenciales mensuales actualizados.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-calculator"></i></div>
                    <h3>Declaraciones Tributarias</h3>
                    <p>Declaración de IGV, Renta, PDT, PLAME y demás obligaciones tributarias ante SUNAT en los plazos establecidos.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-users"></i></div>
                    <h3>Planillas y RR.HH.</h3>
                    <p>Gestión de planillas electrónicas, CTS, gratificaciones, vacaciones y liquidaciones de beneficios sociales.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Asesoría Tributaria</h3>
                    <p>Planeamiento tributario, fiscaliaciones, devoluciones de impuestos y consultoría especializada.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-handshake"></i></div>
                    <h3>Constitución de Empresas</h3>
                    <p>Asesoría en la constitución de sociedades, régimen MYPE, RUS y elección del tipo societario adecuado.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon"><i class="fas fa-search-dollar"></i></div>
                    <h3>Auditoría Financiera</h3>
                    <p>Revisiones de estados financieros, control interno y due diligence para inversiones y fusiones.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT -->
    <section id="contacto">
        <div class="container">
            <div class="text-center">
                <span class="section-tag">Contacto</span>
                <h2 class="section-title">Conversemos sobre tu negocio</h2>
                <p class="section-sub mx-auto">Déjanos tus datos y uno de nuestros asesores se comunicará contigo en menos de 24 horas.</p>
            </div>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3>Información de contacto</h3>
                    <p>Estamos ubicados en las principales ciudades del Perú.</p>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Oficina Principal</h4>
                            <p> Jr cinrcunvalacion cumbaza 1030 Atumpampa - Morales - San Martín</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <div>
                            <h4>Teléfono</h4>
                            <p> (+51) 964 290 705 / 942 319 820</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>groupyupana@gmail.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h4>Horario de Atención</h4>
                            <p>Lun - Vie: 9:00 am - 6:00 pm | Sáb: 9:00 am - 1:00 pm</p>
                        </div>
                    </div>
                </div>
                <form class="contact-form" id="contactForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombres *</label>
                            <input type="text" id="nombre" placeholder="Tu nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="apellido">Apellidos *</label>
                            <input type="text" id="apellido" placeholder="Tus apellidos" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Correo Electrónico *</label>
                            <input type="email" id="email" placeholder="correo@ejemplo.com" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" placeholder="999 888 777">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="servicio">Servicio de Interés</label>
                        <select id="servicio">
                            <option value="">Selecciona un servicio</option>
                            <option>Contabilidad General</option>
                            <option>Declaraciones Tributarias</option>
                            <option>Planillas y RR.HH.</option>
                            <option>Asesoría Tributaria</option>
                            <option>Constitución de Empresas</option>
                            <option>Auditoría Financiera</option>
                            <option>Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mensaje">Mensaje *</label>
                        <textarea id="mensaje" placeholder="Cuéntanos sobre tu consulta..." required></textarea>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Enviar Mensaje</button>
                </form>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3 style="display:flex;align-items:center;gap:10px;"><img src="yu.png" alt="Yupana Estudio Contable" style="height:32px;width:auto;filter:brightness(0) invert(1);"> Yupana Estudio Contable</h3>
                    <p>Brindamos asesoría contable confiable y profesional para impulsar el crecimiento de tu empresa en Perú.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Servicios</h4>
                    <ul>
                        <li><a href="#">Contabilidad General</a></li>
                        <li><a href="#">Declaraciones Tributarias</a></li>
                        <li><a href="#">Planillas</a></li>
                        <li><a href="#">Asesoría Tributaria</a></li>
                        <li><a href="#">Constitución de Empresas</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Enlaces</h4>
                    <ul>
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#nosotros">Nosotros</a></li>
                        <li><a href="#servicios">Servicios</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contacto</h4>
                    <ul>
                        <li><a href="tel:964290705">(+51) 964 290 705</a></li>
                        <li><a href="mailto:groupyupana@gmail.com">groupyupana@gmail.com</a></li>
                        <li><a href="#">San Martín - San Martín - Morales</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; 2026 Yupana Estudio Contable. Todos los derechos reservados.</span>
                <span>Hecho con <i class="fas fa-heart" style="color: var(--primary-light);"></i> en Perú</span>
            </div>
        </div>
    </footer>

    <!-- TOAST -->
    <div class="toast" id="toast"><i class="fas fa-check-circle"></i> Mensaje enviado con éxito. Te contactaremos pronto.</div>

    <script>
        // --- Mobile Menu ---
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('open');
        });

        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navLinks.classList.remove('open');
            });
        });

        // --- Navbar scroll shadow ---
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        });

        // --- Active section highlight ---
        const sections = document.querySelectorAll('section[id]');
        const navAnchors = document.querySelectorAll('.nav-links a:not(.nav-cta)');

        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    navAnchors.forEach(a => {
                        a.classList.toggle('active', a.getAttribute('href') === '#' + entry.target.id);
                    });
                }
            });
        }, {
            threshold: 0.35
        });

        sections.forEach(s => sectionObserver.observe(s));

        // --- Counter Animation ---
        function animateCounters() {
            const counters = document.querySelectorAll('.number[data-target]');
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const duration = 2000;
                const step = Math.max(1, Math.floor(target / 60));
                let current = 0;

                const update = () => {
                    current += step;
                    if (current >= target) {
                        counter.textContent = target + (target >= 98 ? '%' : '+');
                        return;
                    }
                    counter.textContent = current + '+';
                    requestAnimationFrame(update);
                };

                update();
            });
        }

        // --- Intersection Observer for counters ---
        const heroStats = document.querySelector('.hero-stats');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });

        if (heroStats) observer.observe(heroStats);

        // --- Form Submit ---
        const form = document.getElementById('contactForm');
        const toast = document.getElementById('toast');

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const nombre = document.getElementById('nombre').value.trim();
            const apellido = document.getElementById('apellido').value.trim();
            const email = document.getElementById('email').value.trim();
            const mensaje = document.getElementById('mensaje').value.trim();

            if (!nombre || !apellido || !email || !mensaje) {
                alert('Por favor completa todos los campos obligatorios.');
                return;
            }

            const data = {
                nombre,
                apellido,
                email,
                telefono: document.getElementById('telefono').value.trim(),
                servicio: document.getElementById('servicio').value,
                mensaje
            };

            console.log('Formulario enviado:', data);

            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 4000);
            form.reset();
        });

        // --- Smooth reveal on scroll ---
        const revealElements = document.querySelectorAll('.service-card, .about-grid, .contact-grid');

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1
        });

        revealElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            revealObserver.observe(el);
        });
    </script>

    <!-- WHATSAPP FLOAT -->
    <a href="https://wa.me/51942319820?text=Hola%20Group%20Yupana%2C%20necesito%20m%C3%A1s%20informaci%C3%B3n" class="whatsapp-float" target="_blank" rel="noopener" aria-label="WhatsApp">
        <i class="fab fa-whatsapp"></i>
        <span class="tooltip">Escríbenos</span>
    </a>
</body>

</html>