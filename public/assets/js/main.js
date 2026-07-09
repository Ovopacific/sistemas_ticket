/**
 * Help Desk LAN - Custom Scripting Mechanics
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Toggler Collapse Functionality
    const sidebar = document.getElementById('sidebar');
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    if (sidebar && sidebarCollapse) {
        sidebarCollapse.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });
    }

    // 2. Dark/Light Theme Switcher Manager
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeToggleIcon = document.getElementById('themeToggleIcon');
    
    function updateThemeIcon(theme) {
        if (!themeToggleIcon) return;
        if (theme === 'dark') {
            themeToggleIcon.classList.remove('bi-moon');
            themeToggleIcon.classList.add('bi-sun');
        } else {
            themeToggleIcon.classList.remove('bi-sun');
            themeToggleIcon.classList.add('bi-moon');
        }
    }

    // Init theme icon status
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    updateThemeIcon(currentTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function () {
            const activeTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const targetTheme = activeTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', targetTheme);
            localStorage.setItem('theme', targetTheme);
            updateThemeIcon(targetTheme);
        });
    }

    // 3. Auto initialize Simple DataTables
    const dataTables = document.querySelectorAll('.datatable');
    dataTables.forEach(table => {
        new simpleDatatables.DataTable(table, {
            searchable: true,
            fixedHeight: false,
            labels: {
                placeholder: "Buscar...",
                perPage: "{select} registros por página",
                noRows: "No se encontraron registros",
                info: "Mostrando {start} a {end} de {rows} registros",
            }
        });
    });

    // 4. Auto dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // 5. Global Smooth Page Transitions & Loading Bar
    const contentArea = document.getElementById('content');
    if (contentArea) {
        // Add fade-in transition when loaded
        setTimeout(() => {
            contentArea.classList.add('fade-in-active');
        }, 50);

        // Add a smooth loading bar at the top on click of menu links
        const links = document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript"]):not(.dropdown-toggle):not([download])');
        links.forEach(link => {
            link.addEventListener('click', function (e) {
                // If it's a normal left click without ctrl/cmd keys
                if (e.button === 0 && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
                    const href = this.getAttribute('href');
                    if (href && href !== '/logout' && href !== '#') {
                        e.preventDefault();
                        
                        // Show loading bar
                        let loader = document.querySelector('.page-transition-overlay');
                        if (!loader) {
                            loader = document.createElement('div');
                            loader.className = 'page-transition-overlay';
                            document.body.appendChild(loader);
                        }
                        loader.style.transform = 'scaleX(1)';
                        
                        // Fade out current content
                        contentArea.style.opacity = '0';
                        contentArea.style.transform = 'translateY(-10px)';
                        
                        // Redirect after animation completes
                        setTimeout(() => {
                            window.location.href = href;
                        }, 200);
                    }
                }
            });
        });
    }
});
