# Instalación

Una LAMP Stack incluye los componentes principales que usaremos: Apache, MySQL y PHP, además de estar configurados para funcionar de forma conjunta.
Debido a que somos un equipo numeroso y no todos tienen el mismo nivel de conocimiento de cada componente, se ha decidido basarnos en una [LAMP Stack de Bitnami](https://bitnami.com/stack/lamp), que además tiene la ventaja de estar configurada de forma modular, lo cual nos permitirá necesitar de muy pocos cambios externos para funcionar, lo cual además nos permite desplegar todo de forma lo más rápida posible.

## Instalación de la LAMP Stack

Para instalar la LAMP Stack, hay que seguir los siguientes pasos (para una instalación de Linux x64, pero se puede realizar en Linux x86 y Mac OS X):

```
$ cd ~
$ wget https://bitnami.com/redirect/to/51769/bitnami-lampstack-5.4.39-0-linux-x64-installer.run
$ chmod a+x ./bitnami-lampstack-5.4.39-0-linux-x64-installer.run
$ sudo ./bitnami-lampstack-5.4.39-0-linux-x64-installer.run
```

Una vez iniciado el instalador, solo hay que seguir los pasos, pero teniendo en cuenta los siguientes puntos:

- El directorio de instalación deberá ser `/opt/bitnami`.
- Aunque no es imprescindible para el funcionamiento de la aplicación, sí que se recomienda instalarlo en el puerto 80; si ya está ocupado basta con desactivar la aplicación que lo esté usando mientras se esté trabajando en este proyecto, y seguir hacia delante en el instalador.
- La contraseña de la base de datos del entorno de desarrollo será `bitnami1`. El servidor de producción se configurará con otras credenciales, por razones de seguridad.

## Instalación y configuración de nuestros ejemplos de OAuth

Una vez instalada la LAMP Stack, es necesario seguir los siguientes pasos para clonar el repositorio y preparar la aplicación:

```
$ sudo su -
$ mkdir /opt/bitnami/hosted
$ cd /opt/bitnami/hosted
$ git clone https://github.com/marcosbc/oauth-gitt.git
$ cd oauth-gitt
```

### Configurar Apache

Para que nuestros ejemplos sean accesibles, es necesario asegurarnos que Apache está cogiendo los ficheros de configuración.
Para ello, hay que añadir la siguiente línea a `/opt/bitnami/apache2/conf/bitnami/bitnami-apps-vhosts.conf`:

```
Include "/opt/bitnami/hosted/oauth-gitt/service/conf/httpd-vhosts.conf"
```

No hay que olvidar que es necesario un reinicio de Apache para que estos
cambios tengan efecto:

```
$ sudo /opt/bitnami/ctlscript.sh restart apache
```

### Configurar Git

Se recomienda configurar Git para que se pueda saber quién ha hecho un asentamiento:

```
$ git config --global user.name "TU NOMBRE COMPLETO o USUARIO_DE_GITHUB"
$ git config --global user.email "TU_EMAIL@gmail.com"
```

### Subir cambios

Se pueden subir cambios asentados ("commited changes") ejecutando el siguiente comando:

```
$ git push origin master
```

### Bajar los últimos cambios

Se puede descargar los últimos cambios del repositorio con el siguiente comando:

```
$ git pull origin master
```

## Acceder a la aplicación servicio y servidores OAuth en servidor local

Para poder probar las aplicaciones web en local, es necesario añadir las siguientes líneas a `/etc/hosts`:

```
127.0.0.1 oauthg10.tk
127.0.0.1 www.oauthg10.tk
127.0.0.1 api.oauthg10.tk
127.0.0.1 www.api.oauthg10.tk
127.0.0.1 oauth.oauthg10.tk
127.0.0.1 www.oauth.oauthg10.tk
```

Por ejemplo, se puede realizar de la siguiente manera en un solo paso:

```
$ sudo sh -c "echo '127.0.0.1 oauthg10.tk\n127.0.0.1 www.oauthg10.tk\n127.0.0.1 api.oauthg10.tk\n127.0.0.1 www.api.oauthg10.tk\n127.0.0.1 service.oauthg10.tk\n127.0.0.1 www.service.oauthg10.tk' >> /etc/hosts"
```

## Instalar la aplicación y dependencias

Para instalar la aplicación, se ha creado un sencillo script. Por lo tanto, basta con ejecutar lo siguiente:

```
$ cd /opt/bitnami/hosted/oauth-gitt/scripts/
$ ./install.sh
```

**Nota importante:** Se ha supuesto que la contraseña por defecto de la aplicación es `bitnami1`. Si no es el caso, cree una rama (denominada, por ejemplo, `deployed`) con los cambios necesarios en el script `service-install.php` (es decir, cambie `bitnami1` por su contraseña).
Una vez instalados los datos de ejemplo puede volver a la rama `master`, ya que no se volverá a usar esa contraseña.

Una vez realizado esto, debería de tener acceso a las páginas:

 - http://oauth.oauthg10.tk/
 - http://api.oauthg10.tk/

### Eliminar la aplicación

Quizás quieras actualizar los datos de ejemplo o eliminar cualquier rastro dejado por haber instalado este proyecto (como las dependencias de Composer).
Realizar esto es bastante sencillo:

```
$ cd /opt/bitnami/hosted/oauth-gitt/scripts/
$ ./remove.sh
```
