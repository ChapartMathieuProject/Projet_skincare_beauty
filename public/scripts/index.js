document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll("#filter .filtre");
  const cards = document.querySelectorAll("#grid-product .product-col");

  if (buttons.length === 0 || cards.length === 0) return;

  // Détermine si une carte doit être affichée selon le filtre choisi
  const isVisible = (card, filter) => {
    if (filter === "tous")  return true;
    if (filter === "promo") return card.dataset.promo === "1";
    return card.dataset.type === filter;   // sinon : filtre par slug de catégorie
  };

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      buttons.forEach((b) => b.classList.remove("actif"));
      button.classList.add("actif");

      const filter = button.dataset.filter;

      // On masque ou affiche chaque carte
      cards.forEach((card) => {
        card.classList.toggle("d-none", !isVisible(card, filter));
      });
    });
  });
});