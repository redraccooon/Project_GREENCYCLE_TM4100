document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');
    const formRegister = document.getElementById('form-register');
    const tabLogin = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');
    const alertBox = document.getElementById('alert-box');

    // Asignación de eventos a las pestañas
    if (tabLogin && tabRegister) {
        tabLogin.addEventListener('click', (e) => {
            e.preventDefault();
            switchTab('login');
        });

        tabRegister.addEventListener('click', (e) => {
            e.preventDefault();
            switchTab('register');
        });
    }

    // Envío de formularios
    if (formLogin) {
        formLogin.addEventListener('submit', (e) => handleAuth(e, '/api/login'));
    }

    if (formRegister) {
        formRegister.addEventListener('submit', (e) => handleAuth(e, '/api/register'));
    }

    function switchTab(tab) {
        hideAlert();

        if (tab === 'login') {
            // Muestra Login, oculta Registro
            formLogin.classList.remove('form-hidden');
            formRegister.classList.add('form-hidden');

            // Actualiza estilos de pestañas
            tabLogin.classList.add('tab-active');
            tabLogin.classList.remove('tab-inactive');
            tabRegister.classList.add('tab-inactive');
            tabRegister.classList.remove('tab-active');
        } else {
            // Muestra Registro, oculta Login
            formRegister.classList.remove('form-hidden');
            formLogin.classList.add('form-hidden');

            // Actualiza estilos de pestañas
            tabRegister.classList.add('tab-active');
            tabRegister.classList.remove('tab-inactive');
            tabLogin.classList.add('tab-inactive');
            tabLogin.classList.remove('tab-active');
        }
    }

    async function handleAuth(event, endpoint) {
        event.preventDefault();
        hideAlert();

        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                let errorMsg = result.message || 'Ocurrió un error inesperado.';
                if (result.errors) {
                    errorMsg = Object.values(result.errors).flat().join('<br>');
                }
                showAlert(errorMsg, 'error');
                return;
            }

            localStorage.setItem('greencycle_token', result.token);
            showAlert(`¡Bienvenido, ${result.user.name}! Monedas: ${result.user.green_coins} GreenCoins`, 'success');

        } catch (error) {
            showAlert('Error de conexión con el servidor.', 'error');
        }
    }

    function showAlert(message, type) {
        alertBox.className = 'alert-box';
        if (type === 'error') {
            alertBox.classList.add('alert-error');
        } else {
            alertBox.classList.add('alert-success');
        }
        alertBox.innerHTML = message;
    }

    function hideAlert() {
        alertBox.className = 'alert-box alert-hidden';
    }
});