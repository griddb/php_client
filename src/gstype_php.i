/*
    Copyright (c) 2017 TOSHIBA Digital Solutions Corporation.

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

        http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
*/

%{
#include "zend_interfaces.h"
%}

//Read only attribute Container::type
%include <attribute.i>
//%attribute(griddb::Store, int, type_data, get_data);
//Read only attribute ContainerInfo::name
%attribute(griddb::ContainerInfo, GSChar*, name, get_name, set_name);
//Read only attribute ContainerInfo::type
%attribute(griddb::ContainerInfo, GSContainerType, type, get_type, set_type);
//Read only attribute ContainerInfo::rowKey
%attribute(griddb::ContainerInfo, bool, rowKey, get_row_key_assigned, set_row_key_assigned);
//Read only attribute ContainerInfo::columnInfoArray
%attributeval(griddb::ContainerInfo, ColumnInfoList, columnInfoArray, get_column_info_list, set_column_info_list);
//Read only attribute ContainerInfo::expiration
%attribute(griddb::ContainerInfo, griddb::ExpirationInfo*, expiration, get_expiration_info, set_expiration_info);
//Read only attribute ExpirationInfo::time
%attribute(griddb::ExpirationInfo, int, time, get_time, set_time);
//Read only attribute ExpirationInfo::unit
%attribute(griddb::ExpirationInfo, GSTimeUnit, unit, get_time_unit, set_time_unit);
//Read only attribute ExpirationInfo::divisionCount
%attribute(griddb::ExpirationInfo, int, divisionCount, get_division_count, set_division_count);

// rename all method to camel cases
%rename(getErrorMessage) griddb::GSException::get_message;
%rename("%(lowercamelcase)s", %$isfunction) "";

/*
 * ignore unnecessary functions
 */
%ignore griddb::ContainerInfo::ContainerInfo(GSContainerInfo* containerInfo);
%ignore griddb::GSException::get_code;

/**
 * Support throw exception in PHP language
 */
%fragment("throwGSException", "header") {
    static void throwGSException(griddb::GSException* exception) {
        const char* obj_typename = "GSException";
        size_t obj_typename_len = strlen(obj_typename);
        // Create a resource
        zval resource;

        SWIG_SetPointerZval(&resource, (void *)exception, $descriptor(griddb::GSException *), 1);
        zval ex;
        zval ctor_rv;

        // Create a PHP GSException object
        zend_string * obj_typename_zend = zend_string_init(obj_typename, obj_typename_len, 0);
        zend_class_entry* ce = zend_lookup_class(obj_typename_zend);
        zend_string_release(obj_typename_zend);
        if (!ce) {
            SWIG_FAIL();
        }

        object_and_properties_init(&ex, ce, NULL);

        // Constructor, pass resource to constructor argument
        zend_function* constructor = zend_std_get_constructor(Z_OBJ(ex));
        zend_call_method(&ex, ce, &constructor, NULL, 0, &ctor_rv, 1, &resource, NULL TSRMLS_CC);
        if (Z_TYPE(ctor_rv) != IS_UNDEF) {
            zval_ptr_dtor(&ctor_rv);
        }

        // Throw
        zend_throw_exception_object(&ex);
    }
}
%typemap(throws, fragment="throwGSException") griddb::GSException %{
    griddb::GSException* tmpException = new griddb::GSException(&$1);
    throwGSException(tmpException);
    return;
    %}

/**
 * Typemaps for get_store() function
 */
%typemap(in, numinputs = 1)
(const char* host, int32_t port, const char* cluster_name, const char* database, const char* username, const char* password,
        const char* notification_member, const char* notification_provider) (HashTable *arr, HashPosition pos, zval *data) {
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        SWIG_PHP_Error(E_ERROR, "Expected associative array as input");
        SWIG_FAIL();
    }

    arr = Z_ARRVAL_P(&$input);
    int length = (int) zend_hash_num_elements(arr);
    char* name = 0;
    $1 = NULL;
    $2 = 0;
    $3 = NULL;
    $4 = NULL;
    $5 = NULL;
    $6 = NULL;
    $7 = NULL;
    $8 = NULL;
    if (length > 0) {
        zend_string *key;
        int key_len;
        long index;
        for(zend_hash_internal_pointer_reset_ex(arr, &pos);
                (data = zend_hash_get_current_data_ex(arr, &pos)) != NULL;
                zend_hash_move_forward_ex(arr, &pos)) {
            if(zend_hash_get_current_key_ex(arr, &key, (zend_ulong*)&index, &pos) == HASH_KEY_IS_STRING) {
                name = ZSTR_VAL(key);
                if(strcmp(name, "host") == 0) {
                    $1 = Z_STRVAL_P(data);
                }
                else if(strcmp(name, "port") == 0) {
                    //Input valid is number only
                    {
                        if(Z_TYPE_P(data) == IS_LONG) {
                            $2 = Z_LVAL_P(data);
                        } else {
                            SWIG_PHP_Error(E_ERROR, "Expected port number input as int type");
                            SWIG_FAIL();
                        }
                    }
                }
                else if(strcmp(name, "clusterName") == 0) {
                    $3 = Z_STRVAL_P(data);
                }
                else if(strcmp(name, "database") == 0) {
                    $4 = Z_STRVAL_P(data);
                }
                else if(strcmp(name, "username") == 0) {
                    $5 = Z_STRVAL_P(data);
                }
                else if(strcmp(name, "password") == 0) {
                    $6 = Z_STRVAL_P(data);
                }
                else if(strcmp(name, "notificationMember") == 0) {
                    $7 = Z_STRVAL_P(data);
                }
                else if(strcmp(name, "notificationProvider") == 0) {
                    $8 = Z_STRVAL_P(data);
                } else {
                    SWIG_PHP_Error(E_ERROR, "Invalid Property");
                    SWIG_FAIL();
                };
            }
        }
    }
}

%typemap(typecheck,precedence = SWIG_TYPECHECK_CHAR_ARRAY) (const char* host, int32_t port, const char* cluster_name, const char* database, const char* username, const char* password,
        const char* notification_member, const char* notification_provider) {
    $1 = (Z_TYPE_P(&$input) == IS_ARRAY) ? 1 : 0;
}

/**
 * Typemaps for ContainerInfo : support keyword parameter ({"name" : str, "columnInfoArray" : array, "type" : int, 'rowKey':boolean, "expiration" : array})
 */
%typemap(in) (const GSChar* name, const GSColumnInfo* props,
        int propsCount, GSContainerType type, bool row_key, griddb::ExpirationInfo* expiration)
(HashTable *arr1, HashPosition pos1, zval *data1, HashTable *arr2, HashPosition pos2, zval *data2, HashTable *arr3, HashPosition pos3, zval *columnName, zval *columnType) {
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        SWIG_PHP_Error(E_ERROR, "Expected associative array as input");
        SWIG_FAIL();
    }

    arr1 = Z_ARRVAL_P(&$input);
    int length1 = (int) zend_hash_num_elements(arr1);
    char* name = 0;
    //Create $1, $2, $3, $3, $4, $5, $6 with default value
    $1 = NULL;
    $2 = NULL;
    $3 = 0;
    $4 = GS_CONTAINER_COLLECTION;
    $5 = true;//defautl value rowKey = true
    $6 = NULL;
    bool boolVal, vbool;
    griddb::ExpirationInfo* expiration;
    if (length1 > 0) {
        zend_string *key;
        int key_len;
        long index;
        for(zend_hash_internal_pointer_reset_ex(arr1, &pos1);
                (data1 = zend_hash_get_current_data_ex(arr1, &pos1)) != NULL;
                zend_hash_move_forward_ex(arr1, &pos1)) {
            if(zend_hash_get_current_key_ex(arr1, &key, (zend_ulong*)&index, &pos1) == HASH_KEY_IS_STRING) {
                name = ZSTR_VAL(key);
                if(strcmp(name, "name") == 0) {
                    if(Z_TYPE_P(data1) != IS_STRING) {
                        SWIG_PHP_Error(E_ERROR, "Invalid value for property name");
                        SWIG_FAIL();
                    }
                    $1 = Z_STRVAL_P(data1);
                }
                else if(strcmp(name, "columnInfoArray") == 0) {
                    //Input valid is array only
                    if(Z_TYPE_P(data1) != IS_ARRAY) {
                        SWIG_PHP_Error(E_ERROR, "Expected array as input for property columnInfoArray");
                        SWIG_FAIL();
                    }
                    arr2 = Z_ARRVAL_P(data1);
                    int length2 = (int) zend_hash_num_elements(arr2);
                    $3 = length2;
                    if($3 > 0) {
                        $2 = (GSColumnInfo *) malloc($3*sizeof(GSColumnInfo));
                        if($2 == NULL) {
                            SWIG_PHP_Error(E_ERROR, "Memory allocation error");
                            SWIG_FAIL();
                        }
                        memset($2, 0x0, $3*sizeof(GSColumnInfo));
                        int i = 0;
                        //Get element "name", "status".
                        for(zend_hash_internal_pointer_reset_ex(arr2, &pos2);
                                (data2 = zend_hash_get_current_data_ex(arr2, &pos2)) != NULL;
                                zend_hash_move_forward_ex(arr2, &pos2)) {
                            if(Z_TYPE_P(data2) != IS_ARRAY) {
                                SWIG_PHP_Error(E_ERROR, "Expected array as elements for columnInfoArray");
                                SWIG_FAIL();
                            }
                            arr3 = Z_ARRVAL_P(data2);
                            int length3 = (int) zend_hash_num_elements(arr3);
                            if(length3 != 2) {
                                SWIG_PHP_Error(E_ERROR, "Expected 2 elements for columnInfoArray property");
                                SWIG_FAIL();
                            }

                            zend_hash_internal_pointer_reset_ex(arr3, &pos3);
                            if (Z_TYPE_P(columnName = zend_hash_get_current_data_ex(arr3, &pos3)) != IS_STRING) {
                                SWIG_PHP_Error(E_ERROR, "Expected string as column name");
                                SWIG_FAIL();
                            }
                            $2[i].name = strdup(Z_STRVAL_P(columnName));

                            zend_hash_move_forward_ex(arr3, &pos3);
                            if (Z_TYPE_P(columnType = zend_hash_get_current_data_ex(arr3, &pos3)) != IS_LONG) {
                                SWIG_PHP_Error(E_ERROR, "Expected an integer as column type");
                                SWIG_FAIL();
                            }
                            $2[i].type = Z_LVAL_P(columnType);
                            i++;
                        }
                    }
                }
                else if(strcmp(name, "type") == 0) {
                    if(Z_TYPE_P(data1) != IS_LONG) {
                        SWIG_PHP_Error(E_ERROR, "Invalid value for property type");
                        SWIG_FAIL();
                    }
                    $4 = Z_LVAL_P(data1);
                }
                else if(strcmp(name, "rowKey") == 0) {
                    $5 = (bool) zval_is_true(data1);
                }
                else if(strcmp(name, "expiration") == 0) {
                    int res = SWIG_ConvertPtr(data1, (void**)&expiration, $descriptor(griddb::ExpirationInfo*), 0 | 0 );
                    if (!SWIG_IsOK(res)) {
                        SWIG_PHP_Error(E_ERROR, "Invalid value for property expiration");
                        SWIG_FAIL();
                    }
                    $6 = (griddb::ExpirationInfo *) expiration;
                } else {
                    SWIG_PHP_Error(E_ERROR, "Invalid Property");
                    SWIG_FAIL();
                };
            }
        }
    }
}

%typemap(freearg) (const GSChar* name, const GSColumnInfo* props,
        int propsCount, GSContainerType type, bool row_key, griddb::ExpirationInfo* expiration) {
    if ($2) {
        free((void *) $2);
    }
}

%typemap(typecheck, precedence = SWIG_TYPECHECK_CHAR_ARRAY) (const GSChar* name, const GSColumnInfo* props,
        int propsCount, GSContainerType type, bool row_key, griddb::ExpirationInfo* expiration) {
    $1 = (Z_TYPE_P(&$input) == IS_ARRAY) ? 1 : 0;
}

%fragment("convertToFieldWithType", "header") {
    static bool convertToFieldWithType(GSRow *row, int column, zval* value, GSType type) {
        int res;
        GSResult ret;
        if(Z_TYPE_P(value) == IS_NULL) {
            ret = gsSetRowFieldNull(row, column);
            return (ret == GS_RESULT_OK);
        }
        switch(type) {
            case GS_TYPE_STRING: {
                GSChar* stringVal;
                if(Z_TYPE_P(value) != IS_STRING) {
                    return false;
                }
                stringVal = Z_STRVAL_P(value);
                ret = gsSetRowFieldByString(row, column, stringVal);
                break;
            }
            case GS_TYPE_LONG: {
                int64_t longVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                longVal = Z_LVAL_P(value);
                ret = gsSetRowFieldByLong(row, column, longVal);
            }
            case GS_TYPE_BOOL: {
                bool boolVal;
                boolVal = (bool) zval_is_true(data1);
                ret = gsSetRowFieldByBool(row, column, boolVal);
            }
            case GS_TYPE_BYTE: {
                int8_t byteVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                byteVal = Z_LVAL_P(value);
                if (byteVal < std::numeric_limits<int8_t>::min() || 
                        byteVal > std::numeric_limits<int8_t>::max()) {
                    return false;
                }
                ret = gsSetRowFieldByByte(row, column, byteVal);
            }
            case GS_TYPE_SHORT: {
                int16_t byteVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                shortVal = Z_LVAL_P(value);
                ret = gsSetRowFieldByShort(row, column, shortVal);
            }
            case GS_TYPE_INTEGER: {
                int32_t intVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                intVal = Z_LVAL_P(value);
                ret = gsSetRowFieldByInteger(row, column, intVal);
            }
            case GS_TYPE_FLOAT: {
                int32_t intVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                intVal = Z_LVAL_P(value);
                ret = gsSetRowFieldByInteger(row, column, intVal);
            }
            case GS_TYPE_DOUBLE: {
                int32_t intVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                intVal = Z_LVAL_P(value);
                ret = gsSetRowFieldByInteger(row, column, intVal);
            }
            case GS_TYPE_DOUBLE: {
                int32_t intVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                intVal = Z_LVAL_P(value);
                ret = gsSetRowFieldByInteger(row, column, intVal);
            }
            case GS_TYPE_TIMESTAMP: {
                int32_t intVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                intVal = Z_LVAL_P(value);
                ret = gsSetRowFieldByInteger(row, column, intVal);
            }
            case GS_TYPE_BLOB: {
                int32_t intVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                intVal = Z_LVAL_P(value);
                ret = gsSetRowFieldByInteger(row, column, intVal);
            }
        }
    }
}

