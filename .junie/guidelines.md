# Development Guidelines

## Build/Configuration Instructions

### Prerequisites
- PHP 8.2+
- Node.js (for frontend assets)
- Composer (PHP dependency manager)

### Initial Setup
1. Clone the repository
2. Copy `.env.example` to `.env` and configure environment variables
3. Install PHP dependencies: `composer install`
4. Install Node.js dependencies: `npm install`
5. Generate application key: `php artisan key:generate`
6. Run migrations: `php artisan migrate`

### Development Server
The project uses a sophisticated development setup with multiple concurrent services:

```bash
# Start all development services (recommended)
composer run dev
```

This command starts:
- Laravel development server (`php artisan serve`)
- Queue worker (`php artisan queue:listen --tries=1`)
- Log monitoring (`php artisan pail --timeout=0`)
- Vite development server (`npm run dev`)

For SSR (Server-Side Rendering) development:
```bash
composer dev:ssr
```

### Build System
- **Frontend**: Vite with Vue 3 + TypeScript
- **Entry Point**: `resources/js/app.ts`
- **SSR Entry**: `resources/js/ssr.ts`
- **Path Aliases**:
  - `@` → `resources/js`
  - `ziggy-js` → `vendor/tightenco/ziggy`

### Build Commands
```bash
npm run dev          # Development server
npm run build        # Production build
npm run build:ssr    # SSR production build
```

## Testing Information

### Framework
The project uses **Pest PHP** - a modern testing framework built on PHPUnit with cleaner syntax.

### Test Structure
- **Feature Tests**: `tests/Feature/` - Integration/HTTP tests
- **Unit Tests**: `tests/Unit/` - Isolated unit tests
- **Configuration**: `tests/Pest.php`

### Running Tests
```bash
# Run all tests
composer test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run tests with coverage (if configured)
php artisan test --coverage
```

### Test Database
- Uses in-memory SQLite for fast testing
- Database is automatically reset between tests
- No manual database setup required for testing

### Writing Tests

#### Feature Test Example
```php
<?php

test('user can view dashboard', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertStatus(200);
});
```

#### Unit Test Example
```php
<?php

test('can calculate total price', function () {
    $calculator = new PriceCalculator();
    
    $result = $calculator->calculate(100, 0.2);
    
    expect($result)->toBe(120.0);
});
```

### Adding New Tests
1. Create test files in appropriate directory (`tests/Feature/` or `tests/Unit/`)
2. Use descriptive test names with `test()` or `it()` functions
3. Use `expect()` assertions for better readability
4. Follow existing patterns in the codebase

## Code Style & Formatting

### PHP Code Style
- **Tool**: Laravel Pint (automatic PHP formatting)
- **Standard**: Laravel coding standards
- **Command**: `./vendor/bin/pint` (auto-formats PHP files)

### JavaScript/TypeScript Code Style
- **Linting**: ESLint with Vue.js and TypeScript support
- **Formatting**: Prettier with custom configuration

#### ESLint Configuration
- Vue.js essential rules + TypeScript recommended
- Single-word component names allowed
- Explicit `any` types permitted
- Ignores: `vendor/`, `node_modules/`, `public/`, `bootstrap/ssr/`, UI components

#### Prettier Configuration
- **Line Width**: 150 characters
- **Indentation**: 4 spaces (2 for YAML)
- **Quotes**: Single quotes
- **Semicolons**: Required
- **Plugins**: 
  - Auto-organize imports
  - TailwindCSS class sorting (supports `clsx`, `cn` functions)

### Formatting Commands
```bash
# Format JavaScript/TypeScript/Vue files
npm run format

# Check formatting without changes
npm run format:check

# Lint and auto-fix JavaScript/TypeScript
npm run lint

# Format PHP files
./vendor/bin/pint
```

## Technology Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Database**: SQLite (development), configurable for production
- **Queue System**: Laravel Queues
- **Authentication**: WorkOS integration
- **Debugging**: Laravel Telescope
- **Auditing**: Laravel Auditing for model changes
- **AI Integration**: Prism PHP

### Frontend
- **Framework**: Vue 3 with Composition API
- **Language**: TypeScript
- **Styling**: TailwindCSS 4.x
- **UI Components**: Reka UI / shadcn-vue
- **Icons**: Lucide Vue
- **Forms**: VeeValidate + Zod validation
- **SPA**: Inertia.js (Laravel + Vue integration)
- **Animations**: Auto-animate, TW Animate CSS

### Development Tools
- **Build Tool**: Vite
- **Package Manager**: npm
- **Version Control**: Git
- **IDE Helpers**: Laravel IDE Helper (generates PHPDoc)

## Development Workflow

### Code Organization
- **Backend**: Standard Laravel structure (`app/`, `routes/`, `database/`)
- **Frontend**: `resources/js/` with component-based architecture
- **Components**: `resources/js/components/`
- **Pages**: `resources/js/pages/` (Inertia.js pages)
- **Composables**: `resources/js/composables/` (Vue composition functions)
- **Types**: `resources/js/types/` (TypeScript definitions)

### Key Features
- **SSR Support**: Server-side rendering with Inertia.js
- **Real-time Features**: Laravel queues and broadcasting
- **Form Validation**: Client-side (VeeValidate/Zod) + server-side (Laravel)
- **Responsive Design**: TailwindCSS with mobile-first approach
- **Type Safety**: Full TypeScript integration

### Debugging
- **Laravel Telescope**: Web-based debugging dashboard
- **Laravel Pail**: Real-time log monitoring
- **Vue DevTools**: Browser extension for Vue debugging

### Performance
- **Asset Optimization**: Vite handles bundling and optimization
- **Database**: Eloquent ORM with query optimization
- **Caching**: Laravel cache system (configurable drivers)
- **Queue Processing**: Background job processing

## Additional Notes

### Environment Configuration
- Copy `.env.example` to `.env` for local development
- Key services: Database, Mail, Queue, Cache drivers
- Telescope and debugging tools disabled in production

### Database Migrations
- Run `php artisan migrate` for database setup
- Use `php artisan migrate:fresh --seed` for fresh install with sample data

### Asset Compilation
- Development: `npm run dev` (with hot reload)
- Production: `npm run build` (optimized assets)
- SSR: `npm run build:ssr` (server-side rendering)

This project follows Laravel and Vue.js best practices with modern tooling for efficient development and testing.
