document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('pos-login-form');
  if (!form) return;
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const spinner = document.getElementById('login-spinner');
    const buttonText = document.getElementById('button-text');
    const submitButton = document.getElementById('login-submit');
    spinner.classList.remove('hidden');
    buttonText.textContent = 'Signing in...';
    submitButton.disabled = true;
    const formData = new FormData(form);
    fetch(form.action || window.location.href, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(response => response.text())
    .then(html => {
      if (html.includes('login_error') || html.includes('Error!')) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const errorDiv = tempDiv.querySelector('.bg-red-100');
        if (errorDiv) {
          const errorText = errorDiv.textContent.trim();
          const cleanErrorDiv = document.createElement('div');
          cleanErrorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded relative mt-4';
          cleanErrorDiv.setAttribute('role', 'alert');
          cleanErrorDiv.innerHTML = `<strong class="font-bold">Error!</strong><span class="block sm:inline">${errorText.replace(/^Error!/, '')}</span>`;
          const currentErrorDiv = document.querySelector('.bg-red-100');
          if (currentErrorDiv) {
            currentErrorDiv.replaceWith(cleanErrorDiv);
          } else {
            document.querySelector('h2').insertAdjacentElement('afterend', cleanErrorDiv);
          }
        }
        spinner.classList.add('hidden');
        buttonText.textContent = 'Sign in';
        submitButton.disabled = false;
      } else {
        window.location.href = window.aiposLoginRedirectUrl || '/aipos#/pos';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      spinner.classList.add('hidden');
      buttonText.textContent = 'Sign in';
      submitButton.disabled = false;
      const errorHtml = `
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">An unexpected error occurred. Please try again.</span>
        </div>
      `;
      const currentErrorDiv = document.querySelector('.bg-red-100');
      if (currentErrorDiv) {
        currentErrorDiv.outerHTML = errorHtml;
      } else {
        document.querySelector('h2').insertAdjacentHTML('afterend', errorHtml);
      }
    });
  });
}); 