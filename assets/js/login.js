async function login(username, password) {
    const res = await fetch('./endpoints/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    });

    const data = await res.json();

    if (data.status === 'ok') {
        localStorage.setItem('user', data.user);
        localStorage.setItem('token', data.token);
    }

    return data;
}

function logout(clearSession = false) {
    if (clearSession) {
        localStorage.removeItem('user');
        localStorage.removeItem('token');
    }
    updateUI();
}

function updateUI() {
    const user = localStorage.getItem('user');
    const el = document.getElementById('sessionInfo');
    if (!el) return;

    el.innerHTML = user ? `Logeado como <b>${user}</b>` : 'No logeado';

    const btnLogin = document.getElementById('btnLogin');
    const btnLogout = document.getElementById('btnLogout');

    if (btnLogin) btnLogin.disabled = !!user;
    if (btnLogout) btnLogout.disabled = !user;
}

function log(title, data = '') {
    const logEl = document.getElementById('log');
    if (!logEl) return;

    logEl.innerHTML += `[${new Date().toLocaleTimeString()}] ${title}\n`;

    if (data) {
        logEl.innerHTML += (typeof data === 'object'
            ? JSON.stringify(data, null, 2)
            : data) + "\n";
    }

    logEl.innerHTML += "\n";
    logEl.scrollTop = logEl.scrollHeight;
}

async function validateSession() {
    const token = localStorage.getItem('token');
    if (!token) return logout(true);

    try {
        const res = await fetch('./endpoints/validate_token.php', {
            headers: { 'Authorization': `Bearer ${token}` }
        });

        const data = await res.json();

        if (data.status !== 'ok') {
            return logout(true);
        }
    } catch {
        return logout(true);
    }

    updateUI();
}

document.body.addEventListener('click', async (e) => {

    if (e.target.id === 'btnLogin') {
        const username = document.getElementById('user')?.value?.trim();
        const password = document.getElementById('pass')?.value?.trim();

        if (!username || !password) {
            log('ERROR', 'Usuario y contraseña requeridos');
            return;
        }

        log('--- LOGIN START ---');

        const btn = e.target;
        btn.disabled = true;

        try {
            const data = await login(username, password);

            if (data.status === 'ok') {
                log('LOGIN OK', data);
            } else {
                log('LOGIN FAIL', data.message);
            }

            updateUI();

        } catch (err) {
            log('LOGIN ERROR', err.message || err);
            updateUI();
        }

        log('--- LOGIN END ---');
    }

    if (e.target.id === 'btnTest') {
        log('--- TEST START ---');

        const token = localStorage.getItem('token');

        if (!token) {
            log('TEST ERROR', 'No autenticado');
            return;
        }

        try {
            const res = await fetch('./endpoints/me.php', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            const data = await res.json();

            log('TEST OK', data);

        } catch (e) {
            log('TEST ERROR', e.message);
        }

        log('--- TEST END ---');
    }

    if (e.target.id === 'btnLogout') {
        logout(true);
    }
});