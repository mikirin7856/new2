=== Plugin Name ===
Contributors: imacreste
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=P9DG5TCRGDYAW&lc=ES&item_name=imacreste&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHostedGuest
Tags: prestashop, products, productos
Requires at least: 4.6
Tested up to:  6.8.1
Stable tag: 2.0.19
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Conecta Prestashop con Wordpress para extraer los productos y mostrarlos en los artículos.

== Description ==

Es bastante habitual encontrar con que nuestros clientes tienen una tienda online con Prestashop y un blog al que dedican tiempo en posicionar.

Después de varios proyectos en los que se necesitaba mostrar productos Prestashop en el blog Wordpress, he creado este plugin con el que podemos mostrar productos de una tienda online Prestashop. 

¡Y es 100% gratuita!

Este plugin permite comunicarnos con nuestra tienda prestashop de 2 formas diferente:

1. Mediante el uso del WebService integrado en prestashop. (Recomendado)
2. Añadiendo información sobre la base de datos. (Es la primera versión del plugin)

En Prestashop hay muchas combinaciones de producto, de precios, de ofertas, etc. Con lo que es probable que no funcione bien para todas las situaciones. Si nos lo comentas podremos evaluar mejorarlo.

El plugin permite indicar el idioma en el que queremos mostrar los productos y si queremos que los enlaces sean noFollow.

Si no consigues los resultados esperados, no te preocupes, desinstala el plugin, quita los shortcodes (si es que has dejado alguno) y no habrá pasado nada ;-).

Para el funcionamiento no es necesario que el blog este en el mismo servidor que la tienda, de echo podemos colocar productos Prestashop en cualquier blog Wordpress, siendo realmente sencillo configurarlo. En determinados servidores el firewall no permite conectarse con servidores externos, solo tiene que solicitar al hosting que se lo permita, ya que se considera una conexión de confianza y segura.  

En cuanto a los datos de configuración, se almacenan encriptados.

Pruebas realizadas con éxito para: 
prestashop 1.6.1.7
prestashop 1.7.5.2

== Installation ==

Es muy fácil de instalar.

1. Se descarga
2. Se sube desde plugins
3. Desde el listado de plugins se activa
4. Nos sale un enlace hacia la configuración
5. Rellenamos los datos del webservce o de la base de dato y, probamos que la conexión con la tienda se hace correctamente.
6. Introducimos productos en nuestras entradas, páginas o Widgets
[imacPrestashop_productos_ws]
[imacPrestashop_productos_ws productos="1,2,3,4,5,6" idioma="1"]

Si tienes dudas puedes contactar conmigo en: https://imacreste.com

== Frequently Asked Questions ==

= ¿Puedo conectarme con mi tienda online Prestashop si está en un hosting diferente al de Wordpress? =

Si. Solo necesitas rellenar los ajustes del plugin.
En determinados hostings es necesario solicitar que quieres conectarte con otro servidor.

= ¿Es multiidioma? =

Si. En cada shortcode y Widget se puede definir el idioma.

== Screenshots ==

1. Pantallazo de la configuración
2. Pantallazo de la Ayuda
3. Pantallazo de ejemplo

== Changelog ==

= 2.0.18 =
UPDATE WP 6.8.1

= 2.0.17 =
UPDATE WP 5.5

= 2.0.16 =
Posibilidad de añadir Urls sin IDs. UPDATE WP 5.4.2

= 2.0.15 =
Posibilidad de añadir Urls sin IDs

= 2.0.14 =
IMPORTANTE: Habilitar en webservice de Prestashop -> tax_rules. Mejora en el cálculo de precios con varias regals de impuestos.

= 2.0.13 =
Actualización a WordPress 5.4

= 2.0.12 =
Actualización a WordPress 5.3.1

= 2.0.11 =
Actualización a WordPress 5.3

= 2.0.10 =
Se comprueban fecha desde - hasta para mostrar o no las ofertas

= 2.0.9 =
Posibilidad de cambiar el símbolo del € por otro

= 2.0.8 =
Posibilidad de ocultar las ofertas

= 2.0.7 =
Widget con categorías

= 2.0.6 =
URLs multi idioma

= 2.0.5 =
URLS con categoría base

= 2.0.4 =
Añadimos ayuda

= 2.0.3 =
Añadimos shortcode de categorias

= 2.0.2 =
Añadimos widget WebService

= 2.0.1 =
Desarrollo de la conexión a Prestashop mediante su WebService.

= 1.0.14 =
Actualización pantallazos

= 1.0.13 =
Se ha añadido la posibilidad de ocultar las ofertas, de forma que si usas alguna que de momento no esta contemplada en el plugin puedes indicar que solo se muestre el precio por defecto.

= 1.0.12 =
Mejora en la seguridad, encriptación con key variable
TRAS LA ACTUALIZACIÓN, SE REQUIERE VOLVER A GUARDAR LOS AJUSTES

= 1.0.11 =
Mejoras en el calculo de las ofertas
He añadido la opción de poner los enlaces con nofollow

= 1.0.10 =
Actualización de la encriptación de la información.
Ahora se usa openSSL
TRAS LA ACTUALIZACIÓN, SE REQUIERE VOLVER A GUARDAR LOS AJUSTES

= 1.0.9 =
Corrección función obsoleta

= 1.0.8 =
Versión estable, con ayudas

= 1.0.7 =
* Cambios en nombres para evitar choques con otros plugins

= 1.0.6 =
* Mejoras en los enlaces a tiendas multi-idioma

= 1.0.5 =
* Mejoras en la carga de imágenes

= 1.0.4 =
* Mejoras en readme.txt

= 1.0.3 =
* Mejoras en la ayuda e imágenes de presentación y ayuda

= 1.0.2 =
* Mejoras en la ayuda

= 1.0.1 =
* Lanzamiento plugin

== Upgrade Notice ==

= 2.0.17 =
UPDATE WP 5.5

= 2.0.16 =
Posibilidad de añadir Urls sin IDs. UPDATE WP 5.4.2

= 2.0.15 =
Posibilidad de añadir Urls sin IDs

= 2.0.14 =
IMPORTANTE: Habilitar en webservice de Prestashop -> tax_rules. Mejora en el cálculo de precios con varias regals de impuestos.

= 2.0.13 =
Actualización a WordPress 5.4

= 2.0.12 =
Actualización a WordPress 5.3.1

= 2.0.11 =
Actualización a WordPress 5.3

= 2.0.10 =
Se comprueban fecha desde - hasta para mostrar o no las ofertas

= 2.0.9 =
Posibilidad de cambiar el símbolo del € por otro

= 2.0.8 =
Posibilidad de ocultar las ofertas

= 2.0.7 =
Widget con categorías

= 2.0.6 =
URLs multi idioma

= 2.0.5 =
URLS con categoría base

= 2.0.4 =
Añadimos ayuda

= 2.0.3 =
Añadimos shortcode de categorias

= 2.0.2 =
Añadimos widget WebService

= 2.0.1 =
Desarrollo de la conexión a Prestashop mediante su WebService.

= 1.0.14 =
Actualización pantallazos

= 1.0.13 =
Se ha añadido la posibilidad de ocultar las ofertas, de forma que si usas alguna que de momento no esta contemplada en el plugin puedes indicar que solo se muestre el precio por defecto.

= 1.0.12 =
Mejora en la seguridad, encriptación con key variable
TRAS LA ACTUALIZACIÓN, SE REQUIERE VOLVER A GUARDAR LOS AJUSTES

= 1.0.11 =
Mejoras en el calculo de las ofertas
He añadido la opción de poner los enlaces con nofollow

= 1.0.10 =
Actualización de la encriptación de la información.
Ahora se usa openSSL
TRAS LA ACTUALIZACIÓN, SE REQUIERE VOLVER A GUARDAR LOS AJUSTES

= 1.0.9 =
Corrección función obsoleta

= 1.0.8 =
Versión estable, con ayudas

= 1.0.7 =
* Cambios en nombres para evitar choques con otros plugins

= 1.0.6 =
* Mejoras en los enlaces a tiendas multi-idioma

= 1.0.5 =
* Mejoras en la carga de imágenes

= 1.0.4 =
* Mejoras en readme.txt

= 1.0.3 =
* Mejoras en la ayuda e imágenes de presentación y ayuda

= 1.0.2 =
Mejoras en la ayuda

= 1.0.1 =
lanzamiento del plugin