<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['id']) && !isset($_SESSION['user_id'])) {
    header("Location: /silverwebsystem/index.php?error=notloggedin");
    exit();
}

$userId = $_SESSION['id'] ?? $_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

// Check user role and permissions
$roleStmt = $pdo->prepare("
    SELECT r.role_name, r.role_id 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.role_id 
    WHERE u.id = ?
");
$roleStmt->execute([$userId]);
$userRole = $roleStmt->fetch(PDO::FETCH_ASSOC);

$isAdmin = ($userRole && ($userRole['role_name'] == 'super_admin' || $userRole['role_name'] == 'admin'));

// Get user permissions
if ($userRole) {
    $permissionsStmt = $pdo->prepare("
        SELECT p.permission_key 
        FROM user_roles ur
        JOIN role_permissions rp ON ur.role_id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.permission_id
        WHERE ur.user_id = ?
    ");
    $permissionsStmt->execute([$userId]);
    $userPermissions = $permissionsStmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $userPermissions = [];
}

// Helper function
function hasPermission($permission, $userPermissions) {
    return in_array($permission, $userPermissions);
}
$message = '';
$messageType = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Silver Web System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="assets/css/output.css" rel="stylesheet">
    
    <style>
    /* Privileges & Tenant System CSS */
    :root {
        --golden-ratio: 1.618;
        --forest-green: #138a36;
        --spring-green: #18ff6d;
        --black-olive: #34403a;
        --ash-gray: #b4d0bf;
        --almond: #e4f2e9;
    }
    
    .settings-container {
        max-width: 1400px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .privilege-card {
        background: white;
        border-radius: calc(0.5rem * var(--golden-ratio));
        padding: 1.618rem;
        margin-bottom: 1.618rem;
        border-left: 4px solid var(--forest-green);
        box-shadow: 0 2px 10px rgba(52, 64, 58, 0.1);
        transition: all 0.382s ease;
    }
    
    .privilege-card:hover {
        transform: translateX(0.382rem);
        box-shadow: 0 4px 20px rgba(52, 64, 58, 0.15);
    }
    
    .permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .permission-item {
        padding: 0.618rem 1rem;
        background: var(--almond);
        border-radius: 0.382rem;
        display: flex;
        align-items: center;
        gap: 0.618rem;
        transition: all 0.382s ease;
    }
    
    .permission-item:hover {
        background: linear-gradient(135deg, rgba(19, 138, 54, 0.1) 0%, rgba(24, 255, 109, 0.1) 100%);
    }
    
    .permission-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        accent-color: var(--forest-green);
    }
    
    .tenant-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 2px solid var(--ash-gray);
        position: relative;
        overflow: hidden;
    }
    
    .tenant-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--forest-green) 0%, var(--spring-green) 100%);
    }
    
    .tenant-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .tenant-badge.active {
        background: rgba(24, 255, 109, 0.2);
        color: var(--forest-green);
    }
    
    .tenant-badge.inactive {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .role-matrix-table {
        background: white;
        border-radius: 0.618rem;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .role-matrix-table thead {
        background: linear-gradient(135deg, var(--black-olive) 0%, var(--forest-green) 100%);
        color: white;
    }
    
    .role-matrix-table th {
        padding: 1rem;
        font-weight: 600;
        text-align: center;
    }
    
    .role-matrix-table td {
        padding: 0.75rem;
        text-align: center;
        border-bottom: 1px solid var(--almond);
    }
    
    .matrix-checkbox {
        width: 1.5rem;
        height: 1.5rem;
        cursor: pointer;
    }
    
    .user-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, rgba(19, 138, 54, 0.1) 0%, rgba(24, 255, 109, 0.1) 100%);
        border-radius: 50px;
        font-weight: 600;
    }
    
    /* CV Form Styles (Kept from original) */
    .cv-section {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #138a36;
    }
    
    .form-label-cv {
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: #34403a !important;
        margin-bottom: 0.25rem !important;
    }
    </style>
</head>
<body>
    <?php 
    if (file_exists(__DIR__ . '/assets/navmenu/navmenu.php')) {
        require_once __DIR__ . '/assets/navmenu/navmenu.php';
    }
    ?>
    
    <div class="settings-container">
        <h1 class="page-header">
            <i class="fas fa-cog"></i> System Settings
        </h1>
        
        <?php if($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <?php if($isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link active" id="privileges-tab" data-bs-toggle="tab" href="#privileges" role="tab">
                    <i class="fas fa-shield-alt"></i> Privileges
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tenants-tab" data-bs-toggle="tab" href="#tenants" role="tab">
                    <i class="fas fa-building"></i> Tenants
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="users-tab" data-bs-toggle="tab" href="#users" role="tab">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
		<li class="nav-item">
        <a class="nav-link" id="documents-tab" data-bs-toggle="tab" href="#documents" role="tab">
            <i class="fas fa-file-alt"></i> Documents
        </a>
    </li>

 <?php endif; ?>
           
        </ul>
        
        <div class="tab-content" id="settingsTabContent">
            <?php if($isAdmin): ?>
            <!-- Privileges Tab -->
            <div class="tab-pane fade show active" id="privileges" role="tabpanel">
                <div class="row mt-4">
                    <div class="col-lg-4">
                        <div class="privilege-card">
                            <h4><i class="fas fa-user-tag"></i> Roles Management</h4>
                            <button class="btn btn-forest mt-3" onclick="privilegeManager.openCreateRole()">
                                <i class="fas fa-plus"></i> Create New Role
                            </button>
                            
                            <div class="mt-4" id="rolesList">
                                <!-- Roles list will be loaded here -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-8">
                        <div class="privilege-card">
                            <h4><i class="fas fa-key"></i> Permission Matrix</h4>
                            <div class="table-responsive mt-3">
                                <table class="role-matrix-table" id="permissionMatrix">
                                    <!-- Permission matrix will be loaded here -->
                                </table>
                            </div>
                        </div>
                        
                        <div class="privilege-card">
                            <h4><i class="fas fa-user-shield"></i> Quick Assign</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-select" id="quickAssignUser">
                                        <option value="">Select User</option>
                                        <!-- Users will be loaded here -->
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" id="quickAssignRole">
                                        <option value="">Select Role</option>
                                        <!-- Roles will be loaded here -->
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-forest w-100" onclick="privilegeManager.quickAssign()">
                                        Assign
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tenants Tab -->
            <div class="tab-pane fade" id="tenants" role="tabpanel">
                <div class="row mt-4">
                    <div class="col-12 mb-3">
                        <button class="btn btn-forest" onclick="tenantManager.openCreateTenant()">
                            <i class="fas fa-plus"></i> Create New Tenant
                        </button>
                        <button class="btn btn-info ms-2" onclick="tenantManager.refreshList()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                    
                    <div class="col-12" id="tenantsContainer">
                        <!-- Tenant cards will be loaded here -->
                    </div>
                </div>
            </div>
            
            <!-- Users Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between mb-4">
                            <h4>User Management</h4>
                            <button class="btn btn-forest" onclick="userManager.openCreateUser()">
                                <i class="fas fa-user-plus"></i> Create User
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Tenant</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- Users will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            

 <div class="tab-pane fade" <?= !$isAdmin ? 'show active' : '' ?>" id="documents" role="tabpanel">
                <div class="row">
                    <!-- CV Builder Card -->
                    <div class="col-lg-8">
                        <div class="card golden-card">
                            <div class="card-body">
                                <h3 class="card-title mb-4">
                                    <i class="fas fa-user-tie text-forest-green"></i> Professional CV Builder
                                </h3>
                                <p class="text-muted mb-4">Create your professional CV with golden ratio proportions</p>
                                
                                <!-- CV Form -->
                                <form id="cvForm">
                                    <!-- Personal Information -->
                                    <div class="cv-section mb-4">
                                        <h4 class="section-title">Personal Information</h4>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="cv_fullname" value="<?= htmlspecialchars($userData['first_name'] ?? '') . ' ' . htmlspecialchars($userData['last_name'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Professional Title</label>
                                                <input type="text" class="form-control" id="cv_title" placeholder="e.g., Computer Systems Engineer">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" id="cv_email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="tel" class="form-control" id="cv_phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Address</label>
                                                <input type="text" class="form-control" id="cv_address" placeholder="City, State">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="cv-section mb-4">
					    <h4 class="section-title">Profile Photo</h4>
					    <div class="row">
					        <div class="col-md-6">
					            <div class="photo-upload-container">
					                <div id="photoPreview" class="photo-preview mb-3">
					                    <img id="photoPreviewImg" src="" alt="Profile Photo" style="display: none; max-width: 150px; max-height: 150px; object-fit: cover; border-radius: 8px;">
					                    <div id="photoPlaceholder" class="photo-placeholder">
					                        <i class="fas fa-user-circle fa-5x text-muted"></i>
					                        <p class="text-muted mt-2">No photo uploaded</p>
					                    </div>
					                </div>
					                <input type="file" class="form-control" id="cv_photo" accept="image/jpeg,image/jpg,image/png" onchange="cvBuilder.handlePhotoUpload(this)">
					                <small class="text-muted">Recommended: Square image (300x300px), Max size: 2MB</small>
					            </div>
					        </div>
					        <div class="col-md-6">
					            <div class="photo-options">
					                <button type="button" class="btn btn-sm btn-outline-danger" onclick="cvBuilder.removePhoto()">
					                    <i class="fas fa-trash"></i> Remove Photo
					                </button>
					            </div>
					        </div>
					    </div>
					</div>

                                    <!-- Professional Summary -->
                                    <div class="cv-section mb-4">
                                        <h4 class="section-title">Professional Summary</h4>
                                        <textarea class="form-control" id="cv_summary" rows="3" placeholder="Brief professional summary..."></textarea>
                                    </div>
                                    
                                    <!-- Education -->
                                    <div class="cv-section mb-4">
                                        <h4 class="section-title">
                                            Education 
                                            <button type="button" class="btn btn-sm btn-forest float-end" onclick="cvBuilder.addEducation()">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </h4>
                                        <div id="educationList"></div>
                                    </div>
                                    
                                    <!-- Work Experience -->
                                    <div class="cv-section mb-4">
                                        <h4 class="section-title">
                                            Work Experience
                                            <button type="button" class="btn btn-sm btn-forest float-end" onclick="cvBuilder.addExperience()">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </h4>
                                        <div id="experienceList"></div>
                                    </div>
                                    
                                    <!-- Skills -->
                                    <div class="cv-section mb-4">
                                        <h4 class="section-title">Skills</h4>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Technical Skills</label>
                                                <textarea class="form-control" id="cv_technical_skills" rows="4" placeholder="e.g., PHP, JavaScript, SQL..."></textarea>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Soft Skills</label>
                                                <textarea class="form-control" id="cv_soft_skills" rows="4" placeholder="e.g., Team Leadership, Communication..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Languages -->
                                    <div class="cv-section mb-4">
                                        <h4 class="section-title">
                                            Languages
                                            <button type="button" class="btn btn-sm btn-forest float-end" onclick="cvBuilder.addLanguage()">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </h4>
                                        <div id="languageList"></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview & Actions Panel -->
                    <div class="col-lg-4">
                        <div class="card golden-card sticky-top" style="top: 20px;">
                            <div class="card-body">
                                <h4 class="card-title mb-4">
                                    <i class="fas fa-magic text-spring-green"></i> CV Preview & Actions
                                </h4>
                                
                                <!-- Template Selection -->
                             <div class="mb-4">
			    <label class="form-label">Select Template</label>
			    <select class="form-select" id="cv_template">
			        <option value="golden">Golden Template (Default)</option>
			        <option value="spanish">Spanish Template (Español)</option>
			        <option value="modern">Modern Forest</option>
			        <option value="classic">Classic Elegant</option>
			    </select>
			</div>   
                                <!-- Color Scheme -->
                                <div class="mb-4">
                                    <label class="form-label">Color Scheme</label>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="color-option" data-color="forest" style="background: var(--forest-green);"></button>
                                        <button type="button" class="color-option active" data-color="spring" style="background: var(--spring-green);"></button>
                                        <button type="button" class="color-option" data-color="olive" style="background: var(--black-olive);"></button>
                                        <button type="button" class="color-option" data-color="gray" style="background: var(--ash-gray);"></button>
                                   <button type="button" class="color-option" data-color="darkforest" style="background: linear-gradient(135deg, #133c23, #505050);"></button>
					 </div>
                                </div>
                                
                                <!-- CV Preview Stats -->
                                <div class="cv-stats mb-4">
                                    <div class="stat-item">
                                        <span class="stat-label">Completeness</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" id="cvCompleteness" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
				<button type="button" class="btn btn-forest" onclick="cvBuilder.previewHTML()">
				    <i class="fas fa-eye"></i> HTML Preview
				</button>
				<button type="button" class="btn btn-primary" onclick="cvBuilder.previewPDF()">
				    <i class="fas fa-file-pdf"></i> PDF Preview
				</button>
                                    <button type="button" class="btn btn-success" onclick="cvBuilder.generatePDF()">
                                        <i class="fas fa-file-pdf"></i> Generate PDF
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="cvBuilder.saveTemplate()">
                                        <i class="fas fa-save"></i> Save Template
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="cvBuilder.loadTemplate()">
                                        <i class="fas fa-folder-open"></i> Load Template
                                    </button>
                                </div>
                                
                                <!-- Recent Documents -->
                                <div class="mt-4">
                                    <h5>Recent Documents</h5>
                                    <div class="list-group" id="recentDocuments">
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <i class="fas fa-file-pdf text-danger"></i> CV_2025_01.pdf
                                            <small class="text-muted float-end">2 days ago</small>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
<div class="modal fade" id="cvPreviewModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">CV Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="cvPreviewContent" style="min-height: 500px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-forest" onclick="cvBuilder.generatePDF()">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>



            </div>



        </div>
    </div>
    
    <!-- Modals -->
    <!-- Create Role Modal -->
    <div class="modal fade" id="createRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createRoleForm">
                        <div class="mb-3">
                            <label class="form-label">Role Name</label>
                            <input type="text" class="form-control" id="newRoleName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="newRoleDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="permission-grid" id="rolePermissions">
                                <!-- Permissions will be loaded here -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-forest" onclick="privilegeManager.createRole()">Create Role</button>
                </div>
            </div>
        </div>
    </div>

	<div class="modal fade" id="createTenantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Tenant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createTenantForm">
                    <div class="mb-4">
                        <h6 class="text-forest-green">Tenant Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tenant Name *</label>
                                <input type="text" class="form-control" id="tenantName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tenant Code</label>
                                <input type="text" class="form-control" id="tenantCode" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-forest-green">Database Configuration</h6>
                        <div class="alert alert-warning">
                            <strong>Important:</strong> The database must be created in phpMyAdmin first and silverweb user must have access.
                            <br><small>Example: tenant_silvercrm_central</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Database Name (Optional)</label>
                                <input type="text" class="form-control" id="tenantDatabase" 
                                       placeholder="e.g., tenant_silvercrm_central">
                                <div id="databaseFeedback"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-info w-100" id="testDatabaseConnection">
                                    <i class="fas fa-plug"></i> Test Connection
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-forest-green">Tenant Owner</h6>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="createNewOwner">
                            <label class="form-check-label" for="createNewOwner">
                                Create new owner account
                            </label>
                        </div>
                        
                        <div id="existingOwnerDiv">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Select Existing User *</label>
                                    <select class="form-select" id="tenantOwner">
                                        <option value="">Select Owner</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div id="newOwnerFields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="ownerUsername">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="ownerEmail">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="ownerPassword">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Domain (Optional)</label>
                                    <input type="text" class="form-control" id="tenantDomain" 
                                           placeholder="tenant.example.com">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-forest-green">Subscription & Limits</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subscription Plan</label>
                                <select class="form-select" id="tenantSubscription">
                                    <option value="free">Free (1 user, 1GB)</option>
                                    <option value="starter">Starter (5 users, 5GB)</option>
                                    <option value="professional">Professional (15 users, 20GB)</option>
                                    <option value="business">Business (50 users, 100GB)</option>
                                    <option value="enterprise">Enterprise (Unlimited)</option>
                                </select>
                                <div id="planLimitsDisplay" class="mt-2"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-4">
                                        <label class="form-label">Max Users</label>
                                        <input type="number" class="form-control" id="tenantMaxUsers" value="5">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label">Storage (GB)</label>
                                        <input type="number" class="form-control" id="tenantMaxStorage" value="1">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label">Max DBs</label>
                                        <input type="number" class="form-control" id="tenantMaxDatabases" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-forest" onclick="tenantManager.createTenant()">
                    <i class="fas fa-plus"></i> Create Tenant
                </button>
            </div>
        </div>
    </div>
</div>
    

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createUserForm">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" id="newUsername" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="newUserEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" id="newUserPassword" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" id="newUserRole" required>
                                <option value="">Select Role</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tenant (Optional)</label>
                            <select class="form-select" id="newUserTenant">
                                <option value="">No Tenant</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-forest" onclick="userManager.createUser()">Create User</button>
                </div>
            </div>
        </div>
    </div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createRoleForm">
                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="newRoleName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="newRoleDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="permission-grid" id="rolePermissions">
                            <!-- Permissions will be loaded here -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-forest" onclick="privilegeManager.createRole()">Create Role</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Tenant Modal -->
<div class="modal fade" id="createTenantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Tenant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createTenantForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tenant Name</label>
                            <input type="text" class="form-control" id="tenantName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tenant Code</label>
                            <input type="text" class="form-control" id="tenantCode" placeholder="AUTO-GENERATED" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner</label>
                            <select class="form-select" id="tenantOwner" required>
                                <option value="">Select Owner</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subscription</label>
                            <select class="form-select" id="tenantSubscription">
                                <option value="free">Free (5 users, 1GB)</option>
                                <option value="basic">Basic (20 users, 10GB)</option>
                                <option value="pro">Pro (100 users, 100GB)</option>
                                <option value="enterprise">Enterprise (Unlimited)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Domain (Optional)</label>
                            <input type="text" class="form-control" id="tenantDomain" placeholder="tenant.silverwebsystem.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Max Users</label>
                            <input type="number" class="form-control" id="tenantMaxUsers" value="5">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-forest" onclick="tenantManager.createTenant()">Create Tenant</button>
            </div>
        </div>
    </div>
</div>


<script src="assets/js/main.js"></script>    
<script src="generatedocuments/cv-builder.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-ESGX5LYM25"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-ESGX5LYM25');
</script>
<script src="assets/js/tenant-manager.js"></script>


</body>
</html>

