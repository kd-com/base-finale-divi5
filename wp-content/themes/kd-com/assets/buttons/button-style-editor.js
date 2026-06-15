// Ajoute le SVG flèche dans l'aperçu du bouton Gutenberg en admin si le style u-link-arrow est actif
wp.domReady(() => {
  const svg = '<span class="arrow-icon"><svg viewBox="0 0 22 22" width="22" height="22"><g stroke="currentColor" fill="none"><circle class="arrow-icon--circle" cx="11" cy="11" r="10"/><path d="M7 11h8m0 0l-3-3m3 3l-3 3"/></g></svg></span>';

  function updateArrowButtons() {
    document.querySelectorAll('.wp-block-button.is-style-u-link-arrow .wp-block-button__link').forEach(el => {
      // Évite les doublons
      if (!el.querySelector('.arrow-icon')) {
        el.insertAdjacentHTML('beforeend', svg);
      }
    });
  }

  // Mise à jour à chaque changement de l'éditeur
  updateArrowButtons();
  document.addEventListener('keyup', updateArrowButtons);
  document.addEventListener('click', updateArrowButtons);
  document.addEventListener('input', updateArrowButtons);

  // Mise à jour lors de l'ajout/suppression de blocs
  const observer = new MutationObserver(updateArrowButtons);
  observer.observe(document.body, { childList: true, subtree: true });
});
