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

  initBookingFlow();
  initWizard();
});

// Public booking: mode → open days → free slots (Unit 44)
function initBookingFlow() {
  const form = document.querySelector('[data-booking-flow]');
  if (!form) return;

  const modeField = form.querySelector('[data-booking-mode]');
  const dateField = form.querySelector('[data-booking-date]');
  const slotField = form.querySelector('[data-booking-slot]');
  const optionsUrl = form.dataset.optionsUrl;
  const openDates = JSON.parse(form.dataset.openDates || '{}');

  const fillSelect = (select, values, emptyLabel) => {
    while (select.firstChild) select.removeChild(select.firstChild);
    if (!values.length) {
      const option = document.createElement('option');
      option.value = '';
      option.textContent = emptyLabel;
      select.appendChild(option);
      return;
    }
    values.forEach((value) => {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = value;
      select.appendChild(option);
    });
  };

  const loadSlots = async () => {
    const date = dateField.value;
    if (!date) {
      fillSelect(slotField, [], 'Pick a day first');
      return;
    }
    try {
      const response = await fetch(`${optionsUrl}?mode=${encodeURIComponent(modeField.value)}&date=${encodeURIComponent(date)}`);
      const data = await response.json();
      fillSelect(slotField, data.slots, 'No free slots that day');
    } catch {
      fillSelect(slotField, [], 'Could not load slots');
    }
  };

  const loadDates = () => {
    fillSelect(dateField, openDates[modeField.value] || [], 'No open days for this mode');
    loadSlots();
  };

  modeField.addEventListener('change', loadDates);
  dateField.addEventListener('change', loadSlots);
  loadDates();
}

// Setup wizard steps (Unit 44)
function initWizard() {
  const form = document.querySelector('[data-wizard]');
  if (!form) return;

  const steps = [...form.querySelectorAll('.wizard-step')];
  const prevButton = form.querySelector('[data-wizard-prev]');
  const nextButton = form.querySelector('[data-wizard-next]');
  const finishButton = form.querySelector('[data-wizard-finish]');
  let current = 0;

  const render = () => {
    steps.forEach((step, index) => step.classList.toggle('is-hidden', index !== current));
    prevButton.classList.toggle('is-hidden', current === 0);
    nextButton.classList.toggle('is-hidden', current === steps.length - 1);
    finishButton.classList.toggle('is-hidden', current !== steps.length - 1);
  };

  nextButton.addEventListener('click', (event) => {
    event.preventDefault();
    const fields = [...steps[current].querySelectorAll('input, select')];
    if (fields.some((field) => !field.reportValidity())) return;
    current = Math.min(current + 1, steps.length - 1);
    render();
  });

  prevButton.addEventListener('click', (event) => {
    event.preventDefault();
    current = Math.max(current - 1, 0);
    render();
  });

  render();
}

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
