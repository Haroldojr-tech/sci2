// ========== Login Logic ==========

const AUTH_URL = window.location.pathname.includes('/sci') ? '/sci/api/auth.php' : '/api/auth.php';
const USERS_URL = window.location.pathname.includes('/sci') ? '/sci/api/usuarios.php' : '/api/usuarios.php';
const openLoginBtn = document.getElementById('openLogin');
const closeLoginBtn = document.getElementById('closeLogin');
const loginOverlay = document.getElementById('loginOverlay');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const loginError = document.getElementById('loginError');
const registerError = document.getElementById('registerError');
const showRegisterBtn = document.getElementById('showRegister');
const backToLoginBtn = document.getElementById('backToLogin');

function setAuthMode(mode) {
  if (!loginForm || !registerForm) return;

  const isLogin = mode === 'login';
  loginForm.style.display = isLogin ? 'block' : 'none';
  registerForm.style.display = isLogin ? 'none' : 'block';

  if (loginError) loginError.classList.remove('visible');
  if (registerError) registerError.classList.remove('visible');
}

if (openLoginBtn && loginOverlay) {
  openLoginBtn.addEventListener('click', () => {
    loginOverlay.classList.add('active');
    setAuthMode('login');
  });
}

if (closeLoginBtn && loginOverlay) {
  closeLoginBtn.addEventListener('click', () => {
    loginOverlay.classList.remove('active');
    setAuthMode('login');
    if (loginForm) loginForm.reset();
    if (registerForm) registerForm.reset();
  });
}

if (loginOverlay) {
  loginOverlay.addEventListener('click', (e) => {
    if (e.target === loginOverlay) {
      loginOverlay.classList.remove('active');
      setAuthMode('login');
      if (loginForm) loginForm.reset();
      if (registerForm) registerForm.reset();
    }
  });
}

if (showRegisterBtn) {
  showRegisterBtn.addEventListener('click', () => setAuthMode('register'));
}

if (backToLoginBtn) {
  backToLoginBtn.addEventListener('click', () => setAuthMode('login'));
}

if (loginForm && loginError) {
  loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const user = document.getElementById('loginUser').value.trim();
    const pass = document.getElementById('loginPass').value;

    try {
      const response = await fetch(AUTH_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ usuario: user, senha: pass, user: user, pass: pass })
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        loginError.classList.add('visible');
        return;
      }

      window.location.href = 'admin/home.php';
    } catch (error) {
      console.error('Erro no login:', error);
      loginError.classList.add('visible');
    }
  });
}

if (registerForm && registerError) {
  registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const payload = {
      nome: document.getElementById('registerName').value.trim(),
      usuario: document.getElementById('registerUser').value.trim(),
      email: document.getElementById('registerEmail').value.trim(),
      senha: document.getElementById('registerPass').value,
      tipo: document.getElementById('registerType').value
    };

    try {
      const response = await fetch(USERS_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await response.json();

      if (!response.ok) {
        registerError.textContent = data.message || 'Não foi possível cadastrar.';
        registerError.classList.add('visible');
        return;
      }

      alert('Cadastro realizado com sucesso! Agora faça login.');
      registerForm.reset();
      setAuthMode('login');
      document.getElementById('loginUser').value = payload.usuario;
      document.getElementById('loginPass').value = payload.senha;
    } catch (error) {
      console.error('Erro no cadastro:', error);
      registerError.textContent = 'Erro ao cadastrar usuário.';
      registerError.classList.add('visible');
    }
  });
}
