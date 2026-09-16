const API_URL = 'http://localhost:3000';
let isRegisterMode = false;
let filmesData = [];

function showScreen(screenId) {
    document.getElementById('auth-screen').classList.add('hidden');
    document.getElementById('main-screen').classList.add('hidden');
    document.getElementById(screenId).classList.remove('hidden');
}

function toggleAuthMode() {
    isRegisterMode = !isRegisterMode;
    const title = document.getElementById('auth-title');
    const btn = document.getElementById('auth-btn');
    const toggleText = document.getElementById('auth-toggle-text');
    const toggleBtn = document.getElementById('auth-toggle-btn');

    if (isRegisterMode) {
        title.textContent = 'Criar Nova Conta';
        btn.textContent = 'Cadastrar';
        toggleText.textContent = 'Já possui uma conta?';
        toggleBtn.textContent = 'Faça Login';
    } else {
        title.textContent = 'Entrar na Conta';
        btn.textContent = 'Entrar';
        toggleText.textContent = 'Não tem uma conta?';
        toggleBtn.textContent = 'Cadastre-se';
    }
}

async function handleAuthSubmit(event) {
    event.preventDefault();
    const email = document.getElementById('auth-email').value;
    const senha = document.getElementById('auth-senha').value;
    const endpoint = isRegisterMode ? '/usuarios' : '/login';

    try {
        const response = await fetch(`${API_URL}${endpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, senha })
        });

        const data = await response.json();

        if (!response.ok) {
            alert(data.error || (data.errors ? data.errors.join('\n') : 'Erro na requisição.'));
            return;
        }

        if (isRegisterMode) {
            alert('Usuário cadastrado com sucesso! Faça login para continuar.');
            toggleAuthMode();
        } else {
            document.getElementById('user-display').textContent = data.usuario.email;
            showScreen('main-screen');
            iniciarTelaBusca();
        }
    } catch (error) {
        alert('Erro de conexão com o servidor.');
    }
}

function logout() {
    document.getElementById('auth-form').reset();
    showScreen('auth-screen');
}

async function iniciarTelaBusca() {
    await carregarCategorias();
    await carregarFilmes();
}

async function carregarCategorias() {
    const select = document.getElementById('categoria-select');
    try {
        const response = await fetch(`${API_URL}/categorias`);
        const categorias = await response.json();

        if (response.ok && Array.isArray(categorias)) {
            select.innerHTML = '<option value="">Todas as Categorias</option>' +
                categorias.map(c => `<option value="${c.nome}">${c.nome}</option>`).join('');
        }
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
    }
}

async function carregarFilmes() {
    const container = document.getElementById('filmes-container');
    try {
        const response = await fetch(`${API_URL}/filmes`);
        filmesData = await response.json();

        if (!response.ok) throw new Error(filmesData.error || 'Erro ao carregar');

        renderizarFilmes(filmesData);
    } catch (error) {
        container.innerHTML = `<p style="color: #ff5555;">Erro ao carregar filmes: ${error.message}</p>`;
    }
}

function filtrarFilmes() {
    const termoBusca = document.getElementById('search-input').value.toLowerCase();
    const categoriaSelecionada = document.getElementById('categoria-select').value;

    const filmesFiltrados = filmesData.filter(filme => {
        const combinaCategoria = categoriaSelecionada === '' || filme.categoria === categoriaSelecionada;
        const combinaTexto = filme.titulo.toLowerCase().includes(termoBusca) || 
                             (filme.descricao && filme.descricao.toLowerCase().includes(termoBusca));

        return combinaCategoria && combinaTexto;
    });

    renderizarFilmes(filmesFiltrados);
}

function renderizarFilmes(lista) {
    const container = document.getElementById('filmes-container');

    if (!lista || lista.length === 0) {
        container.innerHTML = '<p style="color: #aaa;">Nenhum filme encontrado para os filtros selecionados.</p>';
        return;
    }

    container.innerHTML = lista.map(filme => `
        <div class="card">
            <div>
                <h3>${filme.titulo}</h3>
                <p>${filme.descricao || 'Sem descrição disponível.'}</p>
            </div>
            <span class="badge">${filme.categoria}</span>
        </div>
    `).join('');
}