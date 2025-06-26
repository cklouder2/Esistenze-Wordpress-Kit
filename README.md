Tabii! Aşağıda, merge conflict işaretlerini kaldırarak ve içeriği mantıklı bir şekilde birleştirerek düzenlenmiş metni paylaşıyorum. Metin, WordPress eklentisi dokümantasyonu için uygun bir şekilde Markdown formatında yapılandırılmıştır. İşte nihai hali:

Esistenze WordPress Kit
This package provides several feature-rich modules for WooCommerce based sites. Each module resides in the modules/ directory and is loaded by esistenze_main_plugin.php.

Requirements
WordPress 5.8 or higher
PHP 7.4 or higher
WooCommerce 5.0 or higher
Installation
Download or clone this repository.
Upload the Esistenze-Wordpress-Kit folder to your site's wp-content/plugins/ directory.
Activate Esistenze WordPress Kit from the Plugins screen in WordPress.
A new Esistenze Kit menu will appear in the admin sidebar.
Modules are enabled on activation but can be turned on or off from their own settings pages.

Module Usage
Smart Product Buttons
Adds customizable action buttons to product pages. Open Esistenze Kit → Smart Buttons to create and reorder buttons. Uncheck the module's enable box on this page to disable it.

Category Styler
Styles product categories and provides a [esistenze_display_categories] shortcode. Configure options and toggle the module under Esistenze Kit → Category Styler.

Custom Topbar
Displays a top bar with menus and contact details. Enable it from the General tab of Esistenze Kit → Custom Topbar and choose where it is displayed.

Quick Menu Cards
Creates card groups for quick navigation. Insert them using [quick_menu_cards] or [quick_menu_banner]. Manage groups and settings under Esistenze Kit → Quick Menu Cards. Each card and group has an enable switch.

Price Modifier
Shows a highlighted note next to WooCommerce prices. Activate or deactivate the feature in Esistenze Kit → Price Modifier and adjust the note text and colors.

Save your settings on each screen after making changes. Disabling a module from its page immediately removes its output from the front-end.

Technical Details
Each module lives in the modules/ directory and can be enabled independently. See esistenze_main_plugin.php for the loader and general plugin structure.

This project is licensed under the MIT License.