document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("client-modal");
  if (!modal) return;
  modal.addEventListener("show.bs.modal", (event) => {
    const btn = event.relatedTarget; 
    if (!btn) return;

    const avatar    = btn.getAttribute("data-avatar")    || "";
    const nom       = btn.getAttribute("data-nom")       || "";
    const email     = btn.getAttribute("data-email")     || "";
    const tel       = btn.getAttribute("data-tel")       || "";
    const commandes = btn.getAttribute("data-commandes") || "0";
    const userId    = btn.getAttribute("data-user-id")   || "";
    const roleId    = btn.getAttribute("data-role")      || "";

    modal.querySelector("#cm-avatar").textContent      = avatar;
    modal.querySelector("#cm-name").textContent        = nom;
    modal.querySelector("#cm-email").textContent       = email;
    modal.querySelector("#cm-phone").textContent       = tel;
    modal.querySelector("#cm-user-id").value = userId;
    modal.querySelector("#cm-role").value    = roleId;
  });
});