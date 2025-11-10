        let currentStep = 1;
        const totalSteps = 4;
        
        // File upload handling
        const fileInputs = ['photo', 'cnic_front', 'cnic_back', 'matric_result', 'inter_result'];
        
        fileInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            const card = document.getElementById('card-' + inputId);
            const info = document.getElementById('info-' + inputId);
            const preview = document.getElementById('preview-' + inputId);
            
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Update card appearance
                    card.classList.add('has-file');
                    
                    // Add success checkmark
                    if (!card.querySelector('.file-status')) {
                        const status = document.createElement('div');
                        status.className = 'file-status';
                        status.textContent = '✓';
                        card.appendChild(status);
                    }
                    
                    // Show file info
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    info.innerHTML = `<strong>${file.name}</strong><br>Size: ${fileSize} MB`;
                    
                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = '<img src="' + e.target.result + '" class="preview-image" alt="Preview">';
                    };
                    reader.readAsDataURL(file);
                    
                    // Add animation
                    card.style.animation = 'fadeIn 0.5s';
                }
            });
        });
        
        // Step navigation
        document.getElementById('nextBtn').addEventListener('click', function() {
            if (validateStep(currentStep)) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        });
        
        document.getElementById('prevBtn').addEventListener('click', function() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });
        
        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
            
            // Show current step
            document.querySelector(`.form-step[data-step="${step}"]`).classList.add('active');
            
            // Update step indicator
            document.querySelectorAll('.step').forEach((s, index) => {
                s.classList.remove('active', 'completed');
                if (index + 1 < step) {
                    s.classList.add('completed');
                } else if (index + 1 === step) {
                    s.classList.add('active');
                }
            });
            
            // Update buttons
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            
            prevBtn.style.display = step === 1 ? 'none' : 'block';
            nextBtn.style.display = step === totalSteps ? 'none' : 'block';
            submitBtn.style.display = step === totalSteps ? 'block' : 'none';
            cancelBtn.style.display = step === totalSteps ? 'inline-block' : 'none';
            
            // Generate review content on last step
            if (step === totalSteps) {
                generateReview();
            }
            
            // Scroll to top smoothly
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        function validateStep(step) {
            const currentStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
            const inputs = currentStepElement.querySelectorAll('input[required]:not([type="checkbox"]), select[required], textarea[required]');
            
            for (let input of inputs) {
                if (!input.value) {
                    alert('⚠️ Please fill in all required fields');
                    input.focus();
                    input.style.borderColor = '#f44336';
                    setTimeout(() => { input.style.borderColor = ''; }, 2000);
                    return false;
                }
                
                if (input.type === 'number') {
                    const value = parseFloat(input.value);
                    const min = parseFloat(input.min);
                    const max = parseFloat(input.max);
                    
                    if (value < min || value > max) {
                        alert(`⚠️ ${input.previousElementSibling.textContent} must be between ${min} and ${max}`);
                        input.focus();
                        input.style.borderColor = '#f44336';
                        setTimeout(() => { input.style.borderColor = ''; }, 2000);
                        return false;
                    }
                }
                
                if (input.pattern && !new RegExp(input.pattern).test(input.value)) {
                    alert(`⚠️ Please enter a valid ${input.previousElementSibling.textContent}`);
                    input.focus();
                    input.style.borderColor = '#f44336';
                    setTimeout(() => { input.style.borderColor = ''; }, 2000);
                    return false;
                }
            }
            
            return true;
        }
        
        function generateReview() {
            const formData = new FormData(document.getElementById('updateForm'));
            
            // Personal Information
            let personalHtml = '';
            personalHtml += `<div class="review-item"><span class="review-label">Father's Name:</span><span class="review-value">${formData.get('father_name')}</span></div>`;
            personalHtml += `<div class="review-item"><span class="review-label">Mother's Name:</span><span class="review-value">${formData.get('mother_name')}</span></div>`;
            personalHtml += `<div class="review-item"><span class="review-label">Date of Birth:</span><span class="review-value">${formData.get('dob')}</span></div>`;
            personalHtml += `<div class="review-item"><span class="review-label">CNIC:</span><span class="review-value">${formData.get('cnic')}</span></div>`;
            personalHtml += `<div class="review-item"><span class="review-label">Phone:</span><span class="review-value">${formData.get('phone')}</span></div>`;
            personalHtml += `<div class="review-item"><span class="review-label">Address:</span><span class="review-value">${formData.get('address')}</span></div>`;
            personalHtml += `<div class="review-item"><span class="review-label">City:</span><span class="review-value">${formData.get('city')}</span></div>`;
            personalHtml += `<div class="review-item"><span class="review-label">Province:</span><span class="review-value">${formData.get('province')}</span></div>`;
            document.getElementById('review-personal').innerHTML = personalHtml;
            
            // Academic Information
            let academicHtml = '';
            academicHtml += `<div class="review-item"><span class="review-label">Program Level:</span><span class="review-value">${formData.get('program_level')}</span></div>`;
            academicHtml += `<div class="review-item"><span class="review-label">Program Choice:</span><span class="review-value">${formData.get('program_choice')}</span></div>`;
            academicHtml += `<div class="review-item"><span class="review-label">Matric Marks:</span><span class="review-value">${formData.get('matric_marks')} / 1100</span></div>`;
            academicHtml += `<div class="review-item"><span class="review-label">Matric Board:</span><span class="review-value">${formData.get('matric_board')}</span></div>`;
            academicHtml += `<div class="review-item"><span class="review-label">Matric Year:</span><span class="review-value">${formData.get('matric_year')}</span></div>`;
            academicHtml += `<div class="review-item"><span class="review-label">Intermediate Marks:</span><span class="review-value">${formData.get('inter_marks')} / 1100</span></div>`;
            academicHtml += `<div class="review-item"><span class="review-label">Intermediate Board:</span><span class="review-value">${formData.get('inter_board')}</span></div>`;
            academicHtml += `<div class="review-item"><span class="review-label">Intermediate Year:</span><span class="review-value">${formData.get('inter_year')}</span></div>`;
            document.getElementById('review-academic').innerHTML = academicHtml;
            
            // Calculate and display merit score
            const matricMarks = parseFloat(formData.get('matric_marks'));
            const interMarks = parseFloat(formData.get('inter_marks'));
            const meritScore = ((0.5 * matricMarks / 11) + (0.5 * interMarks / 11)).toFixed(2);
            document.getElementById('merit-score-display').textContent = meritScore;
            
            // Documents Status
            let documentsHtml = '';
            const docFiles = {
                'photo': '📷 Passport Photo',
                'cnic_front': '🪪 CNIC Front',
                'cnic_back': '🪪 CNIC Back',
                'matric_result': '📜 Matric Result',
                'inter_result': '📜 Intermediate Result'
            };
            
            for (let [id, label] of Object.entries(docFiles)) {
                const file = document.getElementById(id).files[0];
                if (file) {
                    documentsHtml += `<div class="review-item"><span class="review-label">${label}:</span><span class="review-value" style="color: #4CAF50;">✓ New file uploaded</span></div>`;
                } else {
                    documentsHtml += `<div class="review-item"><span class="review-label">${label}:</span><span class="review-value" style="color: #2196F3;">✓ Keeping existing file</span></div>`;
                }
            }
            document.getElementById('review-documents').innerHTML = documentsHtml;
        }
        
        // Form submission validation
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            const matricMarks = parseFloat(document.querySelector('input[name="matric_marks"]').value);
            const interMarks = parseFloat(document.querySelector('input[name="inter_marks"]').value);
            
            if (matricMarks < 0 || matricMarks > 1100) {
                e.preventDefault();
                alert('⚠️ Matric marks must be between 0 and 1100');
                return;
            }
            
            if (interMarks < 0 || interMarks > 1100) {
                e.preventDefault();
                alert('⚠️ Intermediate marks must be between 0 and 1100');
                return;
            }
            
            // Confirm submission with beautiful dialog
            if (!confirm('🎓 Are you sure you want to update your application?\n\nThis will recalculate your merit score and update all your information.')) {
                e.preventDefault();
                return;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.textContent = '⏳ Updating...';
            submitBtn.disabled = true;
        });
        
        // CNIC auto-formatting
        document.querySelector('input[name="cnic"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.slice(0, 5) + '-' + value.slice(5);
            }
            if (value.length > 13) {
                value = value.slice(0, 13) + '-' + value.slice(13);
            }
            e.target.value = value.slice(0, 15);
        });
        
        // Add smooth transitions
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'scale(1.02)';
            });
            input.addEventListener('blur', function() {
                this.style.transform = 'scale(1)';
            });
        });