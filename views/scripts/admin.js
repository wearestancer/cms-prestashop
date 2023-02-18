(($) => {
  'use strict';

  $(() => {
    $('.js-show-error').each(function () {
      $(this).parents('.form-group').addClass('has-error');
    });
  });
})(window.jQuery);
