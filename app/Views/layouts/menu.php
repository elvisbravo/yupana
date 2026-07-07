<style>
    .yupana-collapsed .sidenav-menu {
        width: 70px !important;
        min-width: 70px !important;
        transition: width 0.2s ease;
    }
    .yupana-collapsed .sidenav-menu .menu-text,
    .yupana-collapsed .sidenav-menu .side-nav-title,
    .yupana-collapsed .sidenav-menu .badge,
    .yupana-collapsed .sidenav-menu .menu-arrow,
    .yupana-collapsed .sidenav-menu .sidenav-user h5,
    .yupana-collapsed .sidenav-menu .sidenav-user h6,
    .yupana-collapsed .sidenav-menu .sidenav-user span,
    .yupana-collapsed .sidenav-menu .sub-menu,
    .yupana-collapsed .sidenav-menu .menu-collapse-box span {
        display: none !important;
    }
    .yupana-collapsed .sidenav-menu .side-nav-link {
        justify-content: center !important;
        padding: 12px 0 !important;
        text-align: center !important;
    }
    .yupana-collapsed .sidenav-menu .side-nav-link .menu-icon {
        margin: 0 !important;
    }
    .yupana-collapsed .sidenav-menu .sidenav-user a {
        justify-content: center !important;
        padding: 10px 0 !important;
    }
    .yupana-collapsed .sidenav-menu .sidenav-user img {
        margin-right: 0 !important;
    }
    .yupana-collapsed .sidenav-menu .menu-collapse-box {
        padding: 12px 0 !important;
        display: flex !important;
        justify-content: center !important;
    }
    .yupana-collapsed .sidenav-menu .menu-collapse-box .button-collapse-toggle {
        gap: 0 !important;
        padding: 6px !important;
    }
    .yupana-collapsed .content-page {
        margin-left: 70px !important;
    }

    .yupana-collapsed .sidenav-menu.yupana-hover {
        width: 260px !important;
        min-width: 260px !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 1040 !important;
        height: 100% !important;
        box-shadow: 4px 0 20px rgba(0,0,0,0.1) !important;
    }
    .yupana-collapsed .sidenav-menu.yupana-hover .scrollbar {
        overflow: auto !important;
    }
    .yupana-collapsed .sidenav-menu.yupana-hover .menu-text,
    .yupana-collapsed .sidenav-menu.yupana-hover .side-nav-title,
    .yupana-collapsed .sidenav-menu.yupana-hover .badge,
    .yupana-collapsed .sidenav-menu.yupana-hover .menu-arrow,
    .yupana-collapsed .sidenav-menu.yupana-hover .sidenav-user h5,
    .yupana-collapsed .sidenav-menu.yupana-hover .sidenav-user h6,
    .yupana-collapsed .sidenav-menu.yupana-hover .sidenav-user span,
    .yupana-collapsed .sidenav-menu.yupana-hover .sub-menu,
    .yupana-collapsed .sidenav-menu.yupana-hover .menu-collapse-box span {
        display: block !important;
    }
    .yupana-collapsed .sidenav-menu.yupana-hover .sub-menu {
        display: block !important;
    }
    .yupana-collapsed .sidenav-menu.yupana-hover .side-nav-link {
        justify-content: flex-start !important;
        padding: 8px 16px !important;
        text-align: left !important;
    }
    .yupana-collapsed .sidenav-menu.yupana-hover .side-nav-link .menu-icon {
        margin-right: 8px !important;
    }
    .yupana-collapsed .sidenav-menu.yupana-hover .sidenav-user a {
        justify-content: flex-start !important;
        padding: 10px 16px !important;
    }
    .yupana-collapsed .sidenav-menu.yupana-hover .sidenav-user img {
        margin-right: 10px !important;
    }
    .yupana-collapsed .sidenav-menu.yupana-hover .menu-collapse-box {
        padding: 12px 16px !important;
        justify-content: flex-start !important;
    }
    .yupana-collapsed .sidenav-menu.yupana-hover .menu-collapse-box .button-collapse-toggle {
        gap: 8px !important;
        padding: 6px 12px !important;
    }

    @media (max-width: 1199.98px) {
        .yupana-collapsed .sidenav-menu {
            width: 260px !important;
            min-width: 260px !important;
        }
        .yupana-collapsed .sidenav-menu.yupana-hover {
            position: relative !important;
            box-shadow: none !important;
        }
        .yupana-collapsed .content-page {
            margin-left: 0 !important;
        }
    }
</style>

<div class="sidenav-menu">
    <div class="scrollbar" data-simplebar>

        <!-- User -->
        <div class="sidenav-user text-nowrap border border-dashed rounded-3">
            <a href="#!" class="sidenav-user-name d-flex align-items-center overflow-hidden">
                <img src="assets/images/users/user-2.jpg" width="36" class="rounded-circle me-2 d-flex flex-shrink-0" alt="user-image">
                <span class="min-w-0 overflow-hidden">
                    <h5 class="my-0 fw-semibold text-truncate"><?= session('nombres') . ' ' . session('apellidos') ?></h5>
                    <h6 class="my-0 text-muted text-truncate"><?= session('rol_nombre') ?></h6>
                </span>
            </a>
        </div>

        <!--- Sidenav Menu -->
        <ul class="side-nav">
            <?= render_menu(session('rol_id')) ?>
        </ul>
    </div>

    <div class="menu-collapse-box d-none d-xl-block">
        <button class="button-collapse-toggle">
            <i data-lucide="square-chevron-left" class="align-middle flex-shrink-0"></i> <span>Contraer Menú</span>
        </button>
    </div>
</div>

<script>
    (function () {
        var html = document.documentElement;
        var sideNavMenu = document.querySelector('.sidenav-menu');

        // Override data-sidenav-size to prevent theme CSS conflicts
        html.setAttribute('data-sidenav-size', 'default');

        // Restore collapsed state from localStorage
        if (localStorage.getItem('yupana-sidebar-collapsed') === 'true') {
            html.classList.add('yupana-collapsed');
        }

        // Hover expand/collapse for collapsed state (using JS not CSS :hover)
        if (sideNavMenu) {
            sideNavMenu.addEventListener('mouseenter', function () {
                if (html.classList.contains('yupana-collapsed')) {
                    this.classList.add('yupana-hover');
                }
            });
            sideNavMenu.addEventListener('mouseleave', function () {
                this.classList.remove('yupana-hover');
            });
        }

        // Collapse Menu Toggle
        var menuCollapseBtn = document.querySelector('.menu-collapse-box .button-collapse-toggle');
        if (menuCollapseBtn) {
            // Sync button icon/text on page load
            if (html.classList.contains('yupana-collapsed')) {
                var icon = menuCollapseBtn.querySelector('[data-lucide]');
                if (icon) icon.setAttribute('data-lucide', 'square-chevron-right');
                var span = menuCollapseBtn.querySelector('span');
                if (span) span.textContent = 'Expandir Menú';
            }

            menuCollapseBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                var wasCollapsed = html.classList.contains('yupana-collapsed');
                if (wasCollapsed) {
                    html.classList.remove('yupana-collapsed');
                    localStorage.setItem('yupana-sidebar-collapsed', 'false');
                } else {
                    html.classList.add('yupana-collapsed');
                    localStorage.setItem('yupana-sidebar-collapsed', 'true');
                }
                if (sideNavMenu) sideNavMenu.classList.remove('yupana-hover');
                var isCollapsed = !wasCollapsed;
                var icon = this.querySelector('[data-lucide]');
                if (icon) {
                    icon.setAttribute('data-lucide', isCollapsed ? 'square-chevron-right' : 'square-chevron-left');
                }
                var span = this.querySelector('span');
                if (span) span.textContent = isCollapsed ? 'Expandir Menú' : 'Contraer Menú';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        // Sidenav Icons
        lucide.createIcons();
    })();

    // Sidenav Link Activation
    const currentUrlT = window.location.href.split(/[?#]/)[0];
    const currentPageT = window.location.pathname.split("https://coderthemes.com/").pop();
    const sideNavT = document.querySelector('.side-nav');

    document.querySelectorAll('.side-nav-link[href]').forEach(link => {
        const linkHref = link.getAttribute('href');
        if (!linkHref) return;

        const match = linkHref === currentPageT || link.href === currentUrlT;

        if (match) {
            // Mark link and its li active
            link.classList.add('active');
            const li = link.closest('li.side-nav-item');
            if (li) li.classList.add('active');

            // Expand all parent .collapse and set toggles
            let parentCollapse = link.closest('.collapse');
            while (parentCollapse) {
                parentCollapse.classList.add('show');

                const parentToggle = document.querySelector(`a[href="#${parentCollapse.id}"]`);
                if (parentToggle) {
                    parentToggle.setAttribute('aria-expanded', 'true');
                    const parentLi = parentToggle.closest('li.side-nav-item');
                    if (parentLi) parentLi.classList.add('active');
                }

                parentCollapse = parentCollapse.parentElement.closest('.collapse');
            }
        }
    });
</script>