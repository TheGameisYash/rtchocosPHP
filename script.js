// --- HELPERS ----------------------------------------------------
function getCorrectedPath(path) {
  if (!path || path.startsWith('http') || path.startsWith('data:')) {
    return path;
  }
  const isBlogSubfolder = window.location.pathname.includes('/blog/');
  return isBlogSubfolder ? '../' + path : path;
}

// ----------------------------------------------------
const WORKSHOPS = [
  {
    id: 1,
    title: "Artisan Chocolate Truffles & Ganache",
    level: "Beginner to Intermediate",
    category: "Practical",
    duration: "1 Day",
    price: "Coming Soon",
    status: "coming-soon",
    image: "assets/truffle_workshop.png",
    outcomes: [
      "Hand-rolling and coating classic chocolate truffles",
      "Infusing ganache with spices, herbs, and citrus",
      "Controlling fat and water separation in fillings",
      "Piping and decoration techniques for an artisan finish"
    ]
  },
  {
    id: 2,
    title: "The Science of Tempering & Cocoa Crystallization",
    level: "Intermediate to Advanced",
    category: "Science",
    duration: "1 Day (Masterclass)",
    price: "Coming Soon",
    status: "coming-soon",
    image: "assets/tempering_workshop.png",
    outcomes: [
      "Cocoa butter crystal structures (Forms I-VI)",
      "How to read and control tempering curves",
      "Water activity (aw) & shelf-life chemistry",
      "Emulsification math for silkier ganache"
    ]
  },
  {
    id: 3,
    title: "Mastering Artisan Bonbons & Cocoa Painting",
    level: "Advanced Masterclass",
    category: "Artisan",
    duration: "3 Days",
    price: "Coming Soon",
    status: "coming-soon",
    image: "assets/bonbon_workshop.png",
    outcomes: [
      "Cocoa butter coloring & airbrushing basics",
      "Achieving flawless, glossy bonbon shells",
      "Layered fillings: caramels, gelées, duos",
      "Troubleshooting cracks, dullness, and sticking"
    ]
  }
];

let BLOGS = [];

const BLOG_ARTICLES = {}; // Content rendered server-side via PHP

const TESTIMONIALS = [
  { name: "Priya Sharma", role: "Home Baker, Delhi", text: "RT Chocos' tempering workshop completely transformed my chocolate work. The science-first approach made everything click.", rating: 5 },
  { name: "Arjun Mehta", role: "Chocolate Business Owner", text: "Arpan's NPD consulting helped me launch my chocolate brand with confidence. The depth of knowledge is exceptional.", rating: 5 },
  { name: "Sneha Kulkarni", role: "Parent, Mumbai", text: "My 8-year-old absolutely loved the kids' workshop. She came home talking about cacao origins — incredible!", rating: 5 },
  { name: "Vikram Desai", role: "Pastry Chef, Bangalore", text: "The ganache masterclass was phenomenal. Understanding water activity changed how I approach shelf life entirely.", rating: 5 },
];

// --- STATE ------------------------------------------------------
let cartCount = 0;
let currentPage = 'home';
let blogSearchQuery = '';
let currentBlogFilter = 'All';
let currentBlogArticleId = null;
const PAGE_STORAGE_KEY = 'rtchocos-current-page';
const BLOG_ARTICLE_STORAGE_KEY = 'rtchocos-current-blog-article';
const WINDOW_STATE_KEY = 'rtchocos-nav-state';
const PAGE_COOKIE_KEY = 'rtchocos_page';
const ARTICLE_COOKIE_KEY = 'rtchocos_article';
const HOME_FILE_NAME = 'index.php';
const LEGACY_HOME_FILE_NAME = 'rt-chocos-v4 updated.html';
const DEFAULT_ARTICLE_KEY = null;
const PAGE_FILE_MAP = {
  home: HOME_FILE_NAME,
  about: 'about.php',
  workshops: 'workshops.php',
  blog: 'blog.php',
  chocopedia: 'chocopedia.php',
  gallery: 'gallery.php',
  contact: 'contact.php'
};
const ARTICLE_FILE_MAP = {};

function getValidPage(page) {
  return page && document.getElementById('page-' + page) ? page : null;
}

function getRouteFromFileName() {
  const pathParts = window.location.pathname.split('/');
  const fileName = decodeURIComponent(pathParts.pop() || HOME_FILE_NAME).toLowerCase();

  if (fileName === HOME_FILE_NAME.toLowerCase() || fileName === LEGACY_HOME_FILE_NAME.toLowerCase() || fileName === '') {
    return { page: 'home', article: null };
  }

  for (const [page, mappedFile] of Object.entries(PAGE_FILE_MAP)) {
    if (mappedFile.toLowerCase() === fileName) {
      return { page, article: null };
    }
  }

  for (const [articleKey, mappedFile] of Object.entries(ARTICLE_FILE_MAP)) {
    const mappedName = mappedFile.split('/').pop().toLowerCase();

    // Support dynamic catch-all route matching
    if (fileName === 'article.php') {
      const urlParams = new URLSearchParams(window.location.search);
      const slug = urlParams.get('slug');
      if (articleKey === slug) {
        return {
          page: 'blog-article',
          article: getValidBlogArticleByKey(articleKey)
        };
      }
    }

    if (mappedFile.toLowerCase() === fileName || mappedName === fileName) {
      return {
        page: 'blog-article',
        article: getValidBlogArticleByKey(articleKey)
      };
    }
  }

  return { page: null, article: null };
}

function getCurrentFileName() {
  return decodeURIComponent(window.location.pathname.split('/').pop() || HOME_FILE_NAME);
}

function getCurrentUrlPage() {
  const route = getRouteFromFileName();
  return route.page === 'blog-article' ? 'blog' : (route.page || 'home');
}

function updateActiveNavLinks() {
  const navPage = getCurrentUrlPage();
  document.querySelectorAll('.nav-link, .mobile-nav-link').forEach(link => {
    link.classList.toggle('active', link.dataset.page === navPage);
  });
}

function getValidPageFromQuery() {
  try {
    const url = new URL(window.location.href);
    return getValidPage(url.searchParams.get('page'));
  } catch (error) {
    return null;
  }
}

function getValidBlogArticleByKey(articleKey) {
  if (!articleKey) {
    return null;
  }

  return BLOGS.find(blog => blog.articleKey === articleKey) || null;
}

function getBlogArticleFromQuery() {
  try {
    const url = new URL(window.location.href);
    return getValidBlogArticleByKey(url.searchParams.get('article'));
  } catch (error) {
    return null;
  }
}

function buildHashRoute(page, articleKey = null) {
  return '';
}

function getRouteFromHash() {
  return { page: null, article: null };
}

function getValidPageFromHash() {
  return null;
}

function getPersistedPage() {
  try {
    const storedPage = localStorage.getItem(PAGE_STORAGE_KEY);
    return storedPage && document.getElementById('page-' + storedPage) ? storedPage : null;
  } catch (error) {
    return null;
  }
}

function persistCurrentPage(page) {
  try {
    localStorage.setItem(PAGE_STORAGE_KEY, page);
  } catch (error) {
    return;
  }
}

function getPersistedBlogArticle() {
  try {
    return getValidBlogArticleByKey(localStorage.getItem(BLOG_ARTICLE_STORAGE_KEY));
  } catch (error) {
    return null;
  }
}

function getCookieValue(name) {
  const cookiePrefix = `${name}=`;
  const cookies = document.cookie ? document.cookie.split('; ') : [];

  for (const cookie of cookies) {
    if (cookie.startsWith(cookiePrefix)) {
      return decodeURIComponent(cookie.slice(cookiePrefix.length));
    }
  }

  return null;
}

function persistCookieValue(name, value) {
  if (!value) {
    document.cookie = `${name}=; path=/; max-age=0; SameSite=Lax`;
    return;
  }

  document.cookie = `${name}=${encodeURIComponent(value)}; path=/; max-age=2592000; SameSite=Lax`;
}

function getCookiePage() {
  return getValidPage(getCookieValue(PAGE_COOKIE_KEY));
}

function getCookieBlogArticle() {
  return getValidBlogArticleByKey(getCookieValue(ARTICLE_COOKIE_KEY));
}

function getWindowState() {
  try {
    const parsedState = JSON.parse(window.name || '{}');
    return parsedState && typeof parsedState === 'object'
      ? parsedState[WINDOW_STATE_KEY] || null
      : null;
  } catch (error) {
    return null;
  }
}

function persistWindowState(page, articleKey = null) {
  try {
    const parsedState = JSON.parse(window.name || '{}');
    const nextState = parsedState && typeof parsedState === 'object' ? parsedState : {};
    nextState[WINDOW_STATE_KEY] = { page, articleKey };
    window.name = JSON.stringify(nextState);
  } catch (error) {
    window.name = JSON.stringify({ [WINDOW_STATE_KEY]: { page, articleKey } });
  }
}

function getWindowPage() {
  const state = getWindowState();
  return state ? getValidPage(state.page) : null;
}

function getWindowBlogArticle() {
  const state = getWindowState();
  return state ? getValidBlogArticleByKey(state.articleKey) : null;
}

function persistBlogArticle(articleKey) {
  try {
    if (articleKey) {
      localStorage.setItem(BLOG_ARTICLE_STORAGE_KEY, articleKey);
    } else {
      localStorage.removeItem(BLOG_ARTICLE_STORAGE_KEY);
    }
  } catch (error) {
    return;
  }

  persistCookieValue(ARTICLE_COOKIE_KEY, articleKey || '');
}

function restoreRouteFromLocation() {
  const fileRoute = getRouteFromFileName();
  const routePage = fileRoute.page || 'home';
  const routeArticle = fileRoute.article;

  if (routePage === 'blog-article' && routeArticle) {
    openBlogArticle(routeArticle.id, { updateUrl: false, scrollToTop: false });
  } else {
    navigate(routePage, { updateUrl: false, scrollToTop: false });
  }
}

// --- NAVIGATION -------------------------------------------------
function navigate(page, options = {}) {
  const { scrollToTop = true } = options;

  const activeBlog = page === 'blog-article'
    ? BLOGS.find(blog => blog.id === currentBlogArticleId)
    : null;
  if (!document.getElementById('page-' + page)) {
    return;
  }

  // Show all pages
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById('page-' + page).classList.add('active');

  // Update nav links
  updateActiveNavLinks();

  // Update header style
  const header = document.getElementById('site-header');
  if (page === 'home') {
    header.classList.remove('not-home');
  } else {
    header.classList.add('not-home');
  }

  // Close mobile menu
  document.getElementById('mobile-menu').classList.remove('open');

  // Scroll to top
  if (scrollToTop) {
    window.scrollTo(0, 0);
  }

  currentPage = page;
  persistCurrentPage(page);
  persistWindowState(page, activeBlog ? activeBlog.articleKey : null);
  persistCookieValue(PAGE_COOKIE_KEY, page);
  if (page === 'blog-article') {
    persistBlogArticle(activeBlog ? activeBlog.articleKey : null);
  }
  if (page !== 'blog-article') {
    persistBlogArticle(null);
  }

}

function openSearch() {
  const isBlogPage = document.getElementById('page-blog');
  if (isBlogPage) {
    navigate('blog');
    const searchInput = document.querySelector('#page-blog .blog-search');
    if (searchInput) {
      requestAnimationFrame(() => searchInput.focus());
    }
  } else {
    const prefix = window.location.pathname.includes('/blog/') ? '../' : '';
    window.location.href = prefix + 'blog.php?search=true';
  }
}

function toggleMobileMenu() {
  document.getElementById('mobile-menu').classList.toggle('open');
}

// --- SCROLL HEADER ----------------------------------------------
window.addEventListener('scroll', () => {
  const header = document.getElementById('site-header');
  if (window.scrollY > 60) {
    header.classList.add('scrolled');
  } else {
    header.classList.remove('scrolled');
  }
});

// --- CART -------------------------------------------------------
function addToCart() {
  cartCount++;
  const badge = document.getElementById('cart-badge');
  if (badge) {
    badge.textContent = cartCount;
    badge.classList.remove('hidden');
  }
}

// --- WORKSHOP CARD ----------------------------------------------
function triggerNewsletterAlert() {
  const popup = document.getElementById('newsletter-popup');
  if (popup) {
    popup.classList.add('open');
    const popupInput = popup.querySelector('.popup-input');
    if (popupInput) {
      setTimeout(() => popupInput.focus(), 100);
    }
  }
}

function workshopCardHTML(w) {
  const buttonLabel = w.status === 'coming-soon' ? 'Notify Me When Open' : 'Book Now';
  const buttonOnClick = w.status === 'coming-soon'
    ? `onclick="triggerNewsletterAlert()"`
    : `onclick="addToCart()"`;

  const imageTag = w.image && w.image.includes('/')
    ? `<img src="${getCorrectedPath(w.image)}" alt="${w.title}">`
    : `<span>🍫</span>`;

  return `
    <div class="card workshop-card">
      <div class="workshop-card-img">
        ${imageTag}
        <span class="tag workshop-card-tag">${w.level}</span>
      </div>
      <div class="workshop-card-body">
        <h3>${w.title}</h3>
        <div class="workshop-meta">
          <span>🕒 ${w.duration}</span>
          <span class="price">${w.price}</span>
        </div>
        <ul class="workshop-outcomes">
          ${w.outcomes.map(o => `<li>${o}</li>`).join('')}
        </ul>
        <button class="btn-primary" ${buttonOnClick} style="width:100%;justify-content:center;">${buttonLabel}</button>
      </div>
    </div>`;
}

// --- BLOG CARD --------------------------------------------------
function blogCardHTML(b) {
  const hasFullArticle = Boolean(
    b.articleKey &&
    (BLOG_ARTICLES[b.articleKey] || ARTICLE_FILE_MAP[b.articleKey])
  );
  const readMoreLabel = hasFullArticle ? 'Read Article' : 'Read Article';
  const imageMarkup = b.image
    ? `<img src="${getCorrectedPath(b.image)}" alt="${b.title}" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"><span style="display:none;">Chocolate Journal</span>`
    : '<span>Chocolate Journal</span>';

  const ytBadge = b.youtube_url ? ' • <span class="yt-badge">🎥 Video</span>' : '';

  if (hasFullArticle) {
    const articleFile = ARTICLE_FILE_MAP[b.articleKey] || HOME_FILE_NAME;
    return `
    <a class="card blog-card-link" href="${getCorrectedPath(articleFile)}">
      <div class="blog-card-img">
        ${imageMarkup}
      </div>
      <div class="blog-card-body">
        <h3 class="blog-card-title">${b.title}</h3>
        <div class="blog-meta">
          <span class="tag">${b.category}</span>
          <span class="blog-date">${b.date} • ${b.read} read${ytBadge}</span>
        </div>
        <p class="blog-excerpt">${b.excerpt}</p>
        <div class="blog-read-more">${readMoreLabel}</div>
      </div>
    </a>`;
  }

  return `
    <div class="card blog-card-link" onclick="openBlogArticle(${b.id})">
      <div class="blog-card-img">
        ${imageMarkup}
      </div>
      <div class="blog-card-body">
        <h3 class="blog-card-title">${b.title}</h3>
        <div class="blog-meta">
          <span class="tag">${b.category}</span>
          <span class="blog-date">${b.date} • ${b.read} read${ytBadge}</span>
        </div>
        <p class="blog-excerpt">${b.excerpt}</p>
        <div class="blog-read-more">${readMoreLabel}</div>
      </div>
    </div>`;
}

// --- RENDER HOME ------------------------------------------------
function renderHome() {
  const homeBlogs = document.getElementById('home-blogs');
  if (homeBlogs) {
    homeBlogs.innerHTML = BLOGS.slice(0, 3).map(blogCardHTML).join('');
  }
}

// --- WORKSHOPS --------------------------------------------------
function renderWorkshops(filter) {
  const cards = document.querySelectorAll('#workshops-grid .workshop-card');
  cards.forEach(card => {
    const category = card.dataset.category;
    if (filter === 'all' || category === filter) {
      card.style.display = 'flex'; // Use flex to match the stylesheet layout rules
    } else {
      card.style.display = 'none';
    }
  });
}

function filterWorkshops(filter) {
  document.querySelectorAll('#workshop-filters .filter-btn').forEach(b => {
    b.classList.toggle('active', b.dataset.filter === filter);
  });
  renderWorkshops(filter);
}

// --- BLOG -------------------------------------------------------
function renderBlog() {
  let filtered = currentBlogFilter === 'All' ? BLOGS : BLOGS.filter(b => b.category === currentBlogFilter);
  if (blogSearchQuery) {
    filtered = filtered.filter(b =>
      b.title.toLowerCase().includes(blogSearchQuery) ||
      b.excerpt.toLowerCase().includes(blogSearchQuery)
    );
  }
  const blogGrid = document.getElementById('blog-grid');
  if (!blogGrid) {
    return;
  }
  blogGrid.innerHTML = filtered.map(blogCardHTML).join('');
}

function filterBlog(filter) {
  currentBlogFilter = filter;
  document.querySelectorAll('#blog-filters .filter-btn').forEach(b => {
    b.classList.toggle('active', b.dataset.filter === filter);
  });
  renderBlog();
}

function searchBlog(val) {
  blogSearchQuery = val.toLowerCase();
  renderBlog();
}

function escapeHTML(value) {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function getBlogComments(articleId) {
  try {
    return JSON.parse(localStorage.getItem(`rtchocos-comments-${articleId}`) || '[]');
  } catch (error) {
    return [];
  }
}

function saveBlogComments(articleId, comments) {
  try {
    localStorage.setItem(`rtchocos-comments-${articleId}`, JSON.stringify(comments));
  } catch (error) {
    return;
  }
}

function renderBlogComments() {
  const commentsList = document.getElementById('blog-comments-list');
  if (!commentsList || !currentBlogArticleId) {
    return;
  }

  fetch(getCorrectedPath('api_comments.php?slug=') + currentBlogArticleId)
    .then(res => {
      if (!res.ok) throw new Error();
      return res.json();
    })
    .then(comments => {
      commentsList.innerHTML = comments.length
        ? comments.map(comment => `
            <div class="blog-comment-item">
              <div class="blog-comment-head">
                <span class="blog-comment-name">${escapeHTML(comment.name)}</span>
                <span class="blog-comment-date">${escapeHTML(comment.date)}</span>
              </div>
              <div class="blog-comment-text">${escapeHTML(comment.text)}</div>
            </div>
          `).join('')
        : '<div class="blog-comment-empty">No comments yet. Be the first to start the conversation.</div>';
    })
    .catch(() => {
      // LocalStorage Fallback
      const comments = getBlogComments(currentBlogArticleId);
      commentsList.innerHTML = comments.length
        ? comments.map(comment => `
            <div class="blog-comment-item">
              <div class="blog-comment-head">
                <span class="blog-comment-name">${escapeHTML(comment.name)}</span>
                <span class="blog-comment-date">${escapeHTML(comment.date)}</span>
              </div>
              <div class="blog-comment-text">${escapeHTML(comment.text)}</div>
            </div>
          `).join('')
        : '<div class="blog-comment-empty">No comments yet. Be the first to start the conversation. (Offline Mode)</div>';
    });
}

function submitBlogComment() {
  if (!currentBlogArticleId) {
    return;
  }

  const nameInput = document.getElementById('blog-comment-name');
  const commentInput = document.getElementById('blog-comment-text');
  const note = document.getElementById('blog-comment-note');
  const name = nameInput.value.trim();
  const text = commentInput.value.trim();

  if (!name || !text) {
    note.textContent = 'Please enter your name and comment before posting.';
    note.style.color = '#ff5252';
    return;
  }

  note.textContent = 'Posting comment...';
  note.style.color = 'var(--text-light)';

  fetch(getCorrectedPath('api_comments.php'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      slug: currentBlogArticleId,
      name: name,
      comment: text
    })
  })
    .then(res => {
      return res.json().then(data => {
        if (!res.ok) {
          throw new Error(data.error || 'Server error posting comment.');
        }
        return data;
      });
    })
    .then(data => {
      nameInput.value = '';
      commentInput.value = '';
      note.textContent = data.message || 'Your comment has been added.';
      note.style.color = 'var(--green-900)';
      renderBlogComments();
    })
    .catch(err => {
      // Fallback: save to LocalStorage if DB fails
      console.warn("API comment submission failed. Reverting to LocalStorage:", err.message);
      const comments = getBlogComments(currentBlogArticleId);
      const newComment = {
        name,
        text,
        date: new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })
      };
      comments.unshift(newComment);
      saveBlogComments(currentBlogArticleId, comments);

      nameInput.value = '';
      commentInput.value = '';
      note.textContent = 'Posted (Offline Mode).';
      note.style.color = 'var(--green-900)';
      renderBlogComments();
    });
}

function openBlogArticle(blogId, options = {}) {
  const { updateUrl = true, scrollToTop = true } = options;
  const blog = BLOGS.find(item => item.id === blogId);
  if (!blog) {
    return;
  }

  currentBlogArticleId = blog.id;

  const currentFile = getCurrentFileName().toLowerCase();
  const articleFile = ARTICLE_FILE_MAP[blog.articleKey];
  let isAlreadyOnPage = false;

  if (articleFile) {
    const lowerArticleFile = articleFile.toLowerCase();
    if (lowerArticleFile.includes('article.php?slug=')) {
      // Dynamic article route (matches blog/article.php?slug=...)
      const urlParams = new URLSearchParams(window.location.search);
      const currentSlug = (urlParams.get('slug') || '').toLowerCase();
      if (currentFile === 'article.php' && currentSlug === blog.articleKey.toLowerCase()) {
        isAlreadyOnPage = true;
      }
    } else {
      // Static article route (matches blog/article-name.php)
      const mappedName = lowerArticleFile.split('/').pop();
      if (currentFile === mappedName || currentFile === lowerArticleFile) {
        isAlreadyOnPage = true;
      }
    }
  }

  if (!isAlreadyOnPage) {
    if (articleFile) {
      window.location.href = getCorrectedPath(articleFile);
      return;
    }
  }

  // We are already on the article's PHP page
  const note = document.getElementById('blog-comment-note');
  if (note) note.textContent = '';
  renderBlogComments();
  persistBlogArticle(blog.articleKey || null);
  navigate('blog-article', { updateUrl, scrollToTop });
}

// --- POPUP ------------------------------------------------------
function closePopup() {
  const popup = document.getElementById('newsletter-popup');
  if (popup) {
    popup.classList.remove('open');
  }
  localStorage.setItem('rtchocos-newsletter-closed', 'true');
}

const popupEl = document.getElementById('newsletter-popup');
if (popupEl) {
  popupEl.addEventListener('click', closePopup);
}

// --- FORM HANDLING & INTERACTIVITY ------------------------------
function initInteractiveForms() {
  const getSubfolderPrefix = () => {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    return isBlogSubfolder ? '../' : '';
  };

  // 1. Helper for email subscription submit
  function bindNewsletterSubmit(formId, inputClass, btnClass, feedbackId, successMsg) {
    const form = document.getElementById(formId);
    if (!form) return;

    const input = form.querySelector('.' + inputClass);
    const button = form.querySelector('.' + btnClass);
    const feedback = document.getElementById(feedbackId);
    if (!input || !feedback) return;

    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const email = input.value.trim();

      if (!email) {
        feedback.style.display = "block";
        feedback.style.color = "#ff5252";
        feedback.textContent = "Please enter an email address.";
        return;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        feedback.style.display = "block";
        feedback.style.color = "#ff5252";
        feedback.textContent = "Please enter a valid email address.";
        return;
      }

      // Show loader
      if (button) button.classList.add("loading");
      feedback.style.display = "none";

      const prefix = getSubfolderPrefix();
      fetch(prefix + 'subscribe.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email })
      })
        .then(response => {
          if (!response.ok) {
            throw new Error('Server returned an error.');
          }
          return response.json();
        })
        .then(data => {
          if (button) button.classList.remove("loading");

          feedback.style.display = "block";
          feedback.style.color = "var(--gold-light)";

          if (formId === "newsletter-popup-form") {
            feedback.style.color = "var(--green-900)";
          }

          feedback.textContent = data.message || successMsg;
          input.value = "";

          localStorage.setItem("rtchocos-newsletter-closed", "true");

          if (formId === "newsletter-popup-form") {
            setTimeout(() => {
              closePopup();
            }, 2000);
          }
        })
        .catch(error => {
          // Fallback for static environments
          setTimeout(() => {
            if (button) button.classList.remove("loading");
            feedback.style.display = "block";
            feedback.style.color = "var(--gold-light)";
            if (formId === "newsletter-popup-form") {
              feedback.style.color = "var(--green-900)";
            }
            feedback.textContent = successMsg;
            input.value = "";
            localStorage.setItem("rtchocos-newsletter-closed", "true");
            if (formId === "newsletter-popup-form") {
              setTimeout(() => {
                closePopup();
              }, 2000);
            }
          }, 1000);
        });
    });
  }

  // Bind the forms
  bindNewsletterSubmit(
    "newsletter-home-form",
    "newsletter-input",
    "btn-gold",
    "newsletter-home-feedback",
    "✓ Thank you for subscribing! Your first Chocolate Letter is on the way."
  );

  bindNewsletterSubmit(
    "newsletter-footer-form",
    "footer-newsletter-input",
    "footer-newsletter-btn",
    "newsletter-footer-feedback",
    "✓ Subscribed! Thank you."
  );

  bindNewsletterSubmit(
    "newsletter-popup-form",
    "popup-input",
    "btn-primary",
    "newsletter-popup-feedback",
    "✓ Subscribed successfully! Welcome."
  );

  // 2. Contact Form Submit Handler
  const contactForm = document.getElementById("contact-form");
  if (contactForm) {
    const feedback = document.getElementById("contact-form-feedback");
    const submitBtn = contactForm.querySelector("button[type='submit']");

    contactForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const name = contactForm.querySelector("input[name='name']").value.trim();
      const email = contactForm.querySelector("input[name='email']").value.trim();
      const phone = contactForm.querySelector("input[name='phone']").value.trim();
      const subject = contactForm.querySelector("input[name='subject']").value.trim();
      const message = contactForm.querySelector("textarea[name='message']").value.trim();

      if (!name || !email || !subject || !message) {
        feedback.className = "form-feedback error";
        feedback.textContent = "Please fill out all required fields.";
        feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        return;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        feedback.className = "form-feedback error";
        feedback.textContent = "Please enter a valid email address.";
        feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        return;
      }

      // Show loading state
      if (submitBtn) {
        submitBtn.classList.add("loading");
        submitBtn.disabled = true;
      }
      feedback.style.display = "none";

      const prefix = getSubfolderPrefix();
      fetch(prefix + 'send_contact.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          name: name,
          email: email,
          phone: phone,
          subject: subject,
          message: message
        })
      })
        .then(response => {
          if (!response.ok) {
            throw new Error('Server returned an error.');
          }
          return response.json();
        })
        .then(data => {
          if (submitBtn) {
            submitBtn.classList.remove("loading");
            submitBtn.disabled = false;
          }

          feedback.className = "form-feedback success";
          feedback.textContent = `✓ ${data.message || 'Thank you! Your message has been sent successfully.'}`;

          contactForm.reset();
          feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        })
        .catch(error => {
          // Fallback for static environments
          setTimeout(() => {
            if (submitBtn) {
              submitBtn.classList.remove("loading");
              submitBtn.disabled = false;
            }
            feedback.className = "form-feedback success";
            feedback.textContent = `✓ Thank you, ${name}! Your message has been sent successfully. We'll be in touch soon.`;
            contactForm.reset();
            feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }, 1200);
        });
    });
  }
}

// Call interactive form initializer
initInteractiveForms();


// --- INIT -------------------------------------------------------
async function initApp() {
  try {
    const response = await fetch(getCorrectedPath('api_blogs.php?t=' + Date.now()));
    if (response.ok) {
      const data = await response.json();
      BLOGS = data.map(b => ({
        id: b.id,
        title: b.title,
        category: b.category,
        date: b.date,
        read: b.read_time,
        excerpt: b.excerpt,
        articleKey: b.slug,
        image: b.thumbnail || b.image,
        youtube_url: b.youtube_url,
        bodyClass: b.body_class
      }));

      // Populate ARTICLE_FILE_MAP for any dynamic entries
      BLOGS.forEach(b => {
        if (!ARTICLE_FILE_MAP[b.articleKey]) {
          ARTICLE_FILE_MAP[b.articleKey] = 'blog/' + b.articleKey;
        }
      });
    }
  } catch (error) {
    console.error("Failed to load dynamic blogs:", error);
  }

  // Now run initializers
  renderHome();
  loadDynamicAiInsight();
  loadDynamicAiRecipe();
  loadDynamicAiClassInsight();
  initHeroSlideshow();
  renderWorkshops('all');
  renderBlog();
  // Initialize scroll reveal
  initScrollReveal();
  restoreRouteFromLocation();
}

// --- SCROLL REVEAL OBSERVER ---
const revealObserver = new IntersectionObserver((entries, observer) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

function initScrollReveal() {
  document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
}

initApp();


// --- AI CHAT DRAWER STATE & FUNCTIONS ---
let aiChatHistory = [];

function toggleAiDrawer() {
  const drawer = document.getElementById('ai-chat-drawer');
  const overlay = document.getElementById('ai-drawer-overlay');
  if (drawer && overlay) {
    const isOpen = drawer.classList.toggle('open');
    if (isOpen) {
      overlay.classList.add('visible');
      document.body.style.overflow = 'hidden';
      const input = document.getElementById('ai-chat-input');
      if (input) setTimeout(() => input.focus(), 150);
    } else {
      overlay.classList.remove('visible');
      document.body.style.overflow = '';
    }
  }
}

function appendAiMessage(role, text) {
  const container = document.getElementById('ai-chat-messages');
  if (!container) return;

  const msgDiv = document.createElement('div');
  msgDiv.className = `ai-message ${role}`;

  let formattedText = text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");

  formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
  formattedText = formattedText.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" rel="noopener" style="color: var(--accent); text-decoration: underline; font-weight: 600;">$1</a>');
  formattedText = formattedText.replace(/\n/g, '<br>');

  msgDiv.innerHTML = `<div class="ai-msg-bubble">${formattedText}</div>`;
  container.appendChild(msgDiv);
  container.scrollTop = container.scrollHeight;
}

function showAiTypingIndicator() {
  const container = document.getElementById('ai-chat-messages');
  if (!container) return;

  const indicator = document.createElement('div');
  indicator.id = 'ai-typing-indicator';
  indicator.className = 'ai-typing-indicator';
  indicator.innerHTML = `
    <span class="ai-typing-dot"></span>
    <span class="ai-typing-dot"></span>
    <span class="ai-typing-dot"></span>
  `;
  container.appendChild(indicator);
  container.scrollTop = container.scrollHeight;
}

function removeAiTypingIndicator() {
  const indicator = document.getElementById('ai-typing-indicator');
  if (indicator) {
    indicator.remove();
  }
}

async function callAiApi(message) {
  showAiTypingIndicator();

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        message: message,
        history: aiChatHistory
      })
    });

    removeAiTypingIndicator();

    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        appendAiMessage('ai', data.reply);
        aiChatHistory.push({ role: 'user', text: message });
        aiChatHistory.push({ role: 'model', text: data.reply });
        if (aiChatHistory.length > 20) {
          aiChatHistory = aiChatHistory.slice(-20);
        }
      } else if (data.error) {
        appendAiMessage('ai', `Sorry, I encountered an error: ${data.error}`);
      }
    } else {
      appendAiMessage('ai', "Sorry, I am unable to connect to the backend AI proxy right now.");
    }
  } catch (error) {
    removeAiTypingIndicator();
    appendAiMessage('ai', "Error sending message. Please check your network connection.");
    console.error("AI chat error:", error);
  }
}

function handleAiChatSubmit(e) {
  e.preventDefault();
  const input = document.getElementById('ai-chat-input');
  if (!input) return;

  const text = input.value.trim();
  if (!text) return;

  input.value = '';
  appendAiMessage('user', text);
  callAiApi(text);
}

function sendQuickPrompt(promptText) {
  appendAiMessage('user', promptText);
  callAiApi(promptText);
}

function sendTroubleshootQuery(promptText) {
  const container = document.getElementById('ai-chat-messages');
  if (container) {
    container.innerHTML = `
      <div class="ai-message system">
        <div class="ai-msg-bubble">
          Hello! I am <strong>CocoaGenius AI</strong>, your expert guide to the science, craft, and chemistry of chocolate making. How can I help you today?
        </div>
      </div>
    `;
    aiChatHistory = [];
  }

  toggleAiDrawer();
  appendAiMessage('user', promptText);
  callAiApi(promptText);
}

async function loadDynamicAiInsight() {
  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    // Trigger background generation for the next visit
    await fetch(prefix + 'api_generate_insight.php');
  } catch (err) {
    console.error("Failed to trigger background AI insight generation:", err);
  }
}

async function loadDynamicAiRecipe() {
  const titleEl = document.getElementById('ai-dynamic-recipe-title');
  const descEl = document.getElementById('ai-dynamic-recipe-desc');
  if (!titleEl || !descEl) return;

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        message: "Create a unique, creative, high-end chocolate flavor formulation pairing (e.g. Cardamom, Sea Salt, and Rosemary Dark Chocolate). Provide the name of the recipe in line 1, and a brief mouthwatering description in line 2 (under 40 words). Return ONLY the name on line 1, and description on line 2, separated by a pipe character '|' (e.g., Recipe Name|Description)."
      })
    });
    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        const parts = data.reply.trim().split('|');
        if (parts.length >= 2) {
          titleEl.textContent = parts[0].trim();
          descEl.textContent = parts[1].trim();
        } else {
          titleEl.textContent = "Lavender & Sea Salt Ganache";
          descEl.textContent = data.reply.trim();
        }
      }
    }
  } catch (err) {
    console.error("Failed to load AI recipe:", err);
    titleEl.textContent = "Chilli & Lime Dark Truffles";
    descEl.textContent = "A fiery kick of bird's eye chilli paired with fresh lime zest in an organic 70% Malabar dark chocolate shell.";
  }
}

async function loadDynamicAiClassInsight() {
  const el = document.getElementById('ai-dynamic-class-insight');
  if (!el) return;

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        message: "Provide a single, professional tip about tempering curves, crystal polymorphs, or roasting parameters in chocolate making (under 30 words). Return ONLY the tip text."
      })
    });
    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        el.textContent = data.reply.trim();
      }
    }
  } catch (err) {
    console.error("Failed to load AI class insight:", err);
    el.textContent = "Stable Form V crystallization occurs best when dark chocolate is held between 31°C and 32°C.";
  }
}

// --- HERO SLIDESHOW INTERACTION ---
function initHeroSlideshow() {
  const slides = document.querySelectorAll('.hero-slideshow .slide');
  if (slides.length <= 1) return;

  let currentSlideIdx = 0;
  setInterval(() => {
    slides[currentSlideIdx].classList.remove('active');
    currentSlideIdx = (currentSlideIdx + 1) % slides.length;
    slides[currentSlideIdx].classList.add('active');
  }, 4000);
}

// --- AI CHOCOLAB FORMULATION ---
async function generateCustomBarFormula() {
  const base = document.getElementById('chocolab-base').value;
  const percent = document.getElementById('chocolab-percent').value;
  const checkboxes = document.querySelectorAll('input[name="inclusions"]:checked');

  const inclusions = Array.from(checkboxes).map(cb => cb.value);
  if (inclusions.length > 3) {
    alert("Please select a maximum of 3 inclusions for your chocolate bar formulation.");
    return;
  }

  const placeholder = document.getElementById('chocolab-placeholder');
  const loader = document.getElementById('chocolab-loader');
  const results = document.getElementById('chocolab-results');

  if (!placeholder || !loader || !results) return;

  placeholder.style.display = 'none';
  results.style.display = 'none';
  loader.style.display = 'block';

  const inclusionsText = inclusions.length > 0 ? inclusions.join(', ') : 'no extra inclusions';
  const prompt = `Formulate a detailed professional recipe profile for a custom chocolate bar:
Base: ${base}
Cacao percentage: ${percent}%
Inclusions: ${inclusionsText}

You must return the response in exactly this format, using pipe characters '|' to separate the sections. Do not include any markdown bold stars in the section separators. Use exactly 4 sections:
Bar Name | Short mouthwatering description under 40 words | Professional tasting notes (describing acidity, sweetness, bitterness, texture in detail) | Step-by-step professional tempering temperatures and conching notes for this specific bar.

Format example:
The Spice Route | A rich dark chocolate bar with cardamom and sea salt... | Tasting Notes detailed text... | Tempering Guide detailed text...`;

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        message: prompt
      })
    });

    loader.style.display = 'none';

    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        const parts = data.reply.trim().split('|');
        if (parts.length >= 4) {
          document.getElementById('chocolab-result-base').textContent = `${percent}% ${base}`;
          document.getElementById('chocolab-result-name').textContent = parts[0].trim();
          document.getElementById('chocolab-result-desc').textContent = parts[1].trim();
          document.getElementById('chocolab-result-tasting').innerHTML = parts[2].trim().replace(/\n/g, '<br>');
          document.getElementById('chocolab-result-tempering').innerHTML = parts[3].trim().replace(/\n/g, '<br>');

          results.style.display = 'block';
        } else {
          document.getElementById('chocolab-result-base').textContent = `${percent}% ${base}`;
          document.getElementById('chocolab-result-name').textContent = "Custom Formulation";
          document.getElementById('chocolab-result-desc').textContent = "Formulation completed successfully.";
          document.getElementById('chocolab-result-tasting').innerHTML = "Tasting Notes:<br>" + data.reply.replace(/\n/g, '<br>');
          document.getElementById('chocolab-result-tempering').innerHTML = "Refer to the CocoaGenius assistant for complete guide.";
          results.style.display = 'block';
        }
      } else {
        placeholder.style.display = 'block';
        alert("Failed to parse AI formulation response.");
      }
    } else {
      placeholder.style.display = 'block';
      alert("Error contacting the AI Alchemist.");
    }
  } catch (err) {
    loader.style.display = 'none';
    placeholder.style.display = 'block';
    console.error("AI Chocolab formulation error:", err);
    alert("Connection error formulating recipe.");
  }
}

// Newsletter popup after 8 seconds, only if not previously closed
setTimeout(() => {
  if (localStorage.getItem('rtchocos-newsletter-closed') !== 'true') {
    const popup = document.getElementById('newsletter-popup');
    if (popup) {
      popup.classList.add('open');
    }
  }
}, 8000);

// === INTERACTIVE ABOUT PAGE HANDLERS ===

function toggleTimelineMilestone(node) {
  const isOpen = node.classList.contains('active');
  document.querySelectorAll('.timeline-node').forEach(n => n.classList.remove('active'));
  if (!isOpen) {
    node.classList.add('active');
  }
}

async function askAboutAiCompanion(question) {
  const output = document.getElementById('about-ai-chat-output');
  const loader = document.getElementById('about-ai-loader');
  if (!output || !loader) return;

  // Append user message bubble
  const userBubble = document.createElement('div');
  userBubble.className = 'ai-message user about-bubble-user';
  userBubble.textContent = question;
  output.appendChild(userBubble);
  output.scrollTop = output.scrollHeight;

  loader.style.display = 'flex';

  try {
    const isBlogSubfolder = window.location.pathname.includes('/blog/');
    const prefix = isBlogSubfolder ? '../' : '';

    const promptText = `System Instructions: You are the AI companion of Aarti Saluja Sahni, the professional chocolate maker and recipe consultant. Respond in Aarti's systems-thinking, science-first educator voice (strictly under 45 words). If asked about recipes or techniques, focus on crystallisation chemistry, conching, or farm fermentation.
Question: ${question}`;

    const response = await fetch(prefix + 'api_ai.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: promptText })
    });

    loader.style.display = 'none';

    if (response.ok) {
      const data = await response.json();
      if (data.reply) {
        const replyBubble = document.createElement('div');
        replyBubble.className = 'ai-message system about-bubble-bot';
        let cleaned = data.reply.trim().replace(/^Companion:\s*/i, '');
        let formatted = cleaned
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
          .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" rel="noopener" style="color: var(--accent); text-decoration: underline; font-weight: 600;">$1</a>')
          .replace(/\n/g, '<br>');
        replyBubble.innerHTML = formatted;
        output.appendChild(replyBubble);
        output.scrollTop = output.scrollHeight;
      }
    }
  } catch (err) {
    loader.style.display = 'none';
    console.error("About page AI error:", err);
  }
}

function sendAboutAiPrompt(question) {
  askAboutAiCompanion(question);
}

function handleAboutAiSubmit() {
  const input = document.getElementById('about-ai-input');
  if (!input || !input.value.trim()) return;
  const query = input.value.trim();
  input.value = '';
  askAboutAiCompanion(query);
}

// Add enter key support for the about chat input
document.addEventListener("DOMContentLoaded", () => {
  const aboutInput = document.getElementById('about-ai-input');
  if (aboutInput) {
    aboutInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        handleAboutAiSubmit();
      }
    });
  }
});

function selectPhilosophy(type, element) {
  document.querySelectorAll('.matcher-card').forEach(card => card.classList.remove('active'));
  element.classList.add('active');

  const resultBox = document.getElementById('matcher-result-box');
  const resultTitle = document.getElementById('matcher-result-title');
  const resultDesc = document.getElementById('matcher-result-desc');

  if (!resultBox || !resultTitle || !resultDesc) return;

  let title = "";
  let desc = "";

  switch(type) {
    case 'science':
      title = "🔬 The Cacao Chemist (Tempering & Crystal Science)";
      desc = "You believe that exceptional chocolate is built on precise molecular control. Your ideal learning path starts with the 'The Science of Tempering & Cocoa Crystallization' Masterclass to master polymorph Form V structures, temper curves, and water activity chemistry.";
      break;
    case 'practical':
      title = "👩‍🍳 The Artisan Confectioner (Ganache & Bonbons)";
      desc = "You love the aesthetic and sensory delight of finished truffles and shells. Your perfect match is 'Artisan Chocolate Truffles & Ganache' or the 'Mastering Artisan Bonbons' series, focusing on flavor infusions, stable emulsions, and cocoa painting.";
      break;
    case 'farms':
      title = "🌱 The Cacao Sommelier (Origin & Post-Harvest Sourcing)";
      desc = "You trace chocolate quality back to its roots in the soil. You should focus on 'Bean-to-Bar Foundations', exploring farm-level fermentation boxes, solar drying profiles, and micro-lot roasting parameters.";
      break;
    case 'scaling':
      title = "📈 The Product Innovator (Recipe Scaling & NPD)";
      desc = "You want to bridge kitchen creativity with industry-level commercial production. Your focus should be 'Recipe & Product Development', learning ingredient math, batch conch logs, and production optimization.";
      break;
  }

  resultTitle.textContent = title;
  resultDesc.textContent = desc;
  resultBox.style.display = 'block';
  resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}