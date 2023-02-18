document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  const confirm = document.querySelector('.js-stancer-confirm-terms');
  const frame = document.querySelector('.js-stancer-payment-iframe');
  const terms = document.getElementById('conditions_to_approve[terms-and-conditions]');

  if (!frame || !terms) {
    return;
  }

  const parent = frame.parentNode;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.intersectionRatio > 0) {
        if (terms.checked) {
          entry.target.setAttribute('src', entry.target.dataset.target);

          if (confirm.parentNode) {
            parent.removeChild(confirm);
          }

          observer.disconnect();
        } else if (frame.parentNode) {
          parent.removeChild(frame);
        }
      }
    });
  });

  observer.observe(frame);

  const action = () => {
    if (terms.checked) {
      parent.appendChild(frame);

      if (confirm.parentNode) {
        parent.removeChild(confirm);
      }
    } else {
      parent.appendChild(confirm);

      if (frame.parentNode) {
        parent.removeChild(frame);
      }
    }
  };

  terms.addEventListener('change', action);
  action();
});
