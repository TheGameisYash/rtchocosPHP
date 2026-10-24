# RT Chocos - Code Redundancy Analysis & Cleanup Guide

> **Last Updated:** 2026-10-23  
> **Purpose:** Identify duplicate code, unused files, and consolidation opportunities

---

## 📊 Executive Summary

### **Codebase Statistics**
- **Total Files:** 158
- **Total Lines:** 78,896
- **CSS Files:** 19 files, 22,840 lines
- **JavaScript Files:** 18 files, 4,427 lines
- **PHP Files:** ~80 files, ~51,629 lines

### **Key Findings**
1. ✅ **Two unused enhancement files** (landing-enhancements.css/js) - can be deleted
2. ⚠️ **Duplicate CSS patterns** across multiple files
3. ⚠️ **Similar JavaScript initialization patterns** can be consolidated
4. ⚠️ **Repeated database query patterns** can use helper functions
5. ✅ **Well-organized modular structure** - most files have clear purposes

---

## 🗑️ Files That Can Be Safely Deleted

### **1. Unused Landing Page Enhancement Files**

**Status:** Created but never loaded (user rejected changes)

```bash
# Safe to delete:
css/landing-enhancements.css          # 0 references, not loaded
js/landing-enhancements.js            # 0 references, not loaded
```

**Why:** These files were created for a landing page upgrade that the user rejected. They exist on disk but are not referenced in any `<link>` or `<script>` tags.

**Verification:**
```bash
# Check if files are referenced:
grep -r "landing-enhancements" *.php *.html
# Result: No matches (confirmed not loaded)
```

**Action:**
```bash
rm css/landing-enhancements.css
rm js/landing-enhancements.js
```

**Impact:** None - these files are completely unused

---

## 🔄 Duplicate CSS Patterns

### **1. Scroll Reveal Animations**

**Duplicated in:**
- `css/innovation-lab-enhancements.css` (lines 63-87)
- `css/landing-sections-upgrade.css` (lines 63-88, 109-128, 183-207)

**Pattern:**
```css
/* Same pattern repeated 3+ times */
.element {
  opacity: 0;
  transform: translateY(30px);
}

.element.is-visible {
  opacity: 1;
  transform: translateY(0);
  animation: fade-up 0.6s ease forwards;
}

@keyframes fade-up {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
```

**Consolidation Opportunity:**

Create shared utility classes in `css/base.css`:

```css
/* Add to css/base.css */

/* Scroll Reveal Utilities */
.scroll-reveal {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.6s ease, transform 0.6s ease;
}

.scroll-reveal.is-visible {
  opacity: 1;
  transform: translateY(0);
}

@keyframes fade-up {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes fade-up-stagger {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
```

**Then replace** in specific files with class names:
```html
<div class="trust-col scroll-reveal">...</div>
<div class="workshop-card scroll-reveal">...</div>
```

**Savings:** ~150 lines of CSS

---

### **2. Hover Transform Patterns**

**Duplicated in:**
- `css/innovation-lab.css`
- `css/landing-luxury.css`
- `css/shop-luxury.css`
- `css/landing-sections-upgrade.css`

**Pattern:**
```css
/* Same pattern repeated 10+ times */
.card {
  transition: all 0.35s ease;
}

.card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 40px rgba(0,0,0,0.1);
}
```

**Consolidation Opportunity:**

Create utility classes in `css/components.css`:

```css
/* Hover Transform Utilities */
.hover-lift {
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.hover-lift:hover {
  transform: translateY(-6px);
}

.hover-lift-subtle:hover {
  transform: translateY(-3px);
}

.hover-lift-strong:hover {
  transform: translateY(-8px);
}

.hover-shadow {
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  transition: box-shadow 0.3s ease;
}

.hover-shadow:hover {
  box-shadow: 0 16px 40px rgba(0,0,0,0.1);
}
```

**Then use:**
```html
<div class="card hover-lift hover-shadow">...</div>
```

**Savings:** ~200 lines of CSS

---

### **3. GPU Acceleration Optimizations**

**Duplicated in:**
- `css/innovation-lab-enhancements.css` (line 125-131)
- `css/landing-sections-upgrade.css` (line 458-467)

**Pattern:**
```css
.element {
  will-change: transform;
  backface-visibility: hidden;
  transform: translateZ(0);
}
```

**Consolidation:**

Add once in `css/base.css`:

```css
/* Performance optimizations - apply to animated elements */
.gpu-accelerated {
  will-change: transform;
  backface-visibility: hidden;
  transform: translateZ(0);
}
```

**Savings:** ~30 lines of CSS

---

### **4. Focus Visible Styles**

**Duplicated in:**
- `css/innovation-lab-enhancements.css` (lines 133-142)
- `css/landing-sections-upgrade.css` (lines 472-480)

**Pattern:**
```css
.element:focus-visible {
  outline: 3px solid #235436;
  outline-offset: 4px;
  border-radius: 4px;
}
```

**Consolidation:**

Add global focus styles in `css/base.css`:

```css
/* Global accessible focus styles */
:focus-visible {
  outline: 3px solid var(--color-primary, #235436);
  outline-offset: 4px;
  border-radius: 4px;
}

/* Override for specific elements if needed */
.custom-focus:focus-visible {
  /* Custom styles */
}
```

**Savings:** ~40 lines of CSS

---

## 🔄 Duplicate JavaScript Patterns

### **1. Intersection Observer Setup**

**Duplicated in:**
- `js/innovation-lab-enhancements.js` (lines 25-73)
- `js/landing-sections-upgrade.js` (lines 25-88)

**Pattern:**
```javascript
// Same Intersection Observer setup repeated
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    }
  });
}, {
  threshold: 0.15,
  rootMargin: '0px 0px -80px 0px'
});
```

**Consolidation Opportunity:**

Create shared utility in `js/core.js`:

```javascript
/**
 * Scroll Reveal Utility
 * @param {string} selector - CSS selector for elements to reveal
 * @param {object} options - Intersection Observer options
 */
window.ScrollReveal = {
  init(selector, options = {}) {
    const defaultOptions = {
      threshold: 0.15,
      rootMargin: '0px 0px -80px 0px',
      ...options
    };

    if (!('IntersectionObserver' in window)) {
      // Fallback: show everything
      document.querySelectorAll(selector).forEach(el => {
        el.classList.add('is-visible');
      });
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, defaultOptions);

    document.querySelectorAll(selector).forEach(el => observer.observe(el));
  },

  staggered(selector, delay = 100, options = {}) {
    const elements = document.querySelectorAll(selector);
    elements.forEach((el, index) => {
      el.style.transitionDelay = `${index * delay}ms`;
    });
    this.init(selector, options);
  }
};
```

**Then use:**
```javascript
// In any page-specific JS:
ScrollReveal.init('.trust-col, .workshop-card, .experiment-card');
ScrollReveal.staggered('.lab-card', 150);
```

**Savings:** ~200 lines of JavaScript

---

### **2. Keyboard Navigation Pattern**

**Duplicated in:**
- `js/innovation-lab-enhancements.js` (lines 75-118)
- `js/landing-sections-upgrade.js` (lines 94-184)

**Pattern:**
```javascript
// Arrow key navigation repeated
element.addEventListener('keydown', (e) => {
  if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
    targetIndex = (index + 1) % elements.length;
  } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
    targetIndex = (index - 1 + elements.length) % elements.length;
  }
  elements[targetIndex].focus();
});
```

**Consolidation:**

Create utility in `js/core.js`:

```javascript
/**
 * Keyboard Navigation Utility
 */
window.KeyboardNav = {
  addArrowNavigation(selector) {
    const elements = Array.from(document.querySelectorAll(selector));
    
    elements.forEach((el, index) => {
      el.setAttribute('tabindex', '0');
      
      el.addEventListener('keydown', (e) => {
        let targetIndex = index;
        
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
          e.preventDefault();
          targetIndex = (index + 1) % elements.length;
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
          e.preventDefault();
          targetIndex = (index - 1 + elements.length) % elements.length;
        } else if (e.key === 'Home') {
          e.preventDefault();
          targetIndex = 0;
        } else if (e.key === 'End') {
          e.preventDefault();
          targetIndex = elements.length - 1;
        } else {
          return;
        }
        
        elements[targetIndex].focus();
      });
    });
  },

  addEnterSpaceClick(selector) {
    document.querySelectorAll(selector).forEach(el => {
      el.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          el.click();
        }
      });
    });
  }
};
```

**Then use:**
```javascript
KeyboardNav.addArrowNavigation('.hotspot, .card, .nav-item');
KeyboardNav.addEnterSpaceClick('.clickable-card');
```

**Savings:** ~150 lines of JavaScript

---

### **3. Module Initialization Pattern**

**Duplicated in:**
- Every JS file has the same IIFE + DOMContentLoaded pattern

**Pattern:**
```javascript
(function() {
  'use strict';
  
  const MODULE = {
    init() { /* ... */ }
  };
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => MODULE.init());
  } else {
    MODULE.init();
  }
})();
```

**Consolidation:**

Create utility in `js/core.js`:

```javascript
/**
 * Module Registration Utility
 * Auto-handles DOMContentLoaded timing
 */
window.RTChocos = window.RTChocos || {};

RTChocos.registerModule = function(name, initFn) {
  const init = () => {
    try {
      initFn();
      console.log(`✅ Module "${name}" initialized`);
    } catch (error) {
      console.error(`❌ Module "${name}" failed:`, error);
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
};
```

**Then use:**
```javascript
// In any page-specific JS:
RTChocos.registerModule('LandingSections', function() {
  // Module code here
  console.log('Landing sections ready');
});
```

**Savings:** ~50 lines of JavaScript (boilerplate reduction)

---

## 🔄 Duplicate PHP Patterns

### **1. Database Connection Pattern**

**Good:** Already consolidated in `includes/db.php` via `get_db()` singleton ✅

**No action needed** - this is correctly done.

---

### **2. Page Header/Footer Pattern**

**Good:** Already consolidated via `includes/header.php` and `includes/footer.php` ✅

**No action needed** - this is correctly done.

---

### **3. Blog Query Pattern**

**Duplicated in:**
- `blog.php`
- `index.php`
- `admin/blogs.php`
- Multiple API endpoints

**Pattern:**
```php
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE is_published = 1 ORDER BY created_at DESC");
$stmt->execute();
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

**Consolidation:**

Add to `includes/blog-data.php`:

```php
<?php
/**
 * Centralized Blog Data Functions
 */

function get_all_published_blogs($limit = null, $offset = 0) {
    $pdo = get_db();
    $sql = "SELECT * FROM blogs WHERE is_published = 1 ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limit, $offset]);
    } else {
        $stmt = $pdo->query($sql);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_blog_by_slug($slug) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ? AND is_published = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_blogs_by_category($category, $limit = null) {
    $pdo = get_db();
    $sql = "SELECT * FROM blogs WHERE category = ? AND is_published = 1 ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category, $limit]);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category]);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_featured_blogs($limit = 3) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT * FROM blogs 
        WHERE is_published = 1 
        ORDER BY views DESC, created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function increment_blog_views($slug) {
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE blogs SET views = views + 1 WHERE slug = ?");
    return $stmt->execute([$slug]);
}
?>
```

**Then use:**
```php
// Instead of raw queries everywhere:
$blogs = get_all_published_blogs();
$article = get_blog_by_slug($slug);
$scienceBlogs = get_blogs_by_category('Science', 5);
```

**Savings:** ~100 lines of PHP (query consolidation)

---

### **4. Innovation Lab Query Pattern**

**Consolidation:**

Create `includes/lab-data.php`:

```php
<?php
/**
 * Centralized Innovation Lab Data Functions
 */

function get_active_experiments($category = null) {
    $pdo = get_db();
    
    if ($category) {
        $stmt = $pdo->prepare("
            SELECT * FROM lab_experiments 
            WHERE is_active = 1 AND category = ? 
            ORDER BY sort_order ASC, created_at DESC
        ");
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query("
            SELECT * FROM lab_experiments 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, created_at DESC
        ");
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_experiment_by_slug($slug) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT * FROM lab_experiments 
        WHERE slug = ? AND is_active = 1
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_failed_batches() {
    $pdo = get_db();
    $stmt = $pdo->query("
        SELECT * FROM lab_failed_batches 
        WHERE is_active = 1 
        ORDER BY sort_order ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_what_if_questions() {
    $pdo = get_db();
    $stmt = $pdo->query("
        SELECT * FROM lab_what_if_questions 
        WHERE is_active = 1 
        ORDER BY sort_order ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_ingredient_categories() {
    $pdo = get_db();
    $stmt = $pdo->query("
        SELECT * FROM lab_ingredient_categories 
        WHERE is_active = 1 
        ORDER BY sort_order ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_future_pillars() {
    $pdo = get_db();
    $stmt = $pdo->query("
        SELECT * FROM lab_future_pillars 
        WHERE is_active = 1 
        ORDER BY sort_order ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
```

**Savings:** ~80 lines of PHP

---

## 📦 Consolidation Summary

### **Immediate Wins (Low Risk)**

| Action | Files Affected | Lines Saved | Impact |
|--------|---------------|-------------|--------|
| Delete unused landing-enhancements | 2 files | ~800 lines | None (unused) |
| Consolidate scroll reveal CSS | 3 files | ~150 lines | Low |
| Consolidate hover patterns CSS | 5 files | ~200 lines | Low |
| Create ScrollReveal utility JS | 2 files | ~200 lines | Low |
| Create KeyboardNav utility JS | 2 files | ~150 lines | Low |

**Total Immediate Savings:** ~1,500 lines + 2 files deleted

---

### **Medium-Term Improvements (Moderate Risk)**

| Action | Files Affected | Lines Saved | Impact |
|--------|---------------|-------------|--------|
| Consolidate blog queries | 10+ files | ~100 lines | Medium |
| Consolidate lab queries | 5 files | ~80 lines | Medium |
| Create shared PHP helpers | Multiple | ~150 lines | Medium |

**Total Medium-Term Savings:** ~330 lines

---

### **Long-Term Refactoring (Higher Risk)**

1. **Component Library:** Create reusable card/modal/button components
2. **CSS Variables Migration:** Move all hardcoded colors to CSS variables
3. **API Layer:** Centralize all API endpoint patterns
4. **Template System:** Consider lightweight PHP templating

---

## 🎯 Recommended Action Plan

### **Phase 1: Immediate Cleanup (30 minutes)**

1. **Delete unused files:**
   ```bash
   rm css/landing-enhancements.css
   rm js/landing-enhancements.js
   ```

2. **Verify no breakage:**
   ```bash
   # Test homepage
   # Test innovation lab
   # All should work identically
   ```

**Impact:** Zero risk, instant cleanup

---

### **Phase 2: CSS Consolidation (2 hours)**

1. **Add utilities to `css/base.css`:**
   - Scroll reveal classes
   - GPU acceleration class
   - Global focus styles

2. **Add utilities to `css/components.css`:**
   - Hover transform utilities
   - Shadow utilities

3. **Update specific files** to use new utilities

4. **Test thoroughly:**
   - Homepage scroll animations
   - Innovation Lab interactions
   - All hover effects

**Impact:** Low risk, visual consistency maintained

---

### **Phase 3: JavaScript Consolidation (2 hours)**

1. **Add utilities to `js/core.js`:**
   - ScrollReveal utility
   - KeyboardNav utility
   - Module registration utility

2. **Refactor page-specific JS** to use utilities

3. **Test thoroughly:**
   - Scroll reveals
   - Keyboard navigation
   - All interactive elements

**Impact:** Low risk, better maintainability

---

### **Phase 4: PHP Consolidation (3 hours)**

1. **Create `includes/blog-data.php`** with all blog functions

2. **Create `includes/lab-data.php`** with all lab functions

3. **Update all pages** to use new functions

4. **Test thoroughly:**
   - Blog listing pages
   - Innovation Lab page
   - Admin panels

**Impact:** Medium risk, requires thorough testing

---

## 📝 Testing Checklist After Changes

### **Visual/Functional Tests**

- [ ] Homepage loads correctly
- [ ] Scroll animations work on all sections
- [ ] Hover effects work on cards
- [ ] Innovation Lab page loads
- [ ] Keyboard navigation works
- [ ] Blog listing works
- [ ] Single blog article loads
- [ ] Admin panel functions
- [ ] Shop page loads
- [ ] Mobile responsive behavior intact

### **Performance Tests**

- [ ] Page load time unchanged or improved
- [ ] No console errors
- [ ] CSS file sizes reduced
- [ ] JS file sizes reduced or same

### **Browser Tests**

- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (if available)
- [ ] Mobile browsers

---

## 🚫 What NOT to Change

### **Keep As-Is:**

1. **`includes/db.php`** - Already well optimized ✅
2. **`includes/header.php` / `footer.php`** - Already consolidated ✅
3. **`css/variables.css`** - Core design system ✅
4. **Theme switching logic** - Working correctly ✅
5. **Admin panel structure** - Functional and organized ✅

### **Don't Touch:**

- Database schema (unless you need new features)
- Core authentication logic
- Payment processing code
- Email sending logic
- File upload handling

---

## 📊 Before vs After Metrics

### **Current State**
- CSS: 19 files, 22,840 lines
- JS: 18 files, 4,427 lines
- PHP: ~80 files, ~51,629 lines

### **After Phase 1-4**
- CSS: 17 files, ~21,490 lines (~6% reduction)
- JS: 16 files, ~4,077 lines (~8% reduction)
- PHP: ~80 files, ~51,299 lines (~0.6% reduction)

### **Total Reduction**
- **~1,830 lines removed**
- **2 files deleted**
- **Better maintainability**
- **Improved consistency**

---

## 🎓 Lessons & Best Practices

### **What's Working Well:**

1. ✅ Modular file structure
2. ✅ Consistent naming conventions
3. ✅ Database singleton pattern
4. ✅ PHP includes for header/footer
5. ✅ CSS variable theming system

### **What Can Improve:**

1. ⚠️ More shared CSS utilities
2. ⚠️ More shared JS utilities
3. ⚠️ Centralized database query functions
4. ⚠️ Delete unused files promptly

### **Going Forward:**

1. **Before adding new CSS:** Check if utility exists
2. **Before writing JS function:** Check if core utility exists
3. **Before writing SQL:** Check if helper function exists
4. **Test deleted files:** Run full site test after deleting

---

## 🔗 Related Documentation

- `PROJECT_OVERVIEW.md` - Quick reference
- `ARCHITECTURE.md` - System architecture
- `DATABASE_SCHEMA.md` - Complete schema
- `API_REFERENCE.md` - API endpoints

---

**Questions?**
- What file does X? → Check PROJECT_OVERVIEW.md
- How is Y structured? → Check ARCHITECTURE.md
- What does this table store? → Check DATABASE_SCHEMA.md
- Can I delete this? → Check this document first
