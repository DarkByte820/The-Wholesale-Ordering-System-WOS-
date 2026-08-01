async function login(email, password) {
    var response = await API.login(email, password);
    if (response.success) {
        var token = response.token || response.data?.token || response.data?.Token;
        var user = response.user || response.data?.user || response.data?.User || response.data;
        if (token) localStorage.setItem('token', token);
        if (user) localStorage.setItem('user', JSON.stringify(user));
        showAlert('Login successful!', 'success');
        setTimeout(function () {
            var role = user ? (user.Role || user.role) : null;
            var roleLower = role ? role.toLowerCase() : '';
            if (roleLower === 'warehouse_admin' || roleLower === 'system_admin') {
                window.location.href = 'admin/dashboard.html';
            } else if (roleLower === 'delivery_personnel') {
                window.location.href = 'delivery/dashboard.html';
            } else {
                window.location.href = 'profile.html';
            }
        }, 1000);
        return true;
    } else {
        showAlert(response.message || 'Login failed. Please check your credentials.', 'error');
        return false;
    }
}

async function register(data) {
    var response = await API.register(data);
    if (response.success) {
        showAlert('Registration successful! Please login.', 'success');
        setTimeout(function () {
            window.location.href = 'login.html';
        }, 1500);
        return true;
    } else {
        showAlert(response.message || 'Registration failed. Please try again.', 'error');
        return false;
    }
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        showAlert('Logged out successfully', 'success');
        setTimeout(function () {
            window.location.href = 'index.html';
        }, 500);
    }
}

function requireAuth() {
    if (!isLoggedIn()) {
        showAlert('Please login to continue', 'error');
        setTimeout(function () {
            window.location.href = 'login.html';
        }, 1000);
        return false;
    }
    return true;
}

function setupLoginForm() {
    var form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var email = document.getElementById('loginEmail').value.trim();
        var password = document.getElementById('loginPassword').value;

        if (!email || !password) {
            showAlert('Please fill in all fields', 'error');
            return;
        }

        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Logging in...';

        await login(email, password);

        btn.disabled = false;
        btn.textContent = 'Login';
    });
}

function setupRegisterForm() {
    var form = document.getElementById('registerForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        var name = document.getElementById('regName').value.trim();
        var email = document.getElementById('regEmail').value.trim();
        var phone = document.getElementById('regPhone').value.trim();
        var password = document.getElementById('regPassword').value;
        var confirmPassword = document.getElementById('regConfirmPassword').value;

        if (!name || !email || !password) {
            showAlert('Please fill in all required fields', 'error');
            return;
        }

        if (!validateEmail(email)) {
            showAlert('Please enter a valid email address', 'error');
            return;
        }

        if (password.length < 6) {
            showAlert('Password must be at least 6 characters', 'error');
            return;
        }

        if (password !== confirmPassword) {
            showAlert('Passwords do not match', 'error');
            return;
        }

        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Creating account...';

        var role = document.getElementById('regRole') ? document.getElementById('regRole').value : 'bundle_customer';

        await register({ name: name, email: email, phone: phone, password: password, role: role });

        btn.disabled = false;
        btn.textContent = 'Create Account';
    });
}
