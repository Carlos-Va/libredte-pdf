LibreDTE: Ejemplo servicio web PDF desde XML
============================================

Este proyecto contiene un ejemplo completo y funcional para crear un servicio
web que recibe un XML de un DTE y entrega un PDF.

Inicialmente, se crea este proyecto para permitir a los usuarios una
personalización completa de los PDF en la aplicación web de LibreDTE.

El servicio web debe emular el [servicio web generar\_pdf de la aplicación oficial](https://doc.libredte.cl/api/#!/Documentos/post_utilidades_documentos_generar_pdf).
Por lo anterior, recibe los mismos datos de entrada y se esperan los mismos datos de salida.

Términos y condiciones de uso
-----------------------------

Código bajo [Licencia Pública General Affero de GNU (AGPL)](https://raw.githubusercontent.com/LibreDTE/libredte-pdf/master/COPYING)

Si deseas usar el ejemplo, lo correcto es:

1. Hacer fork del proyecto en [GitHub](https://github.com/LibreDTE/libredte-pdf)
2. Crear una *branch* para los cambios: git checkout -b nombre-branch
3. Modificar código: git commit -am 'Se agrega...'
4. Publicar cambios: git push origin nombre-branch

Puedes solicitar un *pull request* si crees que el cambio que estás
implementando debería estar en este ejemplo. En caso contrario, basta que lo
dejes publicado en tu repositorio público.

Formato por defecto
-------------------

El formato por defecto no pretende cumplir con todos los
[puntos que el SII exige](https://archivos.libredte.cl/sii/documentacion/manual_muestras_impresas.pdf)
en cuanto a la posición, textos o colores que debe incluir un PDF. Sólo es la
base sobre la cual se podrá construir el PDF final según los requerimientos de
cada contribuyente y cumpliendo con lo indicado por el SII.

El código que se incluye por defecto genera un PDF con la siguiente estructura:

![Formato PDF](https://raw.githubusercontent.com/LibreDTE/libredte-pdf/master/img/ejemplo.png)

Uso en aplicación web de LibreDTE
---------------------------------

1. Publicar servicio web en una página de acceso público, por ejemplo https://example.com/libredte-pdf
2. Configurar en la pestaña "API" de la configuración de la empresa, la URL: https://example.com/libredte-pdf/dte.php

Si se requiere autenticación para acceder al servicio, definir credenciales en
dte.php y agregarlas a la configuración de la API.
