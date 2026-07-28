async function sendCart(body) {
  const response = await fetch(window.CART_URL, {
    method: "POST",
    body: body,
    headers: { "X-Requested-With": "XMLHttpRequest" },
  });

  // Non connecté : on ouvre la modale de connexion
  if (response.status === 401) {
    const modal = document.getElementById("userModal");
    if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
    return null;
  }

  return await response.json();
}

/* ===== 1. Formulaires d'ajout au panier ===== */
document.addEventListener("submit", async (event) => {
  const form = event.target.closest("form.js-cart");
  if (!form) return;

  event.preventDefault();

  const button = form.querySelector('[type="submit"]');
  if (button) button.disabled = true;

  try {

    const data = await sendCart(new FormData(form));
    if (!data) return;

    renderCart(data);
    openCartModal();
  } catch (error) {
    form.submit(); // repli : soumission classique en PRG
  } finally {
    if (button) button.disabled = false;
  }
});


document.addEventListener("click", async (event) => {
  const btn = event.target.closest("[data-cart-action]");
  if (!btn) return;

  const action = btn.dataset.cartAction;
  const row = btn.closest("[data-cart-row]");
  if (!row) return;

  const current = parseInt(row.dataset.qty, 10) || 0;

  const body = new FormData();
  body.append("id", btn.dataset.id);

  if (action === "remove") {
    body.append("action", "remove");
  } else {

    body.append("action", "update");
    body.append("quantity", String(action === "inc" ? current + 1 : current - 1));
  }

  row.style.opacity = "0.5"; 

  try {
    const data = await sendCart(body);
    if (data) renderCart(data);
  } catch (error) {
    window.location.reload();
  }
});

/* ===== 3. Redessin de la modale ===== */
function renderCart(data) {
  const badge = document.getElementById("cart-count");
  if (badge) badge.textContent = data.count > 0 ? data.count : "";

  const totalEl = document.getElementById("cart-total");
  if (totalEl) totalEl.textContent = formatPrice(data.total);

  const container = document.getElementById("cart-items");
  if (!container) return;

  if (!data.items.length) {
    container.innerHTML =
      '<p class="text-center text-muted py-4">Votre panier est vide.</p>';
    return;
  }

  container.innerHTML = data.items
    .map(
      (item) => `
      <div class="d-flex align-items-center gap-3 border-bottom py-3"
           data-cart-row data-qty="${item.quantity}">

        <img src="${item.image}" alt="" style="width:60px;height:auto;">

        <div class="flex-fill">
          <a href="${item.url}" class="text-decoration-none text-reset fw-semibold">${escapeHtml(item.name)}</a>
          <div class="small text-muted">${formatPrice(item.unit)} l'unité</div>
        </div>

        <div class="choice-quantity d-flex align-items-center">
          <button type="button" data-cart-action="dec" data-id="${item.id}" aria-label="Diminuer">−</button>
          <span class="text-center fw-semibold px-2">${item.quantity}</span>
          <button type="button" data-cart-action="inc" data-id="${item.id}" aria-label="Augmenter">+</button>
        </div>

        <div class="fw-bold" style="min-width:80px;text-align:right;">${formatPrice(item.subtotal)}</div>

        <button type="button" class="btn btn-sm text-danger border-0"
                data-cart-action="remove" data-id="${item.id}" aria-label="Supprimer">
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>`
    )
    .join("");
}

function openCartModal() {
  const cartModal = document.getElementById("cart-modal");
  if (cartModal) bootstrap.Modal.getOrCreateInstance(cartModal).show();
}


document.addEventListener("DOMContentLoaded", async () => {
  if (!document.getElementById("cart-items")) return;

  try {
    const response = await fetch(window.CART_URL + "?action=get", {
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    if (!response.ok) return; 
    const data = await response.json();
    if (data.ok) renderCart(data);
  } catch (error) {
  }
});

function formatPrice(value) {
  return value.toFixed(2).replace(".", ",") + " €";
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}