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

No os olvidéis de añadir los datos vuestros de Git para que cada uno sepa quién ha hecho un asentamiento:

```
$ git config --global user.name "TU NOMBRE COMPLETO o USUARIO_DE_GITHUB"
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

## Acceder a la aplicación servicio y servidores OAuth en servidor local

Para poder probarla en local, deberéis añadir las siguientes líneas a `/etc/hosts`.

```
127.0.0.1 oauthg10.tk
127.0.0.1 www.oauthg10.tk
127.0.0.1 api.oauthg10.tk
127.0.0.1 www.api.oauthg10.tk
127.0.0.1 oauth.oauthg10.tk
127.0.0.1 www.oauth.oauthg10.tk
```

Por ejemplo, se puede realizar de la siguiente manera en un paso:

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


