# AI Smart Sales - Project Handover Documentation

## 🎯 Project Overview

**AI Smart Sales** is a comprehensive WooCommerce Point of Sale (POS) plugin that transforms any WordPress/WooCommerce store into a full-featured retail management system. The plugin provides:

- **Point of Sale Interface**: Complete POS system for in-store sales
- **Multi-outlet Management**: Support for multiple store locations
- **Role-based Access Control**: Different user roles (Shop Manager, Outlet Manager, Cashier)
- **Real-time Inventory**: Synchronized inventory across online and offline channels
- **Sales Analytics**: Comprehensive reporting and dashboard
- **AI Assistance**: Smart sales recommendations and insights

---

## 🏗️ Project Architecture & Structure

### **Main Plugin Flow**

```
WordPress Load → Plugin Initialization → Core Components → API Registration → Frontend/Admin Interface
```

### **File Structure Overview**

```
ai-smart-sales/
├── 📄 ai-smart-sales.php          # Main plugin file (entry point)
├── 📄 composer.json               # Dependency management
├── 📄 package.json               # Frontend build tools
├── 📁 includes/                  # Core PHP backend
│   ├── 📁 Core/                 # Core functionality
│   ├── 📁 Api/                  # REST API handlers
│   ├── 📁 CPT/                  # Custom Post Types
│   ├── 📁 DB/                   # Database operations
│   ├── 📁 Exceptions/           # Error handling
│   └── 📄 functions.php         # Global helper functions
├── 📁 templates/                # Frontend templates
├── 📁 assets/                   # CSS, JS, images
├── 📁 vendor/                   # Composer dependencies
└── 📁 aipos-documentation/      # Project documentation
```

---

## 🚀 Plugin Initialization Flow

### **1. Entry Point (`ai-smart-sales.php`)**

**Purpose**: Main plugin file that bootstraps everything

**Key Responsibilities**:
- Define plugin constants (`CSMSL_VERSION`, `CSMSL_DIR`, etc.)
- Include essential files and autoloader
- Initialize the main Plugin class
- Handle WooCommerce dependency check
- Set up activation/deactivation hooks
- Early URL handling for POS routes

**Critical Code Sections**:
```php
// Constants definition
define('CSMSL_VERSION', $plugin_data['Version']);
define('CSMSL_DIR', plugin_dir_path(__FILE__));

// Main initialization
function csmsl_init() {
    $plugin = CSMSL\Includes\Core\Plugin::instance();
    $plugin->init();
}
add_action('plugins_loaded', 'csmsl_init', 15);
```

### **2. Core Plugin Class (`includes/Core/Plugin.php`)**

**Purpose**: Singleton class that manages the entire plugin lifecycle

**Key Methods**:
- `instance()`: Singleton pattern implementation
- `init()`: Main initialization method
- `init_core_components()`: Initialize Admin, POS, Activation classes
- `init_api_handlers()`: Register all REST API endpoints

**Initialization Sequence**:
1. RolesManager (creates user roles)
2. Admin (backend interface)
3. Activation (setup/cleanup)
4. POS (frontend POS system)
5. PostTypes (custom post types)
6. API Handlers (REST endpoints)

---

## 🔐 Authentication & User Management

### **User Roles System**

The plugin creates three custom user roles:

1. **`aipos_shop_manager`**
   - Full access to all POS features
   - Can manage multiple outlets
   - Admin dashboard access

2. **`aipos_outlet_manager`**
   - Manages specific outlet
   - Can assign cashiers to counters
   - Limited admin access

3. **`aipos_cashier`**
   - Frontend POS access only
   - Assigned to specific counter
   - No admin access

### **Authentication Flow**

```
User Login → Role Check → Outlet/Counter Assignment Check → Redirect to Appropriate Interface
```

**Key Files**:
- `includes/Core/AuthManager.php` - Authentication logic
- `includes/Api/Roles/RolesManager.php` - Role creation and management
- `includes/Api/Roles/UsersApiHandler.php` - User management API

---

## 🏪 POS System Architecture

### **POS URL Structure**

- `/aipos` - Main POS interface (requires cashier role)
- `/aipos/login` - POS login page
- `/aipos/auth/login` - Alternative login URL

### **POS System Flow**

```
URL Request → Early URL Handler → Authentication Check → Template Loading → Frontend App
```

### **Key Components**

**1. POS Class (`includes/Core/POS.php`)**
- Handles POS-specific routing
- Manages authentication for POS access
- Loads POS templates
- Enqueues frontend assets

**2. Templates**
- `templates/aipos-login.php` - POS login interface
- `templates/aipos-template.php` - Main POS application

**3. URL Handling**
```php
// Early URL interception
function csmsl_early_url_handler() {
    // Detects /aipos URLs and prepares routing
}

// Direct access handler
function CSMSL_DIRect_access_handler() {
    // Handles authentication and redirects
}
```

---

## 🌐 REST API Architecture

### **API Namespace**: `ai-smart-sales/v1`

### **API Structure**

All API handlers extend `BaseApiHandler` for consistency:

```php
abstract class BaseApiHandler {
    protected $namespace = 'ai-smart-sales/v1';
    abstract public function register_routes();
    public function check_permission($request);
    // ... helper methods
}
```

### **Available API Endpoints**

**Products** (`includes/Api/Products/ProductApiHandler.php`)
- `GET/POST /products` - List/Create products
- `GET/PUT/DELETE /products/{id}` - Single product operations

**Orders** (`includes/Api/Orders/OrdersApiHandler.php`)
- `GET/POST /orders` - List/Create orders
- `GET/PUT /orders/{id}` - Single order operations
- `POST /orders/{id}/refund` - Process refunds

**Customers** (`includes/Api/Customers/CustomersApiHandler.php`)
- `GET/POST /customers` - Customer management
- `GET /customers/{id}` - Customer details

**Outlets & Counters**
- `includes/Api/Outlets/OutletsApiHandler.php` - Outlet management
- `includes/Api/Outlets/CountersApiHandler.php` - Counter management

**Dashboard** (`includes/Api/Dashboard/DashboardApiHandler.php`)
- `GET /dashboard/summary` - Sales summary
- `GET /dashboard/analytics` - Various analytics endpoints

**Other APIs**:
- Categories, Media, Invoices, Reports, Channels, Users, AI Assistance

---

## 💾 Database & Data Flow

### **Custom Post Types**

Defined in `includes/CPT/PostTypes.php`:
- **`outlet`** - Store locations
- **`counter`** - POS terminals within outlets
- **Custom meta fields** for outlet/counter configuration

### **WooCommerce Integration**

The plugin extends WooCommerce functionality:
- Orders created through POS are standard WC orders
- Products synchronized with WooCommerce inventory
- Customer data integrated with WC customer system

### **Data Flow Example: Creating an Order**

```
Frontend POS → OrdersApiHandler → WooCommerce Order Creation → Inventory Update → Response
```

---

## 🎨 Frontend Architecture

### **Admin Interface**

**Location**: WordPress Admin Dashboard
**Entry Point**: `includes/Core/Admin.php`
**Technology**: Vue.js SPA loaded in admin pages

**Key Features**:
- Dashboard with sales analytics
- Product management
- User and role management
- Outlet/counter configuration

### **POS Interface**

**Location**: Frontend `/aipos` URL
**Entry Point**: `includes/Core/POS.php` → `templates/aipos-template.php`
**Technology**: Vue.js SPA with modern UI

**Key Features**:
- Product search and selection
- Cart management
- Payment processing
- Customer management
- Receipt printing

---

## 🛠️ Development Setup

### **Requirements**
- PHP 7.4+
- WordPress 5.0+
- WooCommerce 5.0+
- Node.js (for frontend build)
- Composer (for PHP dependencies)

### **Key Configuration Files**

**1. Composer (`composer.json`)**
```json
{
  "require": {
    "php": ">=7.4"
  },
  "require-dev": {
    "phpunit/phpunit": "^9.0",
    "squizlabs/php_codesniffer": "^3.6"
  }
}
```

**2. Package.json (`package.json`)**
- Frontend build configuration
- Vue.js and related dependencies

**3. Configuration (`includes/Core/Config.php`)**
- Plugin-wide configuration management
- Environment-specific settings

### **Build Commands**
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Build frontend assets
npm run build

# Code quality checks
composer cs-check
composer test
```

---

## 🔧 Key Classes & Their Purposes

### **Core Classes**

| Class | File | Purpose |
|-------|------|---------|
| `Plugin` | `Core/Plugin.php` | Main plugin orchestrator |
| `Admin` | `Core/Admin.php` | Admin interface management |
| `POS` | `Core/POS.php` | POS system management |
| `Activation` | `Core/Activation.php` | Plugin activation/deactivation |
| `AuthManager` | `Core/AuthManager.php` | Authentication logic |
| `Config` | `Core/Config.php` | Configuration management |

### **API Handler Classes**

| Handler | Purpose |
|---------|---------|
| `ProductApiHandler` | Product CRUD operations |
| `OrdersApiHandler` | Order management and POS transactions |
| `CustomersApiHandler` | Customer management |
| `OutletsApiHandler` | Multi-outlet management |
| `CountersApiHandler` | POS counter management |
| `DashboardApiHandler` | Analytics and reporting |

---

## 🔐 Security Features

### **Permission System**
- Role-based access control
- Nonce verification for AJAX requests
- User capability checks on all API endpoints

### **Data Validation**
- Input sanitization using WordPress functions
- Custom validation in `BaseApiHandler`
- Exception handling with custom exception classes

### **Security Headers**
- XSS protection
- Content type validation
- Frame options for POS interface

---

## 📝 Configuration & Settings

### **Plugin Constants**
```php
CSMSL_VERSION    // Plugin version
CSMSL_DIR        // Plugin directory path
CSMSL_URL        // Plugin URL
CSMSL_DEV_MODE   // Development mode flag
```

### **Configuration System**
```php
// Get configuration value
$value = Config::get('api.namespace');

// Set configuration value
Config::set('pos.session_timeout', 3600);
```

---

## 🚨 Common Issues & Troubleshooting

### **1. POS Not Loading**
**Check**: User roles, rewrite rules, file permissions
**Files**: `POS.php`, `AuthManager.php`

### **2. API Endpoints Not Working**
**Check**: Permalink structure, user permissions, nonce validation
**Files**: `BaseApiHandler.php`, individual API handlers

### **3. Login Issues**
**Check**: Role assignments, outlet/counter assignments
**Files**: `AuthManager.php`, `aipos-login.php`

### **4. Frontend Not Loading**
**Check**: Asset enqueuing, script dependencies, build files
**Files**: `Admin.php`, `POS.php`

---

## 📚 Development Guidelines

### **Code Standards**
- Follow WordPress coding standards
- Use PSR-4 autoloading
- Implement proper error handling
- Document all functions and classes

### **File Organization**
- Group related functionality
- Use consistent naming conventions
- Separate concerns (API, Core, Frontend)

### **Testing**
- PHPUnit for PHP code
- Manual testing for POS interface
- Cross-browser compatibility testing

---

## 🔄 Plugin Lifecycle

### **Activation**
1. Create custom user roles
2. Create custom post types
3. Set up default outlet/counter
4. Flush rewrite rules

### **Deactivation**
1. Cleanup temporary data
2. Preserve user roles (configurable)
3. Flush rewrite rules

### **Uninstall**
1. Remove custom post types
2. Clean up database
3. Remove custom roles (if configured)

---

## 📞 Support & Maintenance

### **Logs & Debugging**
- Error logs in `/wp-content/debug.log`
- Custom logging via `csmsl_log()` function
- Debug mode via `CSMSL_DEV_MODE` constant

### **Performance Considerations**
- Caching system in place
- Optimized database queries
- Minimal asset loading on non-POS pages

### **Future Enhancements**
- Mobile app integration
- Advanced reporting features
- Multi-currency support
- Integration with external POS hardware

---

This documentation provides a comprehensive overview of the AI Smart Sales plugin architecture and should serve as a solid foundation for your new employee to understand and work with the codebase effectively.
