document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll("#filter .filtre");
  const cards = document.querySelectorAll("#grid-product .product-col");

  if (buttons.length === 0 || cards.length === 0) return;

  const isVisible = (card, filter) => {
    if (filter === "tous")  return true;
    if (filter === "promo") return card.dataset.promo === "1";
    return card.dataset.type === filter; 
  };

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      buttons.forEach((b) => b.classList.remove("actif"));
      button.classList.add("actif");

      const filter = button.dataset.filter;

      cards.forEach((card) => {
        card.classList.toggle("d-none", !isVisible(card, filter));
      });
    });
  });
});