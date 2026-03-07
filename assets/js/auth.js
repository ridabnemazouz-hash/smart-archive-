document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const authAlert = document.getElementById('authAlert');

    const showAlert = (message, type = 'danger') => {
        authAlert.textContent = message;
        authAlert.className = `alert alert-${type} mt-3`;
        authAlert.style.display = 'block';
    };

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    body: formData
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error("Server response was not JSON:", text);
                    showAlert("Server Error: " + text.substring(0, 100));
                    return;
                }

                if (result.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    showAlert(result.message);
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.');
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn.innerHTML;

            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

                const response = await fetch('api/register.php', {
                    method: 'POST',
                    body: formData
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error("Server response was not JSON:", text);
                    showAlert("Server Error: Please contact support. (Invalid Response Format)");
                    return;
                }

                if (result.success) {
                    showAlert(result.message, 'success');
                    registerForm.reset();
                    setTimeout(() => {
                        if (typeof toggleAuth === 'function') toggleAuth();
                    }, 2000);
                } else {
                    showAlert(result.message);
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.');
                console.error('Registration error:', error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        });
    }
});

function togglePasswordVisibility(inputId, iconSpan) {
    const input = document.getElementById(inputId);
    const icon = iconSpan.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function checkPasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');

    if (!password) {
        strengthBar.style.width = '0%';
        strengthText.classList.add('d-none');
        return;
    }

    strengthText.classList.remove('d-none');

    let strength = 0;
    if (password.length >= 6) strength += 25;
    if (password.match(/[a-z]+/)) strength += 25;
    if (password.match(/[A-Z]+/)) strength += 25;
    if (password.match(/[0-9]+/) || password.match(/[\W_]+/)) strength += 25;

    strengthBar.style.width = strength + '%';

    strengthBar.className = 'progress-bar transition-all';
    if (strength <= 25) {
        strengthBar.classList.add('bg-danger');
        strengthText.textContent = 'Weak';
        strengthText.className = 'text-danger ms-2';
    } else if (strength <= 50) {
        strengthBar.classList.add('bg-warning');
        strengthText.textContent = 'Fair';
        strengthText.className = 'text-warning ms-2';
    } else if (strength <= 75) {
        strengthBar.classList.add('bg-info');
        strengthText.textContent = 'Good';
        strengthText.className = 'text-info ms-2';
    } else {
        strengthBar.classList.add('bg-success');
        strengthText.textContent = 'Strong';
        strengthText.className = 'text-success ms-2';
    }
}

function showForgotPassword() {
    const authAlert = document.getElementById('authAlert');
    if (authAlert) {
        authAlert.innerHTML = '<i class="bi bi-info-circle me-2"></i> Please contact your administrator to reset your password.';
        authAlert.className = 'alert alert-info mt-3 text-center';
        authAlert.style.display = 'block';
    }
}
