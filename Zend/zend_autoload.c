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
   | Authors: Gina Peter Banyard <girgias@php.net>                        |
   +----------------------------------------------------------------------+
*/

#include "zend.h"
#include "zend_API.h"
#include "zend_autoload.h"
#include "zend_hash.h"
#include "zend_types.h"
#include "zend_exceptions.h"
#include "zend_string.h"
#include "zend_execute.h"
#include "zend_runtime_module.h"

ZEND_TLS HashTable *zend_class_autoload_functions;

ZEND_API void zend_autoload_callback_zval_destroy(zval *element)
{
	zend_fcall_info_cache *fcc = Z_PTR_P(element);
	zend_fcc_dtor(fcc);
	efree(fcc);
}

static HashTable *zend_autoload_get_class_loaders(bool create)
{
	if (RMG(autoload_functions)) {
		return RMG(autoload_functions);
	}

	if (create) {
		ALLOC_HASHTABLE(zend_class_autoload_functions);
		zend_hash_init(zend_class_autoload_functions, 1, NULL, zend_autoload_callback_zval_destroy, false);
		/* Initialize as non-packed hash table for prepend functionality. */
		zend_hash_real_init_mixed(zend_class_autoload_functions);
		RMG(autoload_functions) = zend_class_autoload_functions;
	}

	return RMG(autoload_functions);
}

static Bucket *autoload_find_registered_function(const HashTable *autoloader_table, const zend_fcall_info_cache *function_entry)
{
	zend_fcall_info_cache *current_function_entry;
	ZEND_HASH_MAP_FOREACH_PTR(autoloader_table, current_function_entry) {
		if (zend_fcc_equals(current_function_entry, function_entry)) {
			return _p;
		}
	} ZEND_HASH_FOREACH_END();
	return NULL;
}

static zend_class_entry *zend_autoload_call_table(HashTable *class_autoload_functions, zend_runtime_module *autoload_module, zend_runtime_module *lookup_module, zend_string *class_name, zend_string *lc_name)
{
	if (!class_autoload_functions || zend_hash_num_elements(class_autoload_functions) == 0) {
		return NULL;
	}

	zval zname;
	ZVAL_STR(&zname, class_name);

	/* Cannot use ZEND_HASH_MAP_FOREACH_PTR here as autoloaders may be
	 * added/removed during autoloading. */
	HashPosition pos;
	zend_hash_internal_pointer_reset_ex(class_autoload_functions, &pos);
	while (true) {
		zend_fcall_info_cache *func_info = zend_hash_get_current_data_ptr_ex(class_autoload_functions, &pos);
		if (!func_info) {
			break;
		}
		zend_call_known_fcc_in_runtime_module(
			func_info, autoload_module,
			/* retval */ NULL, /* param_count */ 1, /* params */ &zname, /* named_params */ NULL);

		if (EG(exception)) {
			return NULL;
		}

		zend_class_entry *ce = zend_hash_find_ptr(
			zend_runtime_module_context(lookup_module)->class_table, lc_name);
		if (ce) {
			return ce;
		}

		zend_hash_move_forward_ex(class_autoload_functions, &pos);
	}
	return NULL;
}

ZEND_API zend_class_entry *zend_perform_class_autoload_in_runtime_module(zend_runtime_module *runtime_module, zend_string *class_name, zend_string *lc_name)
{
	zend_class_entry *ce;
	zend_runtime_context *context = zend_runtime_module_context(runtime_module);
	zend_runtime_module *dependency;

	ce = zend_autoload_call_table(context->autoload_functions, runtime_module, runtime_module, class_name, lc_name);
	if (ce || EG(exception)) {
		return ce;
	}

	ZEND_HASH_FOREACH_PTR(context->dependencies, dependency) {
		HashTable *autoload_current_classnames =
			zend_runtime_module_autoload_current_classnames(dependency);

		if (!zend_hash_add_empty_element(autoload_current_classnames, lc_name)) {
			continue;
		}
		ce = zend_autoload_call_table(&dependency->autoload_functions, dependency, runtime_module, class_name, lc_name);
		zend_hash_del(autoload_current_classnames, lc_name);
		if (ce || EG(exception)) {
			return ce;
		}
	} ZEND_HASH_FOREACH_END();

	return NULL;
}

ZEND_API zend_class_entry *zend_perform_class_autoload(zend_string *class_name, zend_string *lc_name)
{
	return zend_perform_class_autoload_in_runtime_module(zend_get_current_runtime_module(), class_name, lc_name);
}

/* Needed for compatibility with spl_register_autoload() */
ZEND_API void zend_autoload_register_class_loader(zend_fcall_info_cache *fcc, bool prepend)
{
	HashTable *class_autoload_functions;

	ZEND_ASSERT(ZEND_FCC_INITIALIZED(*fcc));

	class_autoload_functions = zend_autoload_get_class_loaders(true);

	ZEND_ASSERT(
		fcc->function_handler->type != ZEND_INTERNAL_FUNCTION
		|| !zend_string_equals_literal(fcc->function_handler->common.function_name, "spl_autoload_call")
	);

	/* If function is already registered, don't do anything */
	if (autoload_find_registered_function(class_autoload_functions, fcc)) {
		/* Release potential call trampoline */
		zend_release_fcall_info_cache(fcc);
		return;
	}

	zend_fcc_addref(fcc);
	zend_hash_next_index_insert_mem(class_autoload_functions, fcc, sizeof(zend_fcall_info_cache));
	if (prepend && zend_hash_num_elements(class_autoload_functions) > 1) {
		/* Move the newly created element to the head of the hashtable */
		ZEND_ASSERT(!HT_IS_PACKED(class_autoload_functions));
		Bucket tmp = class_autoload_functions->arData[class_autoload_functions->nNumUsed-1];
		memmove(class_autoload_functions->arData + 1, class_autoload_functions->arData, sizeof(Bucket) * (class_autoload_functions->nNumUsed - 1));
		class_autoload_functions->arData[0] = tmp;
		zend_hash_rehash(class_autoload_functions);
	}
}

ZEND_API bool zend_autoload_unregister_class_loader(const zend_fcall_info_cache *fcc) {
	HashTable *class_autoload_functions = zend_autoload_get_class_loaders(false);

	if (class_autoload_functions) {
		Bucket *p = autoload_find_registered_function(class_autoload_functions, fcc);
		if (p) {
			zend_hash_del_bucket(class_autoload_functions, p);
			return true;
		}
	}
	return false;
}

/* We do not return a HashTable* because zend_empty_array is not collectable,
 * therefore the zval holding this value must do so. Something that ZVAL_EMPTY_ARRAY(); does. */
ZEND_API void zend_autoload_fcc_map_to_callable_zval_map(zval *return_value) {
	HashTable *class_autoload_functions = zend_autoload_get_class_loaders(false);

	if (class_autoload_functions && zend_hash_num_elements(class_autoload_functions) > 0) {
		zend_fcall_info_cache *fcc;

		zend_array *map = zend_new_array(zend_hash_num_elements(class_autoload_functions));
		ZEND_HASH_MAP_FOREACH_PTR(class_autoload_functions, fcc) {
			zval tmp;
			zend_get_callable_zval_from_fcc(fcc, &tmp);
			zend_hash_next_index_insert(map, &tmp);
		} ZEND_HASH_FOREACH_END();
		RETURN_ARR(map);
	}
	RETURN_EMPTY_ARRAY();
}

/* Only for deprecated strange behaviour of spl_autoload_unregister() */
ZEND_API void zend_autoload_clean_class_loaders(void)
{
	HashTable *class_autoload_functions = zend_autoload_get_class_loaders(false);

	if (class_autoload_functions) {
		/* Don't destroy the hash table, as we might be iterating over it right now. */
		zend_hash_clean(class_autoload_functions);
	}
}

void zend_autoload_shutdown(void)
{
	if (zend_class_autoload_functions) {
		zend_hash_destroy(zend_class_autoload_functions);
		FREE_HASHTABLE(zend_class_autoload_functions);
		zend_class_autoload_functions = NULL;
	}
}
