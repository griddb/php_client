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

#define ARRAY_SIZE(x) ((sizeof(x)/sizeof(0[x])) / ((size_t)(!(sizeof(x) % sizeof(0[x])))))
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
        const char* objTypename = "GSException";
        size_t objTypenameLen = strlen(objTypename);
        // Create a resource
        zval resource;

        SWIG_SetPointerZval(&resource, (void *)exception, $descriptor(griddb::GSException *), 1);
        zval ex;
        zval ctorRv;

        // Create a PHP GSException object
        zend_string * objTypenameZend = zend_string_init(objTypename, objTypenameLen, 0);
        zend_class_entry* ce = zend_lookup_class(objTypenameZend);
        zend_string_release(objTypenameZend);
        if (!ce) {
            SWIG_FAIL();
        }

        object_and_properties_init(&ex, ce, NULL);

        // Constructor, pass resource to constructor argument
        zend_function* constructor = zend_std_get_constructor(Z_OBJ(ex));
        zend_call_method(&ex, ce, &constructor, NULL, 0, &ctorRv, 1, &resource, NULL TSRMLS_CC);
        if (Z_TYPE(ctorRv) != IS_UNDEF) {
            zval_ptr_dtor(&ctorRv);
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
 * Typemaps for get_store() function : support keyword parameter ({"host" : str,
 * "port" : int, "clusterName" : str, "database" : str, "usrname" : str,
 * "password " : str, "notificationMember" : str, "notificationProvider" : str}
 */
%typemap(in, numinputs = 1)
(const char* host, int32_t port, const char* cluster_name, const char* database,
        const char* username, const char* password,
        const char* notification_member, const char* notification_provider)
        (HashTable *arr, HashPosition pos, zval *data) {
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        SWIG_PHP_Error(E_ERROR, "Expected associative array as input");
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
                };
            }
        }
    }
}

/**
 * Typemaps for ContainerInfo : support keyword parameter ({"name" : str,
 * "columnInfoArray" : array, "type" : int, 'rowKey' : boolean, "expiration" : array})
 */
%typemap(in, numinputs = 1) (const GSChar* name, const GSColumnInfo* props,
        int propsCount, GSContainerType type, bool row_key,
        griddb::ExpirationInfo* expiration)
        (HashTable *arr1, HashPosition pos1, zval *data1, HashTable *arr2,
                HashPosition pos2, zval *data2, HashTable *arr3,
                HashPosition pos3, zval *columnName, zval *columnType) {
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        SWIG_PHP_Error(E_ERROR, "Expected associative array as input");
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
                    }
                    $1 = Z_STRVAL_P(data1);
                }
                else if(strcmp(name, "columnInfoArray") == 0) {
                    //Input valid is array only
                    if(Z_TYPE_P(data1) != IS_ARRAY) {
                        SWIG_PHP_Error(E_ERROR, "Expected array as input for property columnInfoArray");
                    }
                    arr2 = Z_ARRVAL_P(data1);
                    int length2 = (int) zend_hash_num_elements(arr2);
                    $3 = length2;
                    if($3 > 0) {
                        $2 = (GSColumnInfo *) malloc($3*sizeof(GSColumnInfo));
                        if($2 == NULL) {
                            SWIG_PHP_Error(E_ERROR, "Memory allocation error");
                        }
                        memset($2, 0x0, $3*sizeof(GSColumnInfo));
                        int i = 0;
                        //Get element "name", "status".
                        for(zend_hash_internal_pointer_reset_ex(arr2, &pos2);
                                (data2 = zend_hash_get_current_data_ex(arr2, &pos2)) != NULL;
                                zend_hash_move_forward_ex(arr2, &pos2)) {
                            if(Z_TYPE_P(data2) != IS_ARRAY) {
                                SWIG_PHP_Error(E_ERROR, "Expected array as elements for columnInfoArray");
                            }
                            arr3 = Z_ARRVAL_P(data2);
                            int length3 = (int) zend_hash_num_elements(arr3);
                            if(length3 != 2) {
                                SWIG_PHP_Error(E_ERROR, "Expected 2 elements for columnInfoArray property");
                            }

                            zend_hash_internal_pointer_reset_ex(arr3, &pos3);
                            if (Z_TYPE_P(columnName = zend_hash_get_current_data_ex(arr3, &pos3)) != IS_STRING) {
                                SWIG_PHP_Error(E_ERROR, "Expected string as column name");
                            }
                            $2[i].name = strdup(Z_STRVAL_P(columnName));

                            zend_hash_move_forward_ex(arr3, &pos3);
                            if (Z_TYPE_P(columnType = zend_hash_get_current_data_ex(arr3, &pos3)) != IS_LONG) {
                                SWIG_PHP_Error(E_ERROR, "Expected an integer as column type");
                            }
                            $2[i].type = Z_LVAL_P(columnType);
                            i++;
                        }
                    }
                }
                else if(strcmp(name, "type") == 0) {
                    if(Z_TYPE_P(data1) != IS_LONG) {
                        SWIG_PHP_Error(E_ERROR, "Invalid value for property type");
                    }
                    $4 = Z_LVAL_P(data1);
                }
                else if(strcmp(name, "rowKey") == 0) {
                    $5 = (bool) zval_is_true(data1);
                }
                else if(strcmp(name, "expiration") == 0) {
                    int res = SWIG_ConvertPtr(data1, (void**)&expiration,
                            $descriptor(griddb::ExpirationInfo*), 0 | 0 );
                    if (!SWIG_IsOK(res)) {
                        SWIG_PHP_Error(E_ERROR, "Invalid value for property expiration");
                    }
                    $6 = (griddb::ExpirationInfo *) expiration;
                } else {
                    SWIG_PHP_Error(E_ERROR, "Invalid Property");
                };
            }
        }
    }
}

%typemap(freearg) (const GSChar* name, const GSColumnInfo* props,
        int propsCount, GSContainerType type, bool row_key,
        griddb::ExpirationInfo* expiration) {
    if ($2) {
        free((void *) $2);
    }
}

%fragment("convertToFieldWithType", "header", fragment = "convertZvalValueToFloat",
        fragment = "convertZvalValueToDouble",
        fragment = "convertZvalValueToGSTimestamp") {
    static bool convertToFieldWithType(GSRow *row, int column, zval* value, GSType type) {
        int res;
        GSResult ret;
        bool vbool;

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
                break;
            }
            case GS_TYPE_BOOL: {
                if(Z_TYPE_P(value) == IS_STRING) {
                    return false;
                }
                bool boolVal;
                boolVal = (bool) zval_is_true(value);
                ret = gsSetRowFieldByBool(row, column, boolVal);
                break;
            }
            case GS_TYPE_BYTE: {
                int64_t byteVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                byteVal = Z_LVAL_P(value);
                if (byteVal < std::numeric_limits<int8_t>::min() ||
                        byteVal > std::numeric_limits<int8_t>::max()) {
                    return false;
                }
                ret = gsSetRowFieldByByte(row, column, byteVal);
                break;
            }
            case GS_TYPE_SHORT: {
                int64_t shortVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                shortVal = Z_LVAL_P(value);
                if (shortVal < std::numeric_limits<int16_t>::min() ||
                        shortVal > std::numeric_limits<int16_t>::max()) {
                    return false;
                }
                ret = gsSetRowFieldByShort(row, column, shortVal);
                break;
            }
            case GS_TYPE_INTEGER: {
                int64_t intVal;
                if(Z_TYPE_P(value) != IS_LONG) {
                    return false;
                }
                intVal = Z_LVAL_P(value);
                if (intVal < std::numeric_limits<int32_t>::min() ||
                        intVal > std::numeric_limits<int32_t>::max()) {
                    return false;
                }
                ret = gsSetRowFieldByInteger(row, column, intVal);
                break;
            }
            case GS_TYPE_FLOAT: {
                float floatVal;
                vbool = convertZvalValueToFloat(value, &floatVal);
                if(!vbool) {
                    return false;
                }
                ret = gsSetRowFieldByFloat(row, column, floatVal);
                break;
            }
            case GS_TYPE_DOUBLE: {
                double doubleVal;
                vbool = convertZvalValueToDouble(value, &doubleVal);
                if(!vbool) {
                    return false;
                }
                ret = gsSetRowFieldByDouble(row, column, doubleVal);
                break;
            }
            case GS_TYPE_TIMESTAMP: {
                GSTimestamp timestampValue;
                vbool = convertZvalValueToGSTimestamp(value, &timestampValue);
                if (!vbool) {
                    return false;
                }
                ret = gsSetRowFieldByTimestamp(row, column, timestampValue);
                break;
            }
            case GS_TYPE_BLOB: {
                GSBlob blobVal;
                int64_t size;
                if(Z_TYPE_P(value) != IS_STRING) {
                    return false;
                }
                blobVal.data = Z_STRVAL_P(value);
                size = Z_STRLEN_P(value);
                blobVal.size = size;
                ret = gsSetRowFieldByBlob(row, column, (const GSBlob *)&blobVal);
                break;
            }
            default:
            return false;
            break;
        }
        return (ret == GS_RESULT_OK);
    }
}

/**
 * Support convert type from Zval value to Double. input in target language can be :
 * float or integer
 */
%fragment("convertZvalValueToDouble", "header") {
    static bool convertZvalValueToDouble(zval* value, double* doubleValPtr) {
        if(Z_TYPE_P(value) == IS_LONG) {
            // input can be integer
            int64_t intVal;
            intVal = Z_LVAL_P(value);
            *doubleValPtr = intVal;
            //When input value is integer, it should be between -9007199254740992(-2^53)/9007199254740992(2^53).
            return (-9007199254740992 <= intVal && 9007199254740992 >= intVal);
        } else if(Z_TYPE_P(value) == IS_DOUBLE) {
            *doubleValPtr = Z_DVAL_P(value);
            return (*doubleValPtr < std::numeric_limits<double>::max() &&
                    *doubleValPtr > -1 *std::numeric_limits<double>::max());
        } else {
            return false;
        }
    }
}

/**
 * Support convert type from Zval value to Float. input in target language can be :
 * float or integer
 */
%fragment("convertZvalValueToFloat", "header") {
    static bool convertZvalValueToFloat(zval* value, float* floatValPtr) {
        if(Z_TYPE_P(value) == IS_LONG) {
            // input can be integer
            int64_t intVal;
            intVal = Z_LVAL_P(value);
            *floatValPtr = intVal;
            //When input value is integer, it should be between -16777216(-2^24)/16777216(2^24).
            return (-16777216 <= intVal && 16777216 >= intVal);
        } else if(Z_TYPE_P(value) == IS_DOUBLE) {
            *floatValPtr = Z_DVAL_P(value);
            return (*floatValPtr < std::numeric_limits<float>::max() &&
                    *floatValPtr > -1 *std::numeric_limits<float>::max());
        } else {
            return false;
        }
    }
}

/**
 * Support convert type from object to GSTimestamp : input in target language can be : datetime object
 */
%fragment("convertZvalValueToGSTimestamp", "header"){
    static bool convertZvalValueToGSTimestamp(zval* datetime, GSTimestamp* timestamp) {
        // Support for checking DateTime class
        zend_class_entry *ce = NULL;
        const char* className = "DateTime";

        // Support for checking DateTime object
        const char* functionName1 = "is_a";
        zval retVal1;
        zval functionNameZval1;
        zval classNameZval;

        // Support for get timestamp with second
        const char* functionName2 = "date_timestamp_get";
        zval retVal2;
        zval functionNameZval2;

        // Support for get timestamp with microsecond
        const char* functionName3 = "date_format";
        const char* formatStr = "u";
        zval retVal3;
        zval functionNameZval3;
        zval formatStrZval;

        // Check DateTime class exist or not
        zend_string *zstrClassName = zend_string_init(className, strlen(className ), 0);
        ce = zend_lookup_class(zstrClassName);
        zend_string_release(zstrClassName);
        if (!ce) {
            return false;
        }

        // Check input in target language is DateTime object
        ZVAL_STRING(&functionNameZval1, functionName1);
        ZVAL_STRING(&classNameZval, className);
        zval params1[2] = {
            *datetime,
            classNameZval
        };
        call_user_function(EG(function_table), NULL,
                &functionNameZval1, &retVal1,
                ARRAY_SIZE(params1), params1 TSRMLS_CC);
        bool output = zval_is_true(&retVal1);
        if (!output) {
            return false;
        };

        // Convert from datetime to timestamp
        // (1)Get timestamp with seconds
        ZVAL_STRING(&functionNameZval2, functionName2);
        zval params2[1] = {*datetime};
        call_user_function(EG(function_table), NULL,
                &functionNameZval2,
                &retVal2, ARRAY_SIZE(params2),
                params2 TSRMLS_CC);
        int64_t timestampSecond = Z_LVAL(retVal2);

        // (2)Get timestamp with microsecond
        ZVAL_STRING(&functionNameZval3, functionName3);
        ZVAL_STRING(&formatStrZval, formatStr);
        zval params3[2] = {
            *datetime,
            formatStrZval
        };
        call_user_function(EG(function_table), NULL,
                &functionNameZval3,
                &retVal3, ARRAY_SIZE(params3),
                params3 TSRMLS_CC);
        int64_t timestampMicroSecond = atoi(Z_STRVAL(retVal3));

        // Convert timestamp to milisecond
        *timestamp = (timestampSecond * 1000) + (timestampMicroSecond/1000);
        return true;
    }
}

/**
 * Typemaps for put_row() function
 */
%typemap(in, fragment = "convertToFieldWithType") (GSRow *row)
        (HashTable *arr, HashPosition pos, zval* data) {
    $1 = NULL;
    const int size = 60;
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        SWIG_PHP_Error(E_ERROR, "Expected an array as input");
    }
    arr = Z_ARRVAL_P(&$input);
    int length = (int) zend_hash_num_elements(arr);
    GSRow *tmpRow = arg1->getGSRowPtr();
    int colNum = arg1->getColumnCount();
    GSType* typeList = arg1->getGSTypeList();

    if(length != colNum) {
        SWIG_PHP_Error(E_ERROR, "Num row is different with container info");
    }

    if(length > 0) {
        for(zend_hash_internal_pointer_reset_ex(arr, &pos);
                (data = zend_hash_get_current_data_ex(arr, &pos)) != NULL;
                zend_hash_move_forward_ex(arr, &pos)) {
            GSType type = typeList[pos];
            if (!(convertToFieldWithType(tmpRow, pos, data, type))) {
                char gsType[size];
                sprintf(gsType, "Invalid value for column %d, type should be : %d", pos, type);
                SWIG_PHP_Error(E_ERROR, gsType);
            }
        }
    }
}

/**
 * Support convert row key Field from zval* in target language to C Object with specific type
 */
%fragment("convertToRowKeyFieldWithType", "header") {
static bool convertToRowKeyFieldWithType(griddb::Field &field, zval* value, GSType type) {
    field.type = type;

    if (Z_TYPE_P(value) == IS_NULL) {
        //Not support NULL
        return false;
    }

    int checkConvert = 0;
    switch (type) {
        case (GS_TYPE_STRING):
            if(Z_TYPE_P(value) != IS_STRING) {
                return false;
            }
            field.value.asString = strdup(Z_STRVAL_P(value));
            break;
        case (GS_TYPE_INTEGER):
            int64_t intVal;
            if (Z_TYPE_P(value) != IS_LONG) {
                return false;
            }
            intVal = Z_LVAL_P(value);
            if (intVal < std::numeric_limits<int32_t>::min() ||
                    intVal > std::numeric_limits<int32_t>::max()) {
                return false;
            }
            field.value.asInteger = intVal;
            break;
        case (GS_TYPE_LONG):
            if(Z_TYPE_P(value) != IS_LONG) {
                return false;
            }
            field.value.asLong = Z_LVAL_P(value);
            break;
        case (GS_TYPE_TIMESTAMP):
            return convertZvalValueToGSTimestamp(value, &field.value.asTimestamp);
            break;
        default:
            //Not support for now
            return false;
            break;
    }
    return true;
}
}

/*
* Typemap for get_row
*/
%typemap(in, fragment = "convertToRowKeyFieldWithType")
        (griddb::Field* keyFields)(griddb::Field field) {
    $1 = &field;
    if (Z_TYPE_P(&$input) == IS_NULL) {
        $1->type = GS_TYPE_NULL;
    } else {
        GSType* typeList = arg1->getGSTypeList();
        GSType type = typeList[0];
        if (!convertToRowKeyFieldWithType(*$1, &$input, type)) {
            SWIG_PHP_Error(E_ERROR, "Can not convert to row field");
        }
    }
}

%typemap(in, numinputs = 0) (GSRow *rowdata) {
    $1 = NULL;
}

/**
 * Support convert data from GSRow* row to zval array
 */
%fragment("getRowFields", "header", fragment = "convertTimestampToObject") {
static bool getRowFields(GSRow* row, int columnCount,
        GSType* typeList, int* columnError,
        GSType* fieldTypeError, zval* outList) {
    GSResult ret;
    bool retVal = true;
    for (int i = 0; i < columnCount; i++) {
        //Check NULL value
        GSBool nullValue;
        ret = gsGetRowFieldNull(row, (int32_t) i, &nullValue);
        if (ret != GS_RESULT_OK) {
            *columnError = i;
            retVal = false;
            *fieldTypeError = GS_TYPE_NULL;
            return retVal;
        }
        if (nullValue) {
            add_index_zval(outList, i, NULL);
            continue;
        }
        switch(typeList[i]) {
        case GS_TYPE_LONG: {
            int64_t longValue;
            ret = gsGetRowFieldAsLong(row, (int32_t) i, &longValue);
            add_index_long(outList, i, longValue);
            break;
        }
        case GS_TYPE_STRING: {
            GSChar* stringValue;
            ret = gsGetRowFieldAsString(row, (int32_t) i, (const GSChar **)&stringValue);
            add_index_string(outList, i, stringValue);
            break;
        }
        case GS_TYPE_BLOB: {
            GSBlob blobValue = {0};
            ret = gsGetRowFieldAsBlob(row, (int32_t) i, &blobValue);
            add_index_string(outList, i, (char*)blobValue.data);
            break;
        }
        case GS_TYPE_BOOL: {
            GSBool boolValue;
            bool boolVal;
            ret = gsGetRowFieldAsBool(row, (int32_t) i, &boolValue);
            if(boolValue == GS_TRUE){
                boolVal = true;
            } else {
                boolVal = false;
            }
            add_index_bool(outList, i, boolVal);
            break;
        }
        case GS_TYPE_INTEGER: {
            int32_t intValue;
            ret = gsGetRowFieldAsInteger(row, (int32_t) i, &intValue);
            add_index_long(outList, i, intValue);
            break;
        }
        case GS_TYPE_FLOAT: {
            float floatValue;
            ret = gsGetRowFieldAsFloat(row, (int32_t) i, &floatValue);
            add_index_double(outList, i, floatValue);
            break;
        }
        case GS_TYPE_DOUBLE: {
            double doubleValue;
            ret = gsGetRowFieldAsDouble(row, (int32_t) i, &doubleValue);
            add_index_double(outList, i, doubleValue);
            break;
        }
        case GS_TYPE_TIMESTAMP: {
            GSTimestamp timestampValue;
            ret = gsGetRowFieldAsTimestamp(row, (int32_t) i, &timestampValue);
            zval dateTime;
            convertTimestampToObject(&timestampValue, &dateTime);
            add_index_zval(outList, i, &dateTime);
            break;
        }
        case GS_TYPE_BYTE: {
            int8_t byteValue;
            ret = gsGetRowFieldAsByte(row, (int32_t) i, &byteValue);
            add_index_long(outList, i, byteValue);
            break;
        }
        case GS_TYPE_SHORT: {
            int16_t shortValue;
            ret = gsGetRowFieldAsShort(row, (int32_t) i, &shortValue);
            add_index_long(outList, i, shortValue);
            break;
        }
        default: {
            // NOT OK
            ret = -1;
            break;
        }
        }
        if (ret != GS_RESULT_OK) {
            *columnError = i;
            *fieldTypeError = typeList[i];
            retVal = false;
            return retVal;
        }
    }
    return retVal;
}
}

/**
 * Support convert data from timestamp to DateTime object in target language
 */
%fragment("convertTimestampToObject", "header") {
static void convertTimestampToObject(GSTimestamp* timestamp, zval* dateTime) {
    const int size = 60;
    char timeStr[size];
    zval functionNameZval;
    zval formatStringZval;
    zval formattedTimePhp;
    const char* functionName = "date_create_from_format";
    const char* formatString = "U.u";

    // Get time with seconds
    int64_t second = *timestamp/1000;

    // Get time with microSeconds
    int64_t microSecond = (*timestamp % 1000) * 1000;
    sprintf(timeStr, "%ld.%06d", second, microSecond);

    ZVAL_STRING(&functionNameZval, functionName);
    ZVAL_STRING(&formatStringZval, formatString);
    ZVAL_STRING(&formattedTimePhp, timeStr);
    zval params[2] = {
        formatStringZval,
        formattedTimePhp
    };

    call_user_function(EG(function_table), NULL, &functionNameZval, dateTime,
            ARRAY_SIZE(params), params TSRMLS_CC);
}
}

/*
* This typemap argument out does not get data from argument "GSRow *rowdata"
* The argument "GSRow *rowdata" is not used in the function Container::get(), it only for the purpose of typemap matching pattern
* The actual output data is store in class member and can be get by function getGSRowPtr()
*/
//%typemap(argout, fragment = "getRowFields") (GSRow *rowdata) {
%typemap(argout, fragment = "getRowFields") (GSRow *rowdata) {
    if (result == GS_FALSE) {
        RETVAL_NULL();
    } else {
        bool retVal;
        int errorColumn;
        GSType errorType;
        const int size = 60;

        // Get row pointer
        GSRow* row = arg1->getGSRowPtr();

        // Get row fields
        array_init_size(return_value, arg1->getColumnCount());
        retVal = getRowFields(row, arg1->getColumnCount(),
                arg1->getGSTypeList(),
                &errorColumn, &errorType,
                return_value);

        if (retVal == false) {
            char errorMsg[size];
            sprintf(errorMsg, "Can't get data for field %d with type %d", errorColumn, errorType);
            SWIG_PHP_Error(E_ERROR, errorMsg);
        }
    }
}

/**
 * Type map for Rowset::next()
 */
%typemap(in, numinputs = 0) (GSRowSetType* type, bool* hasNextRow,
    griddb::QueryAnalysisEntry** queryAnalysis, griddb::AggregationResult** aggResult)
    (GSRowSetType typeTmp, bool hasNextRowTmp,
            griddb::QueryAnalysisEntry* queryAnalysisTmp = NULL, griddb::AggregationResult* aggResultTmp = NULL) {
    $1 = &typeTmp;
    hasNextRowTmp = true;
    $2 = &hasNextRowTmp;
    $3 = &queryAnalysisTmp;
    $4 = &aggResultTmp;
}

%typemap(argout, fragment = "getRowFields") (GSRowSetType* type, bool* hasNextRow,
    griddb::QueryAnalysisEntry** queryAnalysis, griddb::AggregationResult** aggResult) {

    const int size = 60;
    switch (*$1) {
        case (GS_ROW_SET_CONTAINER_ROWS): {
            bool retVal;
            int errorColumn;
            if (*$2 == false) {
                RETURN_NULL();
            } else {
                GSRow* row = arg1->getGSRowPtr();
                array_init_size(return_value, arg1->getColumnCount());
                GSType errorType;
                retVal = getRowFields(row, arg1->getColumnCount(),
                        arg1->getGSTypeList(),
                        &errorColumn, &errorType, return_value);
                if (retVal == false) {
                    char errorMsg[size];
                    sprintf(errorMsg, "Can't get data for field %d with type%d", errorColumn, errorType);
                    SWIG_PHP_Error(E_ERROR, errorMsg);
                }
            }
            break;
        }
        case (GS_ROW_SET_AGGREGATION_RESULT): {
            SWIG_SetPointerZval(return_value, (void *) (*$4), 
                    $descriptor(griddb::AggregationResult *), SWIG_CAST_NEW_MEMORY);

            break;
        }
        case (GS_ROW_SET_QUERY_ANALYSIS): {
            // Not support now
            break;
        }
        default: {
            SWIG_PHP_Error(E_ERROR, "Invalid type");
            break;
        }
    }
}

/*
* Typemap for get function in AggregationResult class
*/
%typemap(in, numinputs = 0) (griddb::Field *agValue) (griddb::Field tmpAgValue){
    $1 = &tmpAgValue;
}
%typemap(argout) (griddb::Field *agValue) {
    int i;
    switch ($1->type) {
        case GS_TYPE_LONG: {
            RETVAL_LONG($1->value.asLong);
            break;
        }
        case GS_TYPE_DOUBLE: {
            RETVAL_DOUBLE($1->value.asDouble);
            break;
        }
        case GS_TYPE_TIMESTAMP:
            convertTimestampToObject(&($1->value.asTimestamp), return_value);
        default:
            RETURN_NULL();
    }
}


