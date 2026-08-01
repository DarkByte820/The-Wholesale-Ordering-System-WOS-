# E-Commerce Frontend Design Prompt

Use this as a starting prompt for an AI coding assistant (or as a spec for yourself). Fill in the bracketed parts.

---

**Prompt:**

Design and build the frontend for an e-commerce web app called **Ghana Warehouse Connect**, taking inspiration from Jumia Ghana and Amazon's shopping experience — but not copying their branding, logos, or exact visual identity.

**Tech stack:** [React + Vite / Next.js / Vue / plain HTML-CSS-JS — pick one]
**Styling:** [Tailwind CSS / CSS Modules / styled-components]

### Core pages/screens
1. **Home page**
   - Sticky header: logo, search bar (with category dropdown), account/login, cart icon with item count, wishlist icon
   - Secondary nav bar: category links (Electronics, Phones & Tablets, Fashion, Home & Office, Beauty, Sports, etc.)
   - Hero carousel/banner for promotions
   - Horizontal product rails: "Flash Sales", "Recommended for You", "Top Deals", "New Arrivals" — each with a countdown timer where relevant
   - Category grid with icons
   - Footer: newsletter signup, links (About, Help, Payment methods, Careers), social icons, payment/trust badges

2. **Category / Search results page**
   - Left sidebar filters: price range, brand, rating, category, availability, discount %
   - Sort dropdown (price, rating, newest, popularity)
   - Product grid (4 per row desktop, 2 per row mobile) with: image, title, price, discounted price + % off, star rating, "Add to Cart" button, wishlist heart icon
   - Pagination or infinite scroll

3. **Product detail page**
   - Image gallery with thumbnails and zoom
   - Title, brand, price, discount badge, star rating + review count
   - Variant selectors (size, color)
   - Quantity selector, Add to Cart + Buy Now buttons
   - Delivery estimate, seller info, return policy
   - Tabs: Description, Specifications, Reviews (with rating breakdown bar chart)
   - "You may also like" / "Frequently bought together" carousel

4. **Cart page**
   - Line items with thumbnail, title, variant, quantity stepper, remove/save-for-later
   - Order summary: subtotal, delivery fee, discount, total
   - Promo code input
   - Prominent "Proceed to Checkout" button

5. **Checkout flow**
   - Step indicator (Cart → Address → Payment → Review)
   - Address form / saved addresses
   - Payment method selection (card, mobile money, cash on delivery — reflect local payment norms if targeting Ghana/West Africa)
   - Order review + place order confirmation

6. **Account pages**
   - Order history with status tracking (Processing, Shipped, Delivered)
   - Profile/address book
   - Wishlist

### Design requirements
- Mobile-first, fully responsive (this audience shops heavily on phones)
- Fast perceived performance: skeleton loaders for product grids/images
- Clear visual hierarchy for price and discounts (strike-through original price, red/orange badge for % off)
- Trust signals throughout (ratings, verified badges, secure checkout icons)
- Accessible: proper contrast, alt text, keyboard navigation, ARIA labels on interactive elements
- Empty states for cart, wishlist, search-no-results
- Loading, error, and out-of-stock states for products

### Deliverables
- Component-based structure (reusable ProductCard, Header, Filters, RatingStars, etc.)
- Placeholder data/mock API for products
- [Specify: single page for now / full multi-route app with routing]

