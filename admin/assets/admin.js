/* ==============================================================================
   JMN Admin — shared front-end
   Sidebar toggle, slug auto-generation, image drop-zone, TinyMCE 8 bootstrap.
   ============================================================================== */

(function () {
  'use strict';

  /* ── Sidebar toggle (mobile & tablet) ────────────────────────────── */
  var burger   = document.getElementById('adminBurger');
  var sidebar  = document.getElementById('adminSidebar');
  var backdrop = document.getElementById('adminSidebarBackdrop');
  var closeBtn = document.getElementById('adminSidebarClose');

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add('is-open');
    if (backdrop) backdrop.classList.add('is-open');
    document.body.classList.add('admin-body--locked');
    if (burger) burger.setAttribute('aria-expanded', 'true');
  }

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('is-open');
    if (backdrop) backdrop.classList.remove('is-open');
    document.body.classList.remove('admin-body--locked');
    if (burger) burger.setAttribute('aria-expanded', 'false');
  }

  if (burger && sidebar) {
    burger.addEventListener('click', function (e) {
      e.stopPropagation();
      if (sidebar.classList.contains('is-open')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });

    if (backdrop) {
      backdrop.addEventListener('click', closeSidebar);
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', closeSidebar);
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
        closeSidebar();
      }
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 1024 && sidebar.classList.contains('is-open')) {
        closeSidebar();
      }
    });

    // Close when navigating via a link on mobile
    var navLinks = sidebar.querySelectorAll('.admin-nav a, .admin-sidebar-foot a');
    navLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 1024) {
          closeSidebar();
        }
      });
    });
  }

  /* ── Slug auto-generation on the blog editor ─────────────────────── */
  var titleInput = document.getElementById('f_title');
  var slugInput  = document.getElementById('f_slug');
  var slugLock   = document.getElementById('slug_lock');
  var slugLocked = false;

  if (slugLock) {
    // Start locked when editing an existing post (slug already meaningful).
    slugLocked = slugLock.getAttribute('data-initial-locked') === '1';
    slugLock.classList.toggle('is-locked', slugLocked);
    slugLock.addEventListener('click', function () {
      slugLocked = !slugLocked;
      slugLock.classList.toggle('is-locked', slugLocked);
      if (slugLocked && titleInput && slugInput) {
        slugInput.value = slugify(titleInput.value);
      }
    });
  }

  function slugify(value) {
    return value.toLowerCase().trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  if (titleInput && slugInput && !slugLocked) {
    titleInput.addEventListener('input', function () {
      if (!slugLocked) slugInput.value = slugify(titleInput.value);
    });
  }

  /* ── Image drop-zone ─────────────────────────────────────────────── */
  var drop = document.getElementById('imageDrop');
  var fileInput = document.getElementById('f_image_file');
  var imgPreview = document.getElementById('imgPreview');
  if (drop && fileInput) {
    drop.addEventListener('click', function () { fileInput.click(); });
    drop.addEventListener('dragover', function (e) { e.preventDefault(); drop.classList.add('is-over'); });
    drop.addEventListener('dragleave', function () { drop.classList.remove('is-over'); });
    drop.addEventListener('drop', function (e) {
      e.preventDefault();
      drop.classList.remove('is-over');
      if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        fileInput.files = e.dataTransfer.files;
        showPreview(fileInput);
      }
    });
    fileInput.addEventListener('change', function () { showPreview(fileInput); });
  }
  function showPreview(input) {
    if (!imgPreview || !input.files || !input.files[0]) return;
    var url = URL.createObjectURL(input.files[0]);
    imgPreview.src = url;
    imgPreview.style.display = 'block';
  }

  /* ── TinyMCE 8 (blog content editor) ─────────────────────────────── */
  // Loaded on demand: only the blog editor page sets window.JMN_TINYMCE = true
  // and defines the target textarea (#f_content). Everything else on the admin
  // never contacts the CDN.
  if (window.JMN_TINYMCE && typeof tinymce !== 'undefined') {
    tinymce.init({
      selector: '#f_content',
      license_key: 'gpl',
      height: 520,
      menubar: false,
      branding: false,
      promotion: false,
      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons'
      ],
      toolbar: [
        'undo redo | blocks | bold italic underline strikethrough',
        'forecolor backcolor | alignleft aligncenter alignright alignjustify',
        'bullist numlist outdent indent | link image media table',
        'charmap emoticons | code preview fullscreen | removeformat help'
      ].join(' | '),
      content_style: [
        'body{font-family:Montserrat,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;font-size:15px;line-height:1.7;color:#344054;padding:16px;}',
        'h1,h2,h3,h4{color:#111a24;font-weight:700;line-height:1.3;}',
        'h4{font-size:22px;margin:24px 0 12px;}',
        'p{margin:0 0 16px;}',
        'code{background:#f2f4f7;padding:2px 5px;border-radius:4px;font-size:13px;}',
        'pre{background:#111a24;color:#e6ebf1;padding:16px;border-radius:10px;overflow-x:auto;font-size:13px;}',
        'blockquote{border-left:4px solid #f41a4a;background:#f7f5fb;margin:0 0 16px;padding:12px 18px;font-style:italic;}',
        'img{max-width:100%;height:auto;border-radius:10px;}',
        'a{color:#f41a4a;}'
      ].join(''),
      image_title: true,
      automatic_uploads: false,
      file_picker_types: 'image',
      file_picker_callback: function (cb, value, meta) {
        // Offer a simple URL prompt; upload is handled by the form field.
        var url = prompt('Image URL or path (e.g. assets/img/blog/5.jpg):');
        if (url) cb(url, { alt: '' });
      },
      table_default_attributes: { border: '1' },
      table_default_styles: { 'border-collapse': 'collapse', width: '100%' }
    });
  }
})();
