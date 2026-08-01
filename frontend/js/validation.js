function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    if (!phone) return false;
    var cleaned = phone.replace(/[\s\-()]/g, '');
    if (cleaned.startsWith('+233')) {
        return cleaned.length >= 12 && cleaned.length <= 13;
    }
    if (cleaned.startsWith('0')) {
        return cleaned.length >= 10 && cleaned.length <= 11;
    }
    return false;
}

function validatePassword(password) {
    if (!password || password.length < 6) return { valid: false, message: 'Password must be at least 6 characters' };
    var strength = 0;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    return { valid: true, strength: strength, message: strength < 2 ? 'Weak password' : strength < 3 ? 'Fair password' : 'Strong password' };
}

function validateRequired(value) {
    return value !== null && value !== undefined && String(value).trim() !== '';
}

function validateAddress(form) {
    var errors = [];
    if (!validateRequired(form.fullName)) errors.push('Full name is required');
    if (!validateRequired(form.phone)) errors.push('Phone number is required');
    else if (!validatePhone(form.phone)) errors.push('Please enter a valid phone number');
    if (!validateRequired(form.address)) errors.push('Street address is required');
    if (!validateRequired(form.city)) errors.push('City is required');
    if (!validateRequired(form.region)) errors.push('Region is required');
    return errors;
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('field-error');
    var errorEl = document.createElement('div');
    errorEl.className = 'field-error-message';
    errorEl.textContent = message;
    errorEl.setAttribute('role', 'alert');
    field.parentNode.appendChild(errorEl);
}

function clearFieldError(field) {
    field.classList.remove('field-error');
    var parent = field.parentNode;
    var existing = parent.querySelector('.field-error-message');
    if (existing) existing.remove();
}

function clearAllFieldErrors(form) {
    var fields = form.querySelectorAll('.field-error');
    fields.forEach(function (f) { clearFieldError(f); });
}

function setupRealTimeValidation(formId) {
    var form = document.getElementById(formId);
    if (!form) return;

    var inputs = form.querySelectorAll('input[required], select[required]');
    inputs.forEach(function (input) {
        input.addEventListener('blur', function () {
            if (!validateRequired(this.value)) {
                showFieldError(this, 'This field is required');
            } else {
                clearFieldError(this);
            }
        });

        input.addEventListener('input', function () {
            if (this.classList.contains('field-error') && validateRequired(this.value)) {
                clearFieldError(this);
            }
        });
    });

    var emailInput = form.querySelector('input[type="email"]');
    if (emailInput) {
        emailInput.addEventListener('blur', function () {
            if (this.value && !validateEmail(this.value)) {
                showFieldError(this, 'Please enter a valid email');
            }
        });
    }

    var phoneInput = form.querySelector('input[type="tel"]');
    if (phoneInput) {
        phoneInput.addEventListener('blur', function () {
            if (this.value && !validatePhone(this.value)) {
                showFieldError(this, 'Please enter a valid phone number');
            }
        });
    }
}
