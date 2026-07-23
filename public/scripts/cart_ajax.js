document.addEventListener("submit", async (event) => {
  const form = event.target.closest("form.js-cart");
  if (!form) return;

  event.preventDefault();

  const button = form.querySelector('[type="submit"]');
  if (button) button.disabled = true;

  try {
    const response = await fetch(form.action, {
      method: "POST",
      body: new FormData(form),
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });

    if (response.status === 401) {
      const modal = document.getElementById("userModal");
      if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
      return;
    }

    const data = await response.json();
    renderCart(data);

    const cartModal = document.getElementById("cart-modal");
    if (cartModal) bootstrap.Modal.getOrCreateInstance(cartModal).show();

  } catch (error) {
    form.submit();
  } finally {
    if (button) button.disabled = false;
  }
});

function renderCart(data) {
  const badge = document.getElementById("cart-count");
  if (badge) badge.textContent = data.count > 0 ? data.count : "";

  const totalEl = document.getElementById("cart-total");
  if (totalEl) totalEl.textContent = data.total.toFixed(2).replace(".", ",") + " €";

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
      <div class="d-flex align-items-center gap-3 border-bottom py-3">
        <img src="${item.image}" alt="" style="width:60px;height:auto;">
        <div class="flex-fill">
          <a href="${item.url}" class="text-decoration-none text-reset fw-semibold">${escapeHtml(item.name)}</a>
          <div class="small text-muted">
            ${item.quantity} × ${item.unit.toFixed(2).replace(".", ",")} €
          </div>
        </div>
        <div class="fw-bold">${item.subtotal.toFixed(2).replace(".", ",")} €</div>
      </div>`
    )
    .join("");
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

document.querySelectorAll('form.js-cart').length;