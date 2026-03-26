async function verificarSesion() {
    try {
        const response = await fetch('/red-social-usac/persona1-autenticacion/php/check-session.php');
        const data = await response.json();
        
        if (data.autenticado) {
            console.log('✅ Usuario autenticado:', data.usuario);
            return true;
        } else {
            console.log('❌ No hay sesión activa');
            return false;
        }
    } catch (error) {
        console.error('Error:', error);
        return false;
    }
}