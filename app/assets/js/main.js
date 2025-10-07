 document.addEventListener('DOMContentLoaded', function() {
    


    // ===== PRELOADER =====
    const preloader = document.querySelector('#preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                preloader.style.opacity = '0';
                preloader.style.visibility = 'hidden';
                setTimeout(() => {
                    preloader.remove();
                }, 300);
            }, 300);
        });
    }
    
    // ===== SCROLL TOP BUTTON =====
    const scrollTop = document.querySelector('.scroll-top');
    
    function toggleScrollTop() {
        if (scrollTop) {
            if (window.scrollY > 100) {
                scrollTop.classList.add('active');
                scrollTop.style.opacity = '1';
                scrollTop.style.visibility = 'visible';
            } else {
                scrollTop.classList.remove('active');
                scrollTop.style.opacity = '0';
                scrollTop.style.visibility = 'hidden';
            }
        }
    }
    
    if (scrollTop) {
        scrollTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Initial check
        toggleScrollTop();
        
        // Throttled scroll event
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (scrollTimeout) {
                window.cancelAnimationFrame(scrollTimeout);
            }
            scrollTimeout = window.requestAnimationFrame(() => {
                toggleScrollTop();
            });
        });
    }
    
    // ===== GOLDEN RATIO ANIMATIONS =====
    const goldenRatio = 1.618;
    
    function applyGoldenRatioAnimation(element, duration = 1000) {
        const steps = Math.floor(duration / goldenRatio);
        element.style.transition = `all ${steps}ms cubic-bezier(0.382, 0, 0.618, 1)`;
    }
    
    // Apply golden ratio animations to elements
    document.querySelectorAll('.golden-animate').forEach(element => {
        applyGoldenRatioAnimation(element);
    });
    
    // ===== LAZY LOADING IMAGES =====
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if (lazyImages.length > 0) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    }
    
    // ===== TOOLTIPS =====
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-forest';
            tooltip.textContent = this.dataset.tooltip;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            
            tooltip.style.position = 'fixed';
            tooltip.style.top = `${rect.top - tooltipRect.height - 10}px`;
            tooltip.style.left = `${rect.left + (rect.width - tooltipRect.width) / 2}px`;
            tooltip.style.opacity = '1';
            
            this.tooltipElement = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this.tooltipElement) {
                this.tooltipElement.remove();
                delete this.tooltipElement;
            }
        });
    });
    
    // ===== FORM VALIDATION HELPERS =====
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // ===== PASSWORD STRENGTH INDICATOR =====
    const passwordInputs = document.querySelectorAll('input[type="password"][data-strength]');
    
    passwordInputs.forEach(input => {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        input.parentNode.appendChild(strengthIndicator);
        
        input.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthIndicator.className = 'password-strength';
            
            if (password.length === 0) {
                strengthIndicator.textContent = '';
            } else if (strength < 2) {
                strengthIndicator.classList.add('weak');
                strengthIndicator.textContent = 'Weak';
            } else if (strength < 4) {
                strengthIndicator.classList.add('medium');
                strengthIndicator.textContent = 'Medium';
            } else {
                strengthIndicator.classList.add('strong');
                strengthIndicator.textContent = 'Strong';
            }
        });
    });
    
    // ===== COPY TO CLIPBOARD =====
    const copyButtons = document.querySelectorAll('[data-copy]');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const textToCopy = this.dataset.copy;
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    const originalText = this.textContent;
                    this.textContent = 'Copied!';
                    this.classList.add('copied');
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.classList.remove('copied');
                    }, 2000);
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = textToCopy;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                this.textContent = 'Copied!';
                setTimeout(() => {
                    this.textContent = 'Copy';
                }, 2000);
            }
        });
    });
    
    // ===== AUTO-RESIZE TEXTAREA =====
    const autoResizeTextareas = document.querySelectorAll('textarea[data-autoresize]');
    
    autoResizeTextareas.forEach(textarea => {
        function adjustHeight() {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
        
        textarea.addEventListener('input', adjustHeight);
        adjustHeight(); // Initial adjustment
    });
    
    // ===== DEBOUNCE FUNCTION FOR SEARCH =====
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // ===== SEARCH FUNCTIONALITY =====
    const searchInputs = document.querySelectorAll('[data-search]');
    
    searchInputs.forEach(searchInput => {
        const searchTarget = searchInput.dataset.search;
        const searchItems = document.querySelectorAll(searchTarget);
        
        const performSearch = debounce(function() {
            const searchTerm = searchInput.value.toLowerCase();
            
            searchItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                    item.classList.remove('hidden');
                } else {
                    item.style.display = 'none';
                    item.classList.add('hidden');
                }
            });
            
            // Update results count
            const visibleItems = document.querySelectorAll(`${searchTarget}:not(.hidden)`);
            const resultCount = document.querySelector('[data-search-count]');
            if (resultCount) {
                resultCount.textContent = `${visibleItems.length} results found`;
            }
        }, 300);
        
        searchInput.addEventListener('input', performSearch);
    });
    
    // ===== COUNTDOWN TIMER =====
    const countdownElements = document.querySelectorAll('[data-countdown]');
    
    countdownElements.forEach(element => {
        const endDate = new Date(element.dataset.countdown).getTime();
        
        const updateCountdown = () => {
            const now = new Date().getTime();
            const distance = endDate - now;
            
            if (distance < 0) {
                element.innerHTML = 'EXPIRED';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            element.innerHTML = `
                <span class="countdown-unit">
                    <span class="countdown-value">${days}</span>
                    <span class="countdown-label">Days</span>
                </span>
                <span class="countdown-unit">
                    <span class="countdown-value">${hours}</span>
                    <span class="countdown-label">Hours</span>
                </span>
                <span class="countdown-unit">
                    <span class="countdown-value">${minutes}</span>
                    <span class="countdown-label">Minutes</span>
                </span>
                <span class="countdown-unit">
                    <span class="countdown-value">${seconds}</span>
                    <span class="countdown-label">Seconds</span>
                </span>
            `;
        };
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
    
    // ===== ACCORDION FUNCTIONALITY =====
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const accordionItem = this.parentElement;
            const accordionContent = this.nextElementSibling;
            const isActive = accordionItem.classList.contains('active');
            
            // Close all accordion items
            document.querySelectorAll('.accordion-item').forEach(item => {
                item.classList.remove('active');
                item.querySelector('.accordion-content').style.maxHeight = null;
            });
            
            // Open clicked item if it wasn't active
            if (!isActive) {
                accordionItem.classList.add('active');
                accordionContent.style.maxHeight = accordionContent.scrollHeight + 'px';
            }
        });
    });
    
    // ===== TAB FUNCTIONALITY =====
    const tabButtons = document.querySelectorAll('[data-tab]');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            const tabContent = document.getElementById(tabId);
            
            if (tabContent) {
                // Remove active class from all tabs and contents
                document.querySelectorAll('[data-tab]').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                });
                
                // Add active class to clicked tab and show content
                this.classList.add('active');
                tabContent.classList.add('active');
                tabContent.style.display = 'block';
            }
        });
    });
    
    // ===== NOTIFICATION SYSTEM =====
    window.showNotification = function(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        const container = document.querySelector('.notification-container') || (() => {
            const newContainer = document.createElement('div');
            newContainer.className = 'notification-container';
            document.body.appendChild(newContainer);
            return newContainer;
        })();
        
        container.appendChild(notification);
        
        // Auto-hide after duration
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }, duration);
        
        // Close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    };
    
    // ===== AJAX HELPER FUNCTIONS =====
    window.silverAjax = {
        get: function(url, callback) {
            fetch(url)
                .then(response => response.json())
                .then(data => callback(data))
                .catch(error => {
                    console.error('Ajax GET error:', error);
                    if (window.showNotification) {
                        window.showNotification('Error loading data', 'error');
                    }
                });
        },
        
        post: function(url, data, callback) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => callback(data))
            .catch(error => {
                console.error('Ajax POST error:', error);
                if (window.showNotification) {
                    window.showNotification('Error sending data', 'error');
                }
            });
        },
        
        formData: function(url, formData, callback) {
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => callback(data))
            .catch(error => {
                console.error('Ajax FormData error:', error);
                if (window.showNotification) {
                    window.showNotification('Error submitting form', 'error');
                }
            });
        }
    };
    
    // ===== LOCAL STORAGE HELPERS =====
    window.silverStorage = {
        set: function(key, value) {
            try {
                localStorage.setItem('silver_' + key, JSON.stringify(value));
                return true;
            } catch (e) {
                console.error('Storage set error:', e);
                return false;
            }
        },
        
        get: function(key) {
            try {
                const item = localStorage.getItem('silver_' + key);
                return item ? JSON.parse(item) : null;
            } catch (e) {
                console.error('Storage get error:', e);
                return null;
            }
        },
        
        remove: function(key) {
            localStorage.removeItem('silver_' + key);
        },
        
        clear: function() {
            Object.keys(localStorage)
                .filter(key => key.startsWith('silver_'))
                .forEach(key => localStorage.removeItem(key));
        }
    };
    
    // ===== PRINT FUNCTIONALITY =====
    const printButtons = document.querySelectorAll('[data-print]');
    
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            const elementId = this.dataset.print;
            const element = document.getElementById(elementId);
            
            if (element) {
                const printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write('<html><head><title>Print</title>');
                printWindow.document.write('<link rel="stylesheet" href="/app/assets/css/output.css">');
                printWindow.document.write('</head><body>');
                printWindow.document.write(element.innerHTML);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.print();
            }
        });
    });
    
    // ===== UTILITY: FORMAT CURRENCY =====
    window.formatCurrency = function(amount, currency = 'MXN') {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: currency
        }).format(amount);
    };
    
    // ===== UTILITY: FORMAT DATE =====
    window.formatDate = function(date, format = 'short') {
        const options = format === 'short' 
            ? { year: 'numeric', month: '2-digit', day: '2-digit' }
            : { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        
        return new Date(date).toLocaleDateString('es-MX', options);
    };
    
    // ===== PAGE VISIBILITY API =====
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            console.log('Page is hidden');
            // Pause any animations or timers
        } else {
            console.log('Page is visible');
            // Resume animations or refresh data
        }
    });
    
    // ===== ESCAPE KEY HANDLER =====
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Close any open modals (not navigation modals, those are in silvernavigation.js)
            document.querySelectorAll('.modal.active').forEach(modal => {
                if (!modal.classList.contains('auth-modal-forest')) {
                    modal.classList.remove('active');
                }
            });
            
            // Close notifications
            document.querySelectorAll('.notification').forEach(notification => {
                notification.remove();
            });
            
            // Close tooltips
            document.querySelectorAll('.tooltip-forest').forEach(tooltip => {
                tooltip.remove();
            });
        }
    });




    // ===== PRIVILEGE MANAGER (Global Object) =====
    window.privilegeManager = {
        permissions: [],
        roles: [],
        
        init: function() {
            // Only init if elements exist
            if (!document.getElementById('rolesList') && 
                !document.getElementById('permissionMatrix') && 
                !document.getElementById('quickAssignUser')) {
                return;
            }
            
            this.loadRoles();
            this.loadPermissions();
            this.loadPermissionMatrix();
            this.loadUsers();
        },
        
        loadRoles: async function() {
            try {
                const response = await fetch('/app/ajax/privileges.php?action=getRoles');
                const data = await response.json();
                
                if (data.success) {
                    this.roles = data.roles;
                    this.renderRolesList();
                    this.populateRoleSelects();
                }
            } catch (error) {
                console.error('Error loading roles:', error);
                if (window.showNotification) {
                    window.showNotification('Failed to load roles', 'error');
                }
            }
        },
        
        loadPermissions: async function() {
            try {
                const response = await fetch('/app/ajax/privileges.php?action=getPermissions');
                const data = await response.json();
                
                if (data.success) {
                    this.permissions = data.permissions;
                    this.renderPermissionCheckboxes();
                }
            } catch (error) {
                console.error('Error loading permissions:', error);
            }
        },
        
        loadPermissionMatrix: async function() {
            try {
                const response = await fetch('/app/ajax/privileges.php?action=getMatrix');
                const data = await response.json();
                
                if (data.success) {
                    this.renderMatrix(data.matrix);
                }
            } catch (error) {
                console.error('Error loading matrix:', error);
            }
        },
        
        renderRolesList: function() {
            const container = document.getElementById('rolesList');
            if (!container) return;
            
            let html = '<div class="list-group">';
            this.roles.forEach(role => {
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">${role.role_name}</h6>
                            <small class="text-muted">${role.role_description || ''}</small>
                        </div>
                        <div>
                            ${!role.is_system ? `
                                <button class="btn btn-sm btn-warning" onclick="privilegeManager.editRole(${role.role_id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="privilegeManager.deleteRole(${role.role_id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            ` : '<span class="badge bg-info">System</span>'}
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        },
        
        renderMatrix: function(matrix) {
            const table = document.getElementById('permissionMatrix');
            if (!table) return;
            
            let html = `
                <thead>
                    <tr>
                        <th>Permission</th>
                        ${this.roles.map(role => `<th>${role.role_name}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
            `;
            
            this.permissions.forEach(permission => {
                html += `<tr><td class="text-start">${permission.permission_name}</td>`;
                this.roles.forEach(role => {
                    const hasPermission = matrix[role.role_id]?.includes(permission.permission_id);
                    html += `
                        <td>
                            <input type="checkbox" 
                                   class="matrix-checkbox" 
                                   data-role="${role.role_id}" 
                                   data-permission="${permission.permission_id}"
                                   ${hasPermission ? 'checked' : ''}
                                   ${role.is_system ? 'disabled' : ''}
                                   onchange="privilegeManager.updatePermission(${role.role_id}, ${permission.permission_id}, this.checked)">
                        </td>
                    `;
                });
                html += '</tr>';
            });
            
            html += '</tbody>';
            table.innerHTML = html;
        },
        
        updatePermission: async function(roleId, permissionId, granted) {
            try {
                const response = await fetch('/app/ajax/privileges.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'updatePermission',
                        role_id: roleId,
                        permission_id: permissionId,
                        granted: granted
                    })
                });
                
                const data = await response.json();
                if (!data.success) {
                    alert('Failed to update permission');
                    location.reload();
                }
            } catch (error) {
                console.error('Error updating permission:', error);
            }
        },
        
        openCreateRole: function() {
            this.renderPermissionCheckboxes();
            new bootstrap.Modal(document.getElementById('createRoleModal')).show();
        },
        
        createRole: async function() {
            const name = document.getElementById('newRoleName').value;
            const description = document.getElementById('newRoleDescription').value;
            const permissions = Array.from(document.querySelectorAll('#rolePermissions input:checked'))
                .map(cb => cb.value);
            
            try {
                const response = await fetch('/app/ajax/privileges.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'createRole',
                        name: name,
                        description: description,
                        permissions: permissions
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('createRoleModal')).hide();
                    this.loadRoles();
                    this.loadPermissionMatrix();
                    window.showNotification('Role created successfully', 'success');
                }
            } catch (error) {
                console.error('Error creating role:', error);
                window.showNotification('Failed to create role', 'error');
            }
        },
        
        quickAssign: async function() {
            const userId = document.getElementById('quickAssignUser').value;
            const roleId = document.getElementById('quickAssignRole').value;
            
            if (!userId || !roleId) {
                window.showNotification('Please select both user and role', 'warning');
                return;
            }
            
            try {
                const response = await fetch('/app/ajax/privileges.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'assignRole',
                        user_id: userId,
                        role_id: roleId
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    window.showNotification('Role assigned successfully', 'success');
                    document.getElementById('quickAssignUser').value = '';
                    document.getElementById('quickAssignRole').value = '';
                } else {
                    window.showNotification(data.message || 'Failed to assign role', 'error');
                }
            } catch (error) {
                console.error('Error assigning role:', error);
                window.showNotification('Error assigning role', 'error');
            }
        },
        
        renderPermissionCheckboxes: function() {
            const container = document.getElementById('rolePermissions');
            if (!container) return;
            
            let html = '';
            this.permissions.forEach(perm => {
                html += `
                    <div class="permission-item">
                        <input type="checkbox" class="permission-checkbox" 
                               id="perm_${perm.permission_id}" 
                               value="${perm.permission_id}">
                        <label for="perm_${perm.permission_id}">
                            ${perm.permission_name}
                        </label>
                    </div>
                `;
            });
            container.innerHTML = html;
        },
        
        populateRoleSelects: function() {
            const selects = ['quickAssignRole', 'newUserRole'];
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    select.innerHTML = '<option value="">Select Role</option>';
                    this.roles.forEach(role => {
                        select.innerHTML += `<option value="${role.role_id}">${role.role_name}</option>`;
                    });
                }
            });
        },
        
        loadUsers: async function() {
            try {
                const response = await fetch('/app/ajax/privileges.php?action=getUsers');
                const data = await response.json();
                
                if (data.success) {
                    const select = document.getElementById('quickAssignUser');
                    if (select) {
                        select.innerHTML = '<option value="">Select User</option>';
                        data.users.forEach(user => {
                            select.innerHTML += `<option value="${user.id}">${user.username} (${user.email})</option>`;
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        },
        
        editRole: function(roleId) {
            window.showNotification('Edit role functionality coming soon', 'info');
        },
        
        deleteRole: function(roleId) {
            if (confirm('Are you sure you want to delete this role?')) {
                window.showNotification('Delete role functionality coming soon', 'info');
            }
        }
    };
    


if (window.location.pathname.includes('settings.php')) {
    // Initialize privilege manager if elements exist
    if (document.getElementById('rolesList') || 
        document.getElementById('permissionMatrix') || 
        document.getElementById('quickAssignUser')) {
        window.privilegeManager.init();
    }
    
    // Load tenant manager from external file ONCE
    if (document.getElementById('tenantsContainer') || 
        document.getElementById('createTenantForm')) {
        if (!window.tenantManagerLoaded) {  // Prevent duplicate loading
            window.tenantManagerLoaded = true;
            const script = document.createElement('script');
            script.src = '/app/assets/js/tenant-manager.js';
            script.onload = function() {
                if (!window.tenantManager && typeof TenantManager !== 'undefined') {
                    window.tenantManager = new TenantManager();
                }
            };
            script.onerror = function() {
                console.error('Failed to load tenant-manager.js');
                window.showNotification('Failed to load tenant manager', 'error');
            };
            document.head.appendChild(script);
        }
    }
}


    // ===== INITIALIZE PAGE =====
    console.log('Silver Web System Main.js loaded successfully');
    
    // Dispatch custom event when main.js is ready
    window.dispatchEvent(new CustomEvent('mainJsReady', {
        detail: {
            version: '1.0.0',
            modules: ['preloader', 'scrollTop', 'forms', 'utilities', 'privilegeManager', 'tenantManager']
        }
    }));
});

// ===== GLOBAL ERROR HANDLER =====
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    // You can send errors to a logging service here
    if (window.showNotification) {
        // Only show user-friendly errors, not all console errors
        if (e.error && e.error.message && !e.filename.includes('extension://')) {
            console.log('Error details:', {
                message: e.error.message,
                filename: e.filename,
                line: e.lineno,
                column: e.colno
            });
        }
    }
});


