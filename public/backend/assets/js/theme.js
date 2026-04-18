"use strict";

const button = document.getElementById('button');

button.addEventListener('click', () => {
  const currentTheme = localStorage.getItem('theme_mode');
  const newTheme = currentTheme === 'default-theme' ? 'dark-theme' : 'default-theme';

  document.body.classList.remove('default-theme', 'dark-theme');
  document.body.classList.add(newTheme);
  localStorage.setItem('theme_mode', newTheme);
  updateButtonText(newTheme);
});

const activeTheme = localStorage.getItem('theme_mode');
if (activeTheme) {
  document.body.classList.remove('default-theme', 'dark-theme');
  document.body.classList.add(activeTheme);
}

(function() {
  updateButtonText(activeTheme || 'default-theme');
})();

function updateButtonText(theme) {
  if (theme === 'default-theme') {
    button.innerHTML = '<i class="lar la-sun"></i>';
  } else if (theme === 'dark-theme') {
    button.innerHTML = '<i class="lar la-moon"></i>';
  }
}


