        let currentStep = 1;
        const totalSteps = 4;
        
        // File upload preview
        const fileInputs = ['photo', 'cnic_front', 'cnic_back', 'matric_result', 'inter_result'];
        
        fileInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            const label = document.getElementById(inputId + '-label');
            const preview = document.getElementById(inputId + '-preview');
            
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    label.classList.add('has-file');
                    label.textContent = '✓ ' + file.name;
                    
                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                    };
                    reader.readAsDataURL(file);
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
            document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'block';
            document.getElementById('nextBtn').style.display = step === totalSteps ? 'none' : 'block';
            document.getElementById('submitBtn').style.display = step === totalSteps ? 'block' : 'none';
            document.getElementById('cancelBtn').style.display = step === totalSteps ? 'inline-block' : 'none';
            
            // Generate review content on last step
            if (step === totalSteps) {
                generateReview();
            }
            
            // Scroll to top
            window.scrollTo(0, 0);
        }
        
        function validateStep(step) {
            const currentStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
            const inputs = currentStepElement.querySelectorAll('input[required], select[required], textarea[required]');
            
            for (let input of inputs) {
                if (input.type !== 'checkbox' && !input.value) {
                    alert('Please fill in all required fields');
                    input.focus();
                    return false;
                }
                
                if (input.type === 'number') {
                    const value = parseFloat(input.value);
                    const min = parseFloat(input.min);
                    const max = parseFloat(input.max);
                    
                    if (value < min || value > max) {
                        alert(`${input.previousElementSibling.textContent} must be between ${min} and ${max}`);
                        input.focus();
                        return false;
                    }
                }
                
                if (input.pattern && !new RegExp(input.pattern).test(input.value)) {
                    alert(`Please enter a valid ${input.previousElementSibling.textContent}`);
                    input.focus();
                    return false;
                }
            }
            
            return true;
        }
        
        function generateReview() {
            const formData = new FormData(document.getElementById('updateForm'));
            let html = '<div style="background: #f5f5f5; padding: 20px; border-radius: 5px;">';
            
            html += '<h4>Personal Information</h4>';
            html += `<p><strong>Father's Name:</strong> ${formData.get('father_name')}</p>`;
            html += `<p><strong>Mother's Name:</strong> ${formData.get('mother_name')}</p>`;
            html += `<p><strong>Date of Birth:</strong> ${formData.get('dob')}</p>`;
            html += `<p><strong>CNIC:</strong> ${formData.get('cnic')}</p>`;
            html += `<p><strong>Phone:</strong> ${formData.get('phone')}</p>`;
            html += `<p><strong>Address:</strong> ${formData.get('address')}, ${formData.get('city')}, ${formData.get('province')}</p>`;
            
            html += '<h4 style="margin-top: 20px;">Academic Information</h4>';
            html += `<p><strong>Matric Marks:</strong> ${formData.get('matric_marks')} / 1100</p>`;
            html += `<p><strong>Matric Board:</strong> ${formData.get('matric_board')} (${formData.get('matric_year')})</p>`;
            html += `<p><strong>Intermediate Marks:</strong> ${formData.get('inter_marks')} / 1100</p>`;
            html += `<p><strong>Intermediate Board:</strong> ${formData.get('inter_board')} (${formData.get('inter_year')})</p>`;
            html += `<p><strong>Program Choice:</strong> ${formData.get('program_choice')}</p>`;
            
            const matricMarks = parseFloat(formData.get('matric_marks'));
            const interMarks = parseFloat(formData.get('inter_marks'));
            const meritScore = ((0.5 * matricMarks / 11) + (0.5 * interMarks / 11)).toFixed(2);
            html += `<p><strong>Updated Merit Score:</strong> ${meritScore}</p>`;
            
            html += '<h4 style="margin-top: 20px;">Documents Status</h4>';
            
            const photoFile = document.getElementById('photo').files[0];
            html += `<p>📷 Photo: ${photoFile ? '✓ New file uploaded: ' + photoFile.name : '✓ Keeping existing file'}</p>`;
            
            const cnicFrontFile = document.getElementById('cnic_front').files[0];
            html += `<p>🪪 CNIC Front: ${cnicFrontFile ? '✓ New file uploaded: ' + cnicFrontFile.name : '✓ Keeping existing file'}</p>`;
            
            const cnicBackFile = document.getElementById('cnic_back').files[0];
            html += `<p>🪪 CNIC Back: ${cnicBackFile ? '✓ New file uploaded: ' + cnicBackFile.name : '✓ Keeping existing file'}</p>`;
            
            const matricResultFile = document.getElementById('matric_result').files[0];
            html += `<p>📄 Matric Result: ${matricResultFile ? '✓ New file uploaded: ' + matricResultFile.name : '✓ Keeping existing file'}</p>`;
            
            const interResultFile = document.getElementById('inter_result').files[0];
            html += `<p>📄 Inter Result: ${interResultFile ? '✓ New file uploaded: ' + interResultFile.name : '✓ Keeping existing file'}</p>`;
            
            html += '</div>';
            
            document.getElementById('review-content').innerHTML = html;
        }
        
        // Form submission validation
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            const matricMarks = parseFloat(document.querySelector('input[name="matric_marks"]').value);
            const interMarks = parseFloat(document.querySelector('input[name="inter_marks"]').value);
            
            if (matricMarks < 0 || matricMarks > 1100) {
                e.preventDefault();
                alert('Matric marks must be between 0 and 1100');
                return;
            }
            
            if (interMarks < 0 || interMarks > 1100) {
                e.preventDefault();
                alert('Intermediate marks must be between 0 and 1100');
                return;
            }
            
            // Confirm submission
            if (!confirm('Are you sure you want to update your application? This will recalculate your merit score.')) {
                e.preventDefault();
                return;
            }
        });
        
        // CNIC formatting
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