# Instalación

## Instalación de la LAMP Stack

Una LAMP Stack incluye los componentes principales que usaremos: Apache, MySQL y PHP, además estar configurados adecuadamente.


Vamos a basarnos en una [LAMP Stack de Bitnami](https://bitnami.com/stack/lamp), con la intención de poder reutilizar el código entre todos para el entorno local y el remoto a la vez, y poder actualizarlo a medida que se va actualizando este repositorio.
 

Para ello, hay que seguir los siguientes pasos (los pasos son para una instalación de Linux x64, pero se puede realizar en Linux x86 y Mac OS X si es de vuestra preferencia):
```
$ cd ~
$ wget https://bitnami.com/redirect/to/51769/bitnami-lampstack-5.4.39-0-linux-x64-installer.run
$ chmod a+x ./bitnami-lampstack-5.4.39-0-linux-x64-installer.run
$ sudo ./bitnami-lampstack-5.4.39-0-linux-x64-installer.run
```

Y ya seguís los pasos de la instalación. Notas:

- El directorio de instalación deberá ser `/opt/bitnami`.
- Intentad instalarlo en vuestro puerto 80, si ya está ocupado, desactivar la aplicación que lo use hasta que terminemos este proyecto.

## Instalación y configuración de nuestros ejemplos de OAuth

Nota: Antes de empezar este paso, deberéis aseguraros que tenéis una clave privada en `~/.ssh` (por defecto se denomina `id_rsa`).

- Si la tienes, genera una clave pública a través de esta clave (privada), y subid la clave pública a vuestra configuración de GitHub.
- Si no la tienes, genera una nueva clave privada (denomínala `id_rsa`) y genera una pública a través de esta. Deben estar localizadas en `~/.ssh`. Sube esta clave pública generada a GitHub.

Una vez instalada la LAMP Stack, seguid los siguientes pasos para clonar el repositorio y preparar la aplicación:

```
$ sudo su -
$ mkdir /opt/bitnami/hosted
$ cd /opt/bitnami/hosted
$ git clone https://github.com/marcosbc/oauth-gitt.git
$ cd oauth-gitt
```

### Configurar Apache

Para que nuestros ejemplos sean accesibles, antes tenemos que asegurarnos que Apache está cogiendo los ficheros de configuración.
Para ello, hay que añadir las siguientes líneas a `/opt/bitnami/apache2/conf/bitnami/bitnami-apps-vhosts.conf`:

```
Include "/opt/bitnami/hosted/oauth-gitt/client/conf/httpd-vhosts.conf"
Include "/opt/bitnami/hosted/oauth-gitt/service/conf/httpd-vhosts.conf"
```

No hay que olvidar que es necesario un reinicio de Apache para que estos
cambios tengan efecto:

```
$ sudo /opt/bitnami/ctlscript.sh restart apache
```

### Configurar Git

No os olvidéis de añadir los datos vuestros de Git para que cada uno sepa quién ha hecho un asentamiento:

```
$ git congit --global user.name "TU NOMBRE COMPLETO o USUARIO_DE_GITHUB"
$ git config --global user.email "TU_EMAIL@gmail.com"
```

### Subir cambios

Para subir cambios asentados ("commited changes"), solo tenéis que ejecutar lo siguiente:

```
$ git push origin master
```

### Bajar los últimos cambios

Para bajar los últimos cambios, tenéis que ejecutar lo siguiente:

```
$ git pull origin master
```

## Acceder a la aplicación en servidor local

Para poder probarla en local, deberéis añadir las siguientes líneas a `/etc/hosts`.

```
127.0.0.1 oauthg10.tk
127.0.0.1 www.oauthg10.tk
127.0.0.1 client.oauthg10.tk
127.0.0.1 www.client.oauthg10.tk
127.0.0.1 oauth.oauthg10.tk
127.0.0.1 www.oauth.oauthg10.tk
```

Por ejemplo, se puede realizar de la siguiente manera en un paso:

```
$ sudo sh -c "echo '127.0.0.1 oauthg10.tk\n127.0.0.1 www.oauthg10.tk\n127.0.0.1 client.oauthg10.tk\n127.0.0.1 www.client.oauthg10.tk\n127.0.0.1 service.oauthg10.tk\n127.0.0.1 www.service.oauthg10.tk' >> /etc/hosts"
```

Entonces, deberíamos poder acceder a http://oauthg10.tk/, http://client.oauthg10.tk/ y http://service.oauthg10.tk/ desde nuestro navegador.

## Instalar la base de datos con datos de ejemplo

Para instalar la base de datos, basta con ejecutar lo siguiente:

```
$ /opt/bitnami/use_lampstack
$ php /opt/bitnami/hosted/oauth-gitt/scripts/service-install.php
```

**Nota importante:** Se ha supuesto que la contraseña por defecto de la aplicación es `bitnami1`. Si no es el caso, cree una rama (denominada, por ejemplo, `deployed`) con los cambios necesarios en el script `service-install.php` (es decir, cambie `bitnami1` por su contraseña).
Una vez instalados los datos de ejemplo puede volver a la rama `master`, ya que no se volverá a usar.

### Eliminar la base de datos con los datos de ejemplo

Quizás quieras actualizar los datos de ejemplo o eliminar cualquier rastro dejado por este proyecto.
Realizar esto es bastante sencillo:

```
$ /opt/bitnami/use_lampstack
$ php /opt/bitnami/hosted/oauth-gitt/scripts/service-install.php remove
```


