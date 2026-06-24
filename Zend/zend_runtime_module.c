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

#include "zend.h"
#include "zend_API.h"
#include "zend_autoload.h"
#include "zend_constants.h"
#include "zend_runtime_module.h"

static void zend_runtime_context_init(
		zend_runtime_context *context, HashTable *declared_class_table,
		HashTable *declared_function_table, HashTable *declared_constants_table,
		HashTable *dependencies, HashTable *autoload_functions, HashTable *included_files)
{
	context->class_table = &context->class_table_storage;
	context->function_table = &context->function_table_storage;
	context->constants_table = &context->constants_table_storage;
	context->declared_class_table = declared_class_table;
	context->declared_function_table = declared_function_table;
	context->declared_constants_table = declared_constants_table;
	context->dependencies = dependencies;
	context->autoload_functions = autoload_functions;
	context->included_files = included_files;
	zend_hash_init(context->class_table, 8, NULL, NULL, 0);
	zend_hash_init(context->function_table, 8, NULL, NULL, 0);
	zend_hash_init(context->constants_table, 8, NULL, NULL, 0);
}

static void zend_runtime_context_destroy(zend_runtime_context *context)
{
	zend_hash_destroy(context->constants_table);
	zend_hash_destroy(context->function_table);
	zend_hash_destroy(context->class_table);
}

static void zend_runtime_context_seed_class_table(zend_runtime_context *context, HashTable *class_table, bool internal_only)
{
	zend_string *key;
	zend_class_entry *ce;

	ZEND_HASH_MAP_FOREACH_STR_KEY_PTR(class_table, key, ce) {
		if (!key || (internal_only && ce->type != ZEND_INTERNAL_CLASS)) {
			continue;
		}
		zend_hash_update_ptr(context->class_table, key, ce);
	} ZEND_HASH_FOREACH_END();
}

static void zend_runtime_context_seed_function_table(zend_runtime_context *context, HashTable *function_table, bool internal_only)
{
	zend_string *key;
	zend_function *function;

	ZEND_HASH_MAP_FOREACH_STR_KEY_PTR(function_table, key, function) {
		if (!key || (internal_only && function->type != ZEND_INTERNAL_FUNCTION)) {
			continue;
		}
		zend_hash_update_ptr(context->function_table, key, function);
	} ZEND_HASH_FOREACH_END();
}

static void zend_runtime_context_seed_constant_table(zend_runtime_context *context, HashTable *constants_table, bool internal_only)
{
	zend_string *key;
	zend_constant *constant;

	ZEND_HASH_MAP_FOREACH_STR_KEY_PTR(constants_table, key, constant) {
		if (!key || ZSTR_LEN(key) == 0 || ZSTR_VAL(key)[0] == '\0'
				|| (internal_only && ZEND_CONSTANT_MODULE_NUMBER(constant) == PHP_USER_CONSTANT)) {
			continue;
		}
		zend_hash_update_ptr(context->constants_table, key, constant);
	} ZEND_HASH_FOREACH_END();
}

static zend_runtime_module *zend_runtime_module_alloc(zend_string *name)
{
	zend_runtime_module *module = emalloc(sizeof(zend_runtime_module));

	module->name = zend_string_copy(name);
	module->autoload_current_classnames = NULL;
	zend_hash_init(&module->declared_class_table, 8, NULL, ZEND_CLASS_DTOR, 0);
	zend_hash_init(&module->declared_function_table, 8, NULL, ZEND_FUNCTION_DTOR, 0);
	zend_hash_init(&module->declared_constants_table, 8, NULL, ZEND_CONSTANT_DTOR, 0);
	zend_hash_init(&module->dependencies, 4, NULL, NULL, 0);
	zend_hash_init(&module->dependants, 4, NULL, NULL, 0);
	zend_hash_init(&module->autoload_functions, 1, NULL, zend_autoload_callback_zval_destroy, 0);
	zend_hash_real_init_mixed(&module->autoload_functions);
	zend_hash_init(&module->included_files, 8, NULL, NULL, 0);
	zend_runtime_context_init(&module->context,
		&module->declared_class_table, &module->declared_function_table, &module->declared_constants_table,
		&module->dependencies, &module->autoload_functions, &module->included_files);
	zend_runtime_context_seed_class_table(&module->context, EG(class_table), true);
	zend_runtime_context_seed_function_table(&module->context, EG(function_table), true);
	zend_runtime_context_seed_constant_table(&module->context, EG(zend_constants), true);

	return module;
}

static void zend_runtime_module_free(zend_runtime_module *module)
{
	zend_string_release_ex(module->name, 0);
	zend_runtime_context_destroy(&module->context);
	if (module->autoload_current_classnames) {
		zend_hash_destroy(module->autoload_current_classnames);
		FREE_HASHTABLE(module->autoload_current_classnames);
	}
	zend_hash_destroy(&module->autoload_functions);
	zend_hash_destroy(&module->declared_class_table);
	zend_hash_destroy(&module->declared_function_table);
	zend_hash_destroy(&module->declared_constants_table);
	zend_hash_destroy(&module->dependencies);
	zend_hash_destroy(&module->dependants);
	zend_hash_destroy(&module->included_files);
	efree(module);
}

ZEND_API void zend_runtime_module_ptr_dtor(zval *zv)
{
	zend_runtime_module_free(Z_PTR_P(zv));
}

ZEND_API zend_runtime_module *zend_runtime_module_get_or_create(zend_string *name)
{
	zend_runtime_module *module = zend_hash_find_ptr(&EG(runtime_modules), name);

	if (module) {
		return module;
	}

	module = zend_runtime_module_alloc(name);
	zend_hash_add_new_ptr(&EG(runtime_modules), module->name, module);
	return module;
}

ZEND_API void zend_runtime_context_init_root(void)
{
	zend_runtime_context *context = emalloc(sizeof(zend_runtime_context));

	zend_runtime_context_init(context,
		EG(class_table), EG(function_table), EG(zend_constants),
		&EG(runtime_module_root_dependencies), NULL, &EG(included_files));
	zend_runtime_context_seed_class_table(context, EG(class_table), false);
	zend_runtime_context_seed_function_table(context, EG(function_table), false);
	zend_runtime_context_seed_constant_table(context, EG(zend_constants), false);
	EG(runtime_module_root_context) = context;
}

ZEND_API void zend_runtime_context_shutdown_root(void)
{
	if (EG(runtime_module_root_context)) {
		zend_runtime_context_destroy(EG(runtime_module_root_context));
		efree(EG(runtime_module_root_context));
		EG(runtime_module_root_context) = NULL;
	}
}

ZEND_API zend_runtime_module *zend_get_current_runtime_module(void)
{
	if (EG(current_execute_data)) {
		return EG(current_execute_data)->runtime_module;
	}

	return EG(current_runtime_module);
}

ZEND_API void zend_set_current_runtime_module(zend_runtime_module *module)
{
	EG(current_runtime_module) = module;
}

ZEND_API void zend_set_runtime_module_override(zend_runtime_module *module)
{
	EG(runtime_module_override) = module;
}

ZEND_API void zend_runtime_context_add_visible_class(zend_runtime_context *context, zend_string *key, zend_class_entry *ce)
{
	zend_class_entry *existing_ce;

	if (!key || ZSTR_LEN(key) == 0 || ZSTR_VAL(key)[0] == '\0') {
		return;
	}
	existing_ce = zend_hash_find_ptr(context->class_table, key);
	if (existing_ce) {
		ZEND_ASSERT(existing_ce == ce);
		return;
	}
	zend_hash_add_new_ptr(context->class_table, key, ce);
}

ZEND_API void zend_runtime_module_add_visible_class(zend_runtime_module *module, zend_string *key, zend_class_entry *ce)
{
	zend_runtime_context *context = zend_runtime_module_context(module);
	zend_runtime_module *dependant;

	if (!context) {
		return;
	}
	zend_runtime_context_add_visible_class(context, key, ce);
	if (!module) {
		return;
	}

	ZEND_HASH_MAP_FOREACH_PTR(&module->dependants, dependant) {
		zend_runtime_context_add_visible_class(&dependant->context, key, ce);
	} ZEND_HASH_FOREACH_END();

	if (zend_hash_exists(zend_get_root_runtime_context()->dependencies, module->name)) {
		zend_runtime_context_add_visible_class(zend_get_root_runtime_context(), key, ce);
	}
}

ZEND_API void zend_runtime_context_import_dependency_classes(zend_runtime_context *context, zend_runtime_module *dependency)
{
	zend_string *key;
	zend_class_entry *ce;

	ZEND_HASH_MAP_FOREACH_STR_KEY_PTR(&dependency->declared_class_table, key, ce) {
		zend_runtime_context_add_visible_class(context, key, ce);
	} ZEND_HASH_FOREACH_END();
}

ZEND_API void zend_runtime_context_add_visible_function(zend_runtime_context *context, zend_string *key, zend_function *function)
{
	zend_function *existing_function;

	if (!key || ZSTR_LEN(key) == 0 || ZSTR_VAL(key)[0] == '\0') {
		return;
	}
	existing_function = zend_hash_find_ptr(context->function_table, key);
	if (existing_function) {
		ZEND_ASSERT(existing_function == function);
		return;
	}
	zend_hash_add_new_ptr(context->function_table, key, function);
}

ZEND_API void zend_runtime_module_add_visible_function(zend_runtime_module *module, zend_string *key, zend_function *function)
{
	zend_runtime_context *context = zend_runtime_module_context(module);
	zend_runtime_module *dependant;

	if (!context) {
		return;
	}
	zend_runtime_context_add_visible_function(context, key, function);
	if (!module) {
		return;
	}

	ZEND_HASH_MAP_FOREACH_PTR(&module->dependants, dependant) {
		zend_runtime_context_add_visible_function(&dependant->context, key, function);
	} ZEND_HASH_FOREACH_END();

	if (zend_hash_exists(zend_get_root_runtime_context()->dependencies, module->name)) {
		zend_runtime_context_add_visible_function(zend_get_root_runtime_context(), key, function);
	}
}

ZEND_API void zend_runtime_context_add_visible_internal_function(zend_string *key, zend_function *function)
{
	zend_runtime_module *module;

	if (!EG(runtime_module_root_context)) {
		return;
	}
	zend_runtime_context_add_visible_function(zend_get_root_runtime_context(), key, function);
	ZEND_HASH_MAP_FOREACH_PTR(&EG(runtime_modules), module) {
		zend_runtime_context_add_visible_function(&module->context, key, function);
	} ZEND_HASH_FOREACH_END();
}

ZEND_API void zend_runtime_context_remove_visible_internal_function(zend_string *key)
{
	zend_runtime_module *module;

	if (!EG(runtime_module_root_context)) {
		return;
	}
	zend_hash_del(zend_get_root_runtime_context()->function_table, key);
	ZEND_HASH_MAP_FOREACH_PTR(&EG(runtime_modules), module) {
		zend_hash_del(module->context.function_table, key);
	} ZEND_HASH_FOREACH_END();
}

ZEND_API void zend_runtime_context_import_dependency_functions(zend_runtime_context *context, zend_runtime_module *dependency)
{
	zend_string *key;
	zend_function *function;

	ZEND_HASH_MAP_FOREACH_STR_KEY_PTR(&dependency->declared_function_table, key, function) {
		zend_runtime_context_add_visible_function(context, key, function);
	} ZEND_HASH_FOREACH_END();
}

ZEND_API void zend_runtime_context_add_visible_constant(zend_runtime_context *context, zend_string *key, zend_constant *constant)
{
	zend_constant *existing_constant;

	if (!key || ZSTR_LEN(key) == 0 || ZSTR_VAL(key)[0] == '\0') {
		return;
	}
	existing_constant = zend_hash_find_ptr(context->constants_table, key);
	if (existing_constant) {
		ZEND_ASSERT(existing_constant == constant);
		return;
	}
	zend_hash_add_new_ptr(context->constants_table, key, constant);
}

ZEND_API void zend_runtime_module_add_visible_constant(zend_runtime_module *module, zend_string *key, zend_constant *constant)
{
	zend_runtime_context *context = zend_runtime_module_context(module);
	zend_runtime_module *dependant;

	if (!context) {
		return;
	}
	zend_runtime_context_add_visible_constant(context, key, constant);
	if (!module) {
		return;
	}

	ZEND_HASH_MAP_FOREACH_PTR(&module->dependants, dependant) {
		zend_runtime_context_add_visible_constant(&dependant->context, key, constant);
	} ZEND_HASH_FOREACH_END();

	if (zend_hash_exists(zend_get_root_runtime_context()->dependencies, module->name)) {
		zend_runtime_context_add_visible_constant(zend_get_root_runtime_context(), key, constant);
	}
}

ZEND_API void zend_runtime_context_add_visible_internal_constant(zend_string *key, zend_constant *constant)
{
	zend_runtime_module *module;

	if (!EG(runtime_module_root_context)) {
		return;
	}
	zend_runtime_context_add_visible_constant(zend_get_root_runtime_context(), key, constant);
	ZEND_HASH_MAP_FOREACH_PTR(&EG(runtime_modules), module) {
		zend_runtime_context_add_visible_constant(&module->context, key, constant);
	} ZEND_HASH_FOREACH_END();
}

ZEND_API void zend_runtime_context_remove_visible_internal_constant(zend_string *key)
{
	zend_runtime_module *module;

	if (!EG(runtime_module_root_context)) {
		return;
	}
	zend_hash_del(zend_get_root_runtime_context()->constants_table, key);
	ZEND_HASH_MAP_FOREACH_PTR(&EG(runtime_modules), module) {
		zend_hash_del(module->context.constants_table, key);
	} ZEND_HASH_FOREACH_END();
}

ZEND_API void zend_runtime_context_import_dependency_constants(zend_runtime_context *context, zend_runtime_module *dependency)
{
	zend_string *key;
	zend_constant *constant;

	ZEND_HASH_MAP_FOREACH_STR_KEY_PTR(&dependency->declared_constants_table, key, constant) {
		zend_runtime_context_add_visible_constant(context, key, constant);
	} ZEND_HASH_FOREACH_END();
}

ZEND_API const char *zend_runtime_module_symbol_kind_name(zend_runtime_module_symbol_kind kind)
{
	switch (kind) {
		case ZEND_RUNTIME_MODULE_SYMBOL_CLASS:
			return "class";
		case ZEND_RUNTIME_MODULE_SYMBOL_FUNCTION:
			return "function";
		case ZEND_RUNTIME_MODULE_SYMBOL_CONSTANT:
			return "constant";
	}
	ZEND_UNREACHABLE();
}

static zend_always_inline HashTable *zend_runtime_context_symbol_table(
		zend_runtime_context *context, zend_runtime_module_symbol_kind kind)
{
	switch (kind) {
		case ZEND_RUNTIME_MODULE_SYMBOL_CLASS:
			return context->declared_class_table;
		case ZEND_RUNTIME_MODULE_SYMBOL_FUNCTION:
			return context->declared_function_table;
		case ZEND_RUNTIME_MODULE_SYMBOL_CONSTANT:
			return context->declared_constants_table;
	}
	ZEND_UNREACHABLE();
}

static zend_always_inline bool zend_runtime_module_symbol_key_is_public(zend_string *key)
{
	return key && ZSTR_LEN(key) != 0 && ZSTR_VAL(key)[0] != '\0';
}

ZEND_API zend_runtime_module_symbol_conflict_source zend_runtime_module_global_symbol_source(
		zend_runtime_module_symbol_kind kind, zend_string *key)
{
	switch (kind) {
		case ZEND_RUNTIME_MODULE_SYMBOL_CLASS: {
			zend_class_entry *ce = zend_hash_find_ptr(EG(class_table), key);
			if (!ce) {
				return ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_NONE;
			}
			return ce->type == ZEND_INTERNAL_CLASS
				? ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_INTERNAL
				: ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_ROOT;
		}
		case ZEND_RUNTIME_MODULE_SYMBOL_FUNCTION: {
			zend_function *func = zend_hash_find_ptr(EG(function_table), key);
			if (!func) {
				return ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_NONE;
			}
			return func->type == ZEND_INTERNAL_FUNCTION
				? ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_INTERNAL
				: ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_ROOT;
		}
		case ZEND_RUNTIME_MODULE_SYMBOL_CONSTANT: {
			zend_constant *constant = zend_hash_find_ptr(EG(zend_constants), key);
			if (constant) {
				return ZEND_CONSTANT_MODULE_NUMBER(constant) == PHP_USER_CONSTANT
					? ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_ROOT
					: ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_INTERNAL;
			}
			return zend_get_special_const(ZSTR_VAL(key), ZSTR_LEN(key)) != NULL
				? ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_INTERNAL
				: ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_NONE;
		}
	}
	ZEND_UNREACHABLE();
}

static bool zend_runtime_module_global_symbol_exists(
		zend_runtime_module_symbol_kind kind, zend_string *key, bool include_root_symbols,
		zend_runtime_module_symbol_conflict *conflict)
{
	zend_runtime_module_symbol_conflict_source source = zend_runtime_module_global_symbol_source(kind, key);

	if (source == ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_NONE) {
		return false;
	}
	if (source == ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_ROOT && !include_root_symbols) {
		return false;
	}
	if (conflict) {
		conflict->module = NULL;
		conflict->source = source;
	}
	return true;
}

static void zend_runtime_module_set_module_conflict(
		zend_runtime_module_symbol_conflict *conflict, zend_runtime_module *module)
{
	if (conflict) {
		conflict->module = module;
		conflict->source = ZEND_RUNTIME_MODULE_SYMBOL_CONFLICT_MODULE;
	}
}

static bool zend_runtime_module_visible_symbol_exists(
		zend_runtime_module *module, zend_runtime_module_symbol_kind kind, zend_string *key,
		bool include_own, zend_runtime_module *ignore_dependency,
		zend_runtime_module_symbol_conflict *conflict)
{
	HashTable *dependencies;
	zend_runtime_module *dependency;
	zend_runtime_context *context = zend_runtime_module_context(module);

	if (module) {
		if (include_own && zend_hash_exists(zend_runtime_context_symbol_table(context, kind), key)) {
			zend_runtime_module_set_module_conflict(conflict, module);
			return true;
		}

		if (zend_runtime_module_global_symbol_exists(kind, key, false, conflict)) {
			return true;
		}
	} else if (include_own && zend_runtime_module_global_symbol_exists(kind, key, true, conflict)) {
		return true;
	}

	dependencies = context->dependencies;
	ZEND_HASH_FOREACH_PTR(dependencies, dependency) {
		if (dependency == ignore_dependency) {
			continue;
		}
		if (zend_hash_exists(zend_runtime_context_symbol_table(&dependency->context, kind), key)) {
			zend_runtime_module_set_module_conflict(conflict, dependency);
			return true;
		}
	} ZEND_HASH_FOREACH_END();

	return false;
}

ZEND_API bool zend_runtime_module_check_symbol_declaration(
		zend_runtime_module *module, zend_runtime_module_symbol_kind kind, zend_string *key,
		zend_runtime_module_symbol_conflict *conflict)
{
	if (!zend_runtime_module_symbol_key_is_public(key)) {
		return false;
	}

	if (module) {
		zend_runtime_module *dependant;

		if (zend_runtime_module_visible_symbol_exists(module, kind, key,
					false, NULL, conflict)) {
			return true;
		}

		ZEND_HASH_MAP_FOREACH_PTR(&module->dependants, dependant) {
			if (zend_runtime_module_visible_symbol_exists(dependant, kind, key,
						true, module, conflict)) {
				return true;
			}
		} ZEND_HASH_FOREACH_END();

		if (zend_hash_exists(zend_get_root_runtime_context()->dependencies, module->name)
				&& zend_runtime_module_visible_symbol_exists(NULL, kind, key,
					true, module, conflict)) {
			return true;
		}

		return false;
	}

	if (zend_runtime_module_visible_symbol_exists(NULL, kind, key,
				false, NULL, conflict)) {
		return true;
	}

	return false;
}

static bool zend_runtime_module_check_dependency_table_symbols(
		zend_runtime_module *module, zend_runtime_module *dependency,
		zend_runtime_module_symbol_kind kind, zend_string **conflict_key,
		zend_runtime_module_symbol_conflict *conflict)
{
	HashTable *table = zend_runtime_context_symbol_table(&dependency->context, kind);
	zend_string *key;

	ZEND_HASH_MAP_FOREACH_STR_KEY(table, key) {
		if (!zend_runtime_module_symbol_key_is_public(key)) {
			continue;
		}
		if (zend_runtime_module_visible_symbol_exists(module, kind, key,
					true, dependency, conflict)) {
			if (conflict_key) {
				*conflict_key = key;
			}
			return true;
		}
	} ZEND_HASH_FOREACH_END();

	return false;
}

ZEND_API bool zend_runtime_module_check_dependency_symbols(
		zend_runtime_module *module, zend_runtime_module *dependency,
		zend_runtime_module_symbol_kind *conflict_kind, zend_string **conflict_key,
		zend_runtime_module_symbol_conflict *conflict)
{
	for (zend_runtime_module_symbol_kind kind = ZEND_RUNTIME_MODULE_SYMBOL_CLASS;
			kind <= ZEND_RUNTIME_MODULE_SYMBOL_CONSTANT; kind++) {
		if (zend_runtime_module_check_dependency_table_symbols(
					module, dependency, kind, conflict_key, conflict)) {
			if (conflict_kind) {
				*conflict_kind = kind;
			}
			return true;
		}
	}

	return false;
}
