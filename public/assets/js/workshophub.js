document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-category]').forEach((button) => {
    button.addEventListener('click', () => {
      const category = button.dataset.category;
      document.querySelectorAll('[data-category]').forEach((item) => item.classList.toggle('is-active', item === button));
      document.querySelectorAll('[data-class-category]').forEach((card) => {
        card.hidden = category !== 'all' && card.dataset.classCategory !== category;
      });
    });
  });

  document.querySelectorAll('[data-admin-tab]').forEach((button) => {
    button.addEventListener('click', () => {
      const tab = button.dataset.adminTab;
      document.querySelectorAll('[data-admin-tab]').forEach((item) => item.classList.toggle('is-active', item === button));
      document.querySelectorAll('[data-admin-panel]').forEach((panel) => {
        panel.classList.toggle('is-hidden', panel.dataset.adminPanel !== tab);
      });
    });
  });

  document.querySelectorAll('[data-booking-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const contact = form.querySelector('[name="contact"]').value.trim();
      const seats = Number(form.querySelector('[name="seats"]').value);
      const hasEmail = contact.includes('@');
      const hasPhone = contact.replace(/\D/g, '').length >= 10;
      if ((!hasEmail && !hasPhone) || seats < 1 || seats > 3) {
        event.preventDefault();
        showToast('Check contact details and seat count before submitting.');
      }
    });
  });

  document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
      const value = button.dataset.copy;
      try {
        await navigator.clipboard.writeText(value);
        showToast('Address copied.');
      } catch {
        showToast(value);
      }
    });
  });
});

function showToast(message) {
  let region = document.querySelector('.toast-region');
  if (!region) {
    region = document.createElement('div');
    region.className = 'toast-region';
    document.body.appendChild(region);
  }
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = message;
  region.appendChild(toast);
  setTimeout(() => toast.remove(), 3600);
}
