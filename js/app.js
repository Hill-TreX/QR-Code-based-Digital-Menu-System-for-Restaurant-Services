// ==========================================
// QR CODE DIGITAL MENU SYSTEM - CUSTOMER JS
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    // Read URL params from QR code scan
    const params       = new URLSearchParams(window.location.search);
    const tidParam     = params.get('tid');    // table UID  e.g. TBL-A3F9B2
    const tnumParam    = params.get('tnum');   // table number e.g. 5
    const categoryParam= params.get('category');

    // Persist table identity only while the cart has items
    if (tidParam)  sessionStorage.setItem('tableUid',    tidParam);
    if (tnumParam) sessionStorage.setItem('tableNumber', tnumParam);

    // If user returned to the page WITHOUT QR params and cart is empty,
    // their previous table session is stale — clear it so the prompt shows again
    if (!tidParam && !tnumParam) {
        const activeCart = JSON.parse(localStorage.getItem('cart') || '[]');
        if (activeCart.length === 0) {
            sessionStorage.removeItem('tableNumber');
            sessionStorage.removeItem('tableUid');
        }
    }

    // Restore from session if not in URL (customer navigates back to index with items still in cart)
    const tableUid    = tidParam  || sessionStorage.getItem('tableUid')    || '';
    const tableNumber = tnumParam || sessionStorage.getItem('tableNumber') || '';

    if (tableNumber) {
        showTableBanner(tableNumber, tableUid);
    }

    if (categoryParam) {
        const catBtn = document.querySelector('#categoryFilters .btn[data-category="' + categoryParam + '"]');
        if (catBtn) catBtn.click();
    }

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const query = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.menu-item');
            let visible = 0;

            items.forEach(function(item) {
                const name = item.getAttribute('data-name');
                const desc = item.querySelector('.card-text')?.textContent.toLowerCase() || '';
                if (name.includes(query) || desc.includes(query)) {
                    item.style.display = '';
                    visible++;
                } else {
                    item.style.display = 'none';
                }
            });

            const noResults = document.getElementById('noResults');
            if (noResults) {
                noResults.classList.toggle('d-none', visible > 0);
            }
        }, 300));
    }

    // Dark mode toggle
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        // Load saved preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
            themeToggle.innerHTML = '<i class="bi bi-sun"></i>';
        }

        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark);
            this.innerHTML = isDark ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon"></i>';
        });
    }
});

// ==========================================
// TABLE HELPERS
// ==========================================

function showTableBanner(tableNumber, tableUid) {
    const existing = document.querySelector('.table-banner');
    if (existing) existing.remove();
    const banner = document.createElement('div');
    banner.className = 'alert alert-danger text-center fw-bold mb-0 rounded-0 py-2 table-banner';
    banner.innerHTML = '<i class="bi bi-grid-3x3-gap-fill"></i> You are at Table <strong>' + tableNumber + '</strong>'
        + (tableUid ? '&nbsp;&nbsp;<span class="fw-normal font-monospace small opacity-75">' + tableUid + '</span>' : '');
    document.querySelector('nav')?.insertAdjacentElement('afterend', banner);
}

// Category filter
function filterCategory(catId, btn) {
    // Update active button
    document.querySelectorAll('#categoryFilters .btn').forEach(function(b) {
        b.classList.remove('btn-danger', 'active');
        b.classList.add('btn-outline-danger');
    });
    btn.classList.remove('btn-outline-danger');
    btn.classList.add('btn-danger', 'active');

    // Filter items
    const items = document.querySelectorAll('.menu-item');
    let visible = 0;
    items.forEach(function(item) {
        if (catId === 0 || item.getAttribute('data-category') == catId) {
            item.style.display = '';
            visible++;
        } else {
            item.style.display = 'none';
        }
    });
}

// ==========================================
// CART
// ==========================================

let cart = JSON.parse(localStorage.getItem('cart') || '[]');

function updateCartUI() {
    const count = cart.reduce(function(sum, i) { return sum + i.qty; }, 0);
    const countEl = document.getElementById('cartCount');
    const footerEl = document.getElementById('cartFooter');
    const emptyEl = document.getElementById('cartEmpty');
    const itemsEl = document.getElementById('cartItems');

    if (!countEl) return;

    if (count > 0) {
        countEl.textContent = count;
        countEl.classList.remove('d-none');
    } else {
        countEl.classList.add('d-none');
    }

    if (!itemsEl) return;

    if (cart.length === 0) {
        itemsEl.innerHTML = '';
        emptyEl && emptyEl.classList.remove('d-none');
        footerEl && footerEl.classList.add('d-none');
        return;
    }

    emptyEl && emptyEl.classList.add('d-none');
    footerEl && footerEl.classList.remove('d-none');

    let total = 0;
    itemsEl.innerHTML = cart.map(function(item, idx) {
        total += item.price * item.qty;
        return '<div class="d-flex align-items-center gap-3 mb-3">' +
            '<img src="' + item.image + '" class="cart-item-img" alt="' + item.name + '">' +
            '<div class="flex-grow-1">' +
                '<div class="fw-semibold small">' + item.name + '</div>' +
                '<div class="text-danger small">$' + (item.price * item.qty).toFixed(2) + '</div>' +
            '</div>' +
            '<div class="d-flex align-items-center gap-2">' +
                '<button class="qty-btn" onclick="changeQty(' + idx + ', -1)">−</button>' +
                '<span class="small fw-bold">' + item.qty + '</span>' +
                '<button class="qty-btn" onclick="changeQty(' + idx + ', 1)">+</button>' +
            '</div>' +
        '</div>';
    }).join('');

    const totalEl = document.getElementById('cartTotal');
    if (totalEl) totalEl.textContent = '$' + total.toFixed(2);
}

function addToCart(id, name, price, image) {
    const existing = cart.find(function(i) { return i.id === id; });
    if (existing) {
        existing.qty++;
    } else {
        cart.push({ id: id, name: name, price: price, image: image, qty: 1 });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();

    // Bump animation
    const bubble = document.getElementById('cartBubble');
    if (bubble) {
        bubble.classList.remove('cart-bump');
        void bubble.offsetWidth;
        bubble.classList.add('cart-bump');
    }
}

function changeQty(idx, delta) {
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
}

function placeOrder() {
    if (cart.length === 0) return;
    const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('cartOffcanvas'));
    if (offcanvas) offcanvas.hide();
    // Build absolute path to checkout.php regardless of current subdirectory
    const inPages = window.location.pathname.includes('/pages/');
    const dir     = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
    const root    = inPages ? dir.substring(0, dir.lastIndexOf('/')) : dir;
    window.location.href = root + '/pages/checkout.php';
}

document.addEventListener('DOMContentLoaded', function() {
    updateCartUI();

    const cartOffcanvas = document.getElementById('cartOffcanvas');
    const cartBubble = document.getElementById('cartBubble');
    if (cartOffcanvas && cartBubble) {
        cartOffcanvas.addEventListener('show.bs.offcanvas', function() {
            cartBubble.style.display = 'none';
        });
        cartOffcanvas.addEventListener('hidden.bs.offcanvas', function() {
            cartBubble.style.display = '';
        });
    }
});

// Debounce utility
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() { func.apply(context, args); }, wait);
    };
}
