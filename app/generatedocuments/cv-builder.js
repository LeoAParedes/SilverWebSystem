
class CVBuilder {
    constructor() {
        this.goldenRatio = 1.618;
        this.educationCount = 0;
        this.experienceCount = 0;
        this.languageCount = 0;
        this.selectedTemplate = 'golden';
        this.selectedColor = 'forest';
        
        this.init();
    }
    
    init() {
        this.loadSavedData();
        this.setupEventListeners();
        this.updateCompleteness();
	this.loadPhotoPreview();
	 this.setupModalListeners();	    
}


setupModalListeners() {
    const modal = document.getElementById('cvPreviewModal');
    
    if (modal) {
        // Clean up on modal hide
        modal.addEventListener('hidden.bs.modal', () => {
            this.removeAllBackdrops();
        });
        
        // Prevent multiple backdrops
        modal.addEventListener('show.bs.modal', (event) => {
            // Remove any existing backdrops first
            this.removeAllBackdrops();
        });
        
        // Fix close button
        const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                this.closeModal();
            });
        });
    }
}

    
    setupEventListeners() {
        // Template selection
        const templateSelect = document.getElementById('cv_template');
        if (templateSelect) {
            templateSelect.addEventListener('change', (e) => {
                this.selectedTemplate = e.target.value;
            });
        }
        
        // Color scheme selection
        document.querySelectorAll('.color-option').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.color-option').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.selectedColor = e.target.dataset.color;
            });
        });
        
        // Auto-save on input
        document.querySelectorAll('#cvForm input, #cvForm textarea, #cvForm select').forEach(field => {
            field.addEventListener('input', () => {
                this.autoSave();
                this.updateCompleteness();
            });
        });
    }
    
    addEducation() {
        const id = ++this.educationCount;
        const html = `
            <div class="education-item mb-3" data-id="${id}">
                <div class="card">
                    <div class="card-body">
                        <button type="button" class="btn-close float-end" onclick="window.cvBuilder.removeItem('education', ${id})"></button>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label-cv">Degree/Certification</label>
                                <input type="text" class="form-control" placeholder="e.g., Bachelor of Computer Science" id="edu_degree_${id}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label-cv">Institution</label>
                                <input type="text" class="form-control" placeholder="e.g., UABC" id="edu_institution_${id}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label-cv">Start Date</label>
                                <input type="text" class="form-control" placeholder="e.g., August 2020" id="edu_start_${id}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label-cv">End Date</label>
                                <input type="text" class="form-control" placeholder="e.g., May 2024" id="edu_end_${id}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label-cv">Description</label>
                                <textarea class="form-control" placeholder="Key achievements, relevant coursework, GPA..." rows="2" id="edu_desc_${id}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const container = document.getElementById('educationList');
        if (container) {
            container.insertAdjacentHTML('beforeend', html);
            this.setupNewItemListeners(id, 'education');
        }
    }
    
    addExperience() {
        const id = ++this.experienceCount;
        const html = `
            <div class="experience-item mb-3" data-id="${id}">
                <div class="card">
                    <div class="card-body">
                        <button type="button" class="btn-close float-end" onclick="window.cvBuilder.removeItem('experience', ${id})"></button>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label-cv">Job Title</label>
                                <input type="text" class="form-control" placeholder="e.g., Full Stack Developer" id="exp_title_${id}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label-cv">Company</label>
                                <input type="text" class="form-control" placeholder="e.g., Metro Recycling" id="exp_company_${id}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label-cv">Start Date</label>
                                <input type="text" class="form-control" placeholder="e.g., June 2023" id="exp_start_${id}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label-cv">End Date</label>
                                <input type="text" class="form-control" placeholder="e.g., Present" id="exp_end_${id}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label-cv">Responsibilities & Achievements</label>
                                <textarea class="form-control" placeholder="• Developed web applications&#10;• Led team of 3 developers&#10;• Improved system efficiency by 40%" rows="3" id="exp_desc_${id}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const container = document.getElementById('experienceList');
        if (container) {
            container.insertAdjacentHTML('beforeend', html);
            this.setupNewItemListeners(id, 'experience');
        }
    }
    
    addLanguage() {
        const id = ++this.languageCount;
        const html = `
            <div class="language-item mb-3" data-id="${id}">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <label class="form-label-cv">Language</label>
                        <input type="text" class="form-control" placeholder="e.g., English" id="lang_name_${id}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label-cv">Level</label>
                        <select class="form-select" id="lang_level_${id}">
                            <option value="">Select Level</option>
                            <option value="Native">Native</option>
                            <option value="Fluent">Fluent</option>
                            <option value="Advanced">Advanced</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Basic">Basic</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="window.cvBuilder.removeItem('language', ${id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        const container = document.getElementById('languageList');
        if (container) {
            container.insertAdjacentHTML('beforeend', html);
            this.setupNewItemListeners(id, 'language');
        }
    }
    
    setupNewItemListeners(id, type) {
        const prefix = type.substring(0, 3) + '_';
        document.querySelectorAll(`[id^="${prefix}"][id$="_${id}"]`).forEach(field => {
            field.addEventListener('input', () => {
                this.autoSave();
                this.updateCompleteness();
            });
        });
    }
    
    removeItem(type, id) {
        const item = document.querySelector(`.${type}-item[data-id="${id}"]`);
        if (item) {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-100%)';
            item.style.transition = 'all 0.382s ease';
            setTimeout(() => item.remove(), 382);
        }
        this.autoSave();
        this.updateCompleteness();
    }
    collectFormData() {
    const data = {
        personal: {
            fullname: document.getElementById('cv_fullname')?.value || '',
            title: document.getElementById('cv_title')?.value || '',
            email: document.getElementById('cv_email')?.value || '',
            phone: document.getElementById('cv_phone')?.value || '',
            address: document.getElementById('cv_address')?.value || '',
            summary: document.getElementById('cv_summary')?.value || '',
            photo: localStorage.getItem('cv_photo_data') || null // Add photo data
        },
        education: [],
        experience: [],
        skills: {
            technical: document.getElementById('cv_technical_skills')?.value || '',
            soft: document.getElementById('cv_soft_skills')?.value || ''
        },
        languages: [],
        template: this.selectedTemplate,
        color: this.selectedColor
    };
    
    // Collect education items
    document.querySelectorAll('.education-item').forEach(item => {
        const id = item.dataset.id;
        data.education.push({
            degree: document.getElementById(`edu_degree_${id}`)?.value || '',
            institution: document.getElementById(`edu_institution_${id}`)?.value || '',
            start: document.getElementById(`edu_start_${id}`)?.value || '',
            end: document.getElementById(`edu_end_${id}`)?.value || '',
            description: document.getElementById(`edu_desc_${id}`)?.value || ''
        });
    });
    
    // Collect experience items
    document.querySelectorAll('.experience-item').forEach(item => {
        const id = item.dataset.id;
        data.experience.push({
            title: document.getElementById(`exp_title_${id}`)?.value || '',
            company: document.getElementById(`exp_company_${id}`)?.value || '',
            start: document.getElementById(`exp_start_${id}`)?.value || '',
            end: document.getElementById(`exp_end_${id}`)?.value || '',
            description: document.getElementById(`exp_desc_${id}`)?.value || ''
        });
    });
    
    // Collect language items
    document.querySelectorAll('.language-item').forEach(item => {
        const id = item.dataset.id;
        const name = document.getElementById(`lang_name_${id}`)?.value || '';
        const level = document.getElementById(`lang_level_${id}`)?.value || '';
        if (name || level) { // Changed to OR to save even partial data
            data.languages.push({ name, level });
        }
    });
    
    return data;
}

previewPDF() {
    const data = this.collectFormData();
    
    if (!data.personal.fullname) {
        alert('Please enter your name before generating preview');
        return;
    }
    
    // Add preview flag to data
    data.preview = true;
    
    // First make the fetch request WITHOUT showing modal
    fetch('generatedocuments/generate-cv-pdf.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(result => {
        // Only show modal if we have successful data
        if (result.success && result.pdfData) {
            const previewContent = document.getElementById('cvPreviewContent');
            if (previewContent) {
                previewContent.innerHTML = `
                    <div style="width: 100%; height: 600px; position: relative;">
                        <iframe src="${result.pdfData}" 
                               type="application/pdf" 
                               width="100%" 
                               height="100%" 
                               style="border: none; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <p>Your browser does not support PDFs. 
                               <a href="${result.pdfData}" download="CV_Preview.pdf">Download the PDF</a> instead.
                            </p>
                        </iframe>
                    </div>
                `;
                
                // NOW show the modal after content is ready
                const modal = document.getElementById('cvPreviewModal');
                const modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                modalInstance.show();
            }
        } else if (result.error) {
            // Show error without modal
            alert('Error generating PDF: ' + result.error);
            console.error('PDF Error:', result.error);
        } else {
            // Fallback to HTML preview
            this.previewHTML();
        }
    })
    .catch(error => {
        console.error('Error generating PDF preview:', error);
        // Show simple alert instead of modal
        alert('Failed to generate PDF preview. Please check if TCPDF is installed.');
    });
}

previewHTML() {
    const data = this.collectFormData();
    const previewHTML = this.generatePreviewHTML(data);
    
    const previewContent = document.getElementById('cvPreviewContent');
    if (previewContent) {
        previewContent.innerHTML = previewHTML;
        
        // Show modal only after content is ready
        const modal = document.getElementById('cvPreviewModal');
        const modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
        modalInstance.show();
    }
}

// Add this new function to close modal properly
closeModal() {
    const modal = document.getElementById('cvPreviewModal');
    const modalInstance = bootstrap.Modal.getInstance(modal);
    
    if (modalInstance) {
        modalInstance.hide();
    }
    
    // Force remove any orphaned backdrops
    this.removeAllBackdrops();
}

// Add this helper function to remove all backdrops
removeAllBackdrops() {
    // Remove all modal backdrops
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    
    // Remove modal-open class from body
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
}



previewPDFNewTab() {
    const data = this.collectFormData();
    
    if (!data.personal.fullname) {
        alert('Please enter your name before generating preview');
        return;
    }
    
    // Show loading message
    const loadingWindow = window.open('', '_blank');
    loadingWindow.document.write(`
        <html>
        <head>
            <title>Generating PDF...</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
            <div class="container text-center mt-5">
                <div class="spinner-border text-success" style="width: 3rem; height: 3rem;"></div>
                <p class="mt-3">Generating your CV PDF...</p>
            </div>
        </body>
        </html>
    `);
    
    // Add preview flag
    data.preview = true;
    
    // Send request to generate PDF
    fetch('generatedocuments/generate-cv-pdf.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success && result.pdfData) {
            // Replace loading window content with PDF
            loadingWindow.location.href = result.pdfData;
        } else {
            // Show error in the window
            loadingWindow.document.body.innerHTML = `
                <div class="container mt-5">
                    <div class="alert alert-danger">
                        <h4>Error Generating PDF</h4>
                        <p>${result.error || 'Unknown error occurred'}</p>
                        <button class="btn btn-secondary" onclick="window.close()">Close</button>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loadingWindow.document.body.innerHTML = `
            <div class="container mt-5">
                <div class="alert alert-danger">
                    <h4>Connection Error</h4>
                    <p>${error.message}</p>
                    <button class="btn btn-secondary" onclick="window.close()">Close</button>
                </div>
            </div>
        `;
    });
}








previewHTML() {
    const data = this.collectFormData();
    const previewHTML = this.generatePreviewHTML(data);
    
    const previewContent = document.getElementById('cvPreviewContent');
    if (previewContent) {
        previewContent.innerHTML = previewHTML + `
            <div class="text-center mt-3">
                <button class="btn btn-success" onclick="window.cvBuilder.generatePDF()">
                    <i class="fas fa-download"></i> Download PDF
                </button>
                <button class="btn btn-primary" onclick="window.cvBuilder.previewPDF()">
                    <i class="fas fa-file-pdf"></i> PDF Preview
                </button>
                <button class="btn btn-info" onclick="window.cvBuilder.previewPDFNewTab()">
                    <i class="fas fa-external-link-alt"></i> Open PDF in New Tab
                </button>
            </div>
        `;
        const modal = new bootstrap.Modal(document.getElementById('cvPreviewModal'));
        modal.show();
    }
}




    generatePreviewHTML(data) {
        const colorScheme = this.getColorScheme(data.color);
        
        return `
            <div class="cv-preview" style="font-family: 'Arial', sans-serif; max-width: 800px; margin: 0 auto; padding: 40px; background: white;">
                <!-- Header -->
                <div style="border-bottom: 3px solid ${colorScheme.primary}; padding-bottom: 20px; margin-bottom: 30px;">
                    <h1 style="color: ${colorScheme.primary}; margin: 0; font-size: 2.5em;">${data.personal.fullname || 'Your Name'}</h1>
                    <h2 style="color: ${colorScheme.secondary}; margin: 10px 0; font-size: 1.5em;">${data.personal.title || 'Professional Title'}</h2>
                    <div style="color: #666; margin-top: 15px;">
                        ${data.personal.email ? `<span style="margin-right: 20px;"><i class="fas fa-envelope"></i> ${data.personal.email}</span>` : ''}
                        ${data.personal.phone ? `<span style="margin-right: 20px;"><i class="fas fa-phone"></i> ${data.personal.phone}</span>` : ''}
                        ${data.personal.address ? `<span><i class="fas fa-map-marker-alt"></i> ${data.personal.address}</span>` : ''}
                    </div>
                </div>
                
                ${data.personal.summary ? `
                <div style="margin-bottom: 30px;">
                    <h3 style="color: ${colorScheme.primary}; border-bottom: 2px solid ${colorScheme.light}; padding-bottom: 5px;">Professional Summary</h3>
                    <p style="color: #333; line-height: 1.6;">${data.personal.summary}</p>
                </div>
                ` : ''}
                
                ${data.experience.length > 0 ? `
                <div style="margin-bottom: 30px;">
                    <h3 style="color: ${colorScheme.primary}; border-bottom: 2px solid ${colorScheme.light}; padding-bottom: 5px;">Work Experience</h3>
                    ${data.experience.map(exp => `
                        <div style="margin-bottom: 20px;">
                            <h4 style="color: ${colorScheme.secondary}; margin: 10px 0;">${exp.title}${exp.company ? ` at ${exp.company}` : ''}</h4>
                            <p style="color: #666; font-style: italic;">${exp.start}${exp.end ? ` - ${exp.end}` : ''}</p>
                            ${exp.description ? `<p style="color: #333; line-height: 1.6; white-space: pre-line;">${exp.description}</p>` : ''}
                        </div>
                    `).join('')}
                </div>
                ` : ''}
                
                ${data.education.length > 0 ? `
                <div style="margin-bottom: 30px;">
                    <h3 style="color: ${colorScheme.primary}; border-bottom: 2px solid ${colorScheme.light}; padding-bottom: 5px;">Education</h3>
                    ${data.education.map(edu => `
                        <div style="margin-bottom: 20px;">
                            <h4 style="color: ${colorScheme.secondary}; margin: 10px 0;">${edu.degree}</h4>
                            <p style="color: #666; font-style: italic;">${edu.institution}${edu.start || edu.end ? ` | ${edu.start}${edu.end ? ` - ${edu.end}` : ''}` : ''}</p>
                            ${edu.description ? `<p style="color: #333; line-height: 1.6;">${edu.description}</p>` : ''}
                        </div>
                    `).join('')}
                </div>
                ` : ''}
                
                <div style="display: flex; gap: 40px;">
                    ${data.skills.technical || data.skills.soft ? `
                    <div style="flex: 1;">
                        <h3 style="color: ${colorScheme.primary}; border-bottom: 2px solid ${colorScheme.light}; padding-bottom: 5px;">Skills</h3>
                        ${data.skills.technical ? `
                            <h4 style="color: ${colorScheme.secondary};">Technical Skills</h4>
                            <p style="color: #333; line-height: 1.6; white-space: pre-line;">${data.skills.technical}</p>
                        ` : ''}
                        ${data.skills.soft ? `
                            <h4 style="color: ${colorScheme.secondary};">Soft Skills</h4>
                            <p style="color: #333; line-height: 1.6; white-space: pre-line;">${data.skills.soft}</p>
                        ` : ''}
                    </div>
                    ` : ''}
                    
                    ${data.languages.length > 0 ? `
                    <div style="flex: 0.5;">
                        <h3 style="color: ${colorScheme.primary}; border-bottom: 2px solid ${colorScheme.light}; padding-bottom: 5px;">Languages</h3>
                        ${data.languages.map(lang => `
                            <p style="color: #333; margin: 5px 0;">
                                <strong>${lang.name}</strong> - ${lang.level}
                            </p>
                        `).join('')}
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    
    getColorScheme(color) {
        const schemes = {
            forest: {
                primary: '#138a36',
                secondary: '#18ff6d',
                light: '#e4f2e9'
            },
            spring: {
                primary: '#18ff6d',
                secondary: '#138a36',
                light: '#e4f2e9'
            },
            olive: {
                primary: '#34403a',
                secondary: '#b4d0bf',
                light: '#e4f2e9'
            },
            gray: {
                primary: '#34403a',
                secondary: '#b4d0bf',
                light: '#f0f0f0'
            },
	     darkforest: { 
            primary: '#133c23',
            secondary: '#505050',
            light: '#f5f5f5',
	    text: '#191919' 
        }   
     };
        
        return schemes[color] || schemes.forest;
    }
    
    generatePDF() {
        const data = this.collectFormData();
        
        if (!data.personal.fullname) {
            alert('Please enter your name before generating PDF');
            return;
        }
        
        // Show loading
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Generating PDF',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Send data to PHP handler
        fetch('generatedocuments/generate-cv-pdf.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `CV_${data.personal.fullname.replace(/\s/g, '_')}_${new Date().getTime()}.pdf`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'PDF Generated!',
                    text: 'Your CV has been downloaded successfully.'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to generate PDF. Please try again.'
                });
            } else {
                alert('Failed to generate PDF. Please try again.');
            }
        });
    }
    
    autoSave() {
        const data = this.collectFormData();
        localStorage.setItem('cv_data', JSON.stringify(data));
    }
    
    loadSavedData() {
    const saved = localStorage.getItem('cv_data');
    if (saved) {
        try {
            const data = JSON.parse(saved);
            
            // Load personal info
            if (data.personal) {
                Object.keys(data.personal).forEach(key => {
                    if (key !== 'photo') { // Don't try to load photo into a field
                        const field = document.getElementById(`cv_${key}`);
                        if (field) field.value = data.personal[key];
                    }
                });
            }
            
            // Load skills
            if (data.skills) {
                const techField = document.getElementById('cv_technical_skills');
                const softField = document.getElementById('cv_soft_skills');
                if (techField && data.skills.technical) {
                    techField.value = data.skills.technical;
                }
                if (softField && data.skills.soft) {
                    softField.value = data.skills.soft;
                }
            }
            
            // Load education
            if (data.education && data.education.length > 0) {
                data.education.forEach(edu => {
                    if (edu.degree || edu.institution) { // Only add if has content
                        this.addEducation();
                        const id = this.educationCount;
                        Object.keys(edu).forEach(key => {
                            const field = document.getElementById(`edu_${key}_${id}`);
                            if (field) field.value = edu[key];
                        });
                    }
                });
            }
            
            // Load experience
            if (data.experience && data.experience.length > 0) {
                data.experience.forEach(exp => {
                    if (exp.title || exp.company) { // Only add if has content
                        this.addExperience();
                        const id = this.experienceCount;
                        Object.keys(exp).forEach(key => {
                            const field = document.getElementById(`exp_${key}_${id}`);
                            if (field) field.value = exp[key];
                        });
                    }
                });
            }
            
            // Load languages
            if (data.languages && data.languages.length > 0) {
                data.languages.forEach(lang => {
                    if (lang.name) { // Only add if has name
                        this.addLanguage();
                        const id = this.languageCount;
                        document.getElementById(`lang_name_${id}`).value = lang.name;
                        document.getElementById(`lang_level_${id}`).value = lang.level;
                    }
                });
            }
            
            // Load template and color
            if (data.template) {
                const templateField = document.getElementById('cv_template');
                if (templateField) templateField.value = data.template;
                this.selectedTemplate = data.template;
            }
            
            if (data.color) {
                this.selectedColor = data.color;
                document.querySelectorAll('.color-option').forEach(btn => {
                    btn.classList.remove('active');
                    if (btn.dataset.color === data.color) {
                        btn.classList.add('active');
                    }
                });
            }
        } catch (e) {
            console.error('Error loading saved data:', e);
        }
    }
}

    saveTemplate() {
        const data = this.collectFormData();
        const templateName = prompt('Enter template name:');
        
        if (templateName) {
            const templates = JSON.parse(localStorage.getItem('cv_templates') || '{}');
            templates[templateName] = data;
            localStorage.setItem('cv_templates', JSON.stringify(templates));
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Template Saved!',
                    text: `Template "${templateName}" has been saved.`
                });
            } else {
                alert(`Template "${templateName}" has been saved.`);
            }
        }
    }
    
    loadTemplate() {
        const templates = JSON.parse(localStorage.getItem('cv_templates') || '{}');
        const templateNames = Object.keys(templates);
        
        if (templateNames.length === 0) {
            alert('No saved templates found.');
            return;
        }
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Select Template',
                input: 'select',
                inputOptions: Object.fromEntries(templateNames.map(name => [name, name])),
                inputPlaceholder: 'Select a template',
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const data = templates[result.value];
                    this.loadTemplateData(data);
                }
            });
        } else {
            const templateName = prompt('Enter template name to load:\n' + templateNames.join(', '));
            if (templateName && templates[templateName]) {
                this.loadTemplateData(templates[templateName]);
            }
        }
    }
    
    loadTemplateData(data) {
        // Clear existing dynamic fields
        document.getElementById('educationList').innerHTML = '';
        document.getElementById('experienceList').innerHTML = '';
        document.getElementById('languageList').innerHTML = '';
        
        // Reset counters
        this.educationCount = 0;
        this.experienceCount = 0;
        this.languageCount = 0;
        
        // Load personal info
        if (data.personal) {
            Object.keys(data.personal).forEach(key => {
                const field = document.getElementById(`cv_${key}`);
                if (field) field.value = data.personal[key];
            });
        }
        
        // Load skills
        if (data.skills) {
            const techField = document.getElementById('cv_technical_skills');
            const softField = document.getElementById('cv_soft_skills');
            if (techField && data.skills.technical) {
                techField.value = data.skills.technical;
            }
            if (softField && data.skills.soft) {
                softField.value = data.skills.soft;
            }
        }
        
        // Reload education
        if (data.education) {
            data.education.forEach(edu => {
                this.addEducation();
                const id = this.educationCount;
                Object.keys(edu).forEach(key => {
                    const field = document.getElementById(`edu_${key}_${id}`);
                    if (field) field.value = edu[key];
                });
            });
        }
        
        // Reload experience
        if (data.experience) {
            data.experience.forEach(exp => {
                this.addExperience();
                const id = this.experienceCount;
                Object.keys(exp).forEach(key => {
                    const field = document.getElementById(`exp_${key}_${id}`);
                    if (field) field.value = exp[key];
                });
            });
        }
        
        // Reload languages
        if (data.languages) {
            data.languages.forEach(lang => {
                this.addLanguage();
                const id = this.languageCount;
                document.getElementById(`lang_name_${id}`).value = lang.name;
                document.getElementById(`lang_level_${id}`).value = lang.level;
            });
        }
        
        this.autoSave();
        this.updateCompleteness();
    }
    
    updateCompleteness() {
        const data = this.collectFormData();
        let total = 8; // Base fields to check
        let filled = 0;
        
        // Check personal info
        if (data.personal.fullname) filled++;
        if (data.personal.title) filled++;
        if (data.personal.email) filled++;
        if (data.personal.phone) filled++;
        
        // Check other sections
        if (data.personal.summary) filled++;
        if (data.education.length > 0) filled++;
        if (data.experience.length > 0) filled++;
        if (data.skills.technical || data.skills.soft) filled++;
        
        const percentage = Math.round((filled / total) * 100);
        const progressBar = document.getElementById('cvCompleteness');
        if (progressBar) {
            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';
            
            // Change color based on completeness
            progressBar.className = 'progress-bar';
            if (percentage < 33) {
                progressBar.classList.add('bg-danger');
            } else if (percentage < 66) {
                progressBar.classList.add('bg-warning');
            } else {
                progressBar.classList.add('bg-success');
            }
        }
    }

	handlePhotoUpload(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Check file size (2MB max)
        if (file.size > 2097152) {
            alert('Photo size must be less than 2MB');
            input.value = '';
            return;
        }
        
        // Check file type
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
            alert('Please upload only JPG or PNG images');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            // Store photo data in localStorage
            localStorage.setItem('cv_photo_data', e.target.result);
            
            // Show preview
            document.getElementById('photoPreviewImg').src = e.target.result;
            document.getElementById('photoPreviewImg').style.display = 'block';
            document.getElementById('photoPlaceholder').style.display = 'none';
            
            this.autoSave();
        };
        reader.readAsDataURL(file);
    }
}

removePhoto() {
    localStorage.removeItem('cv_photo_data');
    document.getElementById('photoPreviewImg').src = '';
    document.getElementById('photoPreviewImg').style.display = 'none';
    document.getElementById('photoPlaceholder').style.display = 'block';
    document.getElementById('cv_photo').value = '';
    this.autoSave();
}

loadPhotoPreview() {
    const photoData = localStorage.getItem('cv_photo_data');
    if (photoData) {
        document.getElementById('photoPreviewImg').src = photoData;
        document.getElementById('photoPreviewImg').style.display = 'block';
        document.getElementById('photoPlaceholder').style.display = 'none';
    }
}


}





// Initialize CV Builder when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.cvBuilder = new CVBuilder();
    });
} else {
    window.cvBuilder = new CVBuilder();
}


