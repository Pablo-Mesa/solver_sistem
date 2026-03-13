
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formRegistro');
    const btn = document.getElementById('btnEnviar');
    const feedback = document.getElementById('mensajeFeedback');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Limpiar estado previo
        feedback.className = 'mensaje';
        feedback.style.display = 'none';
        btn.disabled = true;
        btn.innerText = 'Procesando...';

        const formData = new FormData(form);

        try {
            const response = await fetch('api/registrar.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Error en el servidor');
            
            const data = await response.json();

            feedback.innerText = data.mensaje;
            feedback.style.display = 'block';

            console.log('status -> ' +data.status);

            if (data.status === 'ok') {                
                feedback.classList.add('exito');
                form.reset();
            } else {
                feedback.classList.add('error');
            }
        } catch (error) {
            feedback.innerText = 'Error de conexión. Intente más tarde.';
            feedback.classList.add('error');
            feedback.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.innerText = 'Registrarse';
        }
    });
});