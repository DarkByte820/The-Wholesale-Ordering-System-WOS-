<?php
/**
 * Application Constants
 */

// HTTP Status Codes
const HTTP_OK = 200;
const HTTP_CREATED = 201;
const HTTP_BAD_REQUEST = 400;
const HTTP_UNAUTHORIZED = 401;
const HTTP_FORBIDDEN = 403;
const HTTP_NOT_FOUND = 404;
const HTTP_SERVER_ERROR = 500;

// User Roles
const ROLE_BUNDLE_CUSTOMER = 'BundleCustomer';
const ROLE_WHOLESALE_CUSTOMER = 'WholesaleCustomer';
const ROLE_WAREHOUSE_ADMIN = 'WarehouseAdmin';
const ROLE_DELIVERY_PERSONNEL = 'DeliveryPersonnel';
const ROLE_SYSTEM_ADMIN = 'SystemAdmin';

// Order Status
const ORDER_STATUS_PENDING = 'Pending';
const ORDER_STATUS_CONFIRMED = 'Confirmed';
const ORDER_STATUS_PACKED = 'Packed';
const ORDER_STATUS_DISPATCHED = 'Dispatched';
const ORDER_STATUS_DELIVERED = 'Delivered';
const ORDER_STATUS_CANCELLED = 'Cancelled';

// Payment Status
const PAYMENT_STATUS_UNPAID = 'Unpaid';
const PAYMENT_STATUS_PAID = 'Paid';
const PAYMENT_STATUS_FAILED = 'Failed';
const PAYMENT_STATUS_PENDING = 'Pending';

// Product Status
const PRODUCT_STATUS_ACTIVE = 'Active';
const PRODUCT_STATUS_INACTIVE = 'Inactive';
const PRODUCT_STATUS_DISCONTINUED = 'Discontinued';

// Messages
const MSG_SUCCESS = 'Operation successful';
const MSG_ERROR = 'Operation failed';
const MSG_UNAUTHORIZED = 'Unauthorized access';
const MSG_INVALID_INPUT = 'Invalid input provided';

?>