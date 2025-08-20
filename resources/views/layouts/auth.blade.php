<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Auth') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #02178fe0, #021fc4e0);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
            width: 100%;
            max-width: 500px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #021fc4e0, #021fc4e0);
        }

        .brand-name {
            color: #021fc4e0;
            font-weight: 700;
            margin: 0;
            font-size: 1.8rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #021fc4e0;
            box-shadow: 0 0 0 0.2rem rgba(18, 3, 150, 0.25);
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:hover,
        .form-select:hover {
            border-color: #021fc4e0;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .btn-danger {
            background: linear-gradient(45deg, #021fc4e0, #021fc4e0);
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: linear-gradient(45deg, #d60808, #e93030);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(235, 9, 9, 0.4);
        }

        .btn-outline-secondary {
            border-color: #6c757d;
            color: #6c757d;
            border-radius: 8px;
            font-size: 0.875rem;
            padding: 8px 16px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            transform: translateY(-1px);
        }

        a {
            color: #c40202e0;
            transition: color 0.3s ease;
            font-weight: 500;
        }

        a:hover {
            color: #021fc4e0;
        }

        .alert {
            border-radius: 10px;
            border: none;
            font-weight: 500;
        }

        .alert-success {
            background: linear-gradient(45deg, #d4edda, #c3e6cb);
            color: #155724;
        }

        .alert-info {
            background: linear-gradient(45deg, #d1ecf1, #bee5eb);
            color: #0c5460;
        }

        .alert-warning {
            background: linear-gradient(45deg, #fff3cd, #ffeaa7);
            color: #856404;
        }

        .alert-danger {
            background: linear-gradient(45deg, #f8d7da, #f5c6cb);
            color: #721c24;
        }

        .input-group-text {
            background: #f8f9fa;
            border-color: #ddd;
            color: #6c757d;
            border-radius: 8px 0 0 8px;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }

        .form-control-plaintext {
            border-radius: 8px;
            color: #333;
            font-weight: 500;
        }

        .invalid-feedback {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .form-check-input:checked {
            background-color: #eb0909ff;
            border-color: #eb0909ff;
        }

        .form-check-input:focus {
            border-color: #eb0909ff;
            box-shadow: 0 0 0 0.25rem rgba(235, 9, 9, 0.25);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-loading {
            position: relative;
            color: transparent;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .auth-card {
                padding: 2rem 1.5rem;
                border-radius: 12px;
            }
            
            .brand-name {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="auth-card">
        {{-- Brand/Logo opcional --}}
        <div class="text-center mb-4">
            <h2 class="brand-name">{{ config('app.name') }}</h2>
        </div>
        
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(function(alert) {
                    if (alert && typeof bootstrap !== 'undefined') {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);

            // Form validation enhancements
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                const inputs = form.querySelectorAll('input[required], select[required]');
                
                inputs.forEach(function(input) {
                    input.addEventListener('blur', function() {
                        validateField(input);
                    });
                    
                    input.addEventListener('focus', function() {
                        clearFieldValidation(input);
                    });
                });
            });

            // Form submission loading states
            const submitForms = document.querySelectorAll('form');
            submitForms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.classList.add('btn-loading');
                        submitBtn.disabled = true;
                        
                        const originalText = submitBtn.innerHTML;
                        submitBtn.setAttribute('data-original-text', originalText);
                        
                        setTimeout(function() {
                            if (!form.checkValidity()) {
                                submitBtn.classList.remove('btn-loading');
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
                            }
                        }, 100);
                    }
                });
            });
        });

        function validateField(field) {
            const value = field.value.trim();
            const type = field.type;
            const name = field.name;
            
            clearFieldValidation(field);
            
            if (field.hasAttribute('required') && !value) {
                showFieldError(field, 'Este campo es obligatorio');
                return false;
            }
            
            if (type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    showFieldError(field, 'Ingresa un correo válido');
                    return false;
                }
            }
            
            if (name === 'password' && value && value.length < 8) {
                showFieldError(field, 'Mínimo 8 caracteres');
                return false;
            }
            
            if (name === 'password_confirmation' && value) {
                const passwordField = document.querySelector('input[name="password"]');
                if (passwordField && passwordField.value !== value) {
                    showFieldError(field, 'Las contraseñas no coinciden');
                    return false;
                }
            }
            
            showFieldSuccess(field);
            return true;
        }

        function showFieldError(field, message) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            
            let feedback = field.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                field.parentNode.appendChild(feedback);
            }
            feedback.textContent = message;
        }

        function showFieldSuccess(field) {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
            
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        }

        function clearFieldValidation(field) {
            field.classList.remove('is-invalid', 'is-valid');
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        }
    </script>
</body>

</html>