document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');
    const formRegister = document.getElementById('form-register');
    const alertBox = document.getElementById('alert-box');

    // Cambios visuales de la interfaz
    document.getElementById('tab-login')?.addEventListener('click', () => toggleTabs('login'));
    document.getElementById('tab-register')?.addEventListener('click', () => toggleTabs('register'));

    // Captura de eventos para enviar a la API
    formLogin?.addEventListener('submit', (e) => sendAuthRequest(e, '/api/login'));
    formRegister?.addEventListener('submit', (e) => sendAuthRequest(e, '/api/register'));

    function toggleTabs(activeTab) {
        hideAlert();
        if (activeTab === 'login') {
            formLogin.classList.remove('form-hidden');
            formRegister.classList.add('form-hidden');
        } else {
            formRegister.classList.remove('form-hidden');
            formLogin.classList.add('form-hidden');
        }
    }

    async function sendAuthRequest(event, endpoint) {
        event.preventDefault();
        hideAlert();

        // 1. Obtener datos del formulario de la interfaz
        const formData = new FormData(event.target);
        const payload = Object.fromEntries(formData.entries());

        try {
            // 2. Consumir el controlador de Laravel (AuthController)
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            // 3. Modificar la interfaz con la respuesta que envió AuthController
            if (!response.ok) {
                const message = data.errors 
                    ? Object.values(data.errors).flat().join('<br>') 
                    : (data.message || 'Error en la petición');
                showAlert(message, 'error');
                return;
            }

            // Guardar token e informar al usuario en la vista
            localStorage.setItem('greencycle_token', data.token);
            showAlert(`¡Hola, ${data.user.name}! Tienes ${data.user.green_coins} GreenCoins.`, 'success');

        } catch (error) {
            showAlert('No se pudo conectar con el servidor.', 'error');
        }
    }

    function showAlert(message, type) {
        alertBox.className = `alert-box alert-${type}`;
        alertBox.innerHTML = message;
    }

    function hideAlert() {
        alertBox.className = 'alert-box alert-hidden';
    }
});