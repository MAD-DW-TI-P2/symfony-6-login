# Symfony 6 login

<img src="https://jorgebenitezlopez.com/github/symfony.jpg">
<img src="https://img.shields.io/static/v1?label=PHP&message=Symfony&color=green">

# Requisitos

- Symfony CLI: https://symfony.com/download
- PHP 8.3.0: https://www.php.net/manual/en/install.php
- Composer: https://getcomposer.org/download/
- Symfony 6.4: https://symfony.com/releases/6.4

# Instalación de Symfony y paquetes

- symfony new back --version=6.4
- cd back
- composer require symfony/maker-bundle --dev  (Comandos para construir)
- composer require symfony/orm-pack (ORM para pegar la base de datos)
- composer require symfony/profiler-pack --dev (Profiler para tener información)
- composer require form (Para los formularios) 
- composer require validator (Validaciones)
- composer require twig-bundle (Para plantillas) 
- composer require security-csrf api "lexik/jwt-authentication-bundle" symfony/security-bundle (Para seguridad)

# Pasos para el CRUD de users

- Por facilidad de trabajo la base de datos será un sqlite en el propio repo. Modifico el .env para trabajar con sqlite https://www.sqlite.org/index.html
<kbd><img src="https://jorgebenitezlopez.com/github/sqlite.png"></kbd>
- Crear la entidad user: ``php bin/console make:user`` (Campo único, etc.)
- Actualizo la Base de datos: ``php bin/console doctrine:schema:update --force``
- Crear formulario de registro: ``php bin/console make:registration-form`` (Sin validación de emails, te pide una url para ir una vez registrado, todavía no la tengo, marco 0 y la cambiaré). Ya tengo el register. Ejecuto ``symfony server:start`` y compruebo https://127.0.0.1:8000/register
<kbd><img src="https://jorgebenitezlopez.com/github/register.png"></kbd>
- Falla al registrar por la ruta, nos dice donde... Creamos un controlador para la home: ``php bin/console make:controller`` y añado el nombre de la ruta en el controlador: app_home
- Creo el CRUD de User: ``php bin/console make:crud`` de User (Sin test por ahora). El new hago que redireccione al register. ``return $this->redirectToRoute('app_register');``
<kbd><img src="https://jorgebenitezlopez.com/github/CRUD.png"></kbd>
- Pongo un poco bonito el CRUD. En el head añado boostrap ``<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">`` y en el body añado un div ``<div class="container mt-2 p-3 pt-5 p-md-5">``
<kbd><img src="https://jorgebenitezlopez.com/github/boostrap.png"></kbd>
- Hago un login: ``php bin/console make:auth`` con AppCustomAuthenticator y con ruta de log out. Genera un SecurityController. Me pide a dónde le llevo, en la Linea 53: ``return new RedirectResponse($this->urlGenerator->generate('app_home'));``. En el profiler ya estoy logado, se puede ver abajo.
<kbd><img src="https://jorgebenitezlopez.com/github/login.png"><kbd>
- Vamos a darle un poco de seguridad, no queremos que todos el mundo pueda acceder a la tabla de user solo los admin. En el security.yaml, descomento y modifico: ``- { path: ^/user, roles:ROLE_ADMIN }``
<kbd><img src="https://jorgebenitezlopez.com/github/roles.png"><kbd>
- Intento entrar un /user y me da información del error. Podemos cambiar el rol por base de datos para comprobar que funciona. (Aplicar y guardar cambios)


# Pasos para los endpoints de Users

- Generar las claves públicas y privadas de jwt: php bin/console lexik:jwt:generate-keypair
- Configuración del login a través de jwt en el security.yaml y añado la ruta en el config routes.yml `` api_login_check: path: /api/login_check ``.   De tal forma que me puedo recibir un token pasando un usuario y una contraseña válidas por POST a la ruta login_check.

<kbd><img src="https://jorgebenitezlopez.com/github/api-login.png"><kbd>

- Registrarse vía API, cojo el controlador de register, lo duplico y modifico para que lo haga vía api y devuelva un true. (Importa el persist, el coger datos, instanciar un user, etc.)
- Podemos generar un token directamente y enviarlo nada más registrarse, ver los cambios en el controlador de register.
<kbd><img src="https://jorgebenitezlopez.com/github/api-register.png"><kbd>
- Con el token puedo securizar las rutas. He creado una ruta y un controlador en el SecurityController para verificar el acceso. La ruta está securizada: ``- { path: ^/apicheck, roles: ROLE_USER }``. Mando por postman en la cabecera el content-type y el token para verificar el acceso.
<kbd><img src="https://jorgebenitezlopez.com/github/api-check.png"><kbd>
- También puedo sacar información sobre el usuario del token. Ver en el SecurityController a ruta: /apicheckinfo

# Rutas

| URL path           | Método | Permisos                           | Descripción                          |
|---------------------|--------|------------------------------------|--------------------------------------|
| /register          | GET    | open                               | Formulario de registro               |
| /user              | GET    | Acceso permitido a usuarios administradores       | Listado de usuarios                  |
| /user/[id]/edit    | GET    | Acceso permitido a usuarios admninistrdores        | Edición de un usuario                |
| /login             | GET    | open                               | Formulario para logarse               |
| /api/login_check   | POST   | open                               | Mandas un usuario y una contraseña y devuelve un token |
| /apicheck     | POST    | Comprueba el token | Restringida para usuarios con token |
