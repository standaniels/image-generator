/*
 * canvas.c – Canvas class
 *
 * Namespace : StanDaniels\ImageGenerator
 * Class     : Canvas
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <math.h>
#include "php.h"
#include "php_image_generator.h"

static zend_object_handlers igext_canvas_handlers;

/* ── Object lifecycle ────────────────────────────────────────────────────── */

static zend_object *igext_canvas_create_object(zend_class_entry *ce)
{
    igext_canvas_object *obj = zend_object_alloc(sizeof(igext_canvas_object), ce);
    zend_object_std_init(&obj->std, ce);
    object_properties_init(&obj->std, ce);
    ZVAL_UNDEF(&obj->gd_image);
    obj->std.handlers = &igext_canvas_handlers;
    return &obj->std;
}

static void igext_canvas_free_object(zend_object *obj)
{
    igext_canvas_object *canvas = igext_canvas_from_obj(obj);
    zval_ptr_dtor(&canvas->gd_image);
    zend_object_std_dtor(obj);
}

/* ── Argument info ───────────────────────────────────────────────────────── */

ZEND_BEGIN_ARG_INFO_EX(arginfo_Canvas_construct, 0, 0, 2)
    ZEND_ARG_TYPE_INFO(0, width,  IS_LONG,   0)
    ZEND_ARG_TYPE_INFO(0, height, IS_LONG,   0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, antiAliasing, IS_DOUBLE, 0, "1")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_Canvas_create, 0, 2,
    StanDaniels\\ImageGenerator\\Canvas, 0)
    ZEND_ARG_TYPE_INFO(0, width,  IS_LONG,   0)
    ZEND_ARG_TYPE_INFO(0, height, IS_LONG,   0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, antiAliasing, IS_DOUBLE, 0, "1")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_Canvas_background, 0, 1,
    StanDaniels\\ImageGenerator\\Canvas, 0)
    ZEND_ARG_OBJ_INFO(0, color, StanDaniels\\ImageGenerator\\Color, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_Canvas_generate, 0, 0,
    StanDaniels\\ImageGenerator\\Image, 0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, path, IS_STRING, 1, "null")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_Canvas_applyAntiAliasing, 0, 0,
    GdImage, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_Canvas_getDimension, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_Canvas_getResource, 0, 0,
    GdImage, 0)
ZEND_END_ARG_INFO()

/* ── Canvas::__construct ─────────────────────────────────────────────────── */

PHP_METHOD(Canvas, __construct)
{
    zend_long width, height;
    double anti_aliasing = 1.0;

    ZEND_PARSE_PARAMETERS_START(2, 3)
        Z_PARAM_LONG(width)
        Z_PARAM_LONG(height)
        Z_PARAM_OPTIONAL
        Z_PARAM_DOUBLE(anti_aliasing)
    ZEND_PARSE_PARAMETERS_END();

    if (width <= 0) {
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "Width must be greater than 0, %ld given", (long)width);
        RETURN_THROWS();
    }
    if (height <= 0) {
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "Height must be greater than 0, %ld given", (long)height);
        RETURN_THROWS();
    }

    igext_canvas_object *obj = Z_IGEXT_CANVAS_P(ZEND_THIS);
    obj->width        = width;
    obj->height       = height;
    obj->anti_aliasing = anti_aliasing;
    obj->actual_width  = (anti_aliasing > 1.0) ? (zend_long)(width  * anti_aliasing) : width;
    obj->actual_height = (anti_aliasing > 1.0) ? (zend_long)(height * anti_aliasing) : height;

    zval args[2];
    ZVAL_LONG(&args[0], obj->actual_width);
    ZVAL_LONG(&args[1], obj->actual_height);

    if (igext_call_function("imagecreatetruecolor", &obj->gd_image, 2, args) == FAILURE
            || Z_TYPE(obj->gd_image) != IS_OBJECT) {
        zend_throw_exception(NULL, "Failed to create GD image canvas", 0);
        RETURN_THROWS();
    }
}

/* ── Canvas::create (static factory) ────────────────────────────────────── */

PHP_METHOD(Canvas, create)
{
    zend_long width, height;
    double anti_aliasing = 1.0;

    ZEND_PARSE_PARAMETERS_START(2, 3)
        Z_PARAM_LONG(width)
        Z_PARAM_LONG(height)
        Z_PARAM_OPTIONAL
        Z_PARAM_DOUBLE(anti_aliasing)
    ZEND_PARSE_PARAMETERS_END();

    zend_class_entry *called_ce = zend_get_called_scope(execute_data);

    object_init_ex(return_value, called_ce);

    zval args[3], ctor_rv;
    ZVAL_LONG(&args[0], width);
    ZVAL_LONG(&args[1], height);
    ZVAL_DOUBLE(&args[2], anti_aliasing);

    zval z_method;
    ZVAL_STRING(&z_method, "__construct");
    call_user_function(NULL, return_value, &z_method, &ctor_rv, 3, args);
    zval_ptr_dtor(&z_method);
    zval_ptr_dtor(&ctor_rv);
}

/* ── Canvas::background ──────────────────────────────────────────────────── */

PHP_METHOD(Canvas, background)
{
    zend_object *color_obj;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_OBJ_OF_CLASS(color_obj, igext_color_ce)
    ZEND_PARSE_PARAMETERS_END();

    igext_canvas_object *canvas = Z_IGEXT_CANVAS_P(ZEND_THIS);

    /* Allocate the color: $color->allocate($this) */
    zval color_id, alloc_method, z_this_canvas;
    ZVAL_STRING(&alloc_method, "allocate");
    ZVAL_OBJ(&z_this_canvas, Z_OBJ_P(ZEND_THIS));

    zval z_color;
    ZVAL_OBJ(&z_color, color_obj);
    call_user_function(NULL, &z_color, &alloc_method, &color_id, 1, &z_this_canvas);
    zval_ptr_dtor(&alloc_method);

    if (EG(exception) || Z_TYPE(color_id) == IS_FALSE) {
        zval_ptr_dtor(&color_id);
        RETURN_THROWS();
    }

    /* imagefilledrectangle($gd, 0, 0, w-1, h-1, $color_id) */
    zval rect_args[6], rect_rv;
    ZVAL_COPY_VALUE(&rect_args[0], &canvas->gd_image);
    ZVAL_LONG(&rect_args[1], 0);
    ZVAL_LONG(&rect_args[2], 0);
    ZVAL_LONG(&rect_args[3], canvas->actual_width  - 1);
    ZVAL_LONG(&rect_args[4], canvas->actual_height - 1);
    ZVAL_COPY_VALUE(&rect_args[5], &color_id);

    igext_call_function("imagefilledrectangle", &rect_rv, 6, rect_args);
    zval_ptr_dtor(&color_id);
    zval_ptr_dtor(&rect_rv);

    RETVAL_ZVAL(ZEND_THIS, 1, 0);
}

/* ── Canvas::generate ────────────────────────────────────────────────────── */

PHP_METHOD(Canvas, generate)
{
    zend_string *path = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_STR_OR_NULL(path)
    ZEND_PARSE_PARAMETERS_END();

    /* Delegate to Image::create($this, $path) */
    zval args[2], z_method, image_ce_zv, create_rv;
    ZVAL_OBJ(&args[0], Z_OBJ_P(ZEND_THIS));
    if (path) {
        ZVAL_STR(&args[1], path);
    } else {
        ZVAL_NULL(&args[1]);
    }

    zend_class_entry *img_ce = igext_image_ce;
    ZVAL_STRING(&z_method, "create");
    zval z_class;
    ZVAL_OBJ(&z_class, &img_ce->default_object_handlers);

    /* Use call_user_function on the class (static call) */
    zval fn;
    array_init(&fn);
    add_next_index_string(&fn, "StanDaniels\\ImageGenerator\\Image");
    add_next_index_string(&fn, "create");
    ZVAL_UNDEF(&create_rv);
    call_user_function(CG(function_table), NULL, &fn, &create_rv, 2, args);
    zval_ptr_dtor(&fn);
    zval_ptr_dtor(&z_method);

    RETVAL_ZVAL(&create_rv, 0, 1);
}

/* ── Canvas::applyAntiAliasing ───────────────────────────────────────────── */

PHP_METHOD(Canvas, applyAntiAliasing)
{
    ZEND_PARSE_PARAMETERS_NONE();

    igext_canvas_object *canvas = Z_IGEXT_CANVAS_P(ZEND_THIS);

    if (canvas->anti_aliasing <= 1.0) {
        RETURN_ZVAL(&canvas->gd_image, 1, 0);
        return;
    }

    /* Create destination at logical (smaller) size */
    zval dest, create_args[2];
    ZVAL_LONG(&create_args[0], canvas->width);
    ZVAL_LONG(&create_args[1], canvas->height);
    if (igext_call_function("imagecreatetruecolor", &dest, 2, create_args) == FAILURE
            || Z_TYPE(dest) != IS_OBJECT) {
        zend_throw_exception(NULL, "Failed to create destination image for anti-aliasing", 0);
        RETURN_THROWS();
    }

    /* imagecopyresampled(dst, src, 0,0,0,0, dst_w, dst_h, src_w, src_h) */
    zval resample_args[10], resample_rv;
    ZVAL_COPY_VALUE(&resample_args[0], &dest);
    ZVAL_COPY_VALUE(&resample_args[1], &canvas->gd_image);
    ZVAL_LONG(&resample_args[2], 0);
    ZVAL_LONG(&resample_args[3], 0);
    ZVAL_LONG(&resample_args[4], 0);
    ZVAL_LONG(&resample_args[5], 0);
    ZVAL_LONG(&resample_args[6], canvas->width);
    ZVAL_LONG(&resample_args[7], canvas->height);
    ZVAL_LONG(&resample_args[8], canvas->actual_width);
    ZVAL_LONG(&resample_args[9], canvas->actual_height);

    igext_call_function("imagecopyresampled", &resample_rv, 10, resample_args);
    zval_ptr_dtor(&resample_rv);

    RETVAL_ZVAL(&dest, 0, 1);
}

/* ── Canvas::getWidth / getHeight / getResource ──────────────────────────── */

PHP_METHOD(Canvas, getWidth)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_CANVAS_P(ZEND_THIS)->width);
}

PHP_METHOD(Canvas, getHeight)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_CANVAS_P(ZEND_THIS)->height);
}

PHP_METHOD(Canvas, getResource)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_ZVAL(&Z_IGEXT_CANVAS_P(ZEND_THIS)->gd_image, 1, 0);
}

/* ── Method table ────────────────────────────────────────────────────────── */

static const zend_function_entry canvas_methods[] = {
    PHP_ME(Canvas, __construct,        arginfo_Canvas_construct,         ZEND_ACC_PUBLIC)
    PHP_ME(Canvas, create,             arginfo_Canvas_create,            ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Canvas, background,         arginfo_Canvas_background,        ZEND_ACC_PUBLIC)
    PHP_ME(Canvas, generate,           arginfo_Canvas_generate,          ZEND_ACC_PUBLIC)
    PHP_ME(Canvas, applyAntiAliasing,  arginfo_Canvas_applyAntiAliasing, ZEND_ACC_PUBLIC)
    PHP_ME(Canvas, getWidth,           arginfo_Canvas_getDimension,      ZEND_ACC_PUBLIC)
    PHP_ME(Canvas, getHeight,          arginfo_Canvas_getDimension,      ZEND_ACC_PUBLIC)
    PHP_ME(Canvas, getResource,        arginfo_Canvas_getResource,       ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* ── Registration ────────────────────────────────────────────────────────── */

void igext_register_canvas_class(void)
{
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, "StanDaniels\\ImageGenerator", "Canvas", canvas_methods);
    igext_canvas_ce = zend_register_internal_class(&ce);
    igext_canvas_ce->create_object = igext_canvas_create_object;

    memcpy(&igext_canvas_handlers, zend_get_std_object_handlers(),
           sizeof(zend_object_handlers));
    igext_canvas_handlers.offset   = XtOffsetOf(igext_canvas_object, std);
    igext_canvas_handlers.free_obj = igext_canvas_free_object;
}
