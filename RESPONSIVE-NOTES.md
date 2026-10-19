Responsive update

All 17 pages load assets/css/responsive.css after their original styles.
Shared navigation and animations are in assets/js/script.js.

- Compact navigation below 1200px, with separate touch/keyboard submenu buttons.
- Tablet layout adjustments at 1024px; single-column mobile cards below 768px.
- Fluid hero typography, automatic fixed-header spacing, responsive images and forms.
- Scroll reveal, button hover and menu animations; reduced-motion preference supported.
- Corrected CSS/JS path casing and broken local links for case-sensitive hosting.

Usage: extract the complete folder and replace the matching website files, or open
index.html with VS Code Live Server. No npm build is required. Existing Bootstrap,
Font Awesome and Google Fonts CDN resources still need an internet connection.

Validation: JavaScript syntax, local asset/link targets, shared stylesheet/script
inclusion and CSS brace balance checked. Browser layout and interaction testing
could not run in this environment because the Chromium download was blocked.
Contact/career form submission behavior is the original implementation.
