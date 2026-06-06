document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-toggle-details]');
  if (!button) return;
  const target = document.getElementById(button.dataset.toggleDetails);
  if (target) target.classList.toggle('open');
});
