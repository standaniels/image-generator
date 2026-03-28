/*
 * shape.c – Shape (abstract), Circle, Polygon
 *
 * Namespace : StanDaniels\ImageGenerator\Shape
 * Classes   : Shape, Circle, Polygon
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <math.h>
#include "php.h"
#include "ext/random/php_random.h"
#include "ext/spl/spl_exceptions.h"
#include "php_image_generator.h"

/* ── Object handlers ─────────────────────────────────────────────────────── */

static zend_object_handlers igext_shape_handlers;
static zend_object_handlers igext_polygon_handlers;

/* ── Shape / Circle object lifecycle ─────────────────────────────────────── */

static zend_object *igext_shape_create_object(zend_class_entry *ce)
{
    igext_shape_object *obj = zend_object_alloc(sizeof(igext_shape_object), ce);
    zend_object_std_init(&obj->std, ce);
    object_properties_init(&obj->std, ce);
    ZVAL_UNDEF(&obj->canvas);
    ZVAL_UNDEF(&obj->color);
    obj->std.handlers = &igext_shape_handlers;
    return &obj->std;
}

static void igext_shape_free_object(zend_object *obj)
{
    igext_shape_object *shape = igext_shape_from_obj(obj);
    zval_ptr_dtor(&shape->canvas);
    zval_ptr_dtor(&shape->color);
    zend_object_std_dtor(obj);
}

/* ── Polygon object lifecycle ────────────────────────────────────────────── */

static zend_object *igext_polygon_create_object(zend_class_entry *ce)
{
    igext_polygon_object *obj = zend_object_alloc(sizeof(igext_polygon_object), ce);
    zend_object_std_init(&obj->std, ce);
    object_properties_init(&obj->std, ce);
    ZVAL_UNDEF(&obj->canvas);
    ZVAL_UNDEF(&obj->color);
    obj->points = NULL;
    obj->std.handlers = &igext_polygon_handlers;
    return &obj->std;
}

static void igext_polygon_free_object(zend_object *obj)
{
    igext_polygon_object *polygon = igext_polygon_from_obj(obj);
    zval_ptr_dtor(&polygon->canvas);
    zval_ptr_dtor(&polygon->color);
    if (polygon->points) {
        efree(polygon->points);
        polygon->points = NULL;
    }
    zend_object_std_dtor(obj);
}

/* ── Helpers ─────────────────────────────────────────────────────────────── */

/* Random integer in [min, max] */
static zend_long igext_rand_range(zend_long min, zend_long max)
{
    if (min >= max) return min;
    return php_mt_rand_range(min, max);
}

/* Validate canvas coordinates */
static bool igext_validate_coordinates(zend_long x, zend_long y,
                                       igext_canvas_object *canvas)
{
    if (x < 0 || x > canvas->width) {
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "X coordinate %ld is out of range [0, %ld]",
            (long)x, (long)canvas->width);
        return false;
    }
    if (y < 0 || y > canvas->height) {
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "Y coordinate %ld is out of range [0, %ld]",
            (long)y, (long)canvas->height);
        return false;
    }
    return true;
}

/* Calculate polygon vertices into polygon->points */
static void igext_polygon_calculate_points(igext_polygon_object *polygon,
                                           zend_long sides, zend_long rotate)
{
    if (polygon->points) {
        efree(polygon->points);
    }
    polygon->points = (zend_long *)emalloc(sizeof(zend_long) * sides * 2);

    for (zend_long i = 0; i < sides; i++) {
        double angle = (M_PI / 180.0) * (rotate + (360.0 / sides) * i);
        polygon->points[i * 2]     = (zend_long)(polygon->x + polygon->size * cos(angle));
        polygon->points[i * 2 + 1] = (zend_long)(polygon->y + polygon->size * sin(angle));
    }
}

/* ── Argument info ───────────────────────────────────────────────────────── */

ZEND_BEGIN_ARG_INFO(arginfo_Shape_construct, 0)
    ZEND_ARG_OBJ_INFO(0, canvas, StanDaniels\\ImageGenerator\\Canvas, 0)
    ZEND_ARG_TYPE_INFO(0, x,     IS_LONG, 0)
    ZEND_ARG_TYPE_INFO(0, y,     IS_LONG, 0)
    ZEND_ARG_TYPE_INFO(0, size,  IS_LONG, 0)
    ZEND_ARG_OBJ_INFO(0, color, StanDaniels\\ImageGenerator\\Color, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_Shape_random, 0, 1,
    StanDaniels\\ImageGenerator\\Shape\\Shape, 0)
    ZEND_ARG_OBJ_INFO(0, canvas, StanDaniels\\ImageGenerator\\Canvas, 0)
    ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, color,
        StanDaniels\\ImageGenerator\\Color, 1, "null")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_Shape_draw, 0, 0, IS_VOID, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_Shape_getLong, 0, 0, IS_LONG, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_Shape_getColor, 0, 0,
    StanDaniels\\ImageGenerator\\Color, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_Polygon_construct, 0)
    ZEND_ARG_OBJ_INFO(0, canvas, StanDaniels\\ImageGenerator\\Canvas, 0)
    ZEND_ARG_TYPE_INFO(0, x,      IS_LONG, 0)
    ZEND_ARG_TYPE_INFO(0, y,      IS_LONG, 0)
    ZEND_ARG_TYPE_INFO(0, size,   IS_LONG, 0)
    ZEND_ARG_OBJ_INFO(0, color,  StanDaniels\\ImageGenerator\\Color, 0)
    ZEND_ARG_TYPE_INFO(0, sides,  IS_LONG, 0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, rotate, IS_LONG, 0, "0")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_Polygon_randomlyRotate, 0, 0,
    StanDaniels\\ImageGenerator\\Shape\\Polygon, 0)
ZEND_END_ARG_INFO()

/* ── Shape::__construct ──────────────────────────────────────────────────── */

PHP_METHOD(Shape, __construct)
{
    zend_object *canvas_obj, *color_obj;
    zend_long x, y, size;

    ZEND_PARSE_PARAMETERS_START(5, 5)
        Z_PARAM_OBJ_OF_CLASS(canvas_obj, igext_canvas_ce)
        Z_PARAM_LONG(x)
        Z_PARAM_LONG(y)
        Z_PARAM_LONG(size)
        Z_PARAM_OBJ_OF_CLASS(color_obj, igext_color_ce)
    ZEND_PARSE_PARAMETERS_END();

    igext_canvas_object *canvas = igext_canvas_from_obj(canvas_obj);

    if (!igext_validate_coordinates(x, y, canvas)) RETURN_THROWS();

    igext_shape_object *shape = Z_IGEXT_SHAPE_P(ZEND_THIS);
    ZVAL_OBJ_COPY(&shape->canvas, canvas_obj);
    ZVAL_OBJ_COPY(&shape->color,  color_obj);
    shape->x     = x;
    shape->y     = y;
    shape->size  = size;
    shape->sides = 0; /* set by subclass */
}

/* ── Shape::random (static) ──────────────────────────────────────────────── */

PHP_METHOD(Shape, random)
{
    zend_object *canvas_obj;
    zval *z_color = NULL;

    ZEND_PARSE_PARAMETERS_START(1, 2)
        Z_PARAM_OBJ_OF_CLASS(canvas_obj, igext_canvas_ce)
        Z_PARAM_OPTIONAL
        Z_PARAM_ZVAL_OR_NULL(z_color)
    ZEND_PARSE_PARAMETERS_END();

    igext_canvas_object *canvas = igext_canvas_from_obj(canvas_obj);

    zend_long rnd_x    = igext_rand_range(0, canvas->width);
    zend_long rnd_y    = igext_rand_range(0, canvas->height);
    double    geo_avg  = sqrt((double)(canvas->width * canvas->height));
    zend_long size_min = (zend_long)(geo_avg / 8.0);
    zend_long size_max = (zend_long)(geo_avg / 4.0);
    if (size_min < 1)  size_min = 1;
    if (size_max < size_min) size_max = size_min;
    zend_long rnd_size = igext_rand_range(size_min, size_max);

    /* Resolve color */
    zval color_zv;
    if (z_color == NULL || Z_TYPE_P(z_color) == IS_NULL) {
        /* Color::random() */
        zval fn;
        array_init(&fn);
        add_next_index_string(&fn, "StanDaniels\\ImageGenerator\\Color");
        add_next_index_string(&fn, "random");
        ZVAL_UNDEF(&color_zv);
        call_user_function(CG(function_table), NULL, &fn, &color_zv, 0, NULL);
        zval_ptr_dtor(&fn);
    } else {
        ZVAL_COPY(&color_zv, z_color);
    }

    /* Determine which class to instantiate */
    zend_class_entry *called_ce = zend_get_called_scope(execute_data);
    bool make_circle = (called_ce == igext_circle_ce)
        || (called_ce != igext_polygon_ce && igext_rand_range(0, 1) == 1);

    if (make_circle) {
        object_init_ex(return_value, igext_circle_ce);
        igext_shape_object *s = Z_IGEXT_SHAPE_P(return_value);
        ZVAL_OBJ_COPY(&s->canvas, canvas_obj);
        ZVAL_COPY(&s->color, &color_zv);
        s->x     = rnd_x;
        s->y     = rnd_y;
        s->size  = rnd_size;
        s->sides = 2;
    } else {
        zend_long sides = igext_rand_range(3, 8);
        object_init_ex(return_value, igext_polygon_ce);
        igext_polygon_object *p = Z_IGEXT_POLYGON_P(return_value);
        ZVAL_OBJ_COPY(&p->canvas, canvas_obj);
        ZVAL_COPY(&p->color, &color_zv);
        p->x      = rnd_x;
        p->y      = rnd_y;
        p->size   = rnd_size;
        p->sides  = sides;
        p->rotate = 0;
        igext_polygon_calculate_points(p, sides, 0);

        /* randomlyRotate() */
        zend_long max_rot = (sides > 0) ? (zend_long)(360 / sides) : 0;
        p->rotate = igext_rand_range(0, max_rot);
        igext_polygon_calculate_points(p, sides, p->rotate);
    }

    zval_ptr_dtor(&color_zv);
}

/* ── Shape getters ───────────────────────────────────────────────────────── */

PHP_METHOD(Shape, getX)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_SHAPE_P(ZEND_THIS)->x);
}

PHP_METHOD(Shape, getY)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_SHAPE_P(ZEND_THIS)->y);
}

PHP_METHOD(Shape, getSize)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_SHAPE_P(ZEND_THIS)->size);
}

PHP_METHOD(Shape, getSides)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_SHAPE_P(ZEND_THIS)->sides);
}

PHP_METHOD(Shape, getColor)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_ZVAL(&Z_IGEXT_SHAPE_P(ZEND_THIS)->color, 1, 0);
}

/* ── Circle::__construct ─────────────────────────────────────────────────── */

PHP_METHOD(Circle, __construct)
{
    zend_object *canvas_obj, *color_obj;
    zend_long x, y, size;

    ZEND_PARSE_PARAMETERS_START(5, 5)
        Z_PARAM_OBJ_OF_CLASS(canvas_obj, igext_canvas_ce)
        Z_PARAM_LONG(x)
        Z_PARAM_LONG(y)
        Z_PARAM_LONG(size)
        Z_PARAM_OBJ_OF_CLASS(color_obj, igext_color_ce)
    ZEND_PARSE_PARAMETERS_END();

    igext_canvas_object *canvas = igext_canvas_from_obj(canvas_obj);
    if (!igext_validate_coordinates(x, y, canvas)) RETURN_THROWS();

    igext_shape_object *shape = Z_IGEXT_SHAPE_P(ZEND_THIS);
    ZVAL_OBJ_COPY(&shape->canvas, canvas_obj);
    ZVAL_OBJ_COPY(&shape->color,  color_obj);
    shape->x     = x;
    shape->y     = y;
    shape->size  = size;
    shape->sides = 2;
}

/* ── Circle::draw ────────────────────────────────────────────────────────── */

PHP_METHOD(Circle, draw)
{
    ZEND_PARSE_PARAMETERS_NONE();

    igext_shape_object  *shape  = Z_IGEXT_SHAPE_P(ZEND_THIS);
    igext_canvas_object *canvas = igext_canvas_from_obj(Z_OBJ(shape->canvas));

    /* $color->allocate($canvas) */
    zval color_id, alloc_method;
    ZVAL_STRING(&alloc_method, "allocate");
    call_user_function(NULL, &shape->color, &alloc_method, &color_id, 1, &shape->canvas);
    zval_ptr_dtor(&alloc_method);

    if (EG(exception) || Z_TYPE(color_id) != IS_LONG) {
        zval_ptr_dtor(&color_id);
        RETURN_THROWS();
    }

    /* imagefilledellipse($gd, $x, $y, $w, $h, $color) */
    zval args[6], retval;
    ZVAL_COPY_VALUE(&args[0], &canvas->gd_image);
    ZVAL_LONG(&args[1], shape->x);
    ZVAL_LONG(&args[2], shape->y);
    ZVAL_LONG(&args[3], shape->size * 2);
    ZVAL_LONG(&args[4], shape->size * 2);
    ZVAL_COPY_VALUE(&args[5], &color_id);

    igext_call_function("imagefilledellipse", &retval, 6, args);
    zval_ptr_dtor(&color_id);

    if (Z_TYPE(retval) == IS_FALSE) {
        zval_ptr_dtor(&retval);
        zend_throw_exception(spl_ce_RuntimeException, "Could not draw circle", 0);
        RETURN_THROWS();
    }
    zval_ptr_dtor(&retval);
}

/* ── Polygon::__construct ────────────────────────────────────────────────── */

PHP_METHOD(Polygon, __construct)
{
    zend_object *canvas_obj, *color_obj;
    zend_long x, y, size, sides;
    zend_long rotate = 0;

    ZEND_PARSE_PARAMETERS_START(6, 7)
        Z_PARAM_OBJ_OF_CLASS(canvas_obj, igext_canvas_ce)
        Z_PARAM_LONG(x)
        Z_PARAM_LONG(y)
        Z_PARAM_LONG(size)
        Z_PARAM_OBJ_OF_CLASS(color_obj, igext_color_ce)
        Z_PARAM_LONG(sides)
        Z_PARAM_OPTIONAL
        Z_PARAM_LONG(rotate)
    ZEND_PARSE_PARAMETERS_END();

    igext_canvas_object *canvas = igext_canvas_from_obj(canvas_obj);
    if (!igext_validate_coordinates(x, y, canvas)) RETURN_THROWS();

    if (rotate < 0 || rotate > 360) {
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "Rotate must be between 0 and 360, %ld given", (long)rotate);
        RETURN_THROWS();
    }
    if (sides < 3) {
        zend_throw_exception_ex(spl_ce_InvalidArgumentException, 0,
            "Sides must be at least 3, %ld given", (long)sides);
        RETURN_THROWS();
    }

    igext_polygon_object *polygon = Z_IGEXT_POLYGON_P(ZEND_THIS);
    ZVAL_OBJ_COPY(&polygon->canvas, canvas_obj);
    ZVAL_OBJ_COPY(&polygon->color,  color_obj);
    polygon->x      = x;
    polygon->y      = y;
    polygon->size   = size;
    polygon->sides  = sides;
    polygon->rotate = rotate;
    igext_polygon_calculate_points(polygon, sides, rotate);
}

/* ── Polygon::randomlyRotate ─────────────────────────────────────────────── */

PHP_METHOD(Polygon, randomlyRotate)
{
    ZEND_PARSE_PARAMETERS_NONE();

    igext_polygon_object *polygon = Z_IGEXT_POLYGON_P(ZEND_THIS);
    zend_long max_rot = (polygon->sides > 0)
                        ? (zend_long)(360 / polygon->sides)
                        : 0;
    polygon->rotate = igext_rand_range(0, max_rot);
    igext_polygon_calculate_points(polygon, polygon->sides, polygon->rotate);

    RETVAL_ZVAL(ZEND_THIS, 1, 0);
}

/* ── Polygon::draw ───────────────────────────────────────────────────────── */

PHP_METHOD(Polygon, draw)
{
    ZEND_PARSE_PARAMETERS_NONE();

    igext_polygon_object *polygon = Z_IGEXT_POLYGON_P(ZEND_THIS);
    igext_canvas_object  *canvas  = igext_canvas_from_obj(Z_OBJ(polygon->canvas));

    /* $color->allocate($canvas) */
    zval color_id, alloc_method;
    ZVAL_STRING(&alloc_method, "allocate");
    call_user_function(NULL, &polygon->color, &alloc_method, &color_id, 1, &polygon->canvas);
    zval_ptr_dtor(&alloc_method);

    if (EG(exception) || Z_TYPE(color_id) != IS_LONG) {
        zval_ptr_dtor(&color_id);
        RETURN_THROWS();
    }

    /* Build PHP array of points [x0, y0, x1, y1, …] */
    zval z_points;
    array_init_size(&z_points, (uint32_t)(polygon->sides * 2));
    for (zend_long i = 0; i < polygon->sides * 2; i++) {
        add_next_index_long(&z_points, polygon->points[i]);
    }

    /* imagefilledpolygon($gd, $points, $color)  — PHP 8.1+ API (no num_points) */
    zval args[3], retval;
    ZVAL_COPY_VALUE(&args[0], &canvas->gd_image);
    ZVAL_COPY_VALUE(&args[1], &z_points);
    ZVAL_COPY_VALUE(&args[2], &color_id);

    igext_call_function("imagefilledpolygon", &retval, 3, args);
    zval_ptr_dtor(&z_points);
    zval_ptr_dtor(&color_id);
    zval_ptr_dtor(&retval);
}

/* ── Polygon getters (override to use polygon struct) ────────────────────── */

PHP_METHOD(Polygon, getX)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_POLYGON_P(ZEND_THIS)->x);
}

PHP_METHOD(Polygon, getY)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_POLYGON_P(ZEND_THIS)->y);
}

PHP_METHOD(Polygon, getSize)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_POLYGON_P(ZEND_THIS)->size);
}

PHP_METHOD(Polygon, getSides)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_LONG(Z_IGEXT_POLYGON_P(ZEND_THIS)->sides);
}

PHP_METHOD(Polygon, getColor)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_ZVAL(&Z_IGEXT_POLYGON_P(ZEND_THIS)->color, 1, 0);
}

/* ── Method tables ───────────────────────────────────────────────────────── */

static const zend_function_entry shape_methods[] = {
    PHP_ME(Shape, __construct, arginfo_Shape_construct, ZEND_ACC_PUBLIC)
    PHP_ME(Shape, random,      arginfo_Shape_random,    ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ABSTRACT_ME(Shape, draw, arginfo_Shape_draw)
    PHP_ME(Shape, getX,     arginfo_Shape_getLong,  ZEND_ACC_PUBLIC)
    PHP_ME(Shape, getY,     arginfo_Shape_getLong,  ZEND_ACC_PUBLIC)
    PHP_ME(Shape, getSize,  arginfo_Shape_getLong,  ZEND_ACC_PUBLIC)
    PHP_ME(Shape, getSides, arginfo_Shape_getLong,  ZEND_ACC_PUBLIC)
    PHP_ME(Shape, getColor, arginfo_Shape_getColor, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

static const zend_function_entry circle_methods[] = {
    PHP_ME(Circle, __construct, arginfo_Shape_construct, ZEND_ACC_PUBLIC)
    PHP_ME(Circle, draw,        arginfo_Shape_draw,      ZEND_ACC_PUBLIC)
    PHP_FE_END
};

static const zend_function_entry polygon_methods[] = {
    PHP_ME(Polygon, __construct,   arginfo_Polygon_construct,      ZEND_ACC_PUBLIC)
    PHP_ME(Polygon, randomlyRotate, arginfo_Polygon_randomlyRotate, ZEND_ACC_PUBLIC)
    PHP_ME(Polygon, draw,          arginfo_Shape_draw,             ZEND_ACC_PUBLIC)
    PHP_ME(Polygon, getX,     arginfo_Shape_getLong,  ZEND_ACC_PUBLIC)
    PHP_ME(Polygon, getY,     arginfo_Shape_getLong,  ZEND_ACC_PUBLIC)
    PHP_ME(Polygon, getSize,  arginfo_Shape_getLong,  ZEND_ACC_PUBLIC)
    PHP_ME(Polygon, getSides, arginfo_Shape_getLong,  ZEND_ACC_PUBLIC)
    PHP_ME(Polygon, getColor, arginfo_Shape_getColor, ZEND_ACC_PUBLIC)
    PHP_FE_END
};

/* ── Registration ────────────────────────────────────────────────────────── */

void igext_register_shape_classes(void)
{
    zend_class_entry ce;

    /* Shape (abstract) */
    INIT_NS_CLASS_ENTRY(ce, "StanDaniels\\ImageGenerator\\Shape", "Shape",
                        shape_methods);
    igext_shape_ce = zend_register_internal_class(&ce);
    igext_shape_ce->ce_flags     |= ZEND_ACC_ABSTRACT;
    igext_shape_ce->create_object = igext_shape_create_object;

    memcpy(&igext_shape_handlers, zend_get_std_object_handlers(),
           sizeof(zend_object_handlers));
    igext_shape_handlers.offset   = XtOffsetOf(igext_shape_object, std);
    igext_shape_handlers.free_obj = igext_shape_free_object;

    /* Circle extends Shape */
    INIT_NS_CLASS_ENTRY(ce, "StanDaniels\\ImageGenerator\\Shape", "Circle",
                        circle_methods);
    igext_circle_ce = zend_register_internal_class_ex(&ce, igext_shape_ce);
    igext_circle_ce->create_object = igext_shape_create_object;

    /* Polygon extends Shape */
    INIT_NS_CLASS_ENTRY(ce, "StanDaniels\\ImageGenerator\\Shape", "Polygon",
                        polygon_methods);
    igext_polygon_ce = zend_register_internal_class_ex(&ce, igext_shape_ce);
    igext_polygon_ce->create_object = igext_polygon_create_object;

    memcpy(&igext_polygon_handlers, zend_get_std_object_handlers(),
           sizeof(zend_object_handlers));
    igext_polygon_handlers.offset   = XtOffsetOf(igext_polygon_object, std);
    igext_polygon_handlers.free_obj = igext_polygon_free_object;
}
