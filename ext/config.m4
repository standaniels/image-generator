dnl image_generator PHP extension
PHP_ARG_WITH([image-generator],
  [for image-generator support],
  [AS_HELP_STRING([--with-image-generator],
    [Include image-generator support])])

if test "$PHP_IMAGE_GENERATOR" != "no"; then
  PHP_ADD_EXTENSION_DEP(image_generator, gd, true)
  PHP_ADD_EXTENSION_DEP(image_generator, exif, true)

  PHP_NEW_EXTENSION(image_generator,
    image_generator.c \
    canvas.c \
    image_obj.c \
    shape.c,
    $ext_shared,,
    -DZEND_ENABLE_STATIC_TSRMLS_CACHE=1 -std=c99 -Wall)

  PHP_INSTALL_HEADERS([ext/image_generator], [php_image_generator.h])
fi
