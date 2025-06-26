# Esistenze WordPress Kit

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/Build-Passing-brightgreen.svg)]()
[![Version](https://img.shields.io/badge/Version-2.0.0-success.svg)]()
[![Release](https://img.shields.io/badge/Release-Major-red.svg)]()

A comprehensive WordPress plugin suite providing essential modules for WooCommerce-based websites. Each module offers powerful functionality while maintaining simplicity and performance.

## 🚀 Features

### 📱 Smart Product Buttons
- **Customizable Action Buttons**: Add phone, email, WhatsApp, and form trigger buttons to product pages
- **Advanced Analytics**: Track clicks, views, and conversion rates
- **Responsive Design**: Mobile-optimized buttons with smooth animations
- **Import/Export**: Backup and restore button configurations
- **Bulk Operations**: Manage multiple buttons efficiently

### 🎨 Category Styler
- **Visual Category Display**: Beautiful category grids with images and descriptions
- **Shortcode Support**: `[esistenze_display_categories]` with extensive parameters
- **Caching System**: Optimized performance with intelligent caching
- **Lazy Loading**: Improved page speed with image lazy loading
- **Customizable Styling**: Full control over appearance and layout

### 🔝 Custom Topbar
- **Flexible Positioning**: Fixed, sticky, or static positioning options
- **Contact Information**: Display phone, email, and social media links
- **Menu Integration**: Custom menu support with dropdown functionality
- **Analytics Tracking**: Monitor topbar performance and engagement
- **Responsive Design**: Mobile-friendly with touch-optimized controls

### 🎯 Quick Menu Cards
- **Card Groups**: Organize navigation elements into logical groups
- **Multiple Layouts**: Grid, list, and banner display options
- **Advanced Editor**: Intuitive interface for card management
- **Shortcode Integration**: `[quick_menu_cards]` and `[quick_menu_banner]`
- **Performance Optimized**: Efficient loading and caching

### 💰 Price Modifier
- **Custom Price Notes**: Add contextual information to product prices
- **Visual Styling**: Customizable colors, backgrounds, and borders
- **Responsive Design**: Mobile-optimized price displays
- **Easy Configuration**: Simple admin interface for quick setup

## 📋 Requirements

- **WordPress**: 5.8 or higher
- **PHP**: 7.4 or higher  
- **WooCommerce**: 5.0 or higher (for e-commerce features)
- **Memory**: 128MB minimum (256MB recommended)

## 🔧 Installation

### Automatic Installation (Recommended)

1. Download the latest release from [GitHub Releases](https://github.com/esistenze/wordpress-kit/releases)
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**
3. Choose the downloaded ZIP file and click **Install Now**
4. Activate the plugin through the **Plugins** screen
5. Navigate to **Esistenze Kit** in the admin menu to configure modules

### Manual Installation

1. Download and extract the plugin files
2. Upload the `esistenze-wordpress-kit` folder to `/wp-content/plugins/`
3. Activate **Esistenze WordPress Kit** from the **Plugins** screen
4. Configure your modules via **Esistenze Kit** menu

### Development Installation

```bash
# Clone the repository
git clone https://github.com/esistenze/wordpress-kit.git
cd wordpress-kit

# Install dependencies
composer install
npm install

# Build assets
npm run build

# Run tests
composer test
npm test
```

## 🎛️ Configuration

### User Permissions

The plugin supports role-based access control:

- **Administrator**: Full access to all features
- **Editor**: Access to content management features  
- **Author**: Limited access to basic functionality
- **Subscriber**: Read-only access where applicable

### Module Settings

Each module can be configured independently:

1. Go to **Esistenze Kit** in WordPress admin
2. Select the module you want to configure
3. Adjust settings according to your needs
4. Save changes to apply immediately

## 📖 Usage Guide

### Smart Product Buttons

1. Navigate to **Esistenze Kit → Smart Buttons**
2. Click **Add New Button** to create your first button
3. Configure button type, styling, and target action
4. Buttons automatically appear on WooCommerce product pages
5. Monitor performance in the Analytics tab

**Shortcode Usage:**
```php
[esistenze_button id="1"]
[esistenze_button type="whatsapp" number="+1234567890"]
```

### Category Styler

1. Go to **Esistenze Kit → Category Styler**
2. Enable the module and configure display options
3. Use the shortcode to display categories:

```php
[esistenze_display_categories limit="8" orderby="name" order="ASC"]
[esistenze_display_categories parent="0" hide_empty="false"]
```

### Custom Topbar

1. Access **Esistenze Kit → Custom Topbar**
2. Enable the topbar in the General tab
3. Configure content in the Content tab
4. Customize styling in the Design tab
5. Set advanced options in the Advanced tab

### Quick Menu Cards

1. Visit **Esistenze Kit → Quick Menu Cards**
2. Create card groups and individual cards
3. Insert cards using shortcodes:

```php
[quick_menu_cards group="main-menu"]
[quick_menu_banner group="featured" style="grid"]
```

### Price Modifier

1. Open **Esistenze Kit → Price Modifier**
2. Enable the feature and set your custom note
3. Customize colors and styling
4. Changes apply automatically to all WooCommerce products

## 🎨 Customization

### CSS Customization

Add custom styles to your theme's `style.css` or use the built-in custom CSS fields:

```css
/* Customize Smart Buttons */
.esistenze-smart-button {
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Style Category Cards */
.esistenze-category-styler-item {
    transition: transform 0.3s ease;
}

.esistenze-category-styler-item:hover {
    transform: translateY(-5px);
}
```

### PHP Hooks and Filters

```php
// Modify button output
add_filter('esistenze_button_html', function($html, $button) {
    // Your custom modifications
    return $html;
}, 10, 2);

// Customize category query
add_filter('esistenze_category_query_args', function($args) {
    // Modify query parameters
    return $args;
});

// Add custom topbar content
add_action('esistenze_topbar_content', function() {
    echo '<div class="custom-content">Your content here</div>';
});
```

## 🔧 Development

### Project Structure

```
esistenze-wordpress-kit/
├── assets/                 # Main plugin assets
├── languages/             # Translation files
├── modules/               # Individual modules
│   ├── smart-product-buttons/
│   ├── category-styler/
│   ├── custom-topbar/
│   ├── quick-menu-cards/
│   └── price-modifier/
├── tests/                 # PHPUnit tests
├── composer.json          # PHP dependencies
├── package.json          # Node.js dependencies
├── webpack.config.js     # Build configuration
└── esistenze_main_plugin.php # Main plugin file
```

### Building Assets

```bash
# Development build with watching
npm run dev

# Production build
npm run build

# Lint code
npm run lint:js
npm run lint:css

# Format code
npm run format
```

### Running Tests

```bash
# PHP tests
composer test

# JavaScript tests  
npm test

# Code quality checks
composer phpcs
composer phpcbf  # Auto-fix issues
```

### Creating a Release

```bash
# Build production assets
npm run build

# Create release package
npm run zip
```

## 🌐 Translation

The plugin is translation-ready and includes:

- **POT file**: Template for new translations
- **Turkish (tr_TR)**: Complete translation included
- **Translation functions**: All strings properly wrapped

### Adding New Languages

1. Copy `languages/esistenze-wp-kit.pot` to `esistenze-wp-kit-{locale}.po`
2. Translate strings using a tool like Poedit
3. Generate `.mo` file: `npm run build:mo`
4. Submit translations via GitHub or email

## 🐛 Troubleshooting

### Common Issues

**Module not loading:**
- Check file permissions (644 for files, 755 for directories)
- Verify PHP error logs for specific errors
- Ensure WordPress and PHP version requirements are met

**Permission errors:**
- Verify user roles and capabilities
- Check if `esistenze_qmc_capability()` function is working
- Review security plugin conflicts

**Styling issues:**
- Clear any caching plugins
- Check for theme CSS conflicts
- Verify asset files are loading correctly

**Performance problems:**
- Enable caching in module settings
- Optimize images used in categories/cards
- Check for plugin conflicts

### Debug Mode

Enable WordPress debug mode for detailed error information:

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Quick Start for Contributors

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes and test thoroughly
4. Commit: `git commit -m 'Add amazing feature'`
5. Push: `git push origin feature/amazing-feature`
6. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🔒 Security

Security is a top priority. Please review our [Security Policy](SECURITY.md) and report vulnerabilities responsibly to [security@esistenze.com](mailto:security@esistenze.com).

## 📞 Support

- **Documentation**: [GitHub Wiki](https://github.com/esistenze/wordpress-kit/wiki)
- **Issues**: [GitHub Issues](https://github.com/esistenze/wordpress-kit/issues)
- **Email**: [info@esistenze.com](mailto:info@esistenze.com)
- **Website**: [https://esistenze.com](https://esistenze.com)

## 📊 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed history of changes.

## 🙏 Acknowledgments

- WordPress community for excellent documentation and standards
- WooCommerce team for robust e-commerce foundation
- All contributors and users who provide feedback and improvements

---

**Made with ❤️ by [Esistenze](https://esistenze.com)**