(function () {
  const storageKey = 'poscig-admin-theme';
  const root = document.body;

  function applyTheme(theme) {
    root.setAttribute('data-theme', theme);

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      const label = button.querySelector('[data-theme-label]');
      const nextTheme = theme === 'dark' ? 'light' : 'dark';

      button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
      if (label) {
        label.textContent = theme === 'dark' ? 'Tryb jasny' : 'Tryb ciemny';
      } else {
        button.textContent = theme === 'dark' ? 'Tryb jasny' : 'Tryb ciemny';
      }

      button.dataset.nextTheme = nextTheme;
    });
  }

  function initialTheme() {
    const stored = localStorage.getItem(storageKey);
    return stored === 'light' || stored === 'dark' ? stored : root.getAttribute('data-theme') || 'dark';
  }

  applyTheme(initialTheme());

  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-theme-toggle]');
    if (!button) {
      return;
    }

    const currentTheme = root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
    localStorage.setItem(storageKey, nextTheme);
    applyTheme(nextTheme);
  });
})();
