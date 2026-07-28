document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('client-modal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        if (!btn) return;
        const d = btn.dataset;

        modal.querySelector('#cm-avatar').textContent       = d.avatar || '';
        modal.querySelector('#cm-name').textContent         = d.nom || '';
        modal.querySelector('#cm-email').textContent        = d.email || '';
        modal.querySelector('#cm-phone').textContent        = d.tel || '—';
        modal.querySelector('#cm-orders-count').textContent = d.commandes || '0';
        modal.querySelector('#cm-total').textContent        = d.total || '—';

        modal.querySelector('#cm-registered').textContent       = '—';
        modal.querySelector('#cm-address-shipping').textContent = '—';
        modal.querySelector('#cm-address-billing').textContent  = '—';

        const tbody = modal.querySelector('#cm-orders');
        tbody.innerHTML = '';
        let orders = [];
        try { orders = JSON.parse(d.orders || '[]'); } catch (e) { orders = []; }

        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-3">Aucune commande.</td></tr>';
        } else {
            orders.forEach(function (o) {
                const tr = document.createElement('tr');
                tr.innerHTML =
                    '<td class="order-num">#' + o.number + '</td>' +
                    '<td>' + o.date + '</td>' +
                    '<td class="order-total">' + o.total + '</td>' +
                    '<td><span class="statut-badge ' + o.class + '"><span class="point"></span>' + o.status + '</span></td>';
                tbody.appendChild(tr);
            });
        }
    });
});