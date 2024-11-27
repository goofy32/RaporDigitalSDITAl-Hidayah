// resources/js/report-format.js

document.addEventListener("turbo:load", function() {
    initializeUploadForm();
});

async function validatePlaceholders(file) {
    const formData = new FormData();
    formData.append('template', file);
    
    try {
        const response = await fetch('/admin/format-rapor/validate-placeholders', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const result = await response.json();
        if (!result.valid) {
            const invalidPlaceholders = result.invalid_placeholders.join(', ');
            alert(`Template memiliki placeholder yang tidak valid: ${invalidPlaceholders}`);
            return false;
        }
        
        if (result.placeholders && result.placeholders.length === 0) {
            alert('Template tidak memiliki placeholder yang valid');
            return false;
        }
        
        return true;
    } catch (error) {
        console.error('Error validating template:', error);
        alert('Error validating template. Please try again.');
        return false;
    }
}


templateInput.addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const isValid = await validatePlaceholders(file);
    if (!isValid) {
        templateInput.value = '';
    }
});

function initializeUploadForm() {
    const uploadForm = document.querySelector('#uploadForm');
    const templateInput = document.querySelector('#template');
    
    if (uploadForm && templateInput) {
        templateInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('template', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            try {
                const response = await fetch('/admin/format-rapor/validate-placeholders', {
                    method: 'POST',
                    body: formData,
                });
                
                const result = await response.json();
                
                if (!result.valid) {
                    const invalidPlaceholders = result.invalid_placeholders.join(', ');
                    alert(`Template memiliki placeholder yang tidak valid: ${invalidPlaceholders}`);
                    templateInput.value = '';
                }
            } catch (error) {
                console.error('Error validating template:', error);
                alert('Error validating template. Please try again.');
            }
        });
    }
}