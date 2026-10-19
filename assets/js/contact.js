(() => {
  const form = document.querySelector('#contact-form');
  if (!form) return;
  const status = document.querySelector('#contact-status');
  const button = form.querySelector('button[type="submit"]');
  const token = form.querySelector('[name="csrf"]');
  let busy = false;
  const show = (message, error = false) => {
    status.hidden = false;
    status.textContent = message;
    status.setAttribute('role', error ? 'alert' : 'status');
  };
  const loadToken = async () => {
    const response = await fetch(form.action, { credentials: 'same-origin', cache: 'no-store' });
    const data = await response.json();
    if (!response.ok || !data.token) throw new Error('Unable to prepare the form. Refresh and try again.');
    token.value = data.token;
  };
  const ready = loadToken().catch(() => { token.value = ''; });
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (busy || !form.reportValidity()) return;
    busy = true; button.disabled = true; button.textContent = 'Sending…';
    show('Sending your message…');
    try {
      await ready;
      if (!token.value) await loadToken();
      const response = await fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' });
      let data;
      try { data = await response.json(); } catch { throw new Error('The server could not process the form. Please try again later.'); }
      if (!response.ok || !data.success) {
        if (response.status === 419) token.value = '';
        throw new Error(data.error || 'Unable to send your message.');
      }
      form.reset(); show(data.message);
    } catch (error) { show(error.message || 'Connection failed. Please try again.', true); }
    finally { busy = false; button.disabled = false; button.textContent = 'Send Message'; }
  });
})();
