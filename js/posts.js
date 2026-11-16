const API_URL = window.location.pathname.includes('/sci') ? '/sci/posts/api.php' : '/posts/api.php';

const ICONS = {
  logo: `<div class="logoifto">
      <div class="quadrado"></div>
      <div class="quadrado"></div>
      <div class="quadrado"></div>
      <div class="quadrado"></div>
      <div class="quadrado"></div>
      <div class="quadrado"></div>
      <div class="quadrado"></div>
      <div class="quadrado"></div>
      <div class="quadrado"></div>
      <div class="quadrado"></div>
    </div>`,
  clock: `<i class="fa-solid fa-clock" style="color: white; font-size: 20px;"></i>`,
  calendar: `<i class="fa-solid fa-calendar-days" style="color: white; font-size:20px;"></i>`,
  secretary: `<i class="fa-solid fa-user-tie" style="color: white; font-size: 20px;"></i>`
};

async function getPosts() {
  try {
    const response = await fetch(API_URL);
    if (!response.ok) throw new Error('Falha ao buscar posts');
    return await response.json();
  } catch (error) {
    console.error('Erro ao buscar posts:', error);
    return [];
  }
}

async function createPost(postFormData) {
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      body: postFormData
    });
    if (!response.ok) throw new Error('Falha ao criar post');
    return await response.json();
  } catch (error) {
    console.error('Erro ao criar post:', error);
    return null;
  }
}

async function updatePost(id, updates, isJson = false) {
  try {
    let opts = {};

    if (isJson) {
      opts = {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updates)
      };
    } else {
      updates.append('id', id);
      opts = {
        method: 'POST',
        body: updates
      };
    }

    const response = await fetch(`${API_URL}?id=${id}`, opts);
    if (!response.ok) throw new Error('Falha ao atualizar post');
    return await response.json();
  } catch (error) {
    console.error('Erro ao atualizar post:', error);
    return null;
  }
}

async function deletePost(id) {
  try {
    const response = await fetch(`${API_URL}?id=${id}`, {
      method: 'DELETE'
    });
    if (!response.ok) throw new Error('Falha ao excluir post');
    return true;
  } catch (error) {
    console.error('Erro ao excluir post:', error);
    return false;
  }
}

function getIconSVG(iconName) {
  return ICONS[iconName] || ICONS.logo;
}

async function incrementClick(id) {
  const posts = await getPosts();
  const post = posts.find((item) => Number(item.id) === Number(id));

  if (post) {
    const novosCliques = (Number(post.clicks) || 0) + 1;
    await updatePost(id, { clicks: novosCliques }, true);
  }
}

async function getConfig() {
  try {
    const response = await fetch(`${API_URL}?config=1`);
    if (!response.ok) throw new Error('Falha ao buscar configuração');
    return await response.json();
  } catch (error) {
    return { startDate: '', endDate: '' };
  }
}

async function saveConfig(config) {
  try {
    const response = await fetch(`${API_URL}?config=1`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(config)
    });
    return await response.json();
  } catch (error) {
    console.error('Erro ao salvar configuração:', error);
  }
}
