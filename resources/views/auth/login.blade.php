<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCycle - Acceso API</title>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100vh;
            width: 100vw;
            overflow-x: hidden;
        }

        body.auth-wrapper {
            background-color: #030712 !important;
            color: #f3f4f6;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 1.5rem;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background-color: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            overflow: hidden;
        }

        .tabs-header {
            display: flex;
            background-color: #020617;
            border-bottom: 1px solid #1e293b;
        }

        .tab-btn {
            flex: 1;
            padding: 1rem;
            font-size: 0.95rem;
            font-weight: 600;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .tab-active {
            color: #10b981 !important;
            border-bottom: 2px solid #10b981 !important;
            background-color: #0f172a;
        }

        .tab-inactive {
            color: #6b7280 !important;
            border-bottom: 2px solid transparent !important;
        }

        .tab-inactive:hover {
            color: #d1d5db !important;
        }

        .card-body {
            padding: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-hidden {
            display: none !important;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #e5e7eb;
        }

        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: #1e293b !important;
            color: #ffffff !important;
            border: 1px solid #374151;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        .input-field::placeholder {
            color: #9ca3af;
        }

        .input-field:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            background-color: #111827 !important;
        }

        .input-field:-webkit-autofill,
        .input-field:-webkit-autofill:hover, 
        .input-field:-webkit-autofill:focus {
            -webkit-text-fill-color: #ffffff !important;
            -webkit-box-shadow: 0 0 0px 1000px #1e293b inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .btn-primary {
            width: 100%;
            padding: 0.85rem;
            margin-top: 0.5rem;
            background-color: #059669;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            transition: background-color 0.2s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #10b981;
        }

        .alert-box {
            padding: 0.85rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
        }

        .alert-hidden {
            display: none;
        }

        .alert-error {
            background-color: rgba(225, 29, 72, 0.15);
            border: 1px solid rgba(225, 29, 72, 0.3);
            color: #fda4af;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }
    </style>
</head>
<body class="auth-wrapper">

    <main class="auth-card">
        
        <header class="tabs-header">
            <button id="tab-login" class="tab-btn tab-active" type="button">
                Iniciar Sesión
            </button>
            <button id="tab-register" class="tab-btn tab-inactive" type="button">
                Registrarse
            </button>
        </header>

        <section class="card-body">

            <div id="alert-box" class="alert-box alert-hidden"></div>

            <!-- FORMULARIO DE LOGIN -->
            <form id="form-login" class="form-group">
                <div class="input-group">
                    <label for="login-email">Correo Electrónico</label>
                    <input type="email" id="login-email" name="email" required placeholder="tu@ejemplo.com" class="input-field" autocomplete="email">
                </div>

                <div class="input-group">
                    <label for="login-password">Contraseña</label>
                    <input type="password" id="login-password" name="password" required placeholder="••••••••" class="input-field" autocomplete="current-password">
                </div>

                <button type="submit" class="btn-primary">
                    Entrar al Vivero
                </button>
            </form>

            <!-- FORMULARIO DE REGISTRO -->
            <form id="form-register" class="form-group form-hidden">
                <div class="input-group">
                    <label for="reg-name">Nombre Completo</label>
                    <input type="text" id="reg-name" name="name" required placeholder="Juan Pérez" class="input-field" autocomplete="name">
                </div>

                <div class="input-group">
                    <label for="reg-email">Correo Electrónico</label>
                    <input type="email" id="reg-email" name="email" required placeholder="tu@ejemplo.com" class="input-field" autocomplete="email">
                </div>

                <div class="input-group">
                    <label for="reg-password">Contraseña</label>
                    <input type="password" id="reg-password" name="password" required placeholder="Mínimo 8 caracteres" class="input-field" autocomplete="new-password">
                </div>

                <div class="input-group">
                    <label for="reg-password-confirm">Confirmar Contraseña</label>
                    <input type="password" id="reg-password-confirm" name="password_confirmation" required placeholder="••••••••" class="input-field" autocomplete="new-password">
                </div>

                <button type="submit" class="btn-primary">
                    Crear Cuenta y Recibir GreenCoins
                </button>
            </form>

        </section>
    </main>

    <script src="{{ asset('app.js') }}"></script>
</body>
</html>