const API_BASE = '/api';

function getToken() {
  return localStorage.getItem('jwt_token') || '';
}

async function apiFetch(path, options = {}) {
  const headers = new Headers(options.headers || {});
  const token = getToken();

  if (token) {
    headers.set('Authorization', `Bearer ${token}`);
  }

  if (!(options.body instanceof FormData) && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }

  const response = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers,
  });

  const data = await response.json();
  if (!response.ok) {
    throw new Error(data?.message || 'Request failed');
  }

  return data;
}

export async function login(email, password) {
  const response = await apiFetch('/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  });

  if (response?.data?.token) {
    localStorage.setItem('jwt_token', response.data.token);
    localStorage.setItem('jwt_user', JSON.stringify(response.data.user || {}));
  }

  return response;
}

export function logout() {
  localStorage.removeItem('jwt_token');
  localStorage.removeItem('jwt_user');
}

export function getProducts(params = {}) {
  const query = new URLSearchParams(params).toString();
  return apiFetch(`/products${query ? `?${query}` : ''}`, { method: 'GET' });
}

export function getProduct(id) {
  return apiFetch(`/products/${id}`, { method: 'GET' });
}

export function createProduct(formData) {
  return apiFetch('/products', {
    method: 'POST',
    body: formData,
  });
}

export function updateProduct(id, payload) {
  return apiFetch(`/products/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  });
}

export function addToCart(productId, quantity = 1) {
  return apiFetch('/cart/add', {
    method: 'POST',
    body: JSON.stringify({ product_id: productId, quantity }),
  });
}

export function updateCart(productId, quantity) {
  return apiFetch('/cart/update', {
    method: 'PUT',
    body: JSON.stringify({ product_id: productId, quantity }),
  });
}

export function checkoutOrder(payload = {}) {
  return apiFetch('/orders', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}

export function createPayment(payload = {}) {
  return apiFetch('/payments', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
}
