const dashboard = (() => {

    const state = {
        account: null,
        stats: null,
        characters: []
    };

    const init = async () => {
        const token = localStorage.getItem('token');

        if (!token) {
            renderError('No autenticado');
            return;
        }

        try {
            const res = await fetch('./endpoints/account.php', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            if (!res.ok) throw new Error('HTTP error');

            const data = await res.json();

            if (data.status !== 'success') {
                throw new Error(data.message || 'Error API');
            }

            state.account = data.data.account;
            state.stats = data.data.stats;
            state.characters = data.data.characters;

            render();

        } catch (err) {
            renderError(err.message);
        }
    };

    const render = () => {

        document.getElementById('acc-username').textContent = state.account.username;
        document.getElementById('acc-email').textContent = state.account.email;
        document.getElementById('acc-guid').textContent = state.account.guid;

        document.getElementById('stat-characters').textContent = state.characters.length;
        document.getElementById('stat-lastlogin').textContent = state.stats.last_login || '-';

        document.querySelector('.account-name').textContent = state.account.username;
        document.querySelector('.account-meta').textContent = state.stats.last_login || 'Sin login';

        renderCharacters();
    };

    const renderCharacters = () => {
        const grid = document.getElementById('char-grid');
        const template = document.getElementById('char-template');

        grid.innerHTML = '';

        state.characters.forEach(char => {
            const clone = template.content.cloneNode(true);

            clone.querySelector('.char-name').textContent = char.name;
            clone.querySelector('.char-class').textContent = char.class;
            clone.querySelector('.char-level').textContent = `Level ${char.level}`;
            clone.querySelector('.char-resets').textContent = `Resets: ${char.resets}`;
            clone.querySelector('.char-map').textContent = char.map;

            const avatar = clone.querySelector('.char-avatar');
            avatar.src = char.avatar;

            const progress = clone.querySelector('.progress-bar');
            progress.style.width = `${char.progress}%`;

            const pk = clone.querySelector('.pk-badge');
            pk.textContent = char.pk;

            pk.classList.remove('hero','commoner','outlaw','murderer');

            if (char.pk_class.includes('hero')) pk.classList.add('hero');
            else if (char.pk_class.includes('normal')) pk.classList.add('commoner');
            else if (char.pk_class.includes('warning')) pk.classList.add('outlaw');
            else if (char.pk_class.includes('danger')) pk.classList.add('murderer');

            grid.appendChild(clone);
        });
    };

    const renderError = (msg) => {
        const container = document.getElementById('app');

        container.innerHTML = `
            <div class="container mt-4 text-danger">
                ${msg}
            </div>
        `;
    };

    return { init };

})();

window.dashboard = dashboard;