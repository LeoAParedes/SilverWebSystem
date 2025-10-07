class TenantManager {
    constructor() {
        this.goldenRatio = 1.618;
        this.currentTenants = [];
        this.availableUsers = [];
        this.init();
    }

    init() {
        this.loadTenants();
        this.loadUsers();
        this.setupEventListeners();
    }

    setupEventListeners() {
        const tenantNameInput = document.getElementById('tenantName');
        if (tenantNameInput) {
            tenantNameInput.addEventListener('input', (e) => {
                this.generateTenantCode(e.target.value);
            });
        }

        const createOwnerCheckbox = document.getElementById('createNewOwner');
        if (createOwnerCheckbox) {
            createOwnerCheckbox.addEventListener('change', (e) => {
                this.toggleOwnerFields(e.target.checked);
            });
        }

        const subscriptionSelect = document.getElementById('tenantSubscription');
        if (subscriptionSelect) {
            subscriptionSelect.addEventListener('change', (e) => {
                this.updateSubscriptionLimits(e.target.value);
            });
        }

        const dbNameInput = document.getElementById('tenantDatabase');
        if (dbNameInput) {
            dbNameInput.addEventListener('blur', (e) => {
                this.validateDatabase(e.target.value);
            });
        }

        const testDbButton = document.getElementById('testDatabaseConnection');
        if (testDbButton) {
            testDbButton.addEventListener('click', () => {
                const dbName = document.getElementById('tenantDatabase').value;
                if (dbName) {
                    this.validateDatabase(dbName);
                } else {
                    this.showNotification('Please enter a database name', 'warning');
                }
            });
        }
    }

    generateTenantCode(name) {
        if (!name) return;
        const code = 'TNT_' + name.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 8) + '_' + Date.now().toString(36).toUpperCase();
        const codeInput = document.getElementById('tenantCode');
        if (codeInput) codeInput.value = code;
    }

    async validateDatabase(dbName) {
        if (!dbName) return;
        
        const feedback = document.getElementById('databaseFeedback');
        if (feedback) {
            feedback.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking database...';
            feedback.className = 'text-info small';
        }
        
        try {
            const response = await fetch(`/app/ajax/tenants.php?action=validateDatabase&database_name=${encodeURIComponent(dbName)}`);
            const result = await response.json();
            
            if (feedback) {
                if (result.exists && result.accessible) {
                    feedback.innerHTML = `<i class="fas fa-check-circle"></i> Database exists (${result.table_count} tables)`;
                    feedback.className = 'text-success small';
                } else {
                    feedback.innerHTML = '<i class="fas fa-times-circle"></i> Database not accessible. Create and grant access first.';
                    feedback.className = 'text-danger small';
                }
            }
            
            return result.exists && result.accessible;
        } catch (error) {
            if (feedback) {
                feedback.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error checking database';
                feedback.className = 'text-danger small';
            }
            return false;
        }
    }

    toggleOwnerFields(createNew) {
        const existingOwnerDiv = document.getElementById('existingOwnerDiv');
        const newOwnerDiv = document.getElementById('newOwnerFields');
        
        if (createNew) {
            if (existingOwnerDiv) existingOwnerDiv.style.display = 'none';
            if (newOwnerDiv) newOwnerDiv.style.display = 'block';
        } else {
            if (existingOwnerDiv) existingOwnerDiv.style.display = 'block';
            if (newOwnerDiv) newOwnerDiv.style.display = 'none';
        }
    }

    updateSubscriptionLimits(plan) {
        const limits = {
            'free': { users: 1, storage: 1, databases: 1, price: 0 },
            'starter': { users: 5, storage: 5, databases: 1, price: 29 },
            'professional': { users: 15, storage: 20, databases: 3, price: 79 },
            'business': { users: 50, storage: 100, databases: 10, price: 149 },
            'enterprise': { users: 999, storage: 999, databases: 999, price: 299 }
        };

        const selectedLimits = limits[plan] || limits['free'];
        const maxUsersInput = document.getElementById('tenantMaxUsers');
        const maxStorageInput = document.getElementById('tenantMaxStorage');
        const maxDatabasesInput = document.getElementById('tenantMaxDatabases');
        const limitsDisplay = document.getElementById('planLimitsDisplay');
        
        if (maxUsersInput) maxUsersInput.value = selectedLimits.users;
        if (maxStorageInput) maxStorageInput.value = selectedLimits.storage;
        if (maxDatabasesInput) maxDatabasesInput.value = selectedLimits.databases;
        
        if (limitsDisplay) {
            limitsDisplay.innerHTML = `
                <small class="text-muted">
                    <i class="fas fa-users"></i> ${selectedLimits.users} users | 
                    <i class="fas fa-database"></i> ${selectedLimits.databases} databases | 
                    <i class="fas fa-hdd"></i> ${selectedLimits.storage}GB storage
                    ${selectedLimits.price > 0 ? ` | $${selectedLimits.price}/month` : ' | Free'}
                </small>
            `;
        }
    }

    async loadTenants() {
        try {
            const response = await fetch('/app/ajax/tenants.php?action=getTenants');
            const data = await response.json();
            
            if (data.success) {
                this.currentTenants = data.tenants;
                this.renderTenants();
            }
        } catch (error) {
            console.error('Error loading tenants:', error);
            this.showNotification('Failed to load tenants', 'error');
        }
    }

    async loadUsers() {
        try {
            const response = await fetch('/app/ajax/tenants.php?action=getUsers');
            const data = await response.json();
            
            if (data.success) {
                this.availableUsers = data.users;
                this.populateUserSelects();
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    populateUserSelects() {
        const ownerSelect = document.getElementById('tenantOwner');
        if (ownerSelect) {
            ownerSelect.innerHTML = '<option value="">Select Owner</option>';
            this.availableUsers.forEach(user => {
                ownerSelect.innerHTML += `<option value="${user.id}">${user.username} (${user.email || 'No email'})</option>`;
            });
        }
    }

    renderTenants() {
        const container = document.getElementById('tenantsContainer');
        if (!container) return;

        if (this.currentTenants.length === 0) {
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No tenants created yet. Click "Create New Tenant" to get started.
                    </div>
                </div>
            `;
            return;
        }

        let html = '<div class="row">';
        this.currentTenants.forEach(tenant => {
            const isActive = tenant.is_active == 1;
            const hasDatabase = tenant.database_name && tenant.database_name !== '';
            const dbAccessible = tenant.database_accessible;
            
            html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card tenant-card h-100 ${!isActive ? 'opacity-75' : ''}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">${tenant.tenant_name}</h5>
                                    <small class="text-muted">${tenant.tenant_code}</small>
                                </div>
                                <span class="badge bg-${isActive ? 'success' : 'danger'}">
                                    ${isActive ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">Database</small>
                                ${hasDatabase ? 
                                    `<span class="fw-bold ${dbAccessible ? 'text-success' : 'text-warning'}">
                                        <i class="fas fa-database"></i> ${tenant.database_name}
                                        <small class="d-block">${dbAccessible ? `(${tenant.table_count} tables)` : '(Not accessible)'}</small>
                                    </span>` : 
                                    '<span class="text-muted"><i class="fas fa-exclamation-triangle"></i> Not configured</span>'
                                }
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block">Owner</small>
                                <span class="fw-bold">${tenant.owner_name || 'Not assigned'}</span>
                                ${tenant.owner_email ? `<small class="text-muted d-block">${tenant.owner_email}</small>` : ''}
                            </div>
                            
                            <div class="mb-3">
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <small class="text-muted d-block">Plan</small>
                                        <span class="badge bg-info">${tenant.subscription_type || 'free'}</span>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Users</small>
                                        <span>${tenant.user_count || 0}/${tenant.max_users || 5}</span>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Storage</small>
                                        <span>${tenant.storage_limit || 1}GB</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 flex-wrap">
                                ${!hasDatabase ? 
                                    `<button class="btn btn-sm btn-primary" onclick="tenantManager.configureDatabase(${tenant.tenant_id})" title="Configure Database">
                                        <i class="fas fa-database"></i> Setup DB
                                    </button>` : 
                                    !dbAccessible ?
                                        `<button class="btn btn-sm btn-warning" onclick="tenantManager.configureDatabase(${tenant.tenant_id})" title="Fix Database Connection">
                                            <i class="fas fa-exclamation-triangle"></i> Fix DB
                                        </button>` :
                                        `<button class="btn btn-sm btn-info" onclick="tenantManager.viewDatabase('${tenant.database_name}')" title="View Database Info">
                                            <i class="fas fa-database"></i> DB Info
                                        </button>`
                                }
                                
                                <button class="btn btn-sm btn-success" onclick="tenantManager.accessCRM('${tenant.tenant_code}')" title="Access CRM">
                                    <i class="fas fa-external-link-alt"></i> Access CRM
                                </button>
                                
                                <button class="btn btn-sm btn-secondary" onclick="tenantManager.viewTenantDetails(${tenant.tenant_id})" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <button class="btn btn-sm btn-danger" onclick="tenantManager.deleteTenant(${tenant.tenant_id})" title="Delete Tenant">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    formatPlanName(plan) {
        const plans = {
            'free': 'Free',
            'starter': 'Starter',
            'professional': 'Professional',
            'business': 'Business',
            'enterprise': 'Enterprise'
        };
        return plans[plan] || plan;
    }

    openCreateTenant() {
        const form = document.getElementById('createTenantForm');
        if (form) form.reset();
        
        const modal = new bootstrap.Modal(document.getElementById('createTenantModal'));
        this.generateTenantCode('NEW');
        
        const createOwnerCheckbox = document.getElementById('createNewOwner');
        if (createOwnerCheckbox) {
            createOwnerCheckbox.checked = false;
        }
        this.toggleOwnerFields(false);
        this.updateSubscriptionLimits('free');
        
        const databaseFeedback = document.getElementById('databaseFeedback');
        if (databaseFeedback) {
            databaseFeedback.innerHTML = '';
            databaseFeedback.className = '';
        }
        
        this.loadUsers();
        modal.show();
    }

    async createTenant() {
        const createNewOwner = document.getElementById('createNewOwner').checked;
        
        try {
            let ownerId = null;
            
            if (createNewOwner) {
                const userData = {
                    username: document.getElementById('ownerUsername').value,
                    email: document.getElementById('ownerEmail').value,
                    password: document.getElementById('ownerPassword').value,
                    role_id: 3
                };
                
                if (!this.validateUserData(userData)) {
                    return;
                }
                
                const userResponse = await this.createUser(userData);
                if (!userResponse.success) {
                    throw new Error(userResponse.message || 'Failed to create user');
                }
                ownerId = userResponse.user_id;
                this.showNotification('User account created successfully', 'success');
            } else {
                ownerId = document.getElementById('tenantOwner').value;
            }
            
            if (!ownerId) {
                throw new Error('No owner selected or created');
            }
            
            const dbName = document.getElementById('tenantDatabase').value;
            
            if (dbName) {
                const isValid = await this.validateDatabase(dbName);
                if (!isValid) {
                    this.showNotification(`Database '${dbName}' is not accessible. Create it and grant access first.`, 'error');
                    return;
                }
            }
            
            const tenantData = {
                name: document.getElementById('tenantName').value,
                code: document.getElementById('tenantCode').value,
                database_name: dbName,
                owner_id: ownerId,
                subscription: document.getElementById('tenantSubscription').value,
                domain: document.getElementById('tenantDomain')?.value || '',
                max_users: document.getElementById('tenantMaxUsers').value,
                max_storage: document.getElementById('tenantMaxStorage').value,
                max_databases: document.getElementById('tenantMaxDatabases').value
            };
            
            if (!this.validateTenantData(tenantData)) {
                return;
            }
            
            const tenantResponse = await fetch('/app/ajax/tenants.php?action=createTenant', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(tenantData)
            });
            
            const result = await tenantResponse.json();
            
            if (result.success) {
                this.showNotification('Tenant created successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('createTenantModal')).hide();
                this.loadTenants();
            } else {
                throw new Error(result.message || 'Failed to create tenant');
            }
            
        } catch (error) {
            console.error('Error creating tenant:', error);
            this.showNotification(error.message, 'error');
        }
    }

    async createUser(userData) {
        const response = await fetch('/app/ajax/tenants.php?action=createUser', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userData)
        });
        return await response.json();
    }

    validateUserData(data) {
        if (!data.username || data.username.length < 3) {
            this.showNotification('Username must be at least 3 characters', 'error');
            return false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            this.showNotification('Invalid email address', 'error');
            return false;
        }
        
        if (!data.password || data.password.length < 8) {
            this.showNotification('Password must be at least 8 characters', 'error');
            return false;
        }
        
        const confirmPassword = document.getElementById('ownerPasswordConfirm')?.value;
        if (confirmPassword && data.password !== confirmPassword) {
            this.showNotification('Passwords do not match', 'error');
            return false;
        }
        
        return true;
    }

    validateTenantData(data) {
        if (!data.name || data.name.length < 3) {
            this.showNotification('Tenant name must be at least 3 characters', 'error');
            return false;
        }
        
        return true;
    }

    async configureDatabase(tenantId) {
        const dbName = prompt('Enter the database name:\n\nNote: Database must already exist in phpMyAdmin and silverweb user must have access.\n\nExample: tenant_silvercrm_central');
        if (!dbName) return;
        
        this.showNotification('Validating database connection...', 'info');
        
        const isValid = await this.validateDatabase(dbName);
        if (!isValid) {
            this.showNotification(`Cannot connect to database '${dbName}'. Please verify it exists and silverweb user has access.`, 'error');
            return;
        }
        
        try {
            const response = await fetch('/app/ajax/tenants.php?action=updateDatabase', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    tenant_id: tenantId,
                    database_name: dbName 
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                this.loadTenants();
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to configure database', 'error');
        }
    }

    viewDatabase(databaseName) {
        this.showNotification(`Database: ${databaseName}. Access phpMyAdmin to manage tables and data.`, 'info');
    }

    accessCRM(tenantCode) {
        const crmUrl = `http://crm.silverwebsystem.com/?tenant=${tenantCode}`;
        window.open(crmUrl, '_blank');
    }

    async deleteTenant(tenantId) {
        if (!confirm('Are you sure you want to delete this tenant?\n\nThis will remove the tenant configuration but will NOT delete the database.')) {
            return;
        }
        
        try {
            const response = await fetch('/app/ajax/tenants.php?action=deleteTenant', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tenant_id: tenantId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                this.loadTenants();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            this.showNotification(error.message || 'Failed to delete tenant', 'error');
        }
    }

    async viewTenantDetails(tenantId) {
        try {
            const response = await fetch(`/app/ajax/tenants.php?action=getTenantDetails&tenant_id=${tenantId}`);
            const data = await response.json();
            
            if (data.success && data.tenant) {
                const tenant = data.tenant;
                const modalContent = `
                    <div class="modal fade" id="tenantDetailsModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Tenant Details: ${tenant.tenant_name}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <strong>Code:</strong> ${tenant.tenant_code}
                                    </div>
                                    <div class="mb-3">
                                        <strong>Owner:</strong> ${tenant.owner_name || 'Not assigned'}
                                        ${tenant.owner_email ? `<br><small>${tenant.owner_email}</small>` : ''}
                                    </div>
                                    <div class="mb-3">
                                        <strong>Database:</strong> ${tenant.database_name || 'Not configured'}
                                        ${tenant.database_accessible ? 
                                            `<span class="badge bg-success ms-2">Connected</span>` : 
                                            tenant.database_name ? `<span class="badge bg-warning ms-2">Not Accessible</span>` : ''
                                        }
                                    </div>
                                    <div class="mb-3">
                                        <strong>Subscription:</strong> <span class="badge bg-info">${tenant.subscription_type || 'free'}</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Limits:</strong>
                                        <ul class="mb-0">
                                            <li>Users: ${tenant.user_count || 0}/${tenant.max_users || 5}</li>
                                            <li>Storage: ${tenant.storage_limit || 1}GB</li>
                                            <li>Databases: ${tenant.max_databases || 1}</li>
                                        </ul>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Created:</strong> ${new Date(tenant.created_at).toLocaleDateString()}
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-success" onclick="tenantManager.accessCRM('${tenant.tenant_code}')">
                                        <i class="fas fa-external-link-alt"></i> Access CRM
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = modalContent;
                document.body.appendChild(tempDiv.firstElementChild);
                
                const modalEl = new bootstrap.Modal(document.getElementById('tenantDetailsModal'));
                modalEl.show();
                
                document.getElementById('tenantDetailsModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            }
        } catch (error) {
            this.showNotification('Failed to load tenant details', 'error');
        }
    }

    manageTenant(tenantId) {
        window.location.href = `/app/pages/tenant-management.php?id=${tenantId}`;
    }

    refreshList() {
        this.loadTenants();
        this.loadUsers();
        this.showNotification('Refreshing tenant list...', 'info');
    }

    showNotification(message, type = 'info') {
        if (window.showNotification) {
            window.showNotification(message, type);
        } else {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            }[type] || 'alert-info';
            
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            notification.style.zIndex = '9999';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('settings.php')) {
        if (document.getElementById('tenantsContainer') || document.getElementById('createTenantForm')) {
            window.tenantManager = new TenantManager();
        }
    }
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = TenantManager;
}

