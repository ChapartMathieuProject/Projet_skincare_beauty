// Fichier : public/scripts/cart-manager.js
// Gère l'affichage et les interactions de la modale panier.

const API_BASE_URL = 'api/cart.php';

document.addEventListener('DOMContentLoaded', () => {
    const cartModal = document.getElementById('cart-modal');
    if (cartModal) {
        cartModal.addEventListener('show.bs.modal', loadCart);
    }

    const cartItemsContainer = document.getElementById('cart-items');
    if (cartItemsContainer) {
        cartItemsContainer.addEventListener('click', (event) => {
            const row = event.target.closest('[data-id]');
            if (!row) {
                return;
            }

            const productId = row.dataset.id;

            if (event.target.closest('.cart-btn-increase')) {
                updateQuantity(productId, 1);
            }
            if (event.target.closest('.cart-btn-decrease')) {
                updateQuantity(productId, -1);
            }
            if (event.target.closest('.cart-btn-remove')) {
                removeFromCart(productId);
            }
        });
    }

    document.querySelectorAll('.btn-bag').forEach((button) => {
        button.addEventListener('click', () => {
            addToCart({
                id: button.dataset.id,
                quantity: 1,
            });
        });
    });
});

function loadCart() {
    fetch(`${API_BASE_URL}?action=get`)
        .then((response) => response.json())
        .then(renderCart)
        .catch((error) => console.error('Erreur lors du chargement du panier :', error));
}

function renderCart(data) {
    const container = document.getElementById('cart-items');
    if (!container) {
        return;
    }

    if (!data.items || data.items.length === 0) {
        container.innerHTML = '<p class="text-center text-muted py-4" id="cart-empty-message">Votre panier est vide.</p>';
        updateCartCount(0);
        const cartTotal = document.getElementById('cart-total');
        if (cartTotal) {
            cartTotal.textContent = '0,00 €';
        }
        return;
    }

    container.innerHTML = data.items.map((item) => buildItemRow(item)).join('');

    const cartTotal = document.getElementById('cart-total');
    if (cartTotal) {
        cartTotal.textContent = `${data.total.toFixed(2)} €`;
    }

    updateCartCount(data.count);
}

function updateCartCount(count) {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        cartCount.textContent = count;
    }
}

function buildItemRow(item) {
    const subtotal = (item.price * item.quantity).toFixed(2);

    return `
        <div class="d-flex align-items-center border-bottom py-3" data-id="${item.id}">
            <img src="${item.image}" alt="${item.name}" class="rounded cart-item-image">
            <div class="ms-3 flex-grow-1">
                <div class="fw-semibold">${item.name}</div>
                <div class="text-muted small">${item.price.toFixed(2)} €</div>
            </div>
            <div class="d-flex align-items-center me-3">
                <button class="btn btn-sm btn-outline-secondary cart-btn-decrease" type="button">−</button>
                <span class="mx-2">${item.quantity}</span>
                <button class="btn btn-sm btn-outline-secondary cart-btn-increase" type="button">+</button>
            </div>
            <div class="fw-semibold me-3 cart-item-subtotal">${subtotal} €</div>
            <button class="btn btn-sm btn-link text-danger cart-btn-remove" type="button">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    `;
}

function addToCart(product) {
    fetch(`${API_BASE_URL}?action=add`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(product),
    })
        .then((response) => response.json())
        .then((data) => updateCartCount(data.count))
        .catch((error) => console.error('Erreur lors de l\'ajout au panier :', error));
}

function updateQuantity(productId, delta) {
    fetch(`${API_BASE_URL}?action=update`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: productId, delta }),
    })
        .then((response) => response.json())
        .then(renderCart)
        .catch((error) => console.error('Erreur lors de la mise à jour de la quantité :', error));
}

function removeFromCart(productId) {
    fetch(`${API_BASE_URL}?action=remove`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: productId }),
    })
        .then((response) => response.json())
        .then(renderCart)
        .catch((error) => console.error('Erreur lors de la suppression du produit :', error));
}