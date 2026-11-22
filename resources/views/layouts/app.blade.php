<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGAE - @yield('titulo')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            /* Fondo degradado oscuro/morado visto en los diseños */
            background: linear-gradient(135deg, #2b2b36 0%, #4a4a5a 50%, #1e1e24 100%);
            background-attachment: fixed;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            overflow-x: clip;
        }

        .top-navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background-color: #1a1a1a;
            color: white;
            padding: 10px 20px;
            font-weight: bold;
            font-size: 1.15rem;
            text-transform: uppercase;
            border-bottom: 3px solid #333;
            gap: 12px;
        }

        .sgae-brand-short {
            display: none;
        }

        .sgae-header-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .sgae-period-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(25, 135, 84, .2);
            border: 1px solid rgba(25, 135, 84, .55);
            color: #d1f7e5;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: none;
            white-space: nowrap;
        }

        .sgae-user-header {
            font-weight: 600;
            text-transform: none;
            white-space: nowrap;
        }

        .sgae-menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            padding: 0;
            border: 1px solid #495057;
            border-radius: 10px;
            background: #2b3035;
            color: #ffffff;
            font-size: 1.35rem;
            line-height: 1;
            flex-shrink: 0;
            transition: background-color .2s, border-color .2s;
        }

        .sgae-menu-toggle:hover,
        .sgae-menu-toggle:focus-visible {
            background: #343a40;
            border-color: #adb5bd;
            color: #ffffff;
        }

        .sgae-sidebar-close {
            display: none;
            position: absolute;
            top: 14px;
            right: 14px;
            width: 36px;
            height: 36px;
            border: 1px solid #495057;
            border-radius: 10px;
            background: rgba(255, 255, 255, .08);
            color: #ffffff;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 2;
        }

        .sgae-sidebar-close:hover {
            background: rgba(255, 255, 255, .18);
        }

        .sgae-sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .25s ease, visibility .25s ease;
        }

        /* Contraste seguro en paneles con menú lateral */
        body.has-sidebar aside {
            color: #ffffff;
        }

        body.has-sidebar main {
            color: #212529;
        }

        /* Mantiene apariencia de enlace tras remover href para ocultar la vista previa del navegador */
        a[data-sgae-href] {
            cursor: pointer;
        }

        a[data-sgae-href]:not([class]) {
            color: var(--bs-link-color);
            text-decoration: underline;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered,
        .select2-results__option {
            color: #1a1a1a !important;
        }

        /* MODO ADAPTATIVO: CELULARES, TABLETS Y VENTANA REDUCIDA (< 992px) */
        @media (max-width: 991.98px) {
            body.has-sidebar .sgae-menu-toggle {
                display: inline-flex;
            }

            body.has-sidebar .sgae-sidebar-close {
                display: inline-flex;
            }

            body.has-sidebar aside {
                position: fixed !important;
                top: 0;
                left: 0;
                bottom: 0;
                width: 280px !important;
                max-width: 85vw !important;
                height: 100dvh;
                overflow-y: auto;
                z-index: 1050;
                transform: translateX(-105%);
                transition: transform .28s ease-in-out;
                box-shadow: 6px 0 24px rgba(0, 0, 0, 0.35);
                padding-top: 1.75rem !important;
            }

            body.has-sidebar.sgae-menu-open {
                overflow: hidden;
            }

            body.has-sidebar.sgae-menu-open aside {
                transform: translateX(0);
            }

            body.has-sidebar.sgae-menu-open .sgae-sidebar-backdrop {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }

            body.has-sidebar main {
                flex: 0 0 100% !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        @media (max-width: 767.98px) {
            .top-navbar {
                padding: 10px 14px;
                font-size: 1rem;
                flex-wrap: wrap;
            }

            .sgae-brand-full {
                display: none;
            }

            .sgae-brand-short {
                display: inline;
            }

            .sgae-period-label {
                display: none;
            }

            .sgae-period-badge {
                font-size: 0.72rem;
                padding: 3px 9px;
            }

            .sgae-user-header {
                max-width: 45vw;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .sgae-user-header .fs-6 {
                font-size: 0.875rem !important;
            }
        }

        @media (max-width: 575.98px) {
            body.has-sidebar main {
                padding: 1.25rem 1rem !important;
            }

            body.has-sidebar .table-responsive {
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body>

    <header class="top-navbar d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 min-w-0">
            <button type="button"
                    id="sgaeMenuToggle"
                    class="sgae-menu-toggle"
                    aria-label="Abrir menú de navegación"
                    aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>
            <span class="sgae-brand-full text-truncate">SISTEMA DE GESTIÓN ADMINISTRATIVA ESCOLAR (SGAE)</span>
            <span class="sgae-brand-short text-truncate">SGAE PREPA 5</span>
        </div>

        <div class="sgae-header-right">
            @auth
                @if (!empty($periodoEscolarGlobal))
                    <span class="sgae-period-badge" title="Periodo escolar activo">
                        <i class="bi bi-calendar-check"></i>
                        <span class="sgae-period-label">Periodo:</span>
                        <span>{{ $periodoEscolarGlobal }}</span>
                    </span>
                @endif
            @endauth

            <div class="sgae-user-header">@yield('usuario_header')</div>
        </div>
    </header>

    <div id="sgaeSidebarBackdrop" class="sgae-sidebar-backdrop" aria-hidden="true"></div>

    <div class="container-fluid p-0">
        @yield('contenido')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. MENÚ LATERAL ADAPTATIVO (HAMBURGUESA)
            const sidebar = document.querySelector('aside');
            const toggleBtn = document.getElementById('sgaeMenuToggle');
            const backdrop = document.getElementById('sgaeSidebarBackdrop');

            if (sidebar && toggleBtn && backdrop) {
                document.body.classList.add('has-sidebar');

                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'sgae-sidebar-close';
                closeBtn.setAttribute('aria-label', 'Cerrar menú de navegación');
                closeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
                sidebar.prepend(closeBtn);

                const abrirMenu = function () {
                    document.body.classList.add('sgae-menu-open');
                    toggleBtn.setAttribute('aria-expanded', 'true');
                };

                const cerrarMenu = function () {
                    document.body.classList.remove('sgae-menu-open');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                };

                toggleBtn.addEventListener('click', function () {
                    if (document.body.classList.contains('sgae-menu-open')) {
                        cerrarMenu();
                    } else {
                        abrirMenu();
                    }
                });

                closeBtn.addEventListener('click', cerrarMenu);
                backdrop.addEventListener('click', cerrarMenu);

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        cerrarMenu();
                    }
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth >= 992) {
                        cerrarMenu();
                    }
                });
            }

            // 2. OCULTAR VISTA PREVIA DE URL DEL NAVEGADOR EN ENLACES
            const prepararEnlaces = function () {
                document.querySelectorAll('a[href]').forEach(function (enlace) {
                    const href = enlace.getAttribute('href');

                    if (
                        !href ||
                        href.startsWith('#') ||
                        href.startsWith('javascript:') ||
                        enlace.hasAttribute('data-bs-toggle')
                    ) {
                        return;
                    }

                    enlace.dataset.sgaeHref = href;
                    enlace.removeAttribute('href');

                    if (!enlace.hasAttribute('role')) {
                        enlace.setAttribute('role', 'link');
                    }

                    if (!enlace.hasAttribute('tabindex')) {
                        enlace.setAttribute('tabindex', '0');
                    }
                });
            };

            const navegarEnlace = function (enlace, nuevaPestana) {
                const url = enlace.dataset.sgaeHref;
                if (!url) {
                    return;
                }

                if (nuevaPestana || enlace.getAttribute('target') === '_blank') {
                    window.open(url, '_blank', 'noopener,noreferrer');
                } else {
                    window.location.href = url;
                }
            };

            prepararEnlaces();

            document.addEventListener('click', function (event) {
                const enlace = event.target.closest('a[data-sgae-href]');
                if (!enlace || event.defaultPrevented) {
                    return;
                }

                event.preventDefault();
                navegarEnlace(enlace, event.ctrlKey || event.metaKey);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') {
                    return;
                }

                const enlace = event.target.closest('a[data-sgae-href]');
                if (!enlace) {
                    return;
                }

                event.preventDefault();
                navegarEnlace(enlace, false);
            });
        });
    </script>
</body>
</html>