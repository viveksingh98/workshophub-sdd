document.addEventListener('DOMContentLoaded', () => {
  initDarkMode();
  initWysiwyg();
  initStudentSearch();
});

// Light / dark toggle — persists per device (Unit 45)
function initDarkMode() {
  const toggle = document.querySelector('[data-theme-toggle]');
  if (!toggle) return;

  const apply = (dark) => document.body.classList.toggle('dash-dark', dark);
  apply(localStorage.getItem('dash-theme') === 'dark');

  toggle.addEventListener('click', () => {
    const dark = !document.body.classList.contains('dash-dark');
    apply(dark);
    localStorage.setItem('dash-theme', dark ? 'dark' : 'light');
  });
}

// Native contenteditable rich-text editor — no CDN dependencies
function initWysiwyg() {
  document.querySelectorAll('[data-wysiwyg]').forEach((editor) => {
    const area = editor.querySelector('[data-wysiwyg-area]');
    const input = editor.querySelector('[data-wysiwyg-input]');

    if (input.value) {
      area.append(...new DOMParser().parseFromString(input.value, 'text/html').body.childNodes);
    }

    editor.querySelectorAll('[data-cmd]').forEach((button) => {
      button.addEventListener('click', (event) => {
        event.preventDefault();
        area.focus();
        document.execCommand(button.dataset.cmd, false, button.dataset.value || null);
      });
    });

    const form = editor.closest('form');
    form.addEventListener('submit', () => {
      input.value = area.innerHTML;
    });
  });
}

// Manual booking: type a name, autofill from existing students (Unit 45)
function initStudentSearch() {
  const field = document.querySelector('[data-student-search]');
  if (!field) return;

  const results = document.querySelector('[data-student-results]');
  const phoneField = document.querySelector('[data-student-phone]');
  let timer = null;

  field.addEventListener('input', () => {
    clearTimeout(timer);
    const q = field.value.trim();
    if (q.length < 2) {
      results.classList.add('is-hidden');
      return;
    }
    timer = setTimeout(async () => {
      try {
        const response = await fetch(`${field.dataset.searchUrl}?q=${encodeURIComponent(q)}`);
        const students = await response.json();
        while (results.firstChild) results.removeChild(results.firstChild);
        students.forEach((student) => {
          const option = document.createElement('button');
          option.type = 'button';
          option.className = 'autofill-option';
          option.textContent = `${student.name} · ${student.contact}`;
          option.addEventListener('click', () => {
            field.value = student.name;
            phoneField.value = student.contact;
            results.classList.add('is-hidden');
          });
          results.appendChild(option);
        });
        results.classList.toggle('is-hidden', students.length === 0);
      } catch {
        results.classList.add('is-hidden');
      }
    }, 220);
  });

  document.addEventListener('click', (event) => {
    if (!results.contains(event.target) && event.target !== field) {
      results.classList.add('is-hidden');
    }
  });
}
