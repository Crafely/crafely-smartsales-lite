# Crafely SmartSales Lite

A WordPress plugin (Lite) for creating and managing smart sales in your WooCommerce store. This repository contains the Vue.js assets used by the plugin's admin interface.

## Features

- Create smart sales campaigns for WooCommerce products.
- Intuitive admin interface built with Vue.js.
- Responsive and compatible with the WordPress admin dashboard.
- Integrated with WooCommerce for seamless discounts and promotions.
- Optimized for performance and easy customization.

## Installation

1. Download the plugin from your plugin source (repository or ZIP).
2. Upload the plugin to your WordPress website:
   - Go to `Plugins` > `Add New`.
   - Click `Upload Plugin` and choose `crafely-smartsales-lite.zip`.
   - Click `Install Now` and activate the plugin.

Alternatively, place the plugin folder in `wp-content/plugins/` and activate it from the Plugins screen.

## Usage

Once installed and activated, access the plugin from the WordPress admin dashboard under the "Crafely SmartSales" (or "SmartSales") menu.

### Creating a New Sale:
1. Navigate to **Crafely SmartSales** > **Add New Sale**.
2. Configure sale details: product selection, discount type, duration, and conditions.
3. Save the sale; it will be applied to selected products in your store.

## Development (Vue front-end)

This folder contains the Vue.js admin app used by the plugin.

### Prerequisites

- Node.js and npm (for building the Vue.js front-end)
- PHP 7.4+ and WordPress 5.0+
- WooCommerce 3.0+

### Setup

1. Install dependencies:
   ```bash
   cd assets/wp-vue
   npm install
   ```
2. Development (watch):
   ```bash
   npm run serve
   ```
3. Build for production:
   ```bash
   npm run build
   ```
4. Copy the built assets into the plugin's assets directory if your build step outputs elsewhere.

## Contributing

- Use the project's repository or issue tracker for bug reports and feature requests.
- Keep UI changes isolated in the Vue app under assets/wp-vue.

## License

Refer to the main plugin repository for license and copyright information.
