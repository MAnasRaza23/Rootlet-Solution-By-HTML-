(() => {
  'use strict';
  const list = document.getElementById('blog-list') || document.getElementById('home-blog-list');
  const detail = document.getElementById('blog-detail');
  const target = list || detail;
  if (!target) return;
  const element = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };
  const link = (href, className, text) => {
    const node = element('a', className, text);
    node.href = href;
    return node;
  };
  const meta = post => {
    const profile = element('div', 'profile-sec');
    const avatar = element('span', 'blog-author-avatar', Array.from(post.posted_by)[0] || 'A');
    avatar.setAttribute('aria-hidden', 'true');
    const info = element('div', 'details');
    info.append(element('h6', '', post.posted_by), element('p', '', post.formatted_date));
    profile.append(avatar, info);
    return profile;
  };
  const image = (post, lazy = true) => {
    const img = element('img', 'blog-featured-image');
    img.src = post.image_url;
    img.alt = post.title;
    if (lazy) img.loading = 'lazy';
    return img;
  };
  const card = post => {
    const article = element('article', 'col-lg-4 col-md-4 col-sm-6 blogs-col');
    const href = (target.dataset.detailUrl || 'blog-detail.html') + '?id=' + encodeURIComponent(post.id);
    const cover = link(href);
    cover.append(image(post));
    const title = element('h5');
    title.append(link(href, '', post.title));
    const more = link(href, 'read-more-btn', 'Read More ');
    const arrow = element('span');
    arrow.append(element('i', 'fa-solid fa-arrow-right'));
    more.append(arrow);
    article.append(cover, title, element('p', '', post.excerpt), more, meta(post));
    return article;
  };
  const showDetail = post => {
    document.title = post.title + ' - Rootlet Solution';
    const article = element('div', 'rootlet-blog-detail');
    const content = element('div', 'article-content');
    // This HTML is filtered by safe_content() in the PHP API.
    content.innerHTML = post.content;
    article.append(element('h2', '', post.title), meta(post), image(post, false), content,
      link('blog.html', 'read-more-btn', '← Back to all blogs'));
    target.replaceChildren(article);
  };
  async function load() {
    try {
      let endpoint = target.dataset.endpoint || '../api/blogs.php';
      if (detail) {
        const id = new URLSearchParams(window.location.search).get('id');
        if (!id || !/^[1-9]\d*$/.test(id)) throw new Error('Invalid blog link.');
        endpoint += (endpoint.includes('?') ? '&' : '?') + 'id=' + encodeURIComponent(id);
      }
      const response = await fetch(endpoint, {headers: {'Accept': 'application/json'}, cache: 'no-store'});
      const type = response.headers.get('content-type') || '';
      if (!type.includes('application/json')) throw new Error('Unable to load blogs. Check your PHP server and database configuration.');
      let data;
      try { data = await response.json(); }
      catch { throw new Error('Unable to load blogs. Check your PHP server and database configuration.'); }
      if (!response.ok) throw new Error(data.error || 'Unable to load blogs. Please try again.');
      if (detail) showDetail(data.post);
      else if (!data.posts.length) target.replaceChildren(element('p', 'blog-message', 'No blogs published yet. Check back soon.'));
      else target.replaceChildren(...data.posts.map(card));
    } catch (error) {
      const message = element('div', 'col-12 blog-message');
      const text = element('p', '', error instanceof TypeError ? 'Unable to load blogs. Please check your connection and try again.' : error.message);
      text.setAttribute('role', 'alert');
      const retry = element('button', 'read-more-btn', 'Try again');
      retry.type = 'button';
      retry.addEventListener('click', () => {
        target.setAttribute('aria-busy', 'true');
        target.replaceChildren(element('p', '', 'Loading…'));
        load();
      });
      message.append(text, retry);
      if (detail) message.append(link('blog.html', 'read-more-btn', 'Back to all blogs'));
      target.replaceChildren(message);
    } finally { target.setAttribute('aria-busy', 'false'); }
  }
  load();
  const recent = document.getElementById('recent-blog-list');
  if (recent) {
    fetch(recent.dataset.endpoint, {headers: {'Accept': 'application/json'}, cache: 'no-store'})
      .then(async response => {
        if (!response.ok) throw new Error('Unable to load recent posts.');
        return response.json();
      })
      .then(data => {
        const posts = data.posts.map(post => {
          const item = element('div', 'my-2');
          const title = element('h4');
          title.append(link('blog-detail.html?id=' + encodeURIComponent(post.id), '', post.title));
          item.append(element('h6', 'm-0', post.formatted_date), title);
          return item;
        });
        recent.replaceChildren(...(posts.length ? posts : [element('p', '', 'No posts yet.')]));
      })
      .catch(() => recent.replaceChildren(element('p', '', 'Unable to load recent posts.')))
      .finally(() => recent.setAttribute('aria-busy', 'false'));
  }
})();
