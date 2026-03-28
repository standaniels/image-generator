/*
 * image_obj.c – Image class (extends SplFileInfo)
 *
 * Namespace : StanDaniels\ImageGenerator
 * Class     : Image
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "zend_smart_str.h"
#include "ext/spl/spl_directory.h"
#include "ext/spl/spl_exceptions.h"
#include "php_image_generator.h"

/* ── Argument info ───────────────────────────────────────────────────────── */

ZEND_BEGIN_ARG_INFO_EX(arginfo_Image_construct, 0, 0, 1)
    ZEND_ARG_TYPE_INFO(0, path, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_Image_create, 0, 1,
    StanDaniels\\ImageGenerator\\Image, 0)
    ZEND_ARG_OBJ_INFO(0, canvas, StanDaniels\\ImageGenerator\\Canvas, 0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, path, IS_STRING, 1, "null")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_Image_dataUri, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_Image_getMimeType, 0, 0, IS_STRING, 0)
ZEND_END_ARG_INFO()

/* ── Image::__construct ──────────────────────────────────────────────────── */

PHP_METHOD(Image, __construct)
{
    zend_string *path;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(path)
    ZEND_PARSE_PARAMETERS_END();

    /* Validate the file exists */
    if (VCWD_ACCESS(ZSTR_VAL(path), F_OK) != 0) {
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "Image file does not exist: %s", ZSTR_VAL(path));
        RETURN_THROWS();
    }

    /* Call SplFileInfo::__construct($path) */
    zend_class_entry *parent_ce = igext_image_ce->parent;
    if (parent_ce && parent_ce->constructor) {
        zval z_path, ctor_rv;
        ZVAL_STR(&z_path, path);
        zend_call_method_with_1_params(
            Z_OBJ_P(ZEND_THIS), parent_ce,
            &parent_ce->constructor, "__construct",
            &ctor_rv, &z_path);
        zval_ptr_dtor(&ctor_rv);
        if (EG(exception)) RETURN_THROWS();
    }

    /* exif_imagetype($path) to determine image type */
    zval args[1], type_rv;
    ZVAL_STR(&args[0], path);
    if (igext_call_function("exif_imagetype", &type_rv, 1, args) == FAILURE
            || Z_TYPE(type_rv) == IS_FALSE) {
        zval_ptr_dtor(&type_rv);
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "Image type could not be determined for: %s", ZSTR_VAL(path));
        RETURN_THROWS();
    }

    /* image_type_to_mime_type($type) */
    zval mime_rv;
    if (igext_call_function("image_type_to_mime_type", &mime_rv, 1, &type_rv) == FAILURE) {
        zval_ptr_dtor(&type_rv);
        zend_throw_exception(NULL, "Failed to determine MIME type", 0);
        RETURN_THROWS();
    }
    zval_ptr_dtor(&type_rv);

    /* Store mime_type as a PHP property */
    zend_update_property(igext_image_ce, Z_OBJ_P(ZEND_THIS),
                         "mime_type", sizeof("mime_type") - 1, &mime_rv);
    zval_ptr_dtor(&mime_rv);
}

/* ── Image::create (static factory) ─────────────────────────────────────── */

PHP_METHOD(Image, create)
{
    zend_object *canvas_obj;
    zend_string *path = NULL;

    ZEND_PARSE_PARAMETERS_START(1, 2)
        Z_PARAM_OBJ_OF_CLASS(canvas_obj, igext_canvas_ce)
        Z_PARAM_OPTIONAL
        Z_PARAM_STR_OR_NULL(path)
    ZEND_PARSE_PARAMETERS_END();

    /* Get a GdImage via Canvas::applyAntiAliasing() */
    zval z_canvas, z_method, gd_image;
    ZVAL_OBJ(&z_canvas, canvas_obj);
    ZVAL_STRING(&z_method, "applyAntiAliasing");
    call_user_function(NULL, &z_canvas, &z_method, &gd_image, 0, NULL);
    zval_ptr_dtor(&z_method);

    if (EG(exception) || Z_TYPE(gd_image) != IS_OBJECT) {
        zval_ptr_dtor(&gd_image);
        RETURN_THROWS();
    }

    /* Resolve file path */
    zend_string *file_path;
    if (path == NULL) {
        /* tempnam(sys_get_temp_dir(), 'img') */
        zval tmp_dir, tempnam_args[2], tempnam_rv;
        igext_call_function("sys_get_temp_dir", &tmp_dir, 0, NULL);
        ZVAL_COPY_VALUE(&tempnam_args[0], &tmp_dir);
        ZVAL_STRING(&tempnam_args[1], "img");
        igext_call_function("tempnam", &tempnam_rv, 2, tempnam_args);
        zval_ptr_dtor(&tmp_dir);
        zval_ptr_dtor(&tempnam_args[1]);
        if (Z_TYPE(tempnam_rv) != IS_STRING) {
            zval_ptr_dtor(&tempnam_rv);
            zval_ptr_dtor(&gd_image);
            zend_throw_exception(NULL, "Failed to create temporary file", 0);
            RETURN_THROWS();
        }
        file_path = zend_string_copy(Z_STR(tempnam_rv));
        zval_ptr_dtor(&tempnam_rv);
    } else {
        file_path = zend_string_copy(path);
    }

    /* imagepng($gd_image, $file_path) */
    zval png_args[2], png_rv;
    ZVAL_COPY_VALUE(&png_args[0], &gd_image);
    ZVAL_STR(&png_args[1], file_path);
    igext_call_function("imagepng", &png_rv, 2, png_args);
    zval_ptr_dtor(&png_rv);

    /* imagedestroy($gd_image) */
    zval destroy_rv;
    igext_call_function("imagedestroy", &destroy_rv, 1, &gd_image);
    zval_ptr_dtor(&destroy_rv);
    zval_ptr_dtor(&gd_image);

    /* return new Image($file_path) */
    object_init_ex(return_value, igext_image_ce);
    zval z_fp, ctor_rv;
    ZVAL_STR(&z_fp, file_path);
    zend_call_method_with_1_params(
        Z_OBJ_P(return_value), igext_image_ce,
        &igext_image_ce->constructor, "__construct",
        &ctor_rv, &z_fp);
    zval_ptr_dtor(&ctor_rv);
    zend_string_release(file_path);

    if (EG(exception)) {
        zval_ptr_dtor(return_value);
        RETURN_THROWS();
    }
}

/* ── Image::dataUri ──────────────────────────────────────────────────────── */

PHP_METHOD(Image, dataUri)
{
    ZEND_PARSE_PARAMETERS_NONE();

    /* getMimeType() */
    zval z_this, z_method, mime_rv;
    ZVAL_OBJ(&z_this, Z_OBJ_P(ZEND_THIS));
    ZVAL_STRING(&z_method, "getMimeType");
    call_user_function(NULL, &z_this, &z_method, &mime_rv, 0, NULL);
    zval_ptr_dtor(&z_method);

    /* getPathname() – from SplFileInfo */
    ZVAL_STRING(&z_method, "getPathname");
    zval path_rv;
    call_user_function(NULL, &z_this, &z_method, &path_rv, 0, NULL);
    zval_ptr_dtor(&z_method);

    if (Z_TYPE(path_rv) != IS_STRING) {
        zval_ptr_dtor(&mime_rv);
        zval_ptr_dtor(&path_rv);
        zend_throw_exception(NULL, "Failed to get image path", 0);
        RETURN_THROWS();
    }

    /* file_get_contents($path) */
    zval fgc_rv;
    igext_call_function("file_get_contents", &fgc_rv, 1, &path_rv);
    zval_ptr_dtor(&path_rv);

    if (Z_TYPE(fgc_rv) != IS_STRING) {
        zval_ptr_dtor(&fgc_rv);
        zval_ptr_dtor(&mime_rv);
        zend_throw_exception(NULL, "Failed to read image contents", 0);
        RETURN_THROWS();
    }

    /* base64_encode($contents) */
    zval b64_rv;
    igext_call_function("base64_encode", &b64_rv, 1, &fgc_rv);
    zval_ptr_dtor(&fgc_rv);

    /* "data:{mime};base64,{b64}" */
    smart_str uri = {0};
    smart_str_appends(&uri, "data:");
    smart_str_append(&uri, Z_STR(mime_rv));
    smart_str_appends(&uri, ";base64,");
    smart_str_append(&uri, Z_STR(b64_rv));
    smart_str_0(&uri);

    zval_ptr_dtor(&mime_rv);
    zval_ptr_dtor(&b64_rv);

    RETURN_STR(uri.s);
}

/* ── Image::getMimeType ──────────────────────────────────────────────────── */

PHP_METHOD(Image, getMimeType)
{
    ZEND_PARSE_PARAMETERS_NONE();
    zval *prop = zend_read_property(igext_image_ce, Z_OBJ_P(ZEND_THIS),
                                    "mime_type", sizeof("mime_type") - 1, 1, NULL);
    RETURN_STR(zval_get_string(prop));
}

/* ── Method table ────────────────────────────────────────────────────────── */

static const zend_function_entry image_methods[] = {
    PHP_ME(Image, __construct, arginfo_Image_construct,  ZEND_ACC_PUBLIC)
    PHP_ME(Image, create,      arginfo_Image_create,     ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Image, dataUri,     arginfo_Image_dataUri,    ZEND_ACC_PUBLIC)
    PHP_ME(Image, getMimeType, arginfo_Image_getMimeType, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* ── Registration ────────────────────────────────────────────────────────── */

void igext_register_image_class(void)
{
    /* Extend SplFileInfo */
    zend_class_entry *spl_fileinfo_ce = spl_ce_SplFileInfo;
    if (!spl_fileinfo_ce) {
        /* Fall back to lookup if the exported symbol isn't available */
        spl_fileinfo_ce = zend_hash_str_find_ptr(
            CG(class_table), "splfileinfo", sizeof("splfileinfo") - 1);
    }

    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, "StanDaniels\\ImageGenerator", "Image", image_methods);
    igext_image_ce = zend_register_internal_class_ex(&ce, spl_fileinfo_ce);

    /* Protected mime_type property */
    zend_declare_property_null(igext_image_ce,
                               "mime_type", sizeof("mime_type") - 1,
                               ZEND_ACC_PROTECTED);
}
