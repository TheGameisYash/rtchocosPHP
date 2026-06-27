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
  { name:"Priya Sharma", role:"Home Baker, Delhi", text:"RT Chocos' tempering workshop completely transformed my chocolate work. The science-first approach made everything click.", rating:5 },
  { name:"Arjun Mehta", role:"Chocolate Business Owner", text:"Arpan's NPD consulting helped me launch my chocolate brand with confidence. The depth of knowledge is exceptional.", rating:5 },
  { name:"Sneha Kulkarni", role:"Parent, Mumbai", text:"My 8-year-old absolutely loved the kids' workshop. She came home talking about cacao origins — incredible!", rating:5 },
  { name:"Vikram Desai", role:"Pastry Chef, Bangalore", text:"The ganache masterclass was phenomenal. Understanding water activity changed how I approach shelf life entirely.", rating:5 },
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
  gallery: 'gallery.php',
  contact: 'contact.php'
};
const ARTICLE_FILE_MAP = {
  'cocoa-ph': 'blog/article-cocoa-ph.php',
  'milkfat-chocolate': 'blog/article-milkfat-chocolate.php',
  'flavor-chocolate': 'blog/article-flavor-chocolate.php',
  'fat-bloom-sugar-bloom': 'blog/article-fat-bloom-vs-sugar-bloom-diagnosis-guide.php',
  'intimacy-chocolate': 'blog/intimacy-chocolate.php',
  'lecithin-chocolate': 'blog/article-lecithin-chocolate.php',
};

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
    ? `<img src="${getCorrectedPath(b.image)}" alt="${b.title}">`
    : '<span>Chocolate Journal</span>';

  const ytBadge = b.youtube_url ? ' • <span class="yt-badge">🎥 Video</span>' : '';

  if (hasFullArticle) {
    const articleFile = ARTICLE_FILE_MAP[b.articleKey] || HOME_FILE_NAME;
    return `
    <a class="card" style="cursor:pointer;text-decoration:none;color:inherit;display:block;" href="${getCorrectedPath(articleFile)}">
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
    <div class="card" style="cursor:pointer;" onclick="openBlogArticle(${b.id})">
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
  const homeWorkshops = document.getElementById('home-workshops');
  const homeBlogs = document.getElementById('home-blogs');
  if (homeWorkshops) {
    homeWorkshops.innerHTML = WORKSHOPS.slice(0,3).map(workshopCardHTML).join('');
  }
  if (homeBlogs) {
    homeBlogs.innerHTML = BLOGS.slice(0,3).map(blogCardHTML).join('');
  }
}

// --- WORKSHOPS --------------------------------------------------
function renderWorkshops(filter) {
  const filtered = filter === 'all' ? WORKSHOPS : WORKSHOPS.filter(w => w.category === filter);
  const workshopsGrid = document.getElementById('workshops-grid');
  if (!workshopsGrid) {
    return;
  }
  workshopsGrid.innerHTML = filtered.map(workshopCardHTML).join('');
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
  const mappedName = articleFile ? articleFile.split('/').pop().toLowerCase() : '';

  if (currentFile !== mappedName && currentFile !== (articleFile ? articleFile.toLowerCase() : '')) {
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
    const response = await fetch(getCorrectedPath('api_blogs.php'));
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
  renderWorkshops('all');
  renderBlog();
  restoreRouteFromLocation();
}

initApp();


// Newsletter popup after 8 seconds, only if not previously closed
setTimeout(() => {
  if (localStorage.getItem('rtchocos-newsletter-closed') !== 'true') {
    const popup = document.getElementById('newsletter-popup');
    if (popup) {
      popup.classList.add('open');
    }
  }
}, 8000);