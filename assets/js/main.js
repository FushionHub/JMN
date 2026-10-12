/* main js */
(function ($) {
    "use strict";

    /* Loader */
    $(window).on("load", function () {
        $(".bix-loader").fadeOut("slow");
    });

    /* Aos animation on scroll */
    AOS.init({
        once: true,
    });

    /* Header fixed */
    $(function () {
        var header = $(".bix-static");
        $(window).scroll(function () {
            var scroll = $(window).scrollTop();

            if (scroll >= 10) {
                header.removeClass('bix-static').addClass("bix-fixed");
            } else {
                header.removeClass("bix-fixed").addClass('bix-static');
            }
        });
    });

    /* Mobaile menu slider */
    $('.navbar-toggler').on("click", function () {
        $('.bix-sidebar-overlay').fadeIn();
        $('.bix-mobile-menu').addClass("bix-menu-open");
    });

    $('.bix-sidebar-overlay, .bix-close').on("click", function () {
        $('.bix-sidebar-overlay').fadeOut();
        $('.bix-mobile-menu').removeClass("bix-menu-open");
    });

    function ResponsiveMobilemsMenu() {
        var $msNav = $(".bix-menu-content, .overlay-menu"),
            $msNavSubMenu = $msNav.find(".sub-menu");
        $msNavSubMenu.parent().prepend('<span class="menu-toggle"></span>');

        $msNav.on("click", "li a, .menu-toggle", function (e) {
            var $this = $(this);
            if ($this.attr("href") === "#" || $this.hasClass("menu-toggle")) {
                e.preventDefault();
                if ($this.siblings("ul:visible").length) {
                    $this.parent("li").removeClass("active");
                    $this.siblings("ul").slideUp();
                    $this.parent("li").find("li").removeClass("active");
                    $this.parent("li").find("ul:visible").slideUp();
                } else {
                    $this.parent("li").addClass("active");
                    $this.closest("li").siblings("li").removeClass("active").find("li").removeClass("active");
                    $this.closest("li").siblings("li").find("ul:visible").slideUp();
                    $this.siblings("ul").slideDown();
                }
            }
        });
    }

    ResponsiveMobilemsMenu();

    /* hero parallaxmouse */
    if (matchMedia('only screen and (min-width: 991px)').matches) {
        $(window).on('mousemove', function (e) {
            var w = $(window).width();
            var h = $(window).height();
            var offsetX = 0.5 - e.pageX / w;
            var offsetY = 0.5 - e.pageY / h;

            $(".hero-parallax").each(function (i, el) {
                var offset = parseInt($(el).data('offset'));
                var translate = "translate3d(" + Math.round(offsetX * offset) + "px," + Math.round(offsetY * offset) + "px, 0px)";

                $(el).css({
                    '-webkit-transform': translate,
                    'transform': translate,
                    'moz-transform': translate
                });
            });
        });
    }

    /* Portfolio */
    $(function () {
        var filterList = {
            init: function () {
                $('.item-grid').mixItUp({
                    selectors: {
                        target: '.item',
                        filter: '.filter'
                    },
                    load: {
                        filter: 'all'
                    }
                });
            }
        };
        filterList.init();
    });

    /* Testimonials */
    $('.testimonials-slider').owlCarousel({
        loop: true,
        margin: 24,
        responsiveClass: true,
        dots: false,
        nav: false,
        pagination: false,
        autoplay: true,
        autoplaySpeed: 2000,
        autoplayHoverPause: false,
        responsive: {
            0: {
                items: 1,
            }
        }
    });

    /* blog */
    $('.bix-blog-wrap').owlCarousel({
        loop: true,
        margin: 24,
        responsiveClass: true,
        dots: false,
        nav: false,
        pagination: false,
        responsive: {
            0: {
                items: 1,
            },
            768: {
                items: 2,
            }
        }
    });

    /*----------- modal ----------------*/
    $(".bix-modal-toggle").on("click", function () {
        $(".bix-modal-overlay").fadeIn();
        $(".bix-modal").fadeIn();
        $("body").addClass("bix-overflow-hidden")
        $(".bix-modal-dialog").addClass("bix-fadeOutUp");
        $(".bix-modal-dialog").removeClass("bix-fadeInDown");
    });

    $(".bix-close-modal, .bix-modal-overlay").on("click", function () {
        $(".bix-modal-overlay").fadeOut();
        $(".bix-modal").fadeOut();
        $("body").removeClass("bix-overflow-hidden")
        $(".bix-modal-dialog").removeClass("bix-fadeOutUp");
        $(".bix-modal-dialog").addClass("bix-fadeInDown");
    });

    /* Achievement tabs */
    $("#bix_activity li:nth-child(1)").addClass("active");
    $(".tab-activity-pane").hide();
    $(".tab-activity-pane:nth-child(1)").show();
    $("#bix_activity li").click(function () {
        $("#bix_activity li").removeClass("active");
        $(this).addClass("active");
        $(".tab-activity-pane").hide();
        var activeTab = $(this).find("a").attr("href");
        $(activeTab).fadeIn();
        return false;
    });

    /* Accordians toggle (faq) */
    $(".bix-accordion-header").click(function () {
        if ($(".bix-accordion-body").is(":visible")) {
            $(".bix-accordion-body").slideUp(300);
            $("h4").removeClass("active-arrow");
        }
        if ($(this).next(".bix-accordion-body").is(":visible")) {
            $(this).next(".bix-accordion-body").slideUp(300);
            $("h4").removeClass("active-arrow");
        } else {
            $(this).next(".bix-accordion-body").slideDown(300);
            $(this).addClass("active-arrow");
        }
    });

    /* contact tabs */
    $("#contact_tabs li:nth-child(1)").addClass("active");
    $(".tab-contact-pane").hide();
    $(".tab-contact-pane:nth-child(1)").show();
    $("#contact_tabs li").click(function () {
        $("#contact_tabs li").removeClass("active");
        $(this).addClass("active");
        $(".tab-contact-pane").hide();
        var activeTab = $(this).find("a").attr("href");
        $(activeTab).fadeIn();
        return false;
    });

    /* Tab to top */
    $(window).scroll(function () {
        if ($(this).scrollTop() > 50) {
            $(".back-to-top").fadeIn();
        } else {
            $(".back-to-top").fadeOut();
        }
    });

    var progressPath = document.querySelector('.back-to-top-wrap path');
    var pathLength = progressPath.getTotalLength();
    progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
    progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
    progressPath.style.strokeDashoffset = pathLength;
    progressPath.getBoundingClientRect();
    progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';
    var updateProgress = function () {
        var scroll = $(window).scrollTop();
        var height = $(document).height() - $(window).height();
        var progress = pathLength - (scroll * pathLength / height);
        progressPath.style.strokeDashoffset = progress;
    }
    updateProgress();
    $(window).scroll(updateProgress);
    var offset = 50;
    var duration = 550;
    jQuery(window).on('scroll', function () {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.back-to-top-wrap').addClass('active-progress');
        } else {
            jQuery('.back-to-top-wrap').removeClass('active-progress');
        }
    });
    jQuery('.back-to-top-wrap').on('click', function (event) {
        event.preventDefault();
        jQuery('html, body').animate({ scrollTop: 0 }, duration);
        return false;
    })

    /* Date */
    var date = new Date().getFullYear();
    document.getElementById("copyright_year").innerHTML = date;

    /* Tools Sidebar */
    $('.bix-tools-sidebar-toggle').on("click", function () {
        $('.bix-tools-sidebar').addClass("open-tools");
        $('.bix-tools-sidebar-overlay').fadeIn();
        $('.bix-tools-sidebar-toggle').hide();
    });
    $('.bix-tools-sidebar-overlay, .close-tools').on("click", function () {
        $('.bix-tools-sidebar').removeClass("open-tools");
        $('.bix-tools-sidebar-overlay').fadeOut();
        $('.bix-tools-sidebar-toggle').fadeIn();
    });

    /* ---------------------------------------------------------------
     * Appearance & language preference persistence
     * Keys: jmn_theme_mode | jmn_theme_dir | jmn_lang
     *
     * These are the visitor's own UI settings, not tracking. They are
     * written to localStorage unconditionally so a choice survives
     * navigation. They never leave the browser and are never sent to
     * the server, so they need no consent. Analytics remains gated in
     * config/tracker.php.
     * ------------------------------------------------------------- */
    var jmnTheme = (function () {
        var KEYS = { mode: "jmn_theme_mode", dir: "jmn_theme_dir", lang: "jmn_lang" };

        /* Theme / layout / language are the visitor's own UI preferences, not
         * tracking. They are stored in localStorage unconditionally so a choice
         * survives navigation. Analytics remains gated in config/tracker.php. */
        function read(key) {
            try { return window.localStorage.getItem(key); } catch (e) { return null; }
        }

        function write(key, value) {
            try { window.localStorage.setItem(key, value); } catch (e) {}
        }

        function applyStylesheet(id, href) {
            var node = document.getElementById(id);
            if (node) { node.parentNode.removeChild(node); }
            if (href) {
                var link = document.createElement("link");
                link.rel = "stylesheet";
                link.href = href;
                link.id = id;
                document.head.appendChild(link);
            }
        }

        function restore() {
            var mode = read(KEYS.mode) || "light";
            applyStylesheet("add_dark", mode === "dark" ? "assets/css/dark.css" : null);
            $(".bix-tools-dark .bix-tools-item").removeClass("active-dark-mode");
            $(".bix-tools-dark .bix-tools-item." + mode).addClass("active-dark-mode");

            var dir = read(KEYS.dir) || "ltr";
            applyStylesheet("add_rtl", dir === "rtl" ? "assets/css/rtl.css" : null);
            $(".bix-tools-rtl .bix-tools-item").removeClass("active-mode");
            $(".bix-tools-rtl .bix-tools-item." + dir).addClass("active-mode");
        }

        return {
            restore: restore,
            read: read,
            write: write,
            saveMode: function (value) { write(KEYS.mode, value); },
            saveDir: function (value) { write(KEYS.dir, value); },
            saveLang: function (value) { write(KEYS.lang, value); },
            langKey: KEYS.lang
        };
    })();

    jmnTheme.restore();

    /* RTL-LTR Modes */
    $(".bix-tools-rtl .bix-tools-item").on("click", function () {
        $(".active-mode").removeClass("active-mode");
        $(this).addClass("active-mode");
    });
    $(".ltr").on("click", function () {
        $("#add_rtl").remove();
    });
    $(".rtl").on("click", function () {
        $("#add_rtl").remove();
        $("head").append(
            '<link rel="stylesheet" href="assets/css/rtl.css" id="add_rtl">'
        );
    });

    /*Dark Light Modes */
    $(".bix-tools-dark .bix-tools-item").on("click", function () {
        $(".active-dark-mode").removeClass("active-dark-mode");
        $(this).addClass("active-dark-mode");
    });
    $(".light").on("click", function () {
        $("#add_dark").remove();
    });
    $(".dark").on("click", function () {
        $("#add_dark").remove();
        $("head").append(
            '<link rel="stylesheet" href="assets/css/dark.css" id="add_dark">'
        );
    });

    /* Persist appearance choices (gated by Preferences consent) */
    $(".bix-tools-dark .bix-tools-item").on("click", function () {
        jmnTheme.saveMode($(this).hasClass("dark") ? "dark" : "light");
    });
    $(".bix-tools-rtl .bix-tools-item").on("click", function () {
        jmnTheme.saveDir($(this).hasClass("rtl") ? "rtl" : "ltr");
    });

    /* ------------------------------------------------------------- *
     * Site language - Google Translate, loaded strictly on demand.
     *
     * The visible dropdown in the tools sidebar (jmn-lang-dd) shows the
     * selected language's flag and is the only UI. Translation itself is
     * done by Google reading its own first-party `googtrans` cookie on page
     * load - the same mechanism Google's own widget uses. Driving Google's
     * internal DOM (its .goog-te-combo select) is deliberately avoided: its
     * options are populated asynchronously and break without warning.
     *
     * Flow: pick a language -> paint the trigger -> save jmn_lang to
     * localStorage -> write/clear the googtrans cookie -> reload. On the next
     * load, if a non-English jmn_lang is stored, element.js is fetched
     * immediately and Google translates from the cookie. Nothing third-party
     * is requested on a first visit with no stored preference.
     * ------------------------------------------------------------- */
    var jmnTranslate = (function () {
        var LANG_KEY  = jmnTheme.langKey;
        var SCRIPT_ID = "jmn-google-translate";
        var ELEMENT   = "https://translate.googleapis.com/translate_a/element.js?cb=jmnTranslateElementInit";

        var dd        = document.getElementById("jmn-lang-dd");
        var trigger   = document.getElementById("jmn-lang-trigger");
        var menu      = document.getElementById("jmn-lang-menu");
        var flagEl    = document.getElementById("jmn-lang-current-flag");
        var labelEl   = document.getElementById("jmn-lang-current-label");

        function optionFor(lang) {
            return menu ? menu.querySelector('.jmn-lang-dd__option[data-lang="' + (lang || "en") + '"]') : null;
        }

        /* Mirror a language onto the visible trigger + list state. */
        function paint(lang) {
            var opt = optionFor(lang);
            var flag = opt ? opt.getAttribute("data-flag") : (optionFor("en") && optionFor("en").getAttribute("data-flag"));
            var label = opt ? opt.getAttribute("data-label") : "English";

            if (flagEl && flag) { flagEl.setAttribute("src", flag); }
            if (labelEl) { labelEl.textContent = label; }

            if (menu) {
                var opts = menu.querySelectorAll(".jmn-lang-dd__option");
                for (var i = 0; i < opts.length; i++) {
                    var isMatch = opts[i].getAttribute("data-lang") === (lang || "en");
                    opts[i].classList.toggle("is-active", isMatch);
                    opts[i].setAttribute("aria-selected", isMatch ? "true" : "false");
                }
            }
        }

        function openMenu() {
            if (!menu || !trigger) { return; }
            menu.hidden = false;
            trigger.setAttribute("aria-expanded", "true");
        }

        function closeMenu() {
            if (!menu || !trigger) { return; }
            menu.hidden = true;
            trigger.setAttribute("aria-expanded", "false");
        }

        /* googtrans is Google's own first-party cookie. Google's script reads
         * it on load and translates the page without any further input. */
        function writeGoogtrans(lang) {
            var secure = location.protocol === "https:" ? "; Secure" : "";
            if (lang && lang !== "en") {
                document.cookie = "googtrans=/en/" + lang + "; path=/" + secure;
            } else {
                // Clear every variant Google might have written.
                document.cookie = "googtrans=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT" + secure;
                document.cookie = "googtrans=; path=/; domain=" + location.hostname + "; expires=Thu, 01 Jan 1970 00:00:00 GMT" + secure;
                document.cookie = "googtrans=; path=/; domain=." + location.hostname + "; expires=Thu, 01 Jan 1970 00:00:00 GMT" + secure;
            }
        }

        /* Fetch Google's element.js once. Google reads the googtrans cookie
         * during init and translates the page by itself. */
        function ensureScript() {
            if (document.getElementById(SCRIPT_ID)) { return; }
            var s = document.createElement("script");
            s.id = SCRIPT_ID;
            s.src = ELEMENT;
            s.async = true;
            s.onerror = function () {
                paint("en");
                window.console && console.warn("Translation service unavailable.");
            };
            document.body.appendChild(s);
        }

        /* Select a language: paint, persist, write the cookie, reload so
         * Google applies it on a clean page load. */
        function select(lang) {
            lang = lang || "en";
            paint(lang);
            jmnTheme.saveLang(lang);
            writeGoogtrans(lang);
            location.reload();
        }

        if (trigger && menu) {
            trigger.addEventListener("click", function (e) {
                e.stopPropagation();
                if (menu.hidden) { openMenu(); } else { closeMenu(); }
            });

            menu.addEventListener("click", function (e) {
                var opt = e.target && e.target.closest ? e.target.closest(".jmn-lang-dd__option") : null;
                if (!opt) { return; }
                select(opt.getAttribute("data-lang"));
                closeMenu();
            });

            /* Keyboard: Enter/Space on an option selects it. */
            menu.addEventListener("keydown", function (e) {
                if (e.key !== "Enter" && e.key !== " " && e.key !== "Spacebar") { return; }
                var opt = e.target && e.target.closest ? e.target.closest(".jmn-lang-dd__option") : null;
                if (!opt) { return; }
                e.preventDefault();
                select(opt.getAttribute("data-lang"));
                closeMenu();
            });

            document.addEventListener("click", function (e) {
                if (!dd || dd.contains(e.target)) { return; }
                closeMenu();
            });

            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape") { closeMenu(); }
            });
        }

        return { select: select, paint: paint, ensureScript: ensureScript };
    })();

    window.jmnTranslateElementInit = function () {
        try {
            /* global google */
            // InlineLayout.SIMPLE renders Google's compact widget. Our own
            // dropdown is the visible UI, so the widget is kept inside the
            // visually-hidden #jmn-translate-mount (clip rules, not display:none).
            new google.translate.TranslateElement({
                pageLanguage: "en",
                includedLanguages: "en,fr,es,de,pt,it,ru,ar,zh-CN,ja,hi,sw",
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, "jmn-translate-mount");
        } catch (e) {
            window.console && console.warn("Translate widget init failed:", e);
        }
    };

    /* Paint the stored preference and, if it is not English, fetch Google so
     * it translates from the googtrans cookie written on the previous load. */
    var initialLang = jmnTheme.read(jmnTheme.langKey);
    if (initialLang && initialLang !== "en") {
        jmnTranslate.paint(initialLang);
        jmnTranslate.ensureScript();
    } else {
        jmnTranslate.paint("en");
    }

    var forEach = function (array, callback, scope) {
        for (var i = 0; i < array.length; i++) {
            callback.call(scope, i, array[i]);
        }
    };

    /* Skill Circle */
    var progress = $('#progress');
    $(window).scroll(function () {
        var a = 0;
        var b = 0;
        var oTop = 0;
        if (progress.length) {
            var oTop = progress.offset().top - window.innerHeight;
            if (b == 0 && $(window).scrollTop() > oTop) {

                var max = -219.99078369140625;
                forEach(document.querySelectorAll('.progress'), function (index, value) {
                    var percent = value.getAttribute('data-progress');
                    value.querySelector('.fill').setAttribute('style', 'stroke-dashoffset: ' + ((100 - percent) / 100) * max);
                    value.querySelector('.value').innerHTML = percent + '%';
                });

                b = 1;
            }
        }
    });

})(jQuery);