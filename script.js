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
];

const BLOGS = [
  { id:1, title:"Why pH is the Most Underrated Factor in Cocoa Powder", category:"Science", date:"Apr 2026", read:"7 min", excerpt:"How cocoa powder pH shapes colour, flavour, leavening, solubility and flavanol retention in chocolate work.", articleKey:"cocoa-ph", image:"assets/ph.png" },
  { id:2, title:"what really happens when Milk fat enters Chocolate?", category:"Science", date:"Mar 2026", read:"5 min", excerpt:"Why milk chocolate behaves so differently from dark, and what milk fat changes at the molecular level.", articleKey:"milkfat-chocolate", image:"assets/milkfat.png" },
  { id:4, title:"How to Flavor Chocolate Correctly", category:"Beginner Guide", date:"Apr 2026", read:"6 min", excerpt:"A practical guide to adding oils, extracts and infusions without seizing, splitting or dulling your chocolate.", articleKey:"flavor-chocolate", image:"assets/flavor.jpg" },
  { id:5, title:"Fat Bloom vs Sugar Bloom in Chocolate: A Practical Diagnosis Guide", category:"Science", date:"Apr 2026", read:"8 min", excerpt:"Learn to diagnose, prevent and fix the two most common chocolate surface defects — with science explained simply.",  articleKey:"fat-bloom-sugar-bloom", image:"assets/bloom.png" },
  { id:6, title:"Intimacy Chocolate — What It Is, What's In It, and Whether It Actually Works", category:"Science", date:"May 2026", read:"9 min", excerpt:"A deep, honest guide to intimacy chocolate — the ingredients, the science, the psychology, and the truth behind the fastest-growing niche in functional confectionery.", articleKey:"intimacy-chocolate", image:"assets/intimacy.png" },
  { id:7, title:"The Invisible Ingredient That Makes Chocolate Smooth", category:"Science", date:"Jun 2026", read:"6 min", excerpt:"Meet lecithin — the quiet emulsifier behind every velvety bite of chocolate you have ever loved.", articleKey:"lecithin-chocolate", image:"assets/lecithin.png" },
];

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
function workshopCardHTML(w) {
  const outcomes = w.outcomes.map(o => `<li>${o}</li>`).join('');
  return `
    <div class="card workshop-card">
      <div class="workshop-card-img">
        <span style="font-size:56px;">${w.image}</span>
        <span class="tag workshop-card-tag">${w.level}</span>
      </div>
      <div class="workshop-card-body">
        <h3>${w.title}</h3>
        <div class="workshop-meta">
          <span>? ${w.duration}</span>
          <span class="price">${w.price}</span>
        </div>
        <ul class="workshop-outcomes">${outcomes}</ul>
        <button class="btn-primary" style="width:100%;justify-content:center;padding:12px 24px;">Book Now</button>
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
          <span class="blog-date">${b.date} • ${b.read} read</span>
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
          <span class="blog-date">${b.date} • ${b.read} read</span>
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
    : '<div class="blog-comment-empty">No comments yet. Be the first to start the conversation.</div>';
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
    return;
  }

  const comments = getBlogComments(currentBlogArticleId);
  comments.unshift({
    name,
    text,
    date: new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })
  });
  saveBlogComments(currentBlogArticleId, comments);

  nameInput.value = '';
  commentInput.value = '';
  note.textContent = 'Your comment has been added.';
  renderBlogComments();
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

// --- INIT -------------------------------------------------------
renderHome();
renderWorkshops('all');
renderBlog();
restoreRouteFromLocation();


// Newsletter popup after 8 seconds, only if not previously closed
setTimeout(() => {
  if (localStorage.getItem('rtchocos-newsletter-closed') !== 'true') {
    const popup = document.getElementById('newsletter-popup');
    if (popup) {
      popup.classList.add('open');
    }
  }
}, 8000);