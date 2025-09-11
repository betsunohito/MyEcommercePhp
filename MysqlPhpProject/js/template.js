(function ($) {
  'use strict';

  $(function () {
    var body = $('body');
    var contentWrapper = $('.content-wrapper');
    var scroller = $('.container-scroller');
    var footer = $('.footer');
    var sidebar = $('.sidebar');

    // --- Active class for current nav link ---
    function addActiveClass(element) {
      if (current === "") {
        if (element.attr('href').indexOf("index.html") !== -1) {
          element.parents('.nav-item').last().addClass('active');
          if (element.parents('.sub-menu').length) {
            element.closest('.collapse').addClass('show');
            element.addClass('active');
          }
        }
      } else {
        if (element.attr('href').indexOf(current) !== -1) {
          element.parents('.nav-item').last().addClass('active');
          if (element.parents('.sub-menu').length) {
            element.closest('.collapse').addClass('show');
            element.addClass('active');
          }
          if (element.parents('.submenu-item').length) {
            element.addClass('active');
          }
        }
      }
    }

    var current = location.pathname.split("/").slice(-1)[0].replace(/^\/|\/$/g, '');
    $('.nav li a', sidebar).each(function () {
      addActiveClass($(this));
    });

    // --- Collapse behavior ---
    sidebar.on('show.bs.collapse', '.collapse', function () {
      sidebar.find('.collapse.show').collapse('hide');
    });

    // --- Sidebar toggle ---
    $('[data-toggle="minimize"]').on("click", function () {
      body.toggleClass('sidebar-icon-only');
    });

    // --- Add icon to checkboxes/radios ---
    $(".form-check label, .form-radio label").append('<i class="input-helper"></i>');

    // --- Search input focus ---
    $('#navbar-search-icon').click(function () {
      $("#navbar-search-input").focus();
    });

    // --- Banner and navbar spacing logic ---
    const proBanner = document.querySelector('#proBanner');
    const bannerClose = document.querySelector('#bannerClose');
    const navbar = document.querySelector('.navbar');
    const wrapper = document.querySelector('.page-body-wrapper');

    if (typeof $.cookie === 'function') {
      if ($.cookie('royal-free-banner') != "true") {
        if (proBanner) proBanner.classList.add('d-flex');
        if (navbar) navbar.classList.remove('fixed-top');
      } else {
        if (proBanner) proBanner.classList.add('d-none');
        if (navbar) navbar.classList.add('fixed-top');
      }
    } else {
      console.warn('⚠️ $.cookie is not available. Skipping banner logic.');
    }

    if (navbar && wrapper) {
      if ($(navbar).hasClass('fixed-top')) {
        wrapper.classList.remove('pt-0');
        navbar.classList.remove('pt-5');
      } else {
        wrapper.classList.add('pt-0');
        navbar.classList.add('pt-5');
        navbar.classList.add('mt-3');
      }
    }

    if (bannerClose) {
      bannerClose.addEventListener('click', function () {
        if (proBanner) {
          proBanner.classList.add('d-none');
          proBanner.classList.remove('d-flex');
        }
        if (navbar) {
          navbar.classList.remove('pt-5');
          navbar.classList.add('fixed-top');
          navbar.classList.remove('mt-3');
        }
        if (wrapper) {
          wrapper.classList.add('proBanner-padding-top');
        }

        if (typeof $.cookie === 'function') {
          var date = new Date();
          date.setTime(date.getTime() + 24 * 60 * 60 * 1000);
          $.cookie('royal-free-banner', "true", { expires: date });
        } else {
          console.warn('⚠️ $.cookie is not available. Cookie not set.');
        }
      });
    }
  });

})(jQuery);
