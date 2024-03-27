window.addEventListener('message', (event) => {
  'use strict';

  // We cannot check for origin (not allowed in the sandbox) so we check the data
  if (typeof event.data.status === 'undefined' || typeof event.data.width === 'undefined' || typeof event.data.height === 'undefined') {
    return;
  }

  const $iframe = $('.js-stancer-payment-iframe');
  const minHeight = 320;
  const props = {};

  if (event.data.width) {
    props.width = event.data.width;
  }

  if (event.data.height && event.data.height > minHeight) {
    props.height = event.data.height;
  }

  if (props) {
    $iframe.animate(props, {
      duration: 200,
      easing: 'linear',
      queue: false,
    });
  }

  if (typeof event.data.url !== 'undefined') {
    if (!$iframe.data('inner-3ds') || event.data.status !== 'secure-auth-start') {
      window.location.href = event.data.url;
    }
  }
});
