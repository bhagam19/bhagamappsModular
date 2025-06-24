# BhagamApps

**BhagamApps** es una plataforma modular desarrollada con Laravel. Su objetivo es ofrecer una gestión completa y flexible de recursos institucionales mediante módulos independientes.

## 📦 Módulos actuales
- **Users:** Gestión de usuarios, roles y permisos.
- **Inventario:** Gestión de bienes institucionales, ubicaciones, custodios, historial y aprobaciones.
- **Apps:** Gestión de aplicaciones institucionales.
- **CrudGenerator (en desarrollo):** Generación automática de CRUDs para módulos.

## 🚀 Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/tu_usuario/bhagamapps.git
cd bhagamapps
```

2. Instalar dependencias:
```bash
composer install
npm install && npm run build
```

3. Configurar variables de entorno:
```bash
cp .env.example .env
php artisan key:generate
```

4. Migrar base de datos y ejecutar seeders:
```bash
php artisan migrate --seed
```

5. Ejecutar servidor local:
```bash
php artisan serve
```

## ⚙️ Estructura del Proyecto
```bash
Modules/
├── Users/          # Gestión de usuarios
├── Inventario/     # Gestión de bienes
├── Apps/           # Aplicaciones institucionales
├── CrudGenerator/  # Generador automático de CRUDs
```

## 📖 Documentación extendida
La documentación completa se encuentra en la carpeta docs/.
- docs/instalacion.md: Guía detallada de instalación
- docs/estructura.md: Estructura de carpetas y módulos
- docs/api.md: Endpoints disponibles (si aplica)
- docs/usuarios.md: Manual de usuario final
- docs/desarrolladores.md: Guía para nuevos desarrolladores

## 🗓️ CHANGELOG
Consulta los cambios por versión en [CHANGELOG.md](CHANGELOG.md).

## 📄 Licencia
Este proyecto está licenciado bajo la licencia MIT.

## Desarrollado con Laravel + Livewire