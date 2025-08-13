// assets/js/script.js

// Fungsi untuk toggle antara halaman login dan register
function toggleAuthForm() {
    const loginContainer = document.querySelector('.login-container');
    const registerContainer = document.querySelector('.register-container');
    
    if (loginContainer && registerContainer) {
        if (loginContainer.classList.contains('d-none')) {
            loginContainer.classList.remove('d-none');
            registerContainer.classList.add('d-none');
        } else {
            loginContainer.classList.add('d-none');
            registerContainer.classList.remove('d-none');
        }
    }
}

// Tambahkan event listener untuk link login/register
document.addEventListener('DOMContentLoaded', function() {
    // Toggle form login/register
    const loginLinks = document.querySelectorAll('a[href="#login"]');
    const registerLinks = document.querySelectorAll('a[href="#register"]');
    
    loginLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            toggleAuthForm();
        });
    });
    
    registerLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            toggleAuthForm();
        });
    });
    
    // Toggle sidebar on mobile
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Confirm delete
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                form.submit();
            }
        });
    });
});

// Fungsi untuk menampilkan/menyembunyikan password
function togglePassword(fieldId, iconId) {
    const passwordField = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    
    if (passwordField && icon) {
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

// Fungsi untuk validasi form sederhana
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                
                // Tambahkan pesan error
                if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Field ini harus diisi';
                    field.parentNode.insertBefore(errorDiv, field.nextSibling);
                }
            } else {
                field.classList.remove('is-invalid');
                if (field.nextElementSibling && field.nextElementSibling.classList.contains('invalid-feedback')) {
                    field.nextElementSibling.remove();
                }
            }
        });
        
        return isValid;
    }
    return false;
}

// Fungsi untuk menangani submit form login
function handleLoginSubmit(event) {
    event.preventDefault();
    
    if (validateForm('loginForm')) {
        // Di sini nanti akan ada kode untuk mengirim data ke server
        console.log('Form login valid, mengirim data...');
        // Simulasi redirect ke dashboard
        // window.location.href = 'admin/index.php'; // atau halaman sesuai role
    }
}

// Fungsi untuk menangani submit form register
function handleRegisterSubmit(event) {
    event.preventDefault();
    
    if (validateForm('registerForm')) {
        // Di sini nanti akan ada kode untuk mengirim data ke server
        console.log('Form register valid, mengirim data...');
        // Simulasi redirect ke login setelah register
        // toggleAuthForm();
        // alert('Registrasi berhasil! Silakan login.');
    }
}

// Inisialisasi ketika DOM siap
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan event listener untuk form login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLoginSubmit);
    }
    
    // Tambahkan event listener untuk form register
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterSubmit);
    }
    
    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const fieldId = this.getAttribute('data-target');
            const iconId = this.querySelector('i').id;
            togglePassword(fieldId, iconId);
        });
    });
});