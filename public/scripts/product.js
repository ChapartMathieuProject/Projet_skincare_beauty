
document.addEventListener("DOMContentLoaded", () => {
  const formatPrix = (n) => n.toFixed(2).replace(".", ",") + " €";

  /* ===== 1. GALERIE : vignette active ==============================*/
  const vignettes = document.querySelectorAll(".vignette");
  const imgPrincipale = document.querySelector(".img-main img");

  vignettes.forEach((v) => {
    v.addEventListener("click", () => {
      vignettes.forEach((x) => x.classList.remove("active"));
      v.classList.add("active");


    });
  });



  /* ===== 2. WISHLIST ========================= */
  const btnWishlist = document.getElementById("btn-wishlist");
  if (btnWishlist) {
    btnWishlist.addEventListener("click", () => {
      const heart = btnWishlist.querySelector("i");
      const plein = heart.classList.toggle("fa-solid");
      heart.classList.toggle("fa-regular", !plein);
      heart.style.color = plein ? "var(--rose)" : "#c87f86";
    });
  }


  /* ===== 3. CONTENANCE : recalcul prix / remise / économie ========= */
  const actualPrice = document.getElementById("actual-price");
  const strikePrice = document.getElementById("strike-price");
  const promotionBadge = document.getElementById("promotion-badge");
  const saving = document.getElementById("saving");

  document.querySelectorAll(".variante").forEach((btn) => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".variante").forEach((x) => x.classList.remove("active"));
      btn.classList.add("active");

      const prix = parseFloat(btn.dataset.price);
      const old = parseFloat(btn.dataset.old);
      const remise = Math.round((1 - prix / old) * 100);

      if (actualPrice) actualPrice.textContent = formatPrix(prix);
      if (strikePrice) strikePrice.textContent = formatPrix(old);
      if (promotionBadge) promotionBadge.textContent = "-" + remise + "%";
      if (saving) saving.textContent = "Vous économisez " + formatPrix(old - prix);
    });
  });


  /* ===== 4. QUANTITÉ ========================== */
  const qtySpan = document.getElementById("qty");
  if (qtySpan) {
    let qty = Math.max(1, parseInt(qtySpan.textContent) || 1);
    qtySpan.textContent = qty;

    const qtyMore = document.getElementById("qty-more");
    const qtyLess = document.getElementById("qty-less");
    const qtyInput = document.getElementById("qty-input");

    if (qtyMore) qtyMore.addEventListener("click", () => {
      qty++;
      qtySpan.textContent = qty;
      if (qtyInput) qtyInput.value = qty;
    });
    if (qtyLess) qtyLess.addEventListener("click", () => {
      qty = Math.max(1, qty - 1);
      qtySpan.textContent = qty;
      if (qtyInput) qtyInput.value = qty;
    });
    /* ===== 5. AJOUTER AU PANIER : appel à l'API ========== */
    const btnAdd = document.getElementById("btn-add");

    // if (btnAdd) {
    //   btnAdd.addEventListener("click", () => {
    //     addToCart({ id: btnAdd.dataset.id, quantity: qty });
    //   });
    // }
  }

  /* ===== 6. ONGLETS : afficher le bon panneau ====================== */
  const tabs = document.querySelectorAll(".tab");
  const panneaux = document.querySelectorAll("[data-sign]");

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      tabs.forEach((x) => x.classList.remove("active"));
      tab.classList.add("active");

      panneaux.forEach((p) => {
        p.classList.toggle("d-none", p.dataset.sign !== tab.dataset.target);
      });
    });
  });

});

