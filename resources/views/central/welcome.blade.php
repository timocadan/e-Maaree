<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="e-maaree – Simplify school administration, exams, and payments. Multi-tenant school management by ABQO Technology.">
    <meta name="author" content="ABQO Technology">
    <title>e-maaree – School Management System | ABQO Technology</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand-red: #D32F2F;
            --brand-red-dark: #b71c1c;
            --brand-black: #1A1A1A;
            --brand-white: #FFFFFF;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--brand-white);
            color: #333;
            line-height: 1.6;
        }
        /* Navbar */
        .landing-nav {
            background: var(--brand-black);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 2px 10px rgba(0,0,0,.15);
        }
        .landing-nav .container { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; }
        .landing-nav .brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--brand-white);
            text-decoration: none;
            letter-spacing: -0.02em;
        }
        .landing-nav .brand:hover { color: var(--brand-white); text-decoration: none; opacity: .95; }
        .landing-nav .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .landing-nav .nav-links a {
            color: rgba(255,255,255,.9);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .landing-nav .nav-links a:hover { color: var(--brand-red); }
        .landing-nav .btn-login {
            background: var(--brand-red);
            color: var(--brand-white) !important;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            transition: background .2s;
        }
        .landing-nav .btn-login:hover { background: var(--brand-red-dark); color: var(--brand-white) !important; }
        .landing-nav .navbar-toggler {
            border-color: rgba(255,255,255,.3);
            color: #fff;
        }
        @media (max-width: 991px) {
            .landing-nav .nav-links { flex-direction: column; padding: 1rem 0; gap: 0.75rem; }
            .landing-nav .nav-links.ml-auto { margin-left: 0 !important; }
        }
        /* Hero */
        .hero {
            background: linear-gradient(145deg, var(--brand-black) 0%, #2d2d2d 100%);
            color: var(--brand-white);
            padding: 5rem 1.5rem 4.5rem;
            text-align: center;
        }
        .hero h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }
        .hero .subheadline {
            font-size: clamp(1.05rem, 2vw, 1.25rem);
            color: rgba(255,255,255,.85);
            max-width: 560px;
            margin: 0 auto 2rem;
        }
        .hero .btn-cta {
            background: var(--brand-red);
            color: var(--brand-white);
            border: none;
            padding: 0.9rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: background .2s, transform .05s;
        }
        .hero .btn-cta:hover { background: var(--brand-red-dark); color: var(--brand-white); transform: translateY(-1px); }
        /* Sections */
        .section { padding: 4rem 1.5rem; }
        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--brand-black);
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .section-subtitle {
            color: #666;
            text-align: center;
            max-width: 540px;
            margin: 0 auto 3rem;
        }
        /* Features */
        .features { background: #f8f9fa; }
        .feature-card {
            background: var(--brand-white);
            border-radius: 12px;
            padding: 1.75rem;
            height: 100%;
            border: 1px solid #eee;
            transition: box-shadow .2s, border-color .2s;
        }
        .feature-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); border-color: transparent; }
        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: rgba(211, 47, 47, .1);
            color: var(--brand-red);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }
        .feature-card h3 { font-size: 1.1rem; font-weight: 600; color: var(--brand-black); margin-bottom: 0.5rem; }
        .feature-card p { color: #555; font-size: 0.95rem; margin: 0; }
        /* Pricing */
        .pricing-card {
            border-radius: 12px;
            border: 2px solid #eee;
            padding: 2rem;
            height: 100%;
            background: var(--brand-white);
            transition: border-color .2s, box-shadow .2s;
        }
        .pricing-card.featured {
            border-color: var(--brand-red);
            box-shadow: 0 8px 32px rgba(211, 47, 47, .15);
        }
        .pricing-card .plan-name { font-weight: 700; font-size: 1.25rem; color: var(--brand-black); margin-bottom: 0.25rem; }
        .pricing-card .plan-price { font-size: 2rem; font-weight: 700; color: var(--brand-red); margin-bottom: 1rem; }
        .pricing-card .plan-price span { font-size: 0.9rem; font-weight: 500; color: #666; }
        .pricing-card ul {
            list-style: none;
            padding: 0;
            margin: 0 0 1.5rem;
        }
        .pricing-card ul li {
            padding: 0.4rem 0;
            color: #555;
            font-size: 0.95rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .pricing-card ul li:last-child { border-bottom: none; }
        .pricing-card ul li i { color: var(--brand-red); margin-right: 0.5rem; width: 1rem; }
        .pricing-card .btn-pricing {
            display: block;
            text-align: center;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s, color .2s;
        }
        .pricing-card .btn-pricing.outline {
            background: transparent;
            color: var(--brand-red);
            border: 2px solid var(--brand-red);
        }
        .pricing-card .btn-pricing.outline:hover { background: var(--brand-red); color: var(--brand-white); }
        .pricing-card.featured .btn-pricing { background: var(--brand-red); color: var(--brand-white); border: none; }
        .pricing-card.featured .btn-pricing:hover { background: var(--brand-red-dark); color: var(--brand-white); }
        /* Footer */
        .landing-footer {
            background: var(--brand-black);
            color: rgba(255,255,255,.85);
            padding: 2.5rem 1.5rem;
            margin-top: auto;
            font-size: 0.9rem;
        }
        .landing-footer a { color: var(--brand-red); text-decoration: none; }
        .landing-footer a:hover { color: #ff6b6b; text-decoration: underline; }
        .landing-footer .brand-footer { font-weight: 700; color: #fff; margin-bottom: 0.5rem; }
        .landing-footer .social-links { margin-top: 1rem; }
        .landing-footer .social-links a {
            color: rgba(255,255,255,.8);
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        .landing-footer .social-links a:hover { color: var(--brand-red); }
    </style>
</head>
<body>
    <nav class="landing-nav navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="brand navbar-brand mb-0" href="{{ url('/') }}">e-maaree</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navCollapse" aria-controls="navCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navCollapse">
                <ul class="nav-links ml-auto mb-0">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="{{ url('/login') }}" class="btn-login">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h1>The Future of School Management is Here.</h1>
            <p class="subheadline">Simplify your administration, exams, and payments with e-maaree. One platform for your entire school.</p>
            <a href="{{ url('/register') }}" class="btn-cta">Get Started</a>
        </div>
    </section>

    <section class="section features" id="features">
        <div class="container">
            <h2 class="section-title">Why e-maaree?</h2>
            <p class="section-subtitle">Built for schools that want less paperwork and more clarity. Secure, simple, and ready to scale.</p>
            <div class="row">
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-shield-halved"></i></div>
                        <h3>Multi-tenant Architecture</h3>
                        <p>Your data is safe. Each school runs in its own isolated environment with full data separation.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-file-lines"></i></div>
                        <h3>Exam & Report Card Generation</h3>
                        <p>Create exams, record marks, and generate report cards and tabulation sheets in one place.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-wallet"></i></div>
                        <h3>Student Fee & Payment Tracking</h3>
                        <p>Track fees, issue receipts, and manage payments per student with clear records.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-palette"></i></div>
                        <h3>Simple & Clean User Interface</h3>
                        <p>An intuitive dashboard so staff and admins can focus on teaching, not software.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="pricing">
        <div class="container">
            <h2 class="section-title">Simple, Transparent Pricing</h2>
            <p class="section-subtitle">Choose the plan that fits your school. No hidden fees.</p>
            <div class="row justify-content-center">
                <div class="col-lg-5 col-xl-4 mb-4">
                    <div class="pricing-card">
                        <div class="plan-name">Basic</div>
                        <div class="plan-price">$20 <span>/ month</span></div>
                        <ul>
                            <li><i class="fas fa-check"></i> Up to 100 students</li>
                            <li><i class="fas fa-check"></i> Core features</li>
                            <li><i class="fas fa-check"></i> Exam & marks</li>
                            <li><i class="fas fa-check"></i> Fee tracking</li>
                            <li><i class="fas fa-check"></i> Email support</li>
                        </ul>
                        <a href="{{ url('/register') }}" class="btn-pricing outline">Get Started</a>
                    </div>
                </div>
                <div class="col-lg-5 col-xl-4 mb-4">
                    <div class="pricing-card featured">
                        <div class="plan-name">Pro</div>
                        <div class="plan-price">$50 <span>/ month</span></div>
                        <ul>
                            <li><i class="fas fa-check"></i> Unlimited students</li>
                            <li><i class="fas fa-check"></i> Full feature set</li>
                            <li><i class="fas fa-check"></i> Priority support</li>
                            <li><i class="fas fa-check"></i> Advanced stats & reports</li>
                            <li><i class="fas fa-check"></i> Dedicated onboarding</li>
                        </ul>
                        <a href="{{ url('/register') }}" class="btn-pricing">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="landing-footer">
        <div class="container text-center">
            <div class="brand-footer">e-maaree by ABQO Technology</div>
            <p class="mb-0">&copy; {{ date('Y') }} e-maaree. Developed by <a href="https://web.facebook.com/timocadaan" target="_blank" rel="noopener">Cumar Timocade</a>.</p>
            <div class="social-links">
                <a href="https://web.facebook.com/timocadaan" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </footer>

    <script src="{{ asset('global_assets/js/main/jquery.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/main/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(function(a) {
            if (a.getAttribute('href') !== '#') a.addEventListener('click', function(e) {
                var id = this.getAttribute('href').slice(1);
                if (id && document.getElementById(id)) {
                    e.preventDefault();
                    document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
