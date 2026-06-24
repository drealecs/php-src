/*
   +----------------------------------------------------------------------+
   | Zend Engine                                                          |
   +----------------------------------------------------------------------+
   | Copyright © Zend Technologies Ltd., a subsidiary company of          |
   |     Perforce Software, Inc., and Contributors.                       |
   +----------------------------------------------------------------------+
   | This source file is subject to the Modified BSD License that is      |
   | bundled with this package in the file LICENSE, and is available      |
   | through the World Wide Web at <https://www.php.net/license/>.        |
   |                                                                      |
   | SPDX-License-Identifier: BSD-3-Clause                                |
   +----------------------------------------------------------------------+
*/

#ifndef ZEND_RUNTIME_MODULE_H
#define ZEND_RUNTIME_MODULE_H

#include "zend.h"

typedef struct _zend_constant zend_constant;

typedef struct _zend_runtime_context {
	HashTable *class_table;
	HashTable *function_table;
	HashTable *constants_table;
	HashTable *declared_class_table;
	HashTable *declared_function_table;
	HashTable *declared_constants_table;
	HashTable *dependencies;
	HashTable *autoload_functions;
	HashTable *included_files;
	HashTable class_table_storage;
	HashTable function_table_storage;
	HashTable constants_table_storage;
} zend_runtime_context;

typedef struct _zend_runtime_module {
	zend_runtime_context context;
	zend_string *name;
	HashTable *autoload_current_classnames;
	HashTable declared_class_table;
	HashTable declared_function_table;
	HashTable declared_constants_table;
	HashTable dependencies;
	HashTable dependants;
	HashTable autoload_functions;
	HashTable included_files;
} zend_runtime_module;

typedef enum _zend_runtime_module_symbol_conflict_source {
	ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_NONE,
	ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_INTERNAL,
	ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_ROOT,
	ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_MODULE,
} zend_runtime_module_symbol_conflict_source;

typedef enum _zend_runtime_module_symbol_kind {
	ZEND_RUNTIME_MODULE_SYMBOL_CLASS,
	ZEND_RUNTIME_MODULE_SYMBOL_FUNCTION,
	ZEND_RUNTIME_MODULE_SYMBOL_CONSTANT,
} zend_runtime_module_symbol_kind;

typedef struct _zend_runtime_module_symbol_conflict {
	zend_runtime_module *module;
	zend_runtime_module_symbol_conflict_source source;
} zend_runtime_module_symbol_conflict;

ZEND_API void zend_runtime_module_ptr_dtor(zval *zv);
ZEND_API zend_runtime_module *zend_runtime_module_get_or_create(zend_string *name);
ZEND_API void zend_runtime_context_init_root(void);
ZEND_API void zend_runtime_context_shutdown_root(void);
ZEND_API zend_runtime_module *zend_get_current_runtime_module(void);
ZEND_API void zend_set_current_runtime_module(zend_runtime_module *module);
ZEND_API void zend_set_runtime_module_override(zend_runtime_module *module);
ZEND_API void zend_runtime_context_add_visible_class(zend_runtime_context *context, zend_string *key, zend_class_entry *ce);
ZEND_API void zend_runtime_module_add_visible_class(zend_runtime_module *module, zend_string *key, zend_class_entry *ce);
ZEND_API void zend_runtime_context_import_dependency_classes(zend_runtime_context *context, zend_runtime_module *dependency);
ZEND_API void zend_runtime_context_add_visible_function(zend_runtime_context *context, zend_string *key, zend_function *function);
ZEND_API void zend_runtime_module_add_visible_function(zend_runtime_module *module, zend_string *key, zend_function *function);
ZEND_API void zend_runtime_context_add_visible_internal_function(zend_string *key, zend_function *function);
ZEND_API void zend_runtime_context_remove_visible_internal_function(zend_string *key);
ZEND_API void zend_runtime_context_import_dependency_functions(zend_runtime_context *context, zend_runtime_module *dependency);
ZEND_API void zend_runtime_context_add_visible_constant(zend_runtime_context *context, zend_string *key, zend_constant *constant);
ZEND_API void zend_runtime_module_add_visible_constant(zend_runtime_module *module, zend_string *key, zend_constant *constant);
ZEND_API void zend_runtime_context_add_visible_internal_constant(zend_string *key, zend_constant *constant);
ZEND_API void zend_runtime_context_remove_visible_internal_constant(zend_string *key);
ZEND_API void zend_runtime_context_import_dependency_constants(zend_runtime_context *context, zend_runtime_module *dependency);
ZEND_API const char *zend_runtime_module_symbol_kind_name(zend_runtime_module_symbol_kind kind);
ZEND_API zend_runtime_module_symbol_conflict_source zend_runtime_module_global_symbol_source(
	zend_runtime_module_symbol_kind kind, zend_string *key);
ZEND_API bool zend_runtime_module_check_symbol_declaration(
	zend_runtime_module *module, zend_runtime_module_symbol_kind kind, zend_string *key,
	zend_runtime_module_symbol_conflict *conflict);
ZEND_API bool zend_runtime_module_check_dependency_symbols(
	zend_runtime_module *module, zend_runtime_module *dependency,
	zend_runtime_module_symbol_kind *conflict_kind, zend_string **conflict_key,
	zend_runtime_module_symbol_conflict *conflict);

static zend_always_inline zend_runtime_context *zend_get_root_runtime_context(void)
{
	return EG(runtime_module_root_context);
}

static zend_always_inline zend_runtime_context *zend_runtime_module_context(zend_runtime_module *module)
{
	return module ? &module->context : zend_get_root_runtime_context();
}

static zend_always_inline zend_runtime_context *zend_get_current_runtime_context(void)
{
	return zend_runtime_module_context(zend_get_current_runtime_module());
}

#define RMG(v) (zend_get_current_runtime_context()->v)

static zend_always_inline bool zend_runtime_context_is_module_sensitive(zend_runtime_context *context)
{
	return context != zend_get_root_runtime_context() || zend_hash_num_elements(context->dependencies) != 0;
}

#endif /* ZEND_RUNTIME_MODULE_H */
