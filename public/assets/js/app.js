/* ==========================================================================
   The Pacific Hotel — Core JS (vanilla, no build step / no npm dependency)
   ========================================================================== */

// ------------------------------------------------------------- Dark mode --
(function initTheme() {
  const saved = localStorage.getItem('hms_theme');
  const theme = saved || 'light';
  document.documentElement.setAttribute('data-theme', theme);
})();

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('hms_theme', next);
}

// ------------------------------------------------------------ Toasts ----
function showToast(message, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity .3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4200);
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-flash]').forEach((el) => {
    showToast(el.dataset.flash, el.dataset.flashType || 'success');
  });

  // ---------------------------------------------------------- Mobile nav --
  const navToggle = document.getElementById('nav-toggle');
  const mobileMenu = document.getElementById('mobile-menu');
  if (navToggle && mobileMenu) {
    navToggle.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
  }

  // ------------------------------------------------------- Notification --
  const bellButton = document.getElementById('notif-bell');
  const bellPanel = document.getElementById('notif-panel');
  if (bellButton && bellPanel) {
    bellButton.addEventListener('click', () => bellPanel.classList.toggle('hidden'));
    document.addEventListener('click', (e) => {
      if (!bellButton.contains(e.target) && !bellPanel.contains(e.target)) {
        bellPanel.classList.add('hidden');
      }
    });
  }

  initBookingForm();
});

// -------------------------------------------------- Live booking pricing --
function initBookingForm() {
  const form = document.getElementById('booking-form');
  if (!form) return;

  const checkIn = form.querySelector('[name="check_in"]');
  const checkOut = form.querySelector('[name="check_out"]');
  const extras = form.querySelectorAll('[name="extra_service_ids[]"]');
  const couponInput = form.querySelector('[name="coupon_code"]');
  const summaryBox = document.getElementById('booking-summary');
  const csrfToken = form.querySelector('[name="_csrf"]').value;
  const roomTypeSlug = form.dataset.roomTypeSlug;

  const todayStr = new Date().toISOString().split('T')[0];
  if (checkIn) checkIn.min = todayStr;

  function updateMinCheckout() {
    if (checkIn && checkOut && checkIn.value) {
      const next = new Date(checkIn.value);
      next.setDate(next.getDate() + 1);
      checkOut.min = next.toISOString().split('T')[0];
      if (checkOut.value && checkOut.value <= checkIn.value) {
        checkOut.value = checkOut.min;
      }
    }
  }

  async function refreshQuote() {
    if (!checkIn.value || !checkOut.value) return;
    summaryBox.classList.add('opacity-50');

    const body = new URLSearchParams();
    body.append('room_type_slug', roomTypeSlug);
    body.append('check_in', checkIn.value);
    body.append('check_out', checkOut.value);
    body.append('coupon_code', couponInput ? couponInput.value : '');
    extras.forEach((el) => { if (el.checked) body.append('extra_service_ids[]', el.value); });
    body.append('_csrf', csrfToken);

    try {
      const res = await fetch(form.dataset.quoteUrl, { method: 'POST', body });
      const data = await res.json();
      if (data.error) {
        summaryBox.innerHTML = `<p class="text-sm text-red-500">${data.error}</p>`;
        return;
      }
      summaryBox.innerHTML = `
        <div class="flex justify-between text-sm py-1"><span class="text-muted">Room (${data.nights} night${data.nights > 1 ? 's' : ''})</span><span>${formatMoney(data.room_subtotal)}</span></div>
        <div class="flex justify-between text-sm py-1"><span class="text-muted">Extras</span><span>${formatMoney(data.services_total)}</span></div>
        <div class="flex justify-between text-sm py-1"><span class="text-muted">Discount</span><span>-${formatMoney(data.discount_amount)}</span></div>
        <div class="flex justify-between text-sm py-1"><span class="text-muted">Tax</span><span>${formatMoney(data.tax_amount)}</span></div>
        <div class="flex justify-between font-semibold text-base pt-2 mt-2 border-t border-app"><span>Total</span><span class="text-gold">${formatMoney(data.total)}</span></div>
      `;
    } catch (e) {
      summaryBox.innerHTML = '<p class="text-sm text-red-500">Could not calculate price. Please check your dates.</p>';
    } finally {
      summaryBox.classList.remove('opacity-50');
    }
  }

  function formatMoney(v) {
    return (window.HMS_CURRENCY || '$') + Number(v).toFixed(2);
  }

  [checkIn, checkOut, couponInput].forEach((el) => el && el.addEventListener('change', () => { updateMinCheckout(); refreshQuote(); }));
  extras.forEach((el) => el.addEventListener('change', refreshQuote));
}

async function markNotificationsRead(url) {
  await fetch(url, { method: 'POST', body: new URLSearchParams({ _csrf: window.HMS_CSRF || '' }) });
  document.querySelectorAll('.notif-dot').forEach((el) => el.remove());
}
