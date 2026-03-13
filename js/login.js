document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formLogin');
    const btn = document.getElementById('btnEntrar');
    const feedback = document.getElementById('mensajeFeedback');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        btn.disabled = true;
        btn.innerText = 'Verificando...';
        feedback.style.display = 'none';

        const formData = new FormData(form);

        try {
            const response = await fetch('api/login.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.status === 'ok') {
                feedback.className = 'mensaje exito';
                feedback.innerText = data.mensaje;
                feedback.style.display = 'block';
                // Redirigir después de 1.5 segundos
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                feedback.className = 'mensaje error';
                feedback.innerText = data.mensaje;
                feedback.style.display = 'block';
                btn.disabled = false;
                btn.innerText = 'Entrar';
            }
        } catch (error) {
            feedback.className = 'mensaje error';
            feedback.innerText = 'Error de conexión';
            feedback.style.display = 'block';
            btn.disabled = false;
        }
    });
});