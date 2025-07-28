# Smart Sales Lite - Complete POS & WooCommerce Management System

Smart Sales Lite is a comprehensive WordPress plugin that transforms your WooCommerce store into a complete Point of Sale (POS) system with advanced sales management, multi-outlet support, AI assistance, and powerful analytics. This plugin provides a full-featured business management solution for both online and offline sales, making it perfect for retail stores, restaurants, and service businesses.

## 🚀 Features Overview

### 💻 Point of Sale (POS) System

- **Modern POS Interface**: Access via `/aipos` URL with intuitive touch-friendly interface
- **Counter Management**: Support for multiple POS terminals per outlet
- **Real-time Inventory**: Live inventory updates across all channels
- **Role-based Access**: Secure access for cashiers, outlet managers, and shop managers
- **Receipt Printing**: Professional receipt generation and printing support

### 📦 Product Management

- **Complete Product CRUD Operations**: Create, read, update, and delete products
- **Bulk Product Operations**: Efficient bulk editing and management
- **Product Variations**: Full support for variable products and variations
- **Category Management**: Organize products with hierarchical categories
- **Product Search & Filtering**: Advanced search and filtering capabilities

### 🛒 Order Management

- **Comprehensive Order Handling**: Create, modify, and track orders
- **Split Payment Support**: Multiple payment methods per order (cash, card, bank transfer, PayPal, UPI, cryptocurrency)
- **Order Status Management**: Complete order lifecycle tracking
- **Trash & Recovery**: Soft delete with recovery options
- **Guest Order Support**: Handle orders from non-registered customers

### 👥 Customer Management

- **Customer CRUD Operations**: Full customer profile management
- **Guest Customer Support**: Handle walk-in customers without registration
- **Customer History**: Track purchase history and preferences
- **Customer Search**: Quick customer lookup and filtering

### 🏪 Multi-Outlet Management

- **Multiple Store Locations**: Manage multiple physical outlets with custom post types
- **Outlet-Specific Settings**: Customize settings per location
- **Outlet Assignment**: Assign staff to specific outlets
- **Centralized Management**: Control all outlets from one dashboard
- **Counter Configuration**: Multiple POS terminals per outlet

### 👤 User & Role Management

- **Custom User Roles**:
  - `aipos_outlet_manager`: Outlet-level management with full access
  - `aipos_cashier`: POS operation access with limited permissions
  - `aipos_shop_manager`: Store-wide management capabilities
- **Role-Based Permissions**: Granular access control system
- **User Assignment**: Assign users to specific outlets and counters

### 🤖 AI Assistance & Smart Features

- **AI-Powered Insights**: Intelligent sales recommendations and business insights
- **Smart Analytics**: AI-driven business intelligence and reporting
- **Automated Suggestions**: Product and pricing recommendations
- **Customer Behavior Analysis**: AI-powered customer insights and patterns

### 📊 Advanced Analytics & Reporting

- **Dashboard Analytics**: Real-time business metrics and KPIs
- **Sales Reports**: Comprehensive sales analytics with date range filtering
- **Multi-Outlet Reports**: Compare performance across locations
- **Custom Reports**: Generate reports for specific business needs
- **Export Capabilities**: Export data in various formats

### 🧾 Invoice Management System

- **Professional Invoices**: Generate branded invoices with custom templates
- **Invoice CRUD Operations**: Complete invoice lifecycle management
- **Line Item Management**: Detailed product line items with custom pricing
- **Tax Calculations**: Accurate VAT and tax handling per region
- **Bulk Operations**: Bulk invoice management and restoration

### 🗂️ Media & Asset Management

- **Product Images**: Manage product photos and galleries
- **Document Storage**: Store receipts, invoices, and documentation
- **Media Organization**: Structured media library integration
- **File Upload**: Secure file upload and management system

### 📱 Multi-Channel Sales

- **Channel Management**: Online store, POS, mobile, and other channels
- **Channel Analytics**: Performance tracking per sales channel
- **Unified Inventory**: Synchronized stock across all channels
- **Channel-Specific Settings**: Customize behavior per channel

### 🔧 Setup & Configuration

- **Setup Wizard**: Guided initial configuration for business setup
- **Business Profile Management**: Company information and branding
- **System Configuration**: Customize plugin behavior and settings
- **Data Migration**: Import existing data from other systems

## 📋 System Requirements

### Prerequisites

- **PHP**: 7.4 or higher
- **WordPress**: 5.0 or higher
- **WooCommerce**: 3.0 or higher (tested up to 5.7.0)
- **MySQL**: 5.6 or higher
- **Memory**: 256MB minimum (512MB recommended)

### Frontend Dependencies

- **Node.js**: 14.0 or higher (for development)
- **npm**: 6.0 or higher (for development)
- **Vue.js**: 3.x (included in build)
- **Modern Browser**: Chrome 70+, Firefox 65+, Safari 12+

### Server Requirements

- **HTTPS**: Recommended for production use
- **File Permissions**: Proper WordPress file permissions
- **WP REST API**: Must be enabled
- **Permalinks**: Pretty permalinks enabled

## 🛠️ Installation

### Standard Installation

1. **Download the Plugin**: Download Smart Sales Lite from the repository
2. **Upload to WordPress**:
   - Go to `Plugins` > `Add New` in your WordPress admin
   - Click `Upload Plugin` and select `smartsales-lite.zip`
   - Click `Install Now` and activate the plugin

### Manual Installation

1. **Extract Files**: Extract the plugin files to your computer
2. **Upload via FTP**: Upload the entire `smartsales-lite` folder to `/wp-content/plugins/`
3. **Activate**: Go to WordPress admin > Plugins and activate Smart Sales Lite

### Post-Installation Setup

1. **Navigate to Smart Sales**: Go to **Smart Sales Lite** in the WordPress admin menu
2. **Run Setup Wizard**: Complete the guided setup to configure your business
3. **Create Outlet**: Set up your first outlet (store location)
4. **Configure Counter**: Create a POS counter for your outlet
5. **Assign Users**: Create users and assign appropriate roles
6. **Configure Products**: Import or create your product catalog
7. **Start Selling**: Access the POS system at `/aipos` URL

### Quick Start Guide

After installation, the plugin will guide you through:
- Business profile setup
- Outlet and counter configuration
- User role assignment
- Product synchronization
- Payment method configuration

## 🎯 Usage Guide

### Accessing the POS System

1. **POS Interface**: Navigate to `/aipos` in your browser
2. **Login**: Use your WordPress credentials with appropriate POS roles
3. **Authentication**: Only users with `aipos_cashier`, `aipos_outlet_manager`, or `aipos_shop_manager` roles can access

### Initial Setup

1. **Business Configuration**: Set up your business profile and branding
2. **Outlet Creation**: Create your physical store locations
3. **Counter Setup**: Configure POS terminals for each outlet
4. **User Management**: Create users and assign roles (`aipos_cashier`, `aipos_outlet_manager`, `aipos_shop_manager`)
5. **Product Import**: Add your product catalog through WooCommerce

### Daily Operations

- **POS Sales**: Use the modern POS interface for in-store sales at `/aipos`
- **Order Management**: Handle online orders and fulfillment through the admin dashboard
- **Inventory Control**: Monitor and update stock levels in real-time
- **Customer Service**: Manage customer profiles and purchase history
- **Reporting**: Review daily sales and performance metrics

### Advanced Features

- **AI Insights**: Leverage AI recommendations for business growth and optimization
- **Multi-Outlet Reports**: Compare performance across different locations
- **Split Payments**: Handle complex payment scenarios with multiple payment methods
- **Invoice Generation**: Create professional invoices with custom templates
- **Data Export**: Export sales and inventory data for external analysis

### User Roles and Permissions

- **aipos_shop_manager**: Full access to all features and multi-outlet management
- **aipos_outlet_manager**: Manages specific outlets and can assign cashiers
- **aipos_cashier**: Frontend POS access only, assigned to specific counters

## 🔌 API Reference

Smart Sales Lite provides a comprehensive REST API for all functionality.

### Base URL
```
/wp-json/ai-smart-sales/v1/
```

### Core API Endpoints

#### Products API (`/products/`)
- `GET /` - List all products with pagination and filtering
- `POST /` - Create new product
- `GET /{id}` - Get product details
- `PUT /{id}` - Update product
- `DELETE /{id}` - Delete product
- `POST /bulk-action` - Bulk operations (delete, update status)
- `GET /{id}/variations` - Get product variations

#### Orders API (`/orders/`)
- `GET /` - List orders with filtering and pagination
- `POST /` - Create new order
- `GET /{id}` - Get order details
- `PUT /{id}` - Update order
- `DELETE /{id}` - Delete order
- `POST /{id}/split-payment` - Add split payment support

#### Customers API (`/customers/`)
- `GET /` - List customers
- `POST /` - Create customer
- `GET /{id}` - Get customer details
- `PUT /{id}` - Update customer
- `DELETE /{id}` - Delete customer

#### Outlets API (`/outlets/`)
- `GET /` - List outlets
- `POST /` - Create outlet
- `GET /{id}` - Get outlet details
- `PUT /{id}` - Update outlet
- `DELETE /{id}` - Delete outlet

#### Counters API (`/counters/`)
- `GET /` - List counters
- `POST /` - Create counter
- `GET /{id}` - Get counter details
- `PUT /{id}` - Update counter
- `DELETE /{id}` - Delete counter

#### Invoices API (`/invoices/`)
- `GET /` - List invoices with filtering
- `POST /` - Create invoice
- `GET /{id}` - Get invoice details
- `PUT /{id}` - Update invoice
- `DELETE /{id}` - Delete invoice
- `GET /trash` - Get trashed invoices
- `PUT /restore` - Bulk restore invoices

#### Dashboard API (`/dashboard/`)
- `GET /stats` - Get dashboard statistics
- `GET /recent-orders` - Get recent orders
- `GET /analytics` - Get analytics data

#### AI Assistance API (`/assistances/`)
- `GET /` - List AI assistances
- `POST /` - Create AI assistance
- `PUT /{id}` - Update assistance
- `DELETE /{id}` - Delete assistance

### Authentication

All API endpoints support multiple authentication methods:

- **Cookie Authentication**: For logged-in WordPress users
- **Application Passwords**: For external applications and integrations
- **Custom Authentication**: Role-based access control with POS-specific permissions
- **Nonce Verification**: CSRF protection for all requests

### Response Format

All API responses follow a consistent format:
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... },
  "pagination": {
    "total": 100,
    "pages": 10,
    "current": 1
  }
}
```

## 🏗️ Development

### Development Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Crafely/smartsales-lite
   cd smartsales-lite
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**:
   ```bash
   npm install
   ```

4. **Build frontend assets**:
   ```bash
   npm run build
   ```

### Development Commands

- `npm run dev` - Start development server with hot reload
- `npm run build` - Build production assets
- `npm run watch` - Watch for changes and rebuild
- `composer install` - Install PHP dependencies
- `composer dump-autoload` - Regenerate autoloader
- `composer test` - Run PHP unit tests
- `npm test` - Run JavaScript tests

### File Structure

```
smartsales-lite/
├── includes/                 # Core PHP classes
│   ├── Api/                 # REST API handlers
│   │   ├── Products/ProductApiHandler.php
│   │   ├── Orders/OrdersApiHandler.php
│   │   ├── Customers/CustomersApiHandler.php
│   │   ├── Outlets/OutletsApiHandler.php
│   │   ├── Outlets/CountersApiHandler.php
│   │   ├── Roles/UsersApiHandler.php
│   │   ├── Dashboard/DashboardApiHandler.php
│   │   ├── AI/AIAssistancesApiHandler.php
│   │   ├── Reports/SalesReportsApiHandler.php
│   │   ├── Invoices/InvoiceApiHandler.php
│   │   ├── Media/MediaApiHandler.php
│   │   └── Channels/ChannelsApiHandler.php
│   ├── Core/                # Core functionality
│   │   ├── Plugin.php       # Main plugin class
│   │   ├── Admin.php        # Admin interface
│   │   ├── POS.php          # POS system
│   │   ├── Activation.php   # Plugin activation
│   │   ├── AuthManager.php  # Authentication
│   │   └── Config.php       # Configuration
│   ├── CPT/                 # Custom post types
│   │   └── PostTypes.php    # Outlet, Counter, Invoice CPTs
│   └── DB/                  # Database handlers
├── templates/               # PHP templates
│   ├── aipos-template.php   # Main POS interface
│   └── aipos-login.php      # POS login
├── assets/                  # Frontend assets
│   ├── js/                  # JavaScript files
│   ├── css/                 # Stylesheets
│   └── images/              # Image assets
├── vendor/                  # Composer dependencies
├── composer.json            # PHP dependencies
├── package.json             # Node.js dependencies
├── smartsales-lite.php      # Main plugin file
└── README.md               # This file
```

### Development Guidelines

- Follow WordPress coding standards
- Use PSR-4 autoloading for classes
- Write comprehensive PHPDoc comments
- Implement proper error handling
- Use WordPress security best practices
- Test across different PHP and WordPress versions

## 🏛️ Architecture

### Backend Architecture

- **Namespace**: `AISMARTSALES\Includes`
- **Main Class**: `AISMARTSALES\Includes\Core\Plugin`
- **API Handlers**: RESTful API endpoints for all operations
- **Custom Post Types**:
  - `outlet` - Store locations with custom meta fields
  - `counter` - POS terminals within outlets
  - `invoice` - Invoice records with line items
- **User Roles**: Custom roles for POS access control
- **Database Integration**: Seamless WooCommerce integration

### Frontend Architecture

- **Framework**: Vue.js 3 with Composition API
- **Build Tool**: Modern JavaScript and CSS processing
- **Styling**: Tailwind CSS with responsive design
- **POS Interface**: Accessible via `/aipos` URL
- **Admin Interface**: WordPress admin dashboard integration
- **Authentication**: Role-based access control

### Database Schema

The plugin utilizes WordPress custom post types and meta fields:

#### Custom Post Types

- **Outlets**: Store location information with meta fields for address, settings
- **Counters**: POS terminal configurations linked to outlets
- **Invoices**: Invoice records with line items, tax calculations, and customer data

#### Custom User Roles

- **aipos_outlet_manager**: Full outlet management access
- **aipos_cashier**: POS operation permissions only
- **aipos_shop_manager**: Store-wide management capabilities

#### WooCommerce Integration

- Orders created through POS are standard WooCommerce orders
- Products synchronized with WooCommerce inventory
- Customer data integrated with WooCommerce customer system

## 🔐 Security Features

- **Role-Based Access Control**: Granular permissions system with custom POS roles
- **Data Validation**: Server-side validation for all inputs using WordPress standards
- **Sanitization**: Proper data sanitization and escaping for XSS prevention
- **Nonce Verification**: CSRF protection for all forms and AJAX requests
- **Authentication**: Secure user authentication with role verification
- **API Security**: Protected REST API endpoints with permission callbacks
- **File Security**: Secure file upload and media management
- **Security Headers**: XSS protection, content type validation, frame options

## 🌐 Internationalization

The plugin is fully internationalized and supports:

- **Text Domain**: `smartsales-lite`
- **Translation Ready**: All strings are translatable using WordPress i18n functions
- **RTL Support**: Right-to-left language support
- **Multi-Language**: Compatible with translation plugins like WPML
- **Localization**: Date, time, and number formatting based on locale

## 🧪 Testing

### Running Tests

```bash
# PHP Unit Tests
composer test

# JavaScript Tests
npm test

# E2E Tests
npm run test:e2e

# Code Quality
composer phpcs
composer phpstan
```

### Test Coverage

- Unit tests for all API handlers and core classes
- Integration tests for WooCommerce functionality
- Frontend component tests for Vue.js components
- End-to-end workflow tests for POS operations
- Security testing for authentication and permissions

## 📚 Documentation

### Additional Resources

- **API Documentation**: Available in `/aipos-documentation/` directory
- **Developer Guide**: `DEVELOPER_GUIDE.md` with comprehensive project documentation
- **User Guide**: Available in plugin admin dashboard
- **Code Examples**: Sample implementations in documentation

### Support Channels

- **GitHub Issues**: Bug reports and feature requests
- **Documentation**: Comprehensive guides and API reference
- **Community Forum**: User discussions and support
- **Email Support**: Direct support for critical issues

## 🔄 Updates & Maintenance

### Automatic Updates

The plugin supports WordPress automatic updates system and includes:

- **Version Compatibility Checks**: Ensures compatibility with WordPress and WooCommerce
- **Database Migration Scripts**: Handles database updates seamlessly
- **Backward Compatibility**: Maintains compatibility with previous versions
- **Update Notifications**: Notifies users of available updates
- **Safe Updates**: Rollback capability in case of issues

### Manual Updates

1. **Backup**: Always backup your site and database before updating
2. **Upload**: Upload new plugin files via WordPress admin or FTP
3. **Database**: Run database updates if prompted by the plugin
4. **Clear Cache**: Clear any caching systems after update
5. **Test**: Verify all functionality works correctly

### Maintenance Tasks

- **Regular Backups**: Backup your data regularly
- **Performance Monitoring**: Monitor plugin performance and logs
- **Security Updates**: Keep WordPress, WooCommerce, and plugin updated
- **Database Optimization**: Optimize database tables periodically

## 🤝 Contributing

We welcome contributions! Please see our contributing guidelines:

1. **Fork the Repository**: Create your own fork of the project
2. **Create a Feature Branch**: Work on your feature in a separate branch
3. **Make Your Changes**: Implement your feature or bug fix
4. **Add Tests**: Write tests for your changes
5. **Submit a Pull Request**: Submit your changes for review

### Development Guidelines

- Follow WordPress coding standards (WPCS)
- Write comprehensive tests for new features
- Document your code with PHPDoc comments
- Update README and documentation for new features
- Ensure backward compatibility where possible

### Code Standards

- **PHP**: Follow WordPress PHP coding standards
- **JavaScript**: Use ESLint configuration provided
- **CSS**: Follow CSS coding standards
- **Documentation**: Write clear, comprehensive documentation

## 📄 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

### License Compatibility

- **WordPress**: Compatible with WordPress GPL licensing
- **WooCommerce**: Compatible with WooCommerce licensing
- **Third-party Libraries**: All included libraries are GPL-compatible

## 🙏 Acknowledgments

- **WordPress Community**: For the excellent platform and development standards
- **WooCommerce Team**: For the robust e-commerce foundation
- **Vue.js Team**: For the reactive frontend framework
- **Tailwind CSS**: For the utility-first CSS framework
- **Contributors**: All developers who have contributed to this project
- **Users**: Our community of users who provide feedback and support

## 📞 Support

For support and questions:

- **GitHub Issues**: [Report bugs and request features](https://github.com/Crafely/smartsales-lite/issues)
- **Documentation**: [Read the full documentation](https://github.com/Crafely/smartsales-lite/wiki)
- **Email**: support@crafely.com
- **Community**: Join our community discussions

### Getting Help

1. **Check Documentation**: Review the documentation and developer guide
2. **Search Issues**: Search existing GitHub issues for solutions
3. **Create Issue**: Create a detailed issue with reproduction steps
4. **Community Support**: Ask questions in community forums

---

**Smart Sales Lite** - Transforming your WooCommerce store into a complete business management solution with AI-powered insights and modern POS capabilities.
