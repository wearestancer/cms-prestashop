window.addEventListener('message', (event) => {
  'use strict';

  if (typeof STANCER === 'undefined' || event.origin !== STANCER.origin) {
    return;
  }

  const minHeight = 320;
  const props = {};

  if (event.data.width) {
    props.width = event.data.width;
  }

  if (event.data.height && event.data.height > minHeight) {
    props.height = event.data.height;
  }

  if (props) {
    $('.js-stancer-payment-iframe').animate(props, {
      duration: 200,
      easing: 'linear',
      queue: false,
    });
  }

  if (event.data.status === 'secure-auth-start' && typeof event.data.url !== 'undefined') {
    window.location.href = event.data.url;
  }
});
