/* This is a generated file, edit zend_runtime_module.stub.php instead.
 * Stub hash: 5847a1e390ebc62a2ea2e9cf348222d0924f86dc */

ZEND_BEGIN_ARG_INFO_EX(arginfo_class_RuntimeModule___construct, 0, 0, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_OBJ_INFO_EX(arginfo_class_RuntimeModule_current, 0, 0, RuntimeModule, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_class_RuntimeModule_require, 0, 1, IS_MIXED, 0)
	ZEND_ARG_TYPE_INFO(0, filename, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_class_RuntimeModule_addDependency, 0, 1, IS_VOID, 0)
	ZEND_ARG_OBJ_INFO(0, dependency, RuntimeModule, 0)
ZEND_END_ARG_INFO()

ZEND_METHOD(RuntimeModule, __construct);
ZEND_METHOD(RuntimeModule, current);
ZEND_METHOD(RuntimeModule, require);
ZEND_METHOD(RuntimeModule, addDependency);

static const zend_function_entry class_RuntimeModule_methods[] = {
	ZEND_ME(RuntimeModule, __construct, arginfo_class_RuntimeModule___construct, ZEND_ACC_PUBLIC)
	ZEND_ME(RuntimeModule, current, arginfo_class_RuntimeModule_current, ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
	ZEND_ME(RuntimeModule, require, arginfo_class_RuntimeModule_require, ZEND_ACC_PUBLIC)
	ZEND_ME(RuntimeModule, addDependency, arginfo_class_RuntimeModule_addDependency, ZEND_ACC_PUBLIC)
	ZEND_FE_END
};

static zend_class_entry *register_class_RuntimeModule(void)
{
	zend_class_entry ce, *class_entry;

	INIT_CLASS_ENTRY(ce, "RuntimeModule", class_RuntimeModule_methods);
	class_entry = zend_register_internal_class_with_flags(&ce, NULL, ZEND_ACC_FINAL|ZEND_ACC_NO_DYNAMIC_PROPERTIES|ZEND_ACC_NOT_SERIALIZABLE);

	return class_entry;
}
