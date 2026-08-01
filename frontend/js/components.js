function createProductCard(product, options) {
    options = options || {};
    var id = product.ProductID || product.id || 0;
    var name = escapeHtml(product.Name || product.name || 'Product');
    var category = escapeHtml(product.Category || product.category || '');
    var unitPrice = parseFloat(product.UnitPrice || product.price || 0);
    var discounted = parseFloat(product.discountedPrice || product.discounted_price);
    var hasDiscount = !isNaN(discounted) && discounted > 0 && discounted < unitPrice;
    var discountPct = hasDiscount ? getDiscountPercent(unitPrice, discounted) : 0;
    if (!hasDiscount) discounted = unitPrice;
    var rating = product.rating || 0;
    var reviewCount = product.reviewCount || 0;
    var isFlashSale = options.isFlashSale || false;

    var imageHtml = product.image
        ? '<img src="' + escapeHtml(product.image) + '" alt="' + name + '" loading="lazy">'
        : '<div class="image-placeholder" aria-hidden="true"><i class="fa-solid fa-image"></i></div>';

    var badgeHtml = '';
    if (isFlashSale) {
        badgeHtml = '<div class="flash-badge"><i class="fa-solid fa-bolt"></i> FLASH</div>';
    }
    if (discountPct > 0) {
        badgeHtml += '<div class="discount-badge">' + discountPct + '% OFF</div>';
    }

    var starsHtml = generateStars(rating);

    return '<div class="product-card">' +
        badgeHtml +
        '<a href="product_detail.html?id=' + id + '" class="product-image" aria-label="View ' + name + '">' +
        imageHtml +
        '</a>' +
        '<div class="product-info">' +
        '<h3 class="product-name"><a href="product_detail.html?id=' + id + '">' + name + '</a></h3>' +
        (category ? '<p class="product-category">' + category + '</p>' : '') +
        '<div class="product-rating" aria-label="Rating ' + rating + ' out of 5">' +
        '<span class="stars">' + starsHtml + '</span>' +
        '<span class="rating-count">(' + reviewCount + ')</span>' +
        '</div>' +
        '<div class="product-prices">' +
        '<span class="price-current">' + formatCurrency(discounted) + '</span>' +
        (discountPct > 0 ? '<span class="price-original">' + formatCurrency(unitPrice) + '</span>' : '') +
        '</div>' +
        '<button class="btn btn-add-cart" onclick="addProductToCart(' + id + ', \'' + name.replace(/'/g, "\\'") + '\', ' + discounted + ')" aria-label="Add ' + name + ' to cart">' +
        '<i class="fa-solid fa-cart-shopping"></i> Add to Cart' +
        '</button>' +
        '</div>' +
        '</div>';
}

function createProductCardSkeleton() {
    return '<div class="product-card skeleton-card">' +
        '<div class="skeleton skeleton-image"></div>' +
        '<div class="skeleton-card-body">' +
        '<div class="skeleton skeleton-text skeleton-text-lg"></div>' +
        '<div class="skeleton skeleton-text skeleton-text-sm"></div>' +
        '<div class="skeleton skeleton-text skeleton-text-md"></div>' +
        '<div class="skeleton skeleton-text skeleton-text-sm"></div>' +
        '</div>' +
        '</div>';
}

function createOrderCard(order) {
    var orderId = escapeHtml(order.orderId || order.OrderID || '');
    var date = formatDate(order.orderDate || order.CreatedAt || new Date().toISOString());
    var status = order.status || order.Status || 'Processing';
    var items = order.items || order.Items || [];
    var total = parseFloat(order.total || order.Total || 0);

    var statusIcons = {
        'Processing': '<i class="fa-solid fa-hourglass-half"></i>',
        'Confirmed': '<i class="fa-solid fa-circle-check"></i>',
        'Packed': '<i class="fa-solid fa-box"></i>',
        'Dispatched': '<i class="fa-solid fa-truck-fast"></i>',
        'Shipped': '<i class="fa-solid fa-truck-fast"></i>',
        'Delivered': '<i class="fa-solid fa-circle-check"></i>',
        'Cancelled': '<i class="fa-solid fa-circle-xmark"></i>'
    };

    var itemsHtml = items.map(function (item) {
        var itemName = escapeHtml(item.name || item.Name || '');
        var itemQty = item.quantity || item.Quantity || 1;
        var itemPrice = parseFloat(item.price || item.Price || 0);
        return '<div class="order-item"><span>' + itemName + ' x ' + itemQty + '</span><span>' + formatCurrency(itemPrice * itemQty) + '</span></div>';
    }).join('');

    var timelineSteps = ['Processing', 'Shipped', 'Delivered'];
    var statusOrder = { 'Processing': 0, 'Confirmed': 0, 'Packed': 0, 'Dispatched': 1, 'Shipped': 1, 'Delivered': 2, 'Cancelled': -1 };
    var currentIdx = statusOrder[status] !== undefined ? statusOrder[status] : 0;

    var timelineHtml = timelineSteps.map(function (step, idx) {
        var cls = idx <= currentIdx ? 'completed' : '';
        return '<div class="timeline-item ' + cls + '"><div class="timeline-dot"></div><div class="timeline-label">' + step + '</div></div>';
    }).join('');

    return '<div class="order-card">' +
        '<div class="order-header">' +
        '<div class="order-id"><h3>' + orderId + '</h3><p>' + date + '</p></div>' +
        '<div class="order-status"><span class="status-badge status-' + status.toLowerCase() + '">' + (statusIcons[status] || '<i class="fa-solid fa-box"></i>') + ' ' + escapeHtml(status) + '</span></div>' +
        '</div>' +
        '<div class="order-items">' + itemsHtml + '</div>' +
        '<div class="order-footer">' +
        '<div class="order-total">Total: <strong>' + formatCurrency(total) + '</strong></div>' +
        '<div class="order-actions">' +
        '<button class="btn btn-sm" onclick="viewOrderDetails(\'' + orderId + '\')"><i class="fa-solid fa-eye"></i> View Details</button>' +
        '</div>' +
        '</div>' +
        '<div class="order-timeline">' + timelineHtml + '</div>' +
        '</div>';
}

function createCartItem(item, index) {
    var name = escapeHtml(item.name || item.Name || 'Product');
    var price = parseFloat(item.price || item.Price || 0);
    var quantity = item.quantity || item.Quantity || 1;
    var id = item.id || item.ProductID || 0;

    return '<div class="cart-item" data-index="' + index + '">' +
        '<div class="item-image"><div class="image-placeholder" aria-hidden="true"><i class="fa-solid fa-image"></i></div></div>' +
        '<div class="item-details">' +
        '<h3><a href="product_detail.html?id=' + id + '">' + name + '</a></h3>' +
        '<p class="item-price">' + formatCurrency(price) + ' each</p>' +
        '</div>' +
        '<div class="item-quantity">' +
        '<button onclick="updateQuantity(' + index + ', ' + (quantity - 1) + ')" class="qty-btn" aria-label="Decrease quantity"><i class="fa-solid fa-minus"></i></button>' +
        '<input type="number" value="' + quantity + '" readonly aria-label="Quantity">' +
        '<button onclick="updateQuantity(' + index + ', ' + (quantity + 1) + ')" class="qty-btn" aria-label="Increase quantity"><i class="fa-solid fa-plus"></i></button>' +
        '</div>' +
        '<div class="item-price-total">' + formatCurrency(price * quantity) + '</div>' +
        '<div class="item-actions">' +
        '<button onclick="saveForLater(' + index + ')" class="btn btn-sm btn-outline" aria-label="Save for later"><i class="fa-solid fa-bookmark"></i> Save</button>' +
        '<button onclick="removeFromCart(' + index + ')" class="btn btn-sm btn-danger" aria-label="Remove from cart"><i class="fa-solid fa-trash"></i> Remove</button>' +
        '</div>' +
        '</div>';
}

function createRatingStars(rating, count) {
    var r = Math.round(rating || 0);
    var html = '<div class="product-rating" aria-label="Rating ' + r + ' out of 5">';
    html += '<span class="stars">' + generateStars(r) + '</span>';
    if (count !== undefined) {
        html += '<span class="rating-count">(' + count + ')</span>';
    }
    html += '</div>';
    return html;
}

function createPagination(currentPage, totalPages, onClickFn) {
    if (totalPages <= 1) return '';
    var html = '<div class="pagination">';

    if (currentPage > 1) {
        html += '<button class="page-btn" onclick="' + onClickFn + '(' + (currentPage - 1) + ')" aria-label="Previous page"><i class="fa-solid fa-arrow-left"></i> Previous</button>';
    }

    var start = Math.max(1, currentPage - 2);
    var end = Math.min(totalPages, start + 4);
    start = Math.max(1, end - 4);

    if (start > 1) {
        html += '<button class="page-btn" onclick="' + onClickFn + '(1)">1</button>';
        if (start > 2) html += '<span class="page-ellipsis">...</span>';
    }

    for (var i = start; i <= end; i++) {
        html += '<button class="page-btn ' + (i === currentPage ? 'active' : '') + '" onclick="' + onClickFn + '(' + i + ')"' +
            (i === currentPage ? ' aria-current="page"' : '') + '>' + i + '</button>';
    }

    if (end < totalPages) {
        if (end < totalPages - 1) html += '<span class="page-ellipsis">...</span>';
        html += '<button class="page-btn" onclick="' + onClickFn + '(' + totalPages + ')">' + totalPages + '</button>';
    }

    if (currentPage < totalPages) {
        html += '<button class="page-btn" onclick="' + onClickFn + '(' + (currentPage + 1) + ')" aria-label="Next page">Next <i class="fa-solid fa-arrow-right"></i></button>';
    }

    html += '</div>';
    return html;
}

function createEmptyState(message, actionText, actionUrl, iconClass) {
    var html = '<div class="empty-state">';
    html += '<div class="empty-state-icon"><i class="fa-solid ' + (iconClass || 'cart-shopping') + '"></i></div>';
    html += '<p>' + escapeHtml(message) + '</p>';
    if (actionText && actionUrl) {
        html += '<a href="' + actionUrl + '" class="btn btn-primary">' + escapeHtml(actionText) + '</a>';
    }
    html += '</div>';
    return html;
}

function addProductToCart(productId, name, price) {
    addToCart(productId, name, price);
}
