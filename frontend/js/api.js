var API = (function () {
    var BASE_URL = 'http://localhost:8080/api';

    function getToken() {
        return localStorage.getItem('token');
    }

    async function request(method, endpoint, data) {
        var url = BASE_URL + endpoint;
        var options = {
            method: method,
            headers: { 'Content-Type': 'application/json' }
        };

        var token = getToken();
        if (token) {
            options.headers['Authorization'] = 'Bearer ' + token;
        }

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        if (data && method === 'GET') {
            var params = new URLSearchParams();
            Object.keys(data).forEach(function (key) {
                if (data[key] !== undefined && data[key] !== null && data[key] !== '') {
                    params.append(key, data[key]);
                }
            });
            var qs = params.toString();
            if (qs) url += '?' + qs;
        }

        try {
            var response = await fetch(url, options);
            var json;
            var text = await response.text();
            try {
                json = JSON.parse(text);
            } catch (e) {
                json = { success: response.ok, message: text };
            }
            if (!response.ok) {
                return { success: false, message: json.message || 'Request failed', status: response.status };
            }
            return json;
        } catch (error) {
            return { success: false, message: error.message || 'Network error' };
        }
    }

    return {
        getProducts: function (params) {
            return request('GET', '/products', params);
        },

        getProductById: function (id) {
            return request('GET', '/products/' + id);
        },

        searchProducts: function (query) {
            return request('GET', '/products/search', { q: query });
        },

        getProductsByCategory: function (category) {
            return request('GET', '/products/category/' + encodeURIComponent(category));
        },

        createProduct: function (data) {
            return request('POST', '/products', data);
        },

        updateProduct: function (id, data) {
            return request('PUT', '/products/' + id, data);
        },

        deleteProduct: function (id) {
            return request('DELETE', '/products/' + id);
        },

        register: function (data) {
            return request('POST', '/auth/register', data);
        },

        login: function (email, password) {
            return request('POST', '/auth/login', { email: email, password: password });
        },

        getCart: function () {
            return request('GET', '/cart');
        },

        addToCart: function (productId, quantity) {
            return request('POST', '/cart', { product_id: productId, quantity: quantity || 1 });
        },

        removeFromCart: function (cartId) {
            return request('POST', '/cart/remove', { cartId: cartId });
        },

        createOrder: function (data) {
            return request('POST', '/orders', data);
        },

        getMyOrders: function () {
            return request('GET', '/orders');
        },

        initiatePayment: function (data) {
            return request('POST', '/payments/initiate', data);
        },

        verifyPayment: function (data) {
            return request('POST', '/payments/verify', data);
        },

        processCheckout: function (data) {
            return request('POST', '/checkout', data);
        },

        getCheckoutSummary: function (data) {
            return request('POST', '/checkout/summary', data);
        },

        getComboPackages: function () {
            return request('GET', '/combo-packages');
        },

        forgotPassword: function (email) {
            return request('POST', '/auth/forgot-password', { email: email });
        },

        resetPassword: function (token, password) {
            return request('POST', '/auth/reset-password', { token: token, password: password });
        },

        changePassword: function (data) {
            return request('POST', '/auth/change-password', data);
        },

        // ========== ADMIN - ORDERS ==========
        getAllOrders: function (params) {
            return request('GET', '/orders/all', params);
        },

        updateOrderStatus: function (orderId, status) {
            return request('PUT', '/orders/status', { orderId: orderId, status: status });
        },

        cancelOrder: function (orderId) {
            return request('PUT', '/orders/cancel', { orderId: orderId });
        },

        getOrderById: function (id) {
            return request('GET', '/orders/' + id);
        },

        // ========== ADMIN - REPORTS ==========
        getSalesReport: function (params) {
            return request('GET', '/reports/sales', params);
        },

        getInventoryReport: function () {
            return request('GET', '/reports/inventory');
        },

        getCustomerReport: function () {
            return request('GET', '/reports/customers');
        },

        getPaymentReport: function () {
            return request('GET', '/reports/payments');
        },

        getDeliveryReport: function () {
            return request('GET', '/reports/delivery');
        },

        getLowStockReport: function () {
            return request('GET', '/reports/low-stock');
        },

        // ========== ADMIN - DASHBOARD ==========
        getDashboard: function () {
            return request('GET', '/dashboard');
        },

        getSalesTrend: function () {
            return request('GET', '/dashboard/sales-trend');
        },

        getOrderStatusBreakdown: function () {
            return request('GET', '/dashboard/order-status');
        },

        // ========== ADMIN - AUDIT ==========
        getAuditLogs: function (params) {
            return request('GET', '/audit', params);
        },

        // ========== ADMIN - USERS (SystemAdmin) ==========
        getWholesaleCustomers: function () {
            return request('GET', '/customers/wholesale');
        },

        // ========== DELIVERY ==========
        updateDeliveryStatus: function (deliveryId, status, notes) {
            return request('POST', '/delivery/update-status', { deliveryId: deliveryId, status: status, notes: notes });
        },

        getDeliveryStatus: function (orderId) {
            return request('GET', '/delivery/status/' + orderId);
        },

        recordProof: function (deliveryId, data) {
            return request('POST', '/delivery/proof', { deliveryId: deliveryId, notes: data });
        }
    };
})();
