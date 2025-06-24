# BhagamApps

**BhagamApps** es una plataforma modular desarrollada con Laravel. Su objetivo es ofrecer una gestión completa y flexible de recursos institucionales mediante módulos independientes.

---

## ✨ Filosofía del Proyecto

Este es un proyecto **libre, abierto y comunitario**, creado con el objetivo de ofrecer herramientas tecnológicas útiles para la comunidad. Está diseñado para ser **accesible, gratuito y colaborativo**, y siempre permanecerá como un proyecto de **software libre**.

Nuestro propósito es **crear aplicaciones útiles**, permitir que quienes quieran aprender a programar puedan hacerlo libremente y que cualquier persona que quiera colaborar en el proyecto pueda hacerlo de forma voluntaria, sin obligación ni pago.

No buscamos lucro ni propiedad exclusiva sobre el código. La única condición es que el proyecto y cualquier modificación que se haga de él **permanezcan siempre libres**.

---

## 📌 Objetivos
- Mantener una plataforma gratuita, libre y accesible para todos.
- Crear una comunidad de programadores voluntarios/as que aporten al proyecto.
- Ofrecer un espacio para que quienes quieran aprender programación puedan hacerlo de forma práctica y real.
- Permitir que cualquier persona u organización pueda utilizar el código libremente, incluso para fines comerciales.
- Garantizar que nadie pueda reclamar propiedad exclusiva sobre el proyecto o sobre modificaciones al mismo.

---

## 🤝 ¿Cómo colaborar?
1. Puedes colaborar programando, reportando errores, proponiendo ideas o ayudando a quienes están aprendiendo.
2. Si quieres aprender, puedes sumarte y practicar en este proyecto de forma gratuita.
3. Todo lo que desarrolles aquí se comparte con todos, nadie es dueño exclusivo.
4. Puedes utilizar este código para ofrecer servicios o crear productos comerciales, pero **no puedes cerrar el código** ni reclamarlo como propio.
5. **No reclamamos propiedad ni exclusividad sobre este proyecto.** Todo el trabajo aquí es colectivo.

---

## 📜 Licencia
Este proyecto está licenciado bajo una licencia personalizada de **Software Libre**, inspirada en GPLv3. 

**En resumen:**
- **Siempre libre.**
- **Modificaciones también libres.**
- **Permite uso comercial.**
- **Prohibido reclamar propiedad o autoría exclusiva sobre el código o sus modificaciones.**

Puedes ver el texto completo de la licencia en el archivo [`LICENCIA`](./LICENCIA).

---

## 🚀 Únete al proyecto
- Canal de comunicación: 
- Contacto: 3107532341

---

## 📖 Documentación extendida
La documentación completa se encuentra en la carpeta docs/.
- docs/instalacion.md: Guía detallada de instalación
- docs/estructura.md: Estructura de carpetas y módulos
- docs/api.md: Endpoints disponibles (si aplica)
- docs/usuarios.md: Manual de usuario final
- docs/desarrolladores.md: Guía para nuevos desarrolladores

---

## 🗓️ CHANGELOG
Consulta los cambios por versión en [CHANGELOG.md](CHANGELOG.md).

---

## Desarrollado con Laravel + Livewire

---

## 📦 Módulos actuales
- **Users:** Gestión de usuarios, roles y permisos.
- **Inventario:** Gestión de bienes institucionales, ubicaciones, custodios, historial y aprobaciones.
- **Apps:** Gestión de aplicaciones institucionales.
- **CrudGenerator (en desarrollo):** Generación automática de CRUDs para módulos.

---

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
