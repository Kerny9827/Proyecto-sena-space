document.addEventListener('DOMContentLoaded', async function () {
    try {
        const response = await fetch('session_info.php', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            window.location.href = 'Login.html';
            return;
        }

        const data = await response.json();
        if (!data.success) {
            window.location.href = 'Login.html';
            return;
        }

        const name = data.usuario || 'Usuario';
        const userNameTop = document.getElementById('userNameTop');
        if (userNameTop) {
            userNameTop.textContent = name;
        }

        const welcomeUser = document.getElementById('welcomeUser');
        if (welcomeUser) {
            welcomeUser.innerHTML = `Hola <b>${name}</b>, esperamos que tengas un excelente día.`;
        }
    } catch (error) {
        console.error('No se pudo cargar la sesión:', error);
        window.location.href = 'Login.html';
    }
});
