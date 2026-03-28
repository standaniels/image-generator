#ifndef PHP_IMAGE_GENERATOR_H
#define PHP_IMAGE_GENERATOR_H

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "zend_exceptions.h"
#include "ext/spl/spl_exceptions.h"

extern zend_module_entry image_generator_module_entry;
#define phpext_image_generator_ptr &image_generator_module_entry

#define PHP_IMAGE_GENERATOR_EXTNAME "image_generator"
#define PHP_IMAGE_GENERATOR_VERSION "1.0.0"

/* ── Class entries ─────────────────────────────────────────────────────────── */
extern zend_class_entry *igext_color_ce;
extern zend_class_entry *igext_canvas_ce;
extern zend_class_entry *igext_image_ce;
extern zend_class_entry *igext_shape_ce;
extern zend_class_entry *igext_circle_ce;
extern zend_class_entry *igext_polygon_ce;

/* ── Internal object structs ───────────────────────────────────────────────── */

typedef struct {
    zend_long red;
    zend_long green;
    zend_long blue;
    zend_long alpha; /* GD format: 0 = opaque, 127 = transparent */
    zend_object std;
} igext_color_object;

typedef struct {
    zval       gd_image;       /* GdImage PHP object                */
    zend_long  width;          /* logical (original) width          */
    zend_long  height;         /* logical (original) height         */
    double     anti_aliasing;
    zend_long  actual_width;   /* width  * anti_aliasing (canvas)   */
    zend_long  actual_height;  /* height * anti_aliasing (canvas)   */
    zend_object std;
} igext_canvas_object;

typedef struct {
    zval       canvas;   /* Canvas PHP object  */
    zend_long  x;
    zend_long  y;
    zend_long  size;
    zend_long  sides;
    zval       color;    /* Color PHP object   */
    zend_object std;
} igext_shape_object;

typedef struct {
    zval       canvas;
    zend_long  x;
    zend_long  y;
    zend_long  size;
    zend_long  sides;
    zval       color;
    zend_long  rotate;
    zend_long *points;        /* flattened [x0,y0, x1,y1, …] – sides*2 elements */
    zend_object std;
} igext_polygon_object;

/* ── Object-from-zend_object helpers ──────────────────────────────────────── */

static inline igext_color_object *igext_color_from_obj(zend_object *obj) {
    return (igext_color_object *)((char *)obj - XtOffsetOf(igext_color_object, std));
}
#define Z_IGEXT_COLOR_P(zv) igext_color_from_obj(Z_OBJ_P(zv))

static inline igext_canvas_object *igext_canvas_from_obj(zend_object *obj) {
    return (igext_canvas_object *)((char *)obj - XtOffsetOf(igext_canvas_object, std));
}
#define Z_IGEXT_CANVAS_P(zv) igext_canvas_from_obj(Z_OBJ_P(zv))

static inline igext_shape_object *igext_shape_from_obj(zend_object *obj) {
    return (igext_shape_object *)((char *)obj - XtOffsetOf(igext_shape_object, std));
}
#define Z_IGEXT_SHAPE_P(zv) igext_shape_from_obj(Z_OBJ_P(zv))

static inline igext_polygon_object *igext_polygon_from_obj(zend_object *obj) {
    return (igext_polygon_object *)((char *)obj - XtOffsetOf(igext_polygon_object, std));
}
#define Z_IGEXT_POLYGON_P(zv) igext_polygon_from_obj(Z_OBJ_P(zv))

/* ── Shared utilities ─────────────────────────────────────────────────────── */

/* Call a PHP global function by name. retval must be initialised by caller. */
zend_result igext_call_function(const char *name, zval *retval, uint32_t argc, zval *argv);

/* Registration hooks called from MINIT */
void igext_register_color_class(void);
void igext_register_canvas_class(void);
void igext_register_image_class(void);
void igext_register_shape_classes(void);

#endif /* PHP_IMAGE_GENERATOR_H */
