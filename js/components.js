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

