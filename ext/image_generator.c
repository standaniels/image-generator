/*
 * image_generator.c – Module entry point + Color class
 *
 * Namespace : StanDaniels\ImageGenerator
 * Class     : Color
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "ext/random/php_random.h"
#include "php_image_generator.h"

/* ── Class entries (defined once, referenced by all translation units) ──── */
zend_class_entry *igext_color_ce;
zend_class_entry *igext_canvas_ce;
zend_class_entry *igext_image_ce;
zend_class_entry *igext_shape_ce;
zend_class_entry *igext_circle_ce;
zend_class_entry *igext_polygon_ce;

/* ── Utility: call a PHP function by name ─────────────────────────────────── */
zend_result igext_call_function(const char *name, zval *retval, uint32_t argc, zval *argv)
{
    zval fn;
    ZVAL_STRING(&fn, name);
    ZVAL_UNDEF(retval);
    zend_result r = call_user_function(CG(function_table), NULL, &fn, retval, argc, argv);
    zval_ptr_dtor(&fn);
    return r;
}

/* ── Color object handlers ───────────────────────────────────────────────── */

static zend_object_handlers igext_color_handlers;

static zend_object *igext_color_create_object(zend_class_entry *ce)
{
    igext_color_object *obj = zend_object_alloc(sizeof(igext_color_object), ce);
    zend_object_std_init(&obj->std, ce);
    object_properties_init(&obj->std, ce);
    obj->std.handlers = &igext_color_handlers;
    return &obj->std;
}

static void igext_color_free_object(zend_object *obj)
{
    zend_object_std_dtor(obj);
}

/* ── Validate a color component (0-255) ─────────────────────────────────── */
static bool igext_validate_color_component(zend_long val, const char *name)
{
    if (val < 0 || val > 255) {
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "%s must be between 0 and 255, %ld given", name, (long)val);
        return false;
    }
    return true;
}

/* ── Argument info ───────────────────────────────────────────────────────── */

ZEND_BEGIN_ARG_INFO(arginfo_Color_construct, 0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, red,   IS_LONG,   0, "0")
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, green, IS_LONG,   0, "0")
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, blue,  IS_LONG,   0, "0")
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, alpha, IS_DOUBLE, 0, "0")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_Color_random, 0, 0,
    StanDaniels\\ImageGenerator\\Color, 0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, alpha, IS_DOUBLE, 1, "null")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_Color_allocate, 0, 1, IS_LONG, 0)
    ZEND_ARG_OBJ_INFO(0, canvas, StanDaniels\\ImageGenerator\\Canvas, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_Color_getInt, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_Color_setComponent, 0, 1, IS_VOID, 0)
    ZEND_ARG_TYPE_INFO(0, value, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_Color_setAlpha, 0, 1, IS_VOID, 0)
    ZEND_ARG_TYPE_INFO(0, alpha, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

/* ── Color methods ───────────────────────────────────────────────────────── */

PHP_METHOD(Color, __construct)
{
    zend_long red = 0, green = 0, blue = 0;
    double alpha = 0.0;

    ZEND_PARSE_PARAMETERS_START(0, 4)
        Z_PARAM_OPTIONAL
        Z_PARAM_LONG(red)
        Z_PARAM_LONG(green)
        Z_PARAM_LONG(blue)
        Z_PARAM_DOUBLE(alpha)
    ZEND_PARSE_PARAMETERS_END();

    if (!igext_validate_color_component(red,   "Red"))   RETURN_THROWS();
    if (!igext_validate_color_component(green, "Green")) RETURN_THROWS();
    if (!igext_validate_color_component(blue,  "Blue"))  RETURN_THROWS();

    if (alpha < 0.0 || alpha > 1.0) {
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "Alpha must be between 0 and 1, %f given", alpha);
        RETURN_THROWS();
    }

    igext_color_object *obj = Z_IGEXT_COLOR_P(ZEND_THIS);
    obj->red   = red;
    obj->green = green;
    obj->blue  = blue;
    obj->alpha = (zend_long)(alpha * 127.0);
}

PHP_METHOD(Color, random)
{
    zval *z_alpha = NULL;

    ZEND_PARSE_PARAMETERS_START(0, 1)
        Z_PARAM_OPTIONAL
        Z_PARAM_ZVAL_OR_NULL(z_alpha)
    ZEND_PARSE_PARAMETERS_END();

    double alpha;
    if (z_alpha == NULL || Z_TYPE_P(z_alpha) == IS_NULL) {
        alpha = (double)php_mt_rand_range(0, 100) / 100.0;
    } else {
        alpha = zval_get_double(z_alpha);
    }

    object_init_ex(return_value, igext_color_ce);
    igext_color_object *obj = Z_IGEXT_COLOR_P(return_value);
    obj->red   = php_mt_rand_range(0, 255);
    obj->green = php_mt_rand_range(0, 255);
    obj->blue  = php_mt_rand_range(0, 255);
    obj->alpha = (zend_long)(alpha * 127.0);
}

PHP_METHOD(Color, allocate)
{
    zend_object *canvas_obj;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_OBJ_OF_CLASS(canvas_obj, igext_canvas_ce)
    ZEND_PARSE_PARAMETERS_END();

    igext_canvas_object *canvas = igext_canvas_from_obj(canvas_obj);
    igext_color_object  *color  = Z_IGEXT_COLOR_P(ZEND_THIS);

    zval args[5], retval;
    ZVAL_COPY_VALUE(&args[0], &canvas->gd_image);
    ZVAL_LONG(&args[1], color->red);
    ZVAL_LONG(&args[2], color->green);
    ZVAL_LONG(&args[3], color->blue);
    ZVAL_LONG(&args[4], color->alpha);

    if (igext_call_function("imagecolorallocatealpha", &retval, 5, args) == FAILURE
            || Z_TYPE(retval) == IS_FALSE) {
        zval_ptr_dtor(&retval);
        zend_throw_exception(NULL, "Failed to allocate color", 0);
        RETURN_THROWS();
    }

    RETURN_LONG(Z_LVAL(retval));
}

PHP_METHOD(Color, getRed)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_COLOR_P(ZEND_THIS)->red);
}

PHP_METHOD(Color, getGreen)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_COLOR_P(ZEND_THIS)->green);
}

PHP_METHOD(Color, getBlue)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_COLOR_P(ZEND_THIS)->blue);
}

PHP_METHOD(Color, getAlpha)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_COLOR_P(ZEND_THIS)->alpha);
}

PHP_METHOD(Color, setRed)
{
    zend_long value;
    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(value)
    ZEND_PARSE_PARAMETERS_END();

    if (!igext_validate_color_component(value, "Red")) RETURN_THROWS();
    Z_IGEXT_COLOR_P(ZEND_THIS)->red = value;
}

PHP_METHOD(Color, setGreen)
{
    zend_long value;
    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(value)
    ZEND_PARSE_PARAMETERS_END();

    if (!igext_validate_color_component(value, "Green")) RETURN_THROWS();
    Z_IGEXT_COLOR_P(ZEND_THIS)->green = value;
}

PHP_METHOD(Color, setBlue)
{
    zend_long value;
    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_LONG(value)
    ZEND_PARSE_PARAMETERS_END();

    if (!igext_validate_color_component(value, "Blue")) RETURN_THROWS();
    Z_IGEXT_COLOR_P(ZEND_THIS)->blue = value;
}

PHP_METHOD(Color, setAlpha)
{
    double alpha;
    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_DOUBLE(alpha)
    ZEND_PARSE_PARAMETERS_END();

    if (alpha < 0.0 || alpha > 1.0) {
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "Alpha must be between 0 and 1, %f given", alpha);
        RETURN_THROWS();
    }
    Z_IGEXT_COLOR_P(ZEND_THIS)->alpha = (zend_long)(alpha * 127.0);
}

/* ── Method table ────────────────────────────────────────────────────────── */

static const zend_function_entry color_methods[] = {
    PHP_ME(Color, __construct, arginfo_Color_construct, ZEND_ACC_PUBLIC)
    PHP_ME(Color, random,      arginfo_Color_random,    ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(Color, allocate,    arginfo_Color_allocate,  ZEND_ACC_PUBLIC)
    PHP_ME(Color, getRed,      arginfo_Color_getInt,       ZEND_ACC_PUBLIC)
    PHP_ME(Color, getGreen,    arginfo_Color_getInt,       ZEND_ACC_PUBLIC)
    PHP_ME(Color, getBlue,     arginfo_Color_getInt,       ZEND_ACC_PUBLIC)
    PHP_ME(Color, getAlpha,    arginfo_Color_getInt,       ZEND_ACC_PUBLIC)
    PHP_ME(Color, setRed,      arginfo_Color_setComponent, ZEND_ACC_PUBLIC)
    PHP_ME(Color, setGreen,    arginfo_Color_setComponent, ZEND_ACC_PUBLIC)
    PHP_ME(Color, setBlue,     arginfo_Color_setComponent, ZEND_ACC_PUBLIC)
    PHP_ME(Color, setAlpha,    arginfo_Color_setAlpha,     ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* ── Registration ────────────────────────────────────────────────────────── */

void igext_register_color_class(void)
{
    zend_class_entry ce;
    INIT_NS_CLASS_ENTRY(ce, "StanDaniels\\ImageGenerator", "Color", color_methods);
    igext_color_ce = zend_register_internal_class(&ce);
    igext_color_ce->create_object = igext_color_create_object;

    memcpy(&igext_color_handlers, zend_get_std_object_handlers(),
           sizeof(zend_object_handlers));
    igext_color_handlers.offset   = XtOffsetOf(igext_color_object, std);
    igext_color_handlers.free_obj = igext_color_free_object;
}

/* ── Module lifecycle ────────────────────────────────────────────────────── */

PHP_MINIT_FUNCTION(image_generator)
{
    igext_register_color_class();
    igext_register_canvas_class();
    igext_register_image_class();
    igext_register_shape_classes();
    return SUCCESS;
}

PHP_MINFO_FUNCTION(image_generator)
{
    php_info_print_table_start();
    php_info_print_table_row(2, "image_generator support", "enabled");
    php_info_print_table_row(2, "version", PHP_IMAGE_GENERATOR_VERSION);
    php_info_print_table_end();
}

zend_module_entry image_generator_module_entry = {
    STANDARD_MODULE_HEADER,
    PHP_IMAGE_GENERATOR_EXTNAME,
    NULL,           /* functions */
    PHP_MINIT(image_generator),
    NULL,           /* MSHUTDOWN */
    NULL,           /* RINIT     */
    NULL,           /* RSHUTDOWN */
    PHP_MINFO(image_generator),
    PHP_IMAGE_GENERATOR_VERSION,
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_IMAGE_GENERATOR
ZEND_GET_MODULE(image_generator)
#endif
