function chargerMenus() {
  const params = new URLSearchParams();
  ['prix_max','prix_min','theme','regime','nb_min'].forEach(function (id) {
    const el = document.getElementById(id);
    if (el && el.value !== '') params.set(id, el.value);
  });

  fetch('/api_menus.php?' + params.toString())
    .then(function (r) { return r.json(); })
    .then(function (data) {
      const box = document.getElementById('liste-menus');
      if (!data.length) {
        box.innerHTML = '<p class="text-muted">Aucun menu pour ces filtres.</p>';
        return;
      }
      let html = '';
      data.forEach(function (m) {
        const desc = (m.description || '').substring(0, 110);
        const coverStyle = m.image
          ? 'background-image:url(\'/' + escapeAttr(String(m.image).replace(/^\/+/, '')) + '\')'
          : '';
        html += '<div class="col-md-6 col-lg-4">' +
          '<div class="card card-menu h-100">' +
          '<div class="menu-cover" style="' + coverStyle + '"></div>' +
          '<div class="card-body">' +
          '<h2 class="h5">' + escapeHtml(m.titre) + '</h2>' +
          '<p class="small text-muted">' + escapeHtml(desc) + (desc.length >= 110 ? '…' : '') + '</p>' +
          '<p class="prix-menu mb-1">' + formatPrix(m.prix_min) + '</p>' +
          '<p class="small mb-2">' + m.nb_personnes_min + ' pers. min · ' +
            escapeHtml(m.theme) + ' · stock ' + m.stock + '</p>' +
          '<a class="btn btn-sm btn-vg-green" href="/menu.php?id=' + m.id + '">Voir le detail</a>' +
          '</div></div></div>';
      });
      box.innerHTML = html;
    })
    .catch(function () {
      document.getElementById('liste-menus').innerHTML = '<p class="text-danger">Erreur de chargement.</p>';
    });
}

function formatPrix(n) {
  const x = Number(n);
  if (isNaN(x)) return n + ' €';
  return x.toFixed(2).replace('.', ',') + ' €';
}

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function escapeAttr(str) {
  return String(str).replace(/'/g, '%27').replace(/"/g, '&quot;');
}

document.getElementById('btnFiltrer').addEventListener('click', chargerMenus);
['prix_max','prix_min','theme','regime','nb_min'].forEach(function (id) {
  const el = document.getElementById(id);
  if (el) el.addEventListener('change', chargerMenus);
});

chargerMenus();
