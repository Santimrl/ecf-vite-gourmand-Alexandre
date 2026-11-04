// Validation JS simple (submit + messages) - demande formateur
(function () {
  function showError(input, msg) {
    input.classList.add('is-invalid');
    var box = input.parentElement.querySelector('.invalid-feedback');
    if (box && msg) box.textContent = msg;
  }

  function clearError(input) {
    input.classList.remove('is-invalid');
  }

  function emailOk(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  }

  function mdpOk(v) {
    return v.length >= 10
      && /[A-Z]/.test(v)
      && /[a-z]/.test(v)
      && /[0-9]/.test(v)
      && /[^A-Za-z0-9]/.test(v);
  }

  document.querySelectorAll('form.js-validate').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var ok = true;
      form.querySelectorAll('.is-invalid').forEach(function (el) { clearError(el); });

      var email = form.querySelector('[name="email"]');
      if (email) {
        if (!email.value.trim() || !emailOk(email.value.trim())) {
          showError(email, 'Email invalide.');
          ok = false;
        }
      }

      var password = form.querySelector('[name="password"]');
      if (password && form.getAttribute('data-validate') === 'login') {
        if (!password.value) {
          showError(password, 'Mot de passe requis.');
          ok = false;
        }
      }

      var mdp = form.querySelector('[name="password"]');
      if (mdp && form.getAttribute('data-validate') === 'register') {
        if (!mdpOk(mdp.value)) {
          showError(mdp, '10+ caracteres, maj, min, chiffre, special.');
          ok = false;
        }
      }

      var required = form.querySelectorAll('[required]');
      required.forEach(function (input) {
        if (!input.value || !String(input.value).trim()) {
          showError(input, 'Champ obligatoire.');
          ok = false;
        }
      });

      if (!ok) {
        e.preventDefault();
        var first = form.querySelector('.is-invalid');
        if (first) first.focus();
      }
    });
  });
})();
