/* Shared navigation and progressive scroll animation; no animation dependency. */
(() => {
  const header = document.querySelector('header');
  const nav = header?.querySelector('nav');
  const menu = header?.querySelector('.humberger');
  const compact = matchMedia('(max-width: 1199.98px)');
  const motion = matchMedia('(prefers-reduced-motion: reduce)');
  const dropdowns = [];
  const closeDropdowns = () => dropdowns.forEach(({item, button}) => {
    item.classList.remove('is-open'); button.setAttribute('aria-expanded', 'false');
  });
  const setMenu = (open) => {
    nav?.classList.toggle('is-open', open);
    menu?.setAttribute('aria-expanded', String(open));
    menu?.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    if (!open) closeDropdowns();
  };
  if (header) {
    const sizeHeader = () => document.documentElement.style.setProperty('--header-height', `${header.offsetHeight}px`);
    sizeHeader();
    if ('ResizeObserver' in window) new ResizeObserver(sizeHeader).observe(header);
    else window.addEventListener('resize', sizeHeader);
  }
  menu?.addEventListener('click', () => setMenu(!nav.classList.contains('is-open')));
  nav?.querySelectorAll('.dropDown').forEach((item, index) => {
    const list = item.querySelector('.dropDownOption');
    const row = item.querySelector(':scope > span');
    if (!list || !row) return;
    list.id = `navigation-submenu-${index}`;
    const button = document.createElement('button');
    button.type = 'button'; button.className = 'dropdown-toggle-button';
    button.setAttribute('aria-label', `Toggle ${row.querySelector('a')?.textContent.trim()} submenu`);
    button.setAttribute('aria-controls', list.id); button.setAttribute('aria-expanded', 'false');
    row.querySelector(':scope > span')?.remove(); row.append(button);
    dropdowns.push({item, button});
    const toggle = (open) => { item.classList.toggle('is-open', open); button.setAttribute('aria-expanded', String(open)); };
    button.addEventListener('click', () => { const open = !item.classList.contains('is-open'); closeDropdowns(); toggle(open); });
    item.addEventListener('mouseenter', () => { if (!compact.matches && matchMedia('(hover: hover)').matches) toggle(true); });
    item.addEventListener('mouseleave', () => { if (!compact.matches && !item.contains(document.activeElement)) toggle(false); });
    item.addEventListener('focusout', (event) => { if (!item.contains(event.relatedTarget)) toggle(false); });
  });
  document.addEventListener('click', (event) => { if (!header?.contains(event.target)) setMenu(false); });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') { const wasOpen = nav?.classList.contains('is-open'); setMenu(false); if (wasOpen) menu?.focus(); }
  });
  nav?.addEventListener('click', (event) => { if (event.target.closest('a')) setMenu(false); });
  compact.addEventListener('change', () => setMenu(false));
  if (!motion.matches && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => entries.forEach(({target, isIntersecting}) => {
      if (isIntersecting) { target.classList.remove('reveal-pending'); target.classList.add('reveal-in'); observer.unobserve(target); }
    }), {threshold: .08});
    // Animate individual blocks only, never entire long sections or nested targets.
    document.querySelectorAll('main .cols, main .blogs-col, main section > h2').forEach((element) => {
      if (element.closest('.first-section') || element.parentElement.closest('.cols, .blogs-col')) return;
      element.classList.add('reveal-pending'); observer.observe(element);
    });
    motion.addEventListener('change', () => {
      if (motion.matches) { observer.disconnect(); document.querySelectorAll('.reveal-pending').forEach(el => el.classList.remove('reveal-pending')); }
    });
  }
})();
