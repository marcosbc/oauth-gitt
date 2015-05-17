# OAuth 2.0: Implementación y seguridad. Un caso práctico.

## Componentes del equipo

- Marcos Bjorkelund: [marcosbc](https://github.com/marcosbc)
- Luís Casabuena Gómez: [Luiskitriski](https://github.com/Luiskitriski)
- Álvaro Páez Guerra: [AlvaroPaez](https://github.com/AlvaroPaez)
- Jaime Conde Ojeda: [jaimecoj](https://github.com/jaimecoj)
- Pablo González Sojo: [pablogs22](https://github.com/pablogs22)

## Cliente de OAuth en Android

También se ha realizado un proyecto de un cliente de OAuth 2.0, que hace uso de este servidor. El enlace es el siguiente: https://github.com/marcosbc/oauth-gitt-android/

## Enlaces

Los siguientes enlaces servirán para documentación:

- [Instalación](INSTALL.md)
- [RFC 6749](https://tools.ietf.org/html/rfc6749)
- [Wikipedia sobre OAuth](http://es.wikipedia.org/wiki/OAuth)

## Organización del proyecto

Habrán dos equipos:

- **Equipo de desarrollo**: Desarrollará los servicios a poner en marcha, junto con la temática y se asegurará de que el equipo de seguridad puede aprovechar este entorno.
- **Equipo de seguridad**: Desarrollará los tests de seguridad a llevar a cabo, preparará los entornos sobre los que realizar estos tests; finalmente, las llevará finalmente al cabo con las aplicaciones proporcionadas por el equipo de desarrollo. Compuesto por Jaime (coordinador), Pablo y Álvaro.

### Equipo de desarrollo

- **Marcos** (coordinador):
  * Coordinación entre los equipos de desarrollo y seguridad con Jaime.
  * Coordinación del equipo de desarrollo (Marcos y Pablo).
  * Lectura del RFC 6749 (especialmente la relativa al desarrollo).
  * Desarrollo del servicio (de vista a los clientes, con OAuth 2.0).
  * Supervisión del documento borrador, para dar paso al documento final.
- **Luís**:
  * Propuesta de idea sobre la que se va a basar el servicio. Tiene que ser **original**.
  * Desarrollo del servicio (de vista a los usuarios). Debe de ser **funcional**, intercompatible con la parte de OAuth.
  * Desarrollo del servicio cliente (el que se comunicará con OAuth).
  * Redacción del borrador del documento final, relacionado al conjunto de aplicaciones desarrolladas.

### Equipo de seguridad

- **Jaime** (coordinador):
  * Coordinación entre los equipos de desarrollo y seguridad con Marcos.
  * Coordinación del equipo de auditoría (Jaime, Pablo y Álvaro).
  * Lectura del RFC 6749 (especialmente la relativa a la seguridad).
  * Diseño del plan de auditoría:
    - Qué pruebas de seguridad realizar. Por ejemplo: Man in the middle, CSFR... Al menos 3 por persona, realizado mediante propuestas (con la ayuda de los demás componentes del equipo) y con la declaración de todos los componentes que serían necesarios en el entorno.
	- Poner en marcha los entornos necesarios (vía ramas de Git, hijas de *master*).
  * Desarrollo de algunos tests de seguridad.
  * Redacción del borrador del documento final de la parte de seguridad que ha realizado.
- **Pablo**:
  * Lectura del RFC 6749 (entero) y tomar notas.
  * Desarrollar tests de seguridad que decida Jaime.
  * Redacción del borrador del documento final de la parte de seguridad que ha realizado.
  * Redacción de la parte de funcionamiento del protocolo OAuth.
- **Álvaro**:
  * Lectura del RFC 6749 (entero) y tomar notas.
  * Desarrollo de los tests de seguridad que decida Jaime.
  * Redacción del borrador del documento final de la parte de seguridad que ha realizado.
  * Redacción de la parte de funcionamiento del protocolo OAuth.

#### Cómo realizar una propuesta de prueba de seguridad

Se va a realizar un test de seguridad por vulnerabilidad a la que se decida sacar juego (serán en gran parte por no tomar las medidas de seguridad adecuadas por parte del administrador de sistemas).
Para ello, será necesario **por cada prueba**:

1. Lectura del RFC sobre pistas de esta vulnerabilidad.
2. Desarrollo de la propuesta (Jaime, con la ayuda de Pablo y Álvaro), que deberá contener:
  * Título.
  * Descripción de la vulnerabilidad.
  * Descripción sobre el ataque a realizar.
  * Entorno necesario a preparar.
  * Cómo se llevará a cabo el ataque.
3. Implementación del ataque (por Pablo y Álvaro, con la ayuda de Jaime).

Por ello, como se observará, el desarrollo del grupo de seguridad será muy teórico (lo cual viene bien ya que el equipo de desarrollo no estará listo antes de que comience este equipo su trabajo).


