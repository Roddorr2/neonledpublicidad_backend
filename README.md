# 🌟 NEON LED PUBLICIDAD - Backend API

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-3.6-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)

**API Backend para sistema de gestión de NEON LED PUBLICIDAD**

*Una solución completa para la gestión de productos, servicios, empleados y comunicaciones de la empresa*

</div>

---

## 📋 Tabla de Contenidos

- [🎯 Descripción del Proyecto](#-descripción-del-proyecto)
- [🛠️ Tecnologías Utilizadas](#️-tecnologías-utilizadas)
- [📋 Prerrequisitos](#-prerrequisitos)
- [🚀 Instalación y Configuración](#-instalación-y-configuración)
- [⚙️ Configuración del Entorno](#️-configuración-del-entorno)
- [🗄️ Base de Datos](#️-base-de-datos)
- [📧 Configuración de Email](#-configuración-de-email)
- [🏃‍♂️ Uso del Proyecto](#️-uso-del-proyecto)
- [📚 Documentación API](#-documentación-api)
- [🔧 Comandos Útiles](#-comandos-útiles)
- [🐛 Solución de Problemas](#-solución-de-problemas)
- [🤝 Contribución](#-contribución)

---

## 🎯 Descripción del Proyecto

**NEON LED PUBLICIDAD Backend** es una API REST construida con Laravel que proporciona servicios para:

- 👥 **Gestión de Empleados**: Sistema completo de autenticación, roles y permisos
- 🛍️ **Catálogo de Productos**: Administración de productos LED y servicios
- 📞 **Sistema de Contacto**: Gestión de consultas y reclamaciones de clientes
- 📧 **Comunicaciones**: Sistema de envío de emails automáticos
- 📝 **Blog Corporativo**: Gestión de contenido y noticias
- 🔐 **Autenticación**: Sistema seguro con Laravel Sanctum
- 📋 **Roles y Permisos**: Control granular de accesos

---

## 🛠️ Tecnologías Utilizadas

### Backend Framework
- **Laravel 12.x** - Framework PHP moderno y robusto
- **PHP 8.2+** - Lenguaje de programación principal

### Base de Datos
- **MySQL 8.0+** - Sistema de gestión de base de datos relacional

### Autenticación y Seguridad
- **Laravel Sanctum** - Autenticación API con tokens
- **Bcrypt** - Encriptación de contraseñas

### Frontend Assets
- **Tailwind CSS 3.4** - Framework CSS utility-first
- **Vite 6.0** - Build tool moderno y rápido
- **Livewire 3.6** - Framework full-stack para Laravel

### Servicios Externos
- **Cloudinary** - Gestión y optimización de imágenes
- **Gmail SMTP** - Servicio de envío de emails
- **Intervention Image** - Procesamiento de imágenes

### Documentación
- **Swagger/OpenAPI** - Documentación automática de API

### Herramientas de Desarrollo
- **Laravel Pint** - Code styling automático
- **Laravel Sail** - Entorno de desarrollo con Docker
- **PHPUnit** - Testing framework

---

## 📋 Prerrequisitos

### Aplicaciones Requeridas

> ⚠️ **Importante**: Instala estas aplicaciones en el orden indicado

#### 1. 🐘 PHP 8.2 o superior
- **Windows**: [XAMPP](https://www.apachefriends.org/download.html) o [WAMP](https://www.wampserver.com/)
- **macOS**: [MAMP](https://www.mamp.info/) o `brew install php`
- **Linux**: `sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring`

#### 2. 🎼 Composer (Gestor de dependencias PHP)
```bash
# Descarga desde: https://getcomposer.org/download/
# O usando curl:
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### 3. 🟢 Node.js 18+ y npm
- **Descarga**: [nodejs.org](https://nodejs.org/)
- **Verificar instalación**:
```bash
node --version  # v18.0.0+
npm --version   # 8.0.0+
```

#### 4. 🗄️ MySQL 8.0+
- **Windows**: [MySQL Installer](https://dev.mysql.com/downloads/installer/)
- **macOS**: [MySQL Community Server](https://dev.mysql.com/downloads/mysql/)
- **Linux**: `sudo apt install mysql-server`

#### 5. 🔧 Git
- **Descarga**: [git-scm.com](https://git-scm.com/)

### Extensiones PHP Requeridas
```bash
# Verifica que tengas estas extensiones:
php -m | grep -E "(pdo|mysql|curl|json|mbstring|xml|zip|gd)"
```

---

## 🚀 Instalación y Configuración

### Paso 1: Clonar el Repositorio
```bash
git clone https://github.com/Kenia-Vergara/neonledpublicidad_backend.git
cd neonledpublicidad_backend
```

### Paso 2: Instalar Dependencias PHP
```bash
# Instalar paquetes de Composer
composer install

# Si tienes problemas con memoria, usa:
php -d memory_limit=-1 composer install
```

### Paso 3: Instalar Dependencias Node.js
```bash
# Instalar paquetes npm
npm install

# O si prefieres yarn:
yarn install
```

### Paso 4: Configurar Variables de Entorno
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

---

## ⚙️ Configuración del Entorno

### Editar archivo `.env`

Abre el archivo `.env` y configura las siguientes variables:

```env
# === CONFIGURACIÓN BÁSICA ===
APP_NAME="NEON LED PUBLICIDAD"
APP_ENV=local
APP_KEY=base64:TU_CLAVE_GENERADA_AQUI
APP_DEBUG=true
APP_URL=http://localhost:8000

# === BASE DE DATOS ===
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neonhouseled-back
DB_USERNAME=root
DB_PASSWORD=tu_password_mysql

# === CONFIGURACIÓN DE EMAIL ===
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password_gmail
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="tu_email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

# === CLOUDINARY (Opcional) ===
CLOUDINARY_URL=cloudinary://tu_api_key:tu_api_secret@tu_cloud_name
```

### 📧 Configurar Gmail para Emails

1. **Activar 2FA en Gmail**
2. **Generar App Password**:
   - Ir a: [Cuenta Google → Seguridad → Verificación en dos pasos → Contraseñas de aplicaciones](https://myaccount.google.com/apppasswords)
   - Crear contraseña para "Laravel App"
   - Usar esta contraseña en `MAIL_PASSWORD`

---

## 🗄️ Base de Datos

### Paso 1: Crear Base de Datos
```sql
-- Conectar a MySQL y ejecutar:
CREATE DATABASE `neonhouseled-back` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Paso 2: Ejecutar Migraciones
```bash
# Ejecutar todas las migraciones
php artisan migrate

# Si necesitas rehacer las migraciones:
php artisan migrate:fresh
```

### Paso 3: Sembrar Datos (Opcional)
```bash
# Ejecutar seeders si existen
php artisan db:seed

# O combinado con migraciones:
php artisan migrate:fresh --seed
```

---

## 🏃‍♂️ Uso del Proyecto

### Iniciar el Servidor de Desarrollo

#### Método 1: Servidor PHP Artisan
```bash
# Terminal 1: Servidor Laravel
php artisan serve
# Disponible en: http://localhost:8000

# Terminal 2: Compilar assets (en otra terminal)
npm run dev
# O para producción: npm run build
```

#### Método 2: Usando Laravel Sail (Docker)
```bash
# Configurar alias (solo la primera vez)
alias sail='vendor/bin/sail'

# Iniciar contenedores
sail up -d

# Disponible en: http://localhost
```

### 🎯 Endpoints Principales

```http
GET    /api/productos          # Listar productos
POST   /api/auth/login         # Iniciar sesión
POST   /api/auth/register      # Registrar usuario
GET    /api/empleados          # Listar empleados (autenticado)
POST   /api/contactanos        # Enviar consulta
```

---

## 📚 Documentación API

### Swagger/OpenAPI

La documentación completa de la API está disponible en:

```bash
# Generar documentación
php artisan l5-swagger:generate

# Acceder a la documentación
http://localhost:8000/api/documentation
```

### Colección Postman

Importa la colección desde: `docs/NEON_LED_API.postman_collection.json` (si existe)

---

## 🔧 Comandos Útiles

### Artisan Commands
```bash
# Limpiar cache de aplicación
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generar clases
php artisan make:controller NombreController
php artisan make:model NombreModel -m
php artisan make:migration crear_tabla_nombre

# Testing
php artisan test
php artisan test --coverage
```

### NPM Scripts
```bash
# Desarrollo
npm run dev          # Compilar y watch
npm run build        # Compilar para producción

# Otros
npm run lint         # Verificar código
npm audit fix        # Corregir vulnerabilidades
```

### Git Workflow
```bash
# Flujo básico
git checkout main
git pull origin main
git checkout -b feature/nueva-funcionalidad
# ... hacer cambios ...
git add .
git commit -m "feat: agregar nueva funcionalidad"
git push origin feature/nueva-funcionalidad
```

---

## 🐛 Solución de Problemas

### ❌ Problema: "php command not found"
```bash
# Solución: Agregar PHP al PATH
# Windows: Agregar C:\xampp\php a las variables de entorno
# macOS/Linux: Agregar al .bashrc o .zshrc
export PATH="/usr/local/bin/php:$PATH"
```

### ❌ Problema: "composer command not found"
```bash
# Solución: Instalar Composer globalmente
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### ❌ Problema: Error de conexión a base de datos
```bash
# Verificar que MySQL esté ejecutándose
sudo systemctl status mysql  # Linux
brew services list | grep mysql  # macOS

# Verificar credenciales en .env
DB_HOST=127.0.0.1  # No usar 'localhost'
DB_PORT=3306
```

### ❌ Problema: "vendor/autoload.php not found"
```bash
# Instalar dependencias
composer install

# Si persiste el error
rm -rf vendor/
composer install
```

### ❌ Problema: Permisos en storage/
```bash
# Linux/macOS
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/

# Cambiar propietario
sudo chown -R $USER:www-data storage/
sudo chown -R $USER:www-data bootstrap/cache/
```

### ❌ Problema: Emails no se envían
```bash
# Verificar configuración SMTP
php artisan tinker
Mail::raw('Test email', function ($message) {
    $message->to('test@example.com')->subject('Test');
});

# Ver logs
tail -f storage/logs/laravel.log
```

### 🔍 Debugging Tools
```bash
# Ver información del sistema
php artisan about

# Verificar configuración
php artisan config:show database
php artisan config:show mail

# Modo de mantenimiento
php artisan down
php artisan up
```

---

## 🤝 Contribución

### Flujo de Trabajo

1. **Fork** el repositorio
2. **Crear rama** para tu feature: `git checkout -b feature/mi-feature`
3. **Commit** tus cambios: `git commit -m 'feat: agregar mi feature'`
4. **Push** a la rama: `git push origin feature/mi-feature`
5. **Crear Pull Request**

### Estándares de Código

```bash
# Formatear código automáticamente
./vendor/bin/pint

# Ejecutar tests antes de commit
php artisan test
```

### Convención de Commits
```
feat: nueva funcionalidad
fix: corrección de bug
docs: actualización de documentación
style: cambios de formato
refactor: refactorización de código
test: agregar o modificar tests
chore: cambios en build o dependencias
```

---

## 📞 Soporte

### 🆘 ¿Necesitas Ayuda?

- **📧 Email**: desarrollo@neonledpublicidad.com
- **💬 Slack**: #backend-support
- **📋 Issues**: [GitHub Issues](https://github.com/z4val/neonledpublicidad_backend/issues)
- **📖 Wiki**: [Documentación interna](./docs/)

### 📚 Recursos Adicionales

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [MySQL 8.0 Reference](https://dev.mysql.com/doc/refman/8.0/en/)

---

<div align="center">

**🎉 ¡Bienvenido al equipo de desarrollo de NEON LED PUBLICIDAD!**

*Si tienes dudas, no dudes en preguntar. Estamos aquí para ayudarte.*

---

![Made with ❤️](https://img.shields.io/badge/Made%20with-❤️-red?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Powered%20by-Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)

</div>
