/**
 * Gallery Functions - Complete CRUD with Image Management
 * Works with existing design and images tables
 * Fixed Bootstrap loading issue
 */

(function() {
    'use strict';
    
    const goldenRatio = 1.618;
    
    // Wait for Bootstrap to load
    function waitForBootstrap(callback) {
        if (typeof bootstrap !== 'undefined') {
            callback();
        } else {
            setTimeout(() => waitForBootstrap(callback), 100);
        }
    }
    
    // Gallery Manager Object
    const GalleryManager = {
        currentDesign: null,
        featuredDesigns: [],
        bootstrapLoaded: false,
        
        // Initialize
        init: function() {
            // Check if Bootstrap is available
            if (typeof bootstrap !== 'undefined') {
                this.bootstrapLoaded = true;
                this.setupEventListeners();
                this.loadFeaturedDesigns();
                this.initializeModals();
            } else {
                // Wait for Bootstrap and retry
                console.log('Waiting for Bootstrap to load...');
                waitForBootstrap(() => {
                    this.bootstrapLoaded = true;
                    this.setupEventListeners();
                    this.loadFeaturedDesigns();
                    this.initializeModals();
                });
            }
        },
        
        // Setup Event Listeners
        setupEventListeners: function() {
            // Add Design Button
            document.addEventListener('click', (e) => {
                if (e.target.matches('#addDesignBtn, .add-design-btn')) {
                    e.preventDefault();
                    this.openAddModal();
                }
                
                // Edit Button
                if (e.target.closest('.edit-design-btn')) {
                    e.preventDefault();
                    const btn = e.target.closest('.edit-design-btn');
                    const designId = btn.dataset.id;
                    this.openEditModal(designId);
                }
                
                // Delete Button
                if (e.target.closest('.delete-design-btn')) {
                    e.preventDefault();
                    const btn = e.target.closest('.delete-design-btn');
                    const designId = btn.dataset.id;
                    const designName = btn.dataset.name;
                    this.confirmDelete(designId, designName);
                }
                
                // Featured Toggle
                if (e.target.closest('.toggle-featured-btn')) {
                    e.preventDefault();
                    const btn = e.target.closest('.toggle-featured-btn');
                    const designId = btn.dataset.id;
                    this.toggleFeatured(designId);
                }
                
                // View Image Button
                if (e.target.closest('.view-image-btn')) {
                    e.preventDefault();
                    const btn = e.target.closest('.view-image-btn');
                    const imagePath = btn.dataset.image;
                    const name = btn.dataset.name;
                    this.showImageModal(imagePath, name);
                }
            });
            
            // Image Preview on File Select
            document.addEventListener('change', (e) => {
                if (e.target.matches('input[type="file"].design-image')) {
                    this.previewImage(e.target);
                }
            });
        },
        
        // Initialize Modals
        initializeModals: function() {
            // Create modals if they don't exist
            if (!document.getElementById('addDesignModal')) {
                this.createAddModal();
            }
            if (!document.getElementById('editDesignModal')) {
                this.createEditModal();
            }
            if (!document.getElementById('deleteDesignModal')) {
                this.createDeleteModal();
            }
            if (!document.getElementById('imageViewModal')) {
                this.createImageViewModal();
            }
        },
        
        // Create Add Modal
        createAddModal: function() {
            const modalHTML = `
                <div class="modal fade" id="addDesignModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content" style="border-radius: ${goldenRatio}rem; border: 2px solid #138a36;">
                            <div class="modal-header" style="background: linear-gradient(135deg, #34403a 0%, #138a36 100%); color: white;">
                                <h5 class="modal-title">
                                    <i class="fas fa-plus-circle"></i> Add New Design
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="addDesignForm" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control input-golden" name="name" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">SKU <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <select class="form-select" name="sku_prefix" style="max-width: 120px;">
                                                        <option value="PATCH">PATCH</option>
                                                        <option value="SHIRT">SHIRT</option>
                                                        <option value="CAP">CAP</option>
                                                        <option value="HOODIE">HOODIE</option>
                                                        <option value="STICKER">STICKER</option>
                                                        <option value="POSTER">POSTER</option>
                                                        <option value="CUSTOM">CUSTOM</option>
                                                    </select>
                                                    <span class="input-group-text">-</span>
                                                    <input type="text" class="form-control input-golden" name="sku_suffix" placeholder="0001" required>
                                                </div>
                                                <small class="text-muted">Or select CUSTOM to enter full SKU manually</small>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Category</label>
                                                <select class="form-select input-golden" name="category">
                                                    <option value="parche">parche</option>
                                                    <option value="Parche">Parche</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Edition</label>
                                                <input type="text" class="form-control input-golden" name="edition" value="1">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Launch Price (MXN) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control input-golden" name="unit_launch_price" step="0.01" value="300.00" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Size</label>
                                                <input type="text" class="form-control input-golden" name="size" value="6cm">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Design Image <span class="text-danger">*</span></label>
                                                <input type="file" class="form-control design-image" name="image" accept="image/*" required>
                                                <div class="image-preview-container mt-3 text-center">
                                                    <img id="addImagePreview" class="img-fluid rounded" style="display: none; max-height: 200px; border: 2px solid #138a36;">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control input-golden" name="description" rows="3"></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Stock Quantity</label>
                                                <input type="number" class="form-control input-golden" name="stock_quantity" value="100">
                                            </div>
                                            
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="is_featured" id="addIsFeatured">
                                                <label class="form-check-label" for="addIsFeatured">
                                                    <i class="fas fa-star text-warning"></i> Feature this design
                                                </label>
                                            </div>
                                            
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="is_active" id="addIsActive" checked>
                                                <label class="form-check-label" for="addIsActive">
                                                    <i class="fas fa-check-circle text-success"></i> Active
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-forest-primary">
                                        <i class="fas fa-save"></i> Add Design
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Add form submit handler
            document.getElementById('addDesignForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleAddDesign(e.target);
            });
        },
        
        // Show Image Modal
        showImageModal: function(imagePath, name) {
            console.log('Opening image modal for:', name, 'Path:', imagePath);
            
            // Clean the image path
            let cleanPath = imagePath;
            
            // Remove various path issues
            cleanPath = cleanPath.replace(/^\/\.\.\/\.\./, '../..');
            cleanPath = cleanPath.replace(/\/\.\.\//g, '/../');
            cleanPath = cleanPath.replace(/\\/g, '/');
            
            // Create modal if it doesn't exist
            if (!document.getElementById('imageViewModal')) {
                this.createImageViewModal();
            }
            
            // Set modal content
            const titleEl = document.getElementById('imageViewTitle');
            const imgEl = document.getElementById('imageViewImg');
            
            if (titleEl) titleEl.textContent = name;
            if (imgEl) {
                imgEl.src = cleanPath;
                imgEl.onerror = function() {
                    console.error('Failed to load image:', cleanPath);
                    this.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2U0ZjJlOSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzM0NDAzYSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIE5vdCBGb3VuZDwvdGV4dD48L3N2Zz4=';
                };
            }
            
            // Show modal using Bootstrap or fallback
            try {
                if (typeof bootstrap !== 'undefined') {
                    const modal = new bootstrap.Modal(document.getElementById('imageViewModal'));
                    modal.show();
                } else {
                    // Fallback method
                    const modalEl = document.getElementById('imageViewModal');
                    modalEl.classList.add('show');
                    modalEl.style.display = 'block';
                    document.body.classList.add('modal-open');
                    
                    // Create backdrop
                    let backdrop = document.querySelector('.modal-backdrop');
                    if (!backdrop) {
                        backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                    }
                    
                    // Close handlers
                    const closeModal = () => {
                        modalEl.classList.remove('show');
                        modalEl.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        backdrop.remove();
                    };
                    
                    // Close button
                    const closeBtn = modalEl.querySelector('.btn-close');
                    if (closeBtn) {
                        closeBtn.onclick = closeModal;
                    }
                    
                    // Click outside to close
                    modalEl.onclick = function(e) {
                        if (e.target === modalEl) {
                            closeModal();
                        }
                    };
                }
            } catch (error) {
                console.error('Error showing modal:', error);
                // Simple fallback - open image in new window
                window.open(cleanPath, '_blank');
            }
        },

        // Open Add Modal (Fixed)
        openAddModal: function() {
            document.getElementById('addDesignForm').reset();
            document.getElementById('addImagePreview').style.display = 'none';
            
            if (this.bootstrapLoaded && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(document.getElementById('addDesignModal'));
                modal.show();
            } else {
                // Fallback
                const modalEl = document.getElementById('addDesignModal');
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
                document.body.classList.add('modal-open');
            }
        },
        
        // Open Edit Modal (Fixed with correct path)
        openEditModal: function(designId) {
            console.log('Opening edit modal for design ID:', designId);
            
            // Fix the path - use relative path from /app/pages/crud/
            fetch(`../../ajax/designs.php?action=get&id=${designId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received data:', data);
                    
                    if (data.success) {
                        const design = data.data;
                        
                        // Populate form
                        document.getElementById('editDesignId').value = design.designid;
                        document.getElementById('editName').value = design.name;
                        document.getElementById('editSku').value = design.sku || '';
                        document.getElementById('editCategory').value = design.category || 'parche';
                        document.getElementById('editEdition').value = design.edition || '';
                        document.getElementById('editPrice').value = design.unit_launch_price;
                        document.getElementById('editSize').value = design.size || '6cm';
                        document.getElementById('editStock').value = design.stock_quantity || 100;
                        document.getElementById('editDescription').value = design.description || '';
                        document.getElementById('editIsFeatured').checked = design.is_featured == 1;
                        document.getElementById('editIsActive').checked = design.is_active == 1;
                        
                        // Fix image path for display
                        if (design.image_path) {
                            let imagePath = design.image_path;
                            
                            // Handle different path formats
                            if (imagePath.startsWith('/app/assets/img/')) {
                                // Convert absolute to relative from /app/pages/crud/
                                imagePath = '../../assets/img/' + imagePath.split('/').pop();
                            }
                            
                            console.log('Setting image path:', imagePath);
                            document.getElementById('currentImage').src = imagePath;
                            
                            // Add error handler for image
                            document.getElementById('currentImage').onerror = function() {
                                console.error('Failed to load image:', this.src);
                                this.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIj48cmVjdCB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgZmlsbD0iI2U0ZjJlOSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzM0NDAzYSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==';
                            };
                        }
                        
                        // Show modal
                        if (this.bootstrapLoaded && typeof bootstrap !== 'undefined') {
                            const modal = new bootstrap.Modal(document.getElementById('editDesignModal'));
                            modal.show();
                        } else {
                            const modalEl = document.getElementById('editDesignModal');
                            modalEl.classList.add('show');
                            modalEl.style.display = 'block';
                            document.body.classList.add('modal-open');
                        }
                    } else {
                        console.error('Error response:', data);
                        this.showNotification(data.message || 'Error loading design data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error fetching design:', error);
                    this.showNotification('Error loading design data: ' + error.message, 'error');
                });
        },

        // Create Edit Modal
        createEditModal: function() {
            const modalHTML = `
                <div class="modal fade" id="editDesignModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content" style="border-radius: ${goldenRatio}rem; border: 2px solid #138a36;">
                            <div class="modal-header" style="background: linear-gradient(135deg, #34403a 0%, #138a36 100%); color: white;">
                                <h5 class="modal-title">
                                    <i class="fas fa-edit"></i> Edit Design
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="editDesignForm" enctype="multipart/form-data">
                                <input type="hidden" name="designid" id="editDesignId">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control input-golden" name="name" id="editName" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">SKU <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control input-golden" name="sku" id="editSku" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Category</label>
                                                <select class="form-select input-golden" name="category" id="editCategory">
                                                    <option value="parche">parche</option>
                                                    <option value="Parche">Parche</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Edition</label>
                                                <input type="text" class="form-control input-golden" name="edition" id="editEdition">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Launch Price (MXN)</label>
                                                <input type="number" class="form-control input-golden" name="unit_launch_price" id="editPrice" step="0.01" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Size</label>
                                                <input type="text" class="form-control input-golden" name="size" id="editSize">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Current Image</label>
                                                <div class="current-image-container mb-2 text-center">
                                                    <img id="currentImage" class="img-fluid rounded" style="max-height: 150px; border: 2px solid #138a36;">
                                                </div>
                                                <label class="form-label">New Image (optional)</label>
                                                <input type="file" class="form-control design-image" name="image" accept="image/*">
                                                <div class="image-preview-container mt-2 text-center">
                                                    <img id="editImagePreview" class="img-fluid rounded" style="display: none; max-height: 150px;">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control input-golden" name="description" id="editDescription" rows="3"></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Stock Quantity</label>
                                                <input type="number" class="form-control input-golden" name="stock_quantity" id="editStock">
                                            </div>
                                            
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="is_featured" id="editIsFeatured">
                                                <label class="form-check-label" for="editIsFeatured">
                                                    <i class="fas fa-star text-warning"></i> Feature in gallery
                                                </label>
                                            </div>
                                            
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive">
                                                <label class="form-check-label" for="editIsActive">
                                                    <i class="fas fa-check-circle text-success"></i> Active
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-forest-primary">
                                        <i class="fas fa-save"></i> Update Design
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Add form submit handler
            document.getElementById('editDesignForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleEditDesign(e.target);
            });
        },
        
        // Create Delete Modal
        createDeleteModal: function() {
            const modalHTML = `
                <div class="modal fade" id="deleteDesignModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="border-radius: ${goldenRatio}rem; border: 2px solid #dc3545;">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-exclamation-triangle"></i> Delete Design
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Are you sure you want to delete this design?</p>
                                <h5 class="mt-3 text-danger" id="deleteDesignName"></h5>
                                <p class="text-muted">This will also delete the associated image. This action cannot be undone.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        },
        
        // Create Image View Modal
        createImageViewModal: function() {
            const modalHTML = `
                <div class="modal fade" id="imageViewModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content" style="background: transparent; border: none;">
                            <div class="modal-header border-0">
                                <h5 class="modal-title text-white" id="imageViewTitle"></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="imageViewImg" class="img-fluid rounded" style="max-width: 100%; box-shadow: 0 10px 40px rgba(0,0,0,0.5);">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        },
        
        // Confirm Delete (Fixed)
        confirmDelete: function(designId, designName) {
            document.getElementById('deleteDesignName').textContent = designName;
            
            if (this.bootstrapLoaded && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(document.getElementById('deleteDesignModal'));
                modal.show();
                
                // Setup delete handler
                document.getElementById('confirmDeleteBtn').onclick = () => {
                    this.handleDeleteDesign(designId);
                    modal.hide();
                };
            } else {
                // Fallback confirmation
                if (confirm(`Are you sure you want to delete "${designName}"? This action cannot be undone.`)) {
                    this.handleDeleteDesign(designId);
                }
            }
        },
        
        // Handle Add Design (Fixed path)
        handleAddDesign: function(form) {
            const formData = new FormData(form);
            const skuPrefix = formData.get('sku_prefix');
            const skuSuffix = formData.get('sku_suffix');

            if (skuPrefix === 'CUSTOM') {
                // Use the suffix as the full SKU
                formData.set('sku', skuSuffix);
            } else {
                // Combine prefix and suffix
                formData.set('sku', skuPrefix + '-' + skuSuffix.padStart(4, '0'));
            }

            formData.delete('sku_prefix');
            formData.delete('sku_suffix');        

            formData.append('action', 'add');
            
            // Show loading
            this.showLoading();
            
            fetch('../../ajax/designs.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.hideLoading();
                
                if (data.success) {
                    this.showNotification('Design added successfully!', 'success');
                    
                    // Close modal
                    if (this.bootstrapLoaded && typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getInstance(document.getElementById('addDesignModal')).hide();
                    } else {
                        document.getElementById('addDesignModal').style.display = 'none';
                    }
                    
                    // Reload page
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showNotification(data.message || 'Error adding design', 'error');
                }
            })
            .catch(error => {
                this.hideLoading();
                console.error('Error:', error);
                this.showNotification('Error adding design', 'error');
            });
        },
        
        // Handle Edit Design (Fixed path)
        handleEditDesign: function(form) {
            const formData = new FormData(form);
            formData.append('action', 'edit');
            
            // Show loading
            this.showLoading();
            
            fetch('../../ajax/designs.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                this.hideLoading();
                
                if (data.success) {
                    this.showNotification('Design updated successfully!', 'success');
                    
                    // Close modal
                    if (this.bootstrapLoaded && typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getInstance(document.getElementById('editDesignModal')).hide();
                    } else {
                        document.getElementById('editDesignModal').style.display = 'none';
                    }
                    
                    // Reload page
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showNotification(data.message || 'Error updating design', 'error');
                }
            })
            .catch(error => {
                this.hideLoading();
                console.error('Error:', error);
                this.showNotification('Error updating design', 'error');
            });
        },
        
        // Handle Delete Design (Fixed path)
        handleDeleteDesign: function(designId) {
            this.showLoading();
            
            fetch('../../ajax/designs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&id=${designId}`
            })
            .then(response => response.json())
            .then(data => {
                this.hideLoading();
                
                if (data.success) {
                    this.showNotification('Design deleted successfully!', 'success');
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showNotification(data.message || 'Error deleting design', 'error');
                }
            })
            .catch(error => {
                this.hideLoading();
                console.error('Error:', error);
                this.showNotification('Error deleting design', 'error');
            });
        },
        
        // Toggle Featured Status (Fixed path and proper unfeature)
        toggleFeatured: function(designId) {
            console.log('Toggling featured status for design:', designId);
            
            fetch('../../ajax/designs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle_featured&id=${designId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification(data.message, 'success');
                    
                    // Reload page to update both button and featured_designs table
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showNotification(data.message || 'Error updating featured status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('Error updating featured status', 'error');
            });
        },
        
        // Preview Image
        previewImage: function(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewId = input.closest('.modal').id === 'addDesignModal' 
                        ? 'addImagePreview' 
                        : 'editImagePreview';
                    
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        },
        
        // Load Featured Designs (Fixed path)
        loadFeaturedDesigns: function() {
            fetch('../../ajax/designs.php?action=get_featured')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.featuredDesigns = data.data;
                        this.updateFeaturedUI();
                    }
                })
                .catch(error => console.error('Error loading featured designs:', error));
        },
        
        // Update Featured UI
        updateFeaturedUI: function() {
            // Update featured badges in table
            document.querySelectorAll('.toggle-featured-btn').forEach(btn => {
                const designId = btn.dataset.id;
                const isFeatured = this.featuredDesigns.some(d => d.design_id == designId);
                
                if (isFeatured) {
                    btn.classList.remove('btn-outline-warning');
                    btn.classList.add('btn-warning');
                    btn.querySelector('i')?.classList.replace('far', 'fas');
                } else {
                    btn.classList.remove('btn-warning');
                    btn.classList.add('btn-outline-warning');
                    btn.querySelector('i')?.classList.replace('fas', 'far');
                }
            });
        },
        
        // Show Loading
        showLoading: function() {
            if (!document.getElementById('loadingOverlay')) {
                const loadingHTML = `
                    <div id="loadingOverlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0,0,0,0.5);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        z-index: 9999;
                    ">
                        <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', loadingHTML);
            }
        },
        
        // Hide Loading
        hideLoading: function() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.remove();
            }
        },
        
        // Show Notification
        showNotification: function(message, type = 'info') {
            const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
            const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
            
            const notificationHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 80px; right: 20px; z-index: 9999; min-width: 300px;" 
                     role="alert">
                    <i class="fas fa-${icon}"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', notificationHTML);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                const alert = document.querySelector('.alert:last-child');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => GalleryManager.init());
    } else {
        GalleryManager.init();
    }
    
    // Export to global scope
    window.GalleryManager = GalleryManager;
})();

// CSS for forest theme
const style = document.createElement('style');
style.textContent = `
    .btn-forest-primary {
        background: linear-gradient(135deg, #138a36 0%, #138a36 100%);
        color: white;
        border: none;
    }
    .btn-forest-primary:hover {
        background: linear-gradient(135deg, #138a36 0%, #18ff6d 100%);
        color: white;
    }
    .input-golden {
        border-color: #138a36;
    }
    .input-golden:focus {
        border-color: #18ff6d;
        box-shadow: 0 0 0 0.2rem rgba(19, 138, 54, 0.25);
    }
`;
document.head.appendChild(style);

