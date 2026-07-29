// --- HELPERS ----------------------------------------------------
function getCorrectedPath(path) {
  if (!path || path.startsWith('http') || path.startsWith('data:')) {
    return path;
  }
  const isBlogSubfolder = window.location.pathname.includes('/blog/');
  return isBlogSubfolder ? '../' + path : path;
}

// --- RESILIENT API FETCH WRAPPER ---
async function fetchApi(url, options = {}) {
  const timeout = options.timeout || 12000;
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), timeout);

  const fetchOptions = {
    ...options,
    signal: controller.signal
  };

  try {
    const res = await fetch(url, fetchOptions);
    clearTimeout(timeoutId);
    if (!res.ok) {
      throw new Error(`HTTP ${res.status}: ${res.statusText}`);
    }
    return await res.json();
  } catch (err) {
    clearTimeout(timeoutId);
    if (err.name === 'AbortError') {
      throw new Error('Request timed out. Please try again.');
    }
    throw err;
  }
}

// --- GLOBAL KEYBOARD ACCESSIBILITY LISTENERS ---
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' || e.keyCode === 27) {
    // Close AI Chat drawer if open
    const aiDrawer = document.getElementById('ai-chat-drawer');
    const aiOverlay = document.getElementById('ai-drawer-overlay');
    if (aiDrawer && aiDrawer.classList.contains('open')) {
      if (typeof toggleAiDrawer === 'function') {
        toggleAiDrawer();
      } else {
        aiDrawer.classList.remove('open');
        if (aiOverlay) aiOverlay.classList.remove('visible');
        document.body.style.overflow = '';
      }
    }
    // Close Mobile menu if open
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenu && mobileMenu.classList.contains('open')) {
      mobileMenu.classList.remove('open');
    }
    // Close Theme Tester menu if open
    const themeMenu = document.getElementById('theme-tester-menu');
    if (themeMenu && themeMenu.classList.contains('open')) {
      themeMenu.classList.remove('open');
    }
  }
});

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

// --- SCROLL REVEAL OBSERVER ---
// --- ANIME.JS POWERED SCROLL REVEAL & STAGGER ANIMATIONS ---
function initAnimeAnimations() {
  if (typeof anime === 'undefined') return;

  // 1. Staggered scroll entrance for sections & grid cards
  const animeObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        
        // Section container smooth fade up
        anime({
          targets: el,
          translateY: [30, 0],
          opacity: [0, 1],
          duration: 900,
          easing: 'easeOutCubic'
        });

        // Stagger child cards if present
        const cards = el.querySelectorAll('.card, .why-card, .workshop-card, .value-item, .cred-item');
        if (cards.length > 0) {
          anime({
            targets: cards,
            translateY: [25, 0],
            opacity: [0, 1],
            scale: [0.97, 1],
            delay: anime.stagger(100, { start: 150 }),
            duration: 800,
            easing: 'easeOutQuad'
          });
        }

        // Counter animation for credibility numbers
        const credNums = el.querySelectorAll('.cred-num');
        credNums.forEach(numEl => {
          const rawText = numEl.textContent.trim();
          const targetVal = parseInt(rawText, 10);
          if (!isNaN(targetVal)) {
            const obj = { val: 0 };
            const suffix = rawText.replace(/[0-9]/g, '');
            anime({
              targets: obj,
              val: targetVal,
              round: 1,
              duration: 2000,
              easing: 'easeOutExpo',
              update: function() {
                numEl.textContent = obj.val + suffix;
              }
            });
          }
        });

        observer.unobserve(el);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.reveal, #cred-strip').forEach(el => {
    animeObserver.observe(el);
  });

  // 2. Floating leaves bobbing animation with Anime.js
  anime({
    targets: '.deco-leaf-left',
    translateY: [-12, 12],
    rotate: [-5, 8],
    duration: 6000,
    direction: 'alternate',
    loop: true,
    easing: 'easeInOutSine'
  });

  anime({
    targets: '.deco-leaf-right',
    translateY: [10, -14],
    rotate: [6, -8],
    duration: 7500,
    direction: 'alternate',
    loop: true,
    easing: 'easeInOutSine'
  });

  // 3. Card 3D tilt micro-interactions on mousemove
  document.querySelectorAll('.card, .why-card, .workshop-card, .wheel-detail-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
      anime({
        targets: card,
        scale: 1.025,
        duration: 350,
        easing: 'easeOutCubic'
      });
    });

    card.addEventListener('mouseleave', () => {
      anime({
        targets: card,
        scale: 1,
        rotateX: 0,
        rotateY: 0,
        duration: 450,
        easing: 'easeOutElastic(1, .6)'
      });
    });
  });
}

function initScrollReveal() {
  initAnimeAnimations();
}

initApp();

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

document.addEventListener('DOMContentLoaded', () => {
  initAnimeAnimations();
});

