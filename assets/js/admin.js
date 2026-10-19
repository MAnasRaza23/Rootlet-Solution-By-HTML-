// Match the current sidebar markup to the existing mobile rules.
const currentSidebar = document.querySelector('#sidebar');
currentSidebar?.classList.add('sidebar');
const menu=document.querySelector('.menu-toggle');const sidebar=document.querySelector('#sidebar');menu?.addEventListener('click',()=>{const open=sidebar.classList.toggle('open');menu.setAttribute('aria-expanded',String(open));});document.querySelectorAll('[data-confirm-delete]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm('Are you sure you want to delete this blog?'))event.preventDefault();}));const editor=document.querySelector('#editor');const field=document.querySelector('#content-field');if(editor&&field){field.value=editor.innerHTML;editor.addEventListener('input',()=>{field.value=editor.innerHTML;});document.querySelectorAll('[data-command]').forEach(button=>button.addEventListener('click',()=>{editor.focus();if(button.dataset.command==='createLink'){const link=prompt('Enter a link (https://...)');if(link&&/^https?:\/\//i.test(link))document.execCommand('createLink',false,link);}else document.execCommand(button.dataset.command,false,button.dataset.value||null);}));editor.closest('form').addEventListener('submit',()=>{field.value=editor.innerHTML;});}

const adminCompact = matchMedia('(max-width: 800px)');
const closeAdminMenu = () => { sidebar?.classList.remove('open'); menu?.setAttribute('aria-expanded', 'false'); };
adminCompact.addEventListener('change', closeAdminMenu);
document.addEventListener('keydown', event => { if (event.key === 'Escape') closeAdminMenu(); });
document.addEventListener('click', event => { if (adminCompact.matches && !sidebar?.contains(event.target) && !menu?.contains(event.target)) closeAdminMenu(); });
