// Mobile Menu Toggle for KPRCAS Attendance System
document.addEventListener('DOMContentLoaded', function() {
    // Check if sidebar exists
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) {
        console.log('No sidebar found, skipping mobile menu initialization');
        return;
    }
    
    // Create mobile menu toggle button if not exists
    if (!document.querySelector('.mobile-menu-toggle')) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'mobile-menu-toggle';
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
        toggleBtn.setAttribute('aria-label', 'Toggle Menu');
        toggleBtn.setAttribute('type', 'button');
        document.body.insertBefore(toggleBtn, document.body.firstChild);
    }
    
    // Create overlay if not exists
    if (!document.querySelector('.sidebar-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.insertBefore(overlay, document.body.firstChild);
    }
    
    const toggleBtn = document.querySelector('.mobile-menu-toggle');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (toggleBtn && sidebar && overlay) {
        // Toggle sidebar
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            
            // Change icon
            const icon = toggleBtn.querySelector('i');
            if (icon) {
                if (sidebar.classList.contains('show')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
        
        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            const icon = toggleBtn.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        });
        
        // Close sidebar when clicking a link (on mobile)
        const sidebarLinks = sidebar.querySelectorAll('.nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                    const icon = toggleBtn.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        });
    }
    
    // Add viewport meta tag if not exists
    if (!document.querySelector('meta[name="viewport"]')) {
        const viewportMeta = document.createElement('meta');
        viewportMeta.name = 'viewport';
        viewportMeta.content = 'width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes';
        document.head.appendChild(viewportMeta);
    }
    
    // Responsive table wrapper
    const tables = document.querySelectorAll('table:not(.table-responsive table)');
    tables.forEach(table => {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
    
    // Adjust DataTables for mobile (only if jQuery and DataTables are loaded)
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        jQuery.extend(true, jQuery.fn.dataTable.defaults, {
            responsive: true,
            autoWidth: false,
            language: {
                lengthMenu: '_MENU_',
                search: '_INPUT_',
                searchPlaceholder: 'Search...'
            }
        });
    }
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Close sidebar on desktop view
            if (window.innerWidth > 992) {
                if (sidebar) sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
                if (toggleBtn) {
                    const icon = toggleBtn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            }
        }, 250);
    });
});
