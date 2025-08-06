# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

VMStats is a Laravel application for tracking virtual machine servers and their guests. It provides both a web interface and HTTP API for managing VM infrastructure data.

## Key Commands

### Development
- `php artisan serve` - Start development server
- `php artisan tinker` - Access interactive shell
- `npm run dev` - Start Vite development server
- `npm run build` - Build assets for production

### Testing
- `php artisan test` - Run all tests using Pest
- `./vendor/bin/pest` - Run Pest tests directly
- `php artisan test --filter=ApiTest` - Run specific test class

### Code Quality
- `./vendor/bin/pint` - Laravel Pint code formatter
- `php artisan optimize` - Optimize application for production

### Database
- `php artisan migrate` - Run database migrations  
- `php artisan db:seed` - Seed database with test data
- `php artisan migrate:fresh --seed` - Fresh database with seeds

## Architecture Overview

### Core Models
- **Server** (`app/Models/Server.php`) - Represents VM host servers
- **Guest** (`app/Models/Guest.php`) - Represents VMs running on servers
- **User** (`app/Models/User.php`) - Application users with LDAP/SSO integration

### Key Controllers
- **VmController** (`app/Http/Controllers/Api/VmController.php`) - Main API endpoints for server/VM management
- **NotesController** (`app/Http/Controllers/Api/NotesController.php`) - API for updating notes
- **SSOController** (`app/Http/Controllers/Auth/SSOController.php`) - Single sign-on authentication

### API Endpoints
All API routes are in `/api` namespace:
- `GET /api/servers` - List all servers with guests
- `POST /api/vms` - Create/update VM records
- `POST /api/vms/delete` - Delete VM
- `POST /api/servers/delete` - Delete server
- `POST /api/server/notes` - Update server notes
- `POST /api/guest/notes` - Update guest notes

### Frontend Technologies
- **Livewire** - Used for interactive components (`VmList`, `UserList`)
- **Livewire Flux** - UI component library (Pro version included)
- **Bulma CSS** - CSS framework
- **Vite** - Asset bundling

### Authentication
- Supports both local authentication and SSO via Keycloak
- LDAP integration available
- Configuration in `config/sso.php` and `config/ldap.php`

### Database Schema
- `servers` table: id, name, notes, timestamps
- `guests` table: id, name, server_id, notes, timestamps
- Standard Laravel auth tables for users

### Testing
- Uses **Pest PHP** testing framework
- Test files in `tests/Feature/` and `tests/Unit/`
- Database uses SQLite in-memory for testing

## Development Notes

### Environment Setup
- Requires PHP 8.2+
- Uses Laravel 12.x
- Database defaults to SQLite for simplicity
- Docker configuration available (`Dockerfile`, `docker-compose.yml`)

### Key Configuration Files
- `config/vmstats.php` - Wiki integration settings
- `config/sso.php` - SSO authentication settings
- Multiple PHPUnit configurations for different CI environments

### Special Features
- Base64 encoded notes support via API
- Wiki link generation for servers/guests
- Automatic user creation via SSO
- Example KVM integration script (`kvm_vmstats.sh`)