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

#define ARRAY_SIZE(x) \
    ((sizeof(x)/sizeof(0[x])) / ((size_t)(!(sizeof(x) % sizeof(0[x])))))
%{
#include "zend_interfaces.h"
%}
/*
 * Change method names in PHP
 */
// Rename all method to camel cases
%rename("%(lowercamelcase)s", %$isfunction) "";
// "getMessage" is a default final method of Exception class in PHP.
// So convert name into getErrorMessage
%rename(getErrorMessage) griddb::GSException::get_message;
// "default" is a PHP keyword. So convert name into DEFAULT_TYPE
%rename(DEFAULT_TYPE) griddb::IndexType::DEFAULT;

/*
 * Use attribute in PHP language
 */
%include <attribute.i>

// Read only attribute Container::type
%attribute(griddb::Container, int, type, get_type);
// Read only attribute GSException::isTimeout
%attribute(griddb::GSException, bool, isTimeout, is_timeout);
// Read only attribute RowSet::size
%attribute(griddb::RowSet, int32_t, size, size);
// Read only attribute RowSet::type
%attribute(griddb::RowSet, GSRowSetType, type, type);
// Read only attribute ContainerInfo::name
%attribute(griddb::ContainerInfo, GSChar*, name, get_name, set_name);
// Read only attribute ContainerInfo::type
%attribute(griddb::ContainerInfo, GSContainerType, type, get_type, set_type);
// Read only attribute ContainerInfo::rowKey
%attribute(griddb::ContainerInfo, bool, rowKey,
           get_row_key_assigned, set_row_key_assigned);
// Read only attribute ContainerInfo::columnInfoArray
%attributeval(griddb::ContainerInfo, ColumnInfoList, columnInfoArray,
              get_column_info_list, set_column_info_list);
// Read only attribute ContainerInfo::expiration
%attribute(griddb::ContainerInfo, griddb::ExpirationInfo*, expiration,
           get_expiration_info, set_expiration_info);
// Read only attribute ExpirationInfo::time
%attribute(griddb::ExpirationInfo, int, time, get_time, set_time);
// Read only attribute ExpirationInfo::unit
%attribute(griddb::ExpirationInfo, GSTimeUnit, unit,
           get_time_unit, set_time_unit);
// Read only attribute ExpirationInfo::divisionCount
%attribute(griddb::ExpirationInfo, int, divisionCount,
           get_division_count, set_division_count);
// Read only attribute Store::partitionController
%attribute(griddb::Store, griddb::PartitionController*,
           partitionController, partition_info);
// Read only attribute PartitionController::partitionCount
%attribute(griddb::PartitionController, int, partitionCount,
           get_partition_count);

/*
 * Ignore unnecessary functions
 */
%ignore griddb::ContainerInfo::ContainerInfo(GSContainerInfo* containerInfo);
%ignore griddb::GSException::get_code;
%ignore ColumnInfoList;

/**
 * Support throw exception in PHP language
 */
%fragment("throwGSException", "header") {
static void throwGSException(griddb::GSException* exception) {
    const char* objTypename = "GSException";
    size_t objTypenameLen = strlen(objTypename);
    // Create a resource
    zval resource;

    SWIG_SetPointerZval(&resource, reinterpret_cast<void *>(exception),
                        $descriptor(griddb::GSException *), 1);
    zval ex;
    zval ctorRv;

    // Create a PHP GSException object
    zend_string * objTypenameZend = zend_string_init(objTypename,
                                                     objTypenameLen, 0);
    zend_class_entry* ce = zend_lookup_class(objTypenameZend);
    zend_string_release(objTypenameZend);
    if (!ce) {
        SWIG_FAIL();
    }

    object_and_properties_init(&ex, ce, NULL);

    // Constructor, pass resource to constructor argument
    zend_function* constructor = zend_std_get_constructor(Z_OBJ(ex));
    zend_call_method(&ex, ce, &constructor, NULL, 0, &ctorRv,
                     1, &resource, NULL TSRMLS_CC);
    if (Z_TYPE(ctorRv) != IS_UNDEF) {
        zval_ptr_dtor(&ctorRv);
    }

    // Throw
    zend_throw_exception_object(&ex);
    }
}

%typemap(throws, fragment = "throwGSException") griddb::GSException %{
    griddb::GSException* tmpException = new griddb::GSException(&$1);
    throwGSException(tmpException);
    return;
%}

/**
 * Typemaps for get_store() function : support keyword parameter ("host" : str,
 * "port" : int, "clusterName" : str, "database" : str, "username" : str,
 * "password " : str, "notificationMember" : str, "notificationProvider" : str)
 */
%typemap(in, numinputs = 1)
        (const char* host, int32_t port, const char* cluster_name,
                const char* database, const char* username,
                const char* password, const char* notification_member,
                const char* notification_provider)
        (HashTable *arr, HashPosition pos, zval *data) {
    if (Z_TYPE_P(&$input) != IS_ARRAY) {
        SWIG_exception(E_ERROR, "Expected associative array as input");
    }

    arr = Z_ARRVAL_P(&$input);
    int length = zend_hash_num_elements(arr);
    char* name = 0;
    // Create $1, $2, $3, $3, $4, $5, $6, $7, $8 with default value
    $1 = NULL;
    $2 = 0;
    $3 = NULL;
    $4 = NULL;
    $5 = NULL;
    $6 = NULL;
    $7 = NULL;
    $8 = NULL;

    if (length == 0) {
        SWIG_exception(E_ERROR, "Expected not empty array as input");
    }

    zend_string *key;
    int key_len;
    ulong index;
    for (zend_hash_internal_pointer_reset_ex(arr, &pos);
            (data = zend_hash_get_current_data_ex(arr, &pos)) != NULL;
            zend_hash_move_forward_ex(arr, &pos)) {
        if (zend_hash_get_current_key_ex(arr, &key, &index,
                                         &pos) != HASH_KEY_IS_STRING) {
            SWIG_exception(E_ERROR, "Expected string as input for key");
        }

        name = ZSTR_VAL(key);
        if (strcmp(name, "host") == 0) {
            if (Z_TYPE_P(data) != IS_STRING) {
                SWIG_exception(E_ERROR, "Expected string as"
                    " input for host property");
            }
            $1 = Z_STRVAL_P(data);
        } else if (strcmp(name, "port") == 0) {
            // Input valid is number only
            if (Z_TYPE_P(data) != IS_LONG) {
                SWIG_exception(E_ERROR, "Expected integer as"
                    " input for port number property");
            }
            $2 = Z_LVAL_P(data);
        } else if (strcmp(name, "clusterName") == 0) {
            if (Z_TYPE_P(data) != IS_STRING) {
                SWIG_exception(E_ERROR, "Expected string as"
                    " input for clusterName property");
            }
            $3 = Z_STRVAL_P(data);
        } else if (strcmp(name, "database") == 0) {
            if (Z_TYPE_P(data) != IS_STRING) {
                SWIG_exception(E_ERROR, "Expected string as"
                    " input for database property");
            }
            $4 = Z_STRVAL_P(data);
        } else if (strcmp(name, "username") == 0) {
            if (Z_TYPE_P(data) != IS_STRING) {
                SWIG_exception(E_ERROR, "Expected string as"
                    " input for username property");
            }
            $5 = Z_STRVAL_P(data);
        } else if (strcmp(name, "password") == 0) {
            if (Z_TYPE_P(data) != IS_STRING) {
                SWIG_exception(E_ERROR, "Expected string as"
                    " input for password property");
            }
            $6 = Z_STRVAL_P(data);
        } else if (strcmp(name, "notificationMember") == 0) {
            if (Z_TYPE_P(data) != IS_STRING) {
                SWIG_exception(E_ERROR, "Expected string as"
                    " input for notificationMember property");
            }
            $7 = Z_STRVAL_P(data);
        } else if (strcmp(name, "notificationProvider") == 0) {
            if (Z_TYPE_P(data) != IS_STRING) {
                SWIG_exception(E_ERROR, "Expected string as"
                    " input for host notificationProvider property");
            }
            $8 = Z_STRVAL_P(data);
        } else {
            SWIG_exception(E_ERROR, "Invalid Property");
        }
    }
}

/**
 * Typemaps for ContainerInfo : support keyword parameter ("name" : str,
 * "columnInfoArray" : array, "type" : int, 'rowKey' : boolean,
 * "expiration" : expiration object)
 */
%typemap(in, numinputs = 1, fragment = "freeArgContainerInfo")
        (const GSChar* name, const GSColumnInfo* props, int propsCount,
                GSContainerType type, bool row_key,
                griddb::ExpirationInfo* expiration)
        (HashTable *arrContainerInfo, HashPosition posContainerInfo,
                zval *dataContainerInfo, HashTable *arrColumnInfoArray,
                HashPosition posColumnInfoArray, zval *dataColumnInfoArray,
                HashTable *arrColumnInfo, HashPosition posColumnInfo,
                zval *columnName, zval *columnType) {
    if (Z_TYPE_P(&$input) != IS_ARRAY) {
        SWIG_exception(E_ERROR, "Expected associative array as input");
    }

    char* name = 0;
    // Create $1, $2, $3, $3, $4, $5, $6 with default value
    $1 = NULL;
    $2 = NULL;
    $3 = 0;
    $4 = GS_CONTAINER_COLLECTION;
    $5 = true;  // default value rowKey = true
    $6 = NULL;
    griddb::ExpirationInfo* expiration;

    // Fetch the hash table from a zval input
    arrContainerInfo = Z_ARRVAL_P(&$input);
    int sizeOfContainerInfo = zend_hash_num_elements(arrContainerInfo);

    if (sizeOfContainerInfo == 0) {
        SWIG_exception(E_ERROR, "Expected not empty array as input"
                " for ContainerInfo");
    }

    zend_string *key;
    int key_len;
    ulong index;
    for (zend_hash_internal_pointer_reset_ex(arrContainerInfo, &posContainerInfo);
            (dataContainerInfo = zend_hash_get_current_data_ex(arrContainerInfo, &posContainerInfo)) != NULL;
            zend_hash_move_forward_ex(arrContainerInfo, &posContainerInfo)) {
        if (zend_hash_get_current_key_ex(arrContainerInfo, &key, &index, &posContainerInfo) != HASH_KEY_IS_STRING) {
            freeArgContainerInfo($2);
            SWIG_exception(E_ERROR, "Expected string as input for key");
        }

        name = ZSTR_VAL(key);
        if (strcmp(name, "name") == 0) {
            if (Z_TYPE_P(dataContainerInfo) != IS_STRING) {
                freeArgContainerInfo($2);
                SWIG_exception(E_ERROR, "Expected string as input"
                        " for name property");
            }
            $1 = Z_STRVAL_P(dataContainerInfo);
        } else if (strcmp(name, "columnInfoArray") == 0) {
            // Input valid is array only
            if (Z_TYPE_P(dataContainerInfo) != IS_ARRAY) {
                freeArgContainerInfo($2);
                SWIG_exception(E_ERROR, "Expected array as input"
                        " for columnInfo property");
            }
            // Fetch the hash table from a zval
            arrColumnInfoArray = Z_ARRVAL_P(dataContainerInfo);
            int sizeOfColumnInfoArray = zend_hash_num_elements(arrColumnInfoArray);
            $3 = sizeOfColumnInfoArray;
            if ($3 == 0) {
                freeArgContainerInfo($2);
                SWIG_exception(E_ERROR, "Expected not empty array");
            }
            $2 = new GSColumnInfo[$3];
            if ($2 == NULL) {
                SWIG_exception(E_ERROR, "Memory allocation error");
            }
            memset($2, 0x0, $3*sizeof(GSColumnInfo));

            // Get name and type of column
            int i = 0;
            for (zend_hash_internal_pointer_reset_ex(arrColumnInfoArray, &posColumnInfoArray);
                    (dataColumnInfoArray = zend_hash_get_current_data_ex(arrColumnInfoArray, &posColumnInfoArray)) != NULL;
                    zend_hash_move_forward_ex(arrColumnInfoArray, &posColumnInfoArray)) {
                if (Z_TYPE_P(dataColumnInfoArray) != IS_ARRAY) {
                    freeArgContainerInfo($2);
                    SWIG_exception(E_ERROR, "Expected array property as"
                            " ColumnInfo element");
                }
                // Fetch the hash table from a zval
                arrColumnInfo = Z_ARRVAL_P(dataColumnInfoArray);
                int sizeOfColumnInfo = zend_hash_num_elements(arrColumnInfo);
                if (sizeOfColumnInfo != 2) {
                    freeArgContainerInfo($2);
                    SWIG_exception(E_ERROR, "Expected two elements for"
                            " columnInfo property");
                }

                // Get column name
                zend_hash_internal_pointer_reset_ex(arrColumnInfo,
                                                    &posColumnInfo);
                if (Z_TYPE_P(columnName = zend_hash_get_current_data_ex(
                        arrColumnInfo, &posColumnInfo)) != IS_STRING) {
                    freeArgContainerInfo($2);
                    SWIG_exception(E_ERROR, "Expected string as column name");
                }

                $2[i].name = Z_STRVAL_P(columnName);

                // Get column type
                zend_hash_move_forward_ex(arrColumnInfo, &posColumnInfo);
                if (Z_TYPE_P(columnType = zend_hash_get_current_data_ex(
                        arrColumnInfo, &posColumnInfo)) != IS_LONG) {
                    freeArgContainerInfo($2);
                    SWIG_exception(E_ERROR, "Expected an integer as"
                            " column type");
                }
                $2[i].type = Z_LVAL_P(columnType);
                i++;
            }
        } else if (strcmp(name, "type") == 0) {
            if (Z_TYPE_P(dataContainerInfo) != IS_LONG) {
                freeArgContainerInfo($2);
                SWIG_exception(E_ERROR, "Expected integer as input"
                        " for type property");
            }
            $4 = Z_LVAL_P(dataContainerInfo);
        } else if (strcmp(name, "rowKey") == 0) {
            if (Z_TYPE_P(dataContainerInfo) == IS_STRING) {
                freeArgContainerInfo($2);
                SWIG_exception(E_ERROR, "Expected boolean as input"
                        " for rowKey property");
            }
            $5 = static_cast<bool> (zval_is_true(dataContainerInfo));
        } else if (strcmp(name, "expiration") == 0) {
            int res = SWIG_ConvertPtr(dataContainerInfo,
                                      reinterpret_cast<void**>(&expiration),
                                      $descriptor(griddb::ExpirationInfo*),
                                      0 | 0);
            if (!SWIG_IsOK(res)) {
                freeArgContainerInfo($2);
                SWIG_exception(E_ERROR, "Expected expiration object"
                        " as input for expiration property");
            }
            $6 = (griddb::ExpirationInfo *) expiration;
        } else {
            freeArgContainerInfo($2);
            SWIG_exception(E_ERROR, "Invalid Property");
        }
    }
}

/**
 * Cleanup argument data for ContainerInfo constructor
 */
%typemap(freearg, fragment = "freeArgContainerInfo")
        (const GSChar* name, const GSColumnInfo* props,
                int propsCount, GSContainerType type, bool row_key,
                griddb::ExpirationInfo* expiration) {
    freeArgContainerInfo($2);
}

%fragment("freeArgContainerInfo", "header") {
    //SWIG_exception does not include freearg, so we need this function
    static void freeArgContainerInfo(const GSColumnInfo* props) {
    if (props) {
      delete[] props;
    }
}
}

%fragment("convertToFieldWithType", "header",
        fragment = "convertZvalValueToFloat",
        fragment = "convertZvalValueToDouble",
        fragment = "convertDateTimeObjectToGSTimestamp") {
static bool convertToFieldWithType(GSRow *row, int column,
                                   zval* value, GSType type) {
    GSResult returnCode;
    bool isSuccess;

    if (Z_TYPE_P(value) == IS_NULL) {
        returnCode = gsSetRowFieldNull(row, column);
        return (GS_SUCCEEDED(returnCode));
    }

    switch (type) {
        case GS_TYPE_STRING: {
            GSChar* stringVal;
            if (Z_TYPE_P(value) != IS_STRING) {
                return false;
            }
            stringVal = Z_STRVAL_P(value);
            returnCode = gsSetRowFieldByString(row, column, stringVal);
            break;
        }
        case GS_TYPE_LONG: {
            int64_t longVal;
            if (Z_TYPE_P(value) != IS_LONG) {
                return false;
            }
            longVal = Z_LVAL_P(value);
            returnCode = gsSetRowFieldByLong(row, column, longVal);
            break;
        }
        case GS_TYPE_BOOL: {
            if (Z_TYPE_P(value) == IS_STRING) {
                return false;
            }
            bool boolVal;
            boolVal = static_cast<bool> (zval_is_true(value));
            returnCode = gsSetRowFieldByBool(row, column, boolVal);
            break;
        }
        case GS_TYPE_BYTE: {
            int64_t byteVal;
            if (Z_TYPE_P(value) != IS_LONG) {
                return false;
            }
            byteVal = Z_LVAL_P(value);
            if (byteVal < std::numeric_limits<int8_t>::min() ||
                    byteVal > std::numeric_limits<int8_t>::max()) {
                return false;
            }
            returnCode = gsSetRowFieldByByte(row, column, byteVal);
            break;
        }
        case GS_TYPE_SHORT: {
            int64_t shortVal;
            if (Z_TYPE_P(value) != IS_LONG) {
                return false;
            }
            shortVal = Z_LVAL_P(value);
            if (shortVal < std::numeric_limits<int16_t>::min() ||
                    shortVal > std::numeric_limits<int16_t>::max()) {
                return false;
            }
            returnCode = gsSetRowFieldByShort(row, column, shortVal);
            break;
        }
        case GS_TYPE_INTEGER: {
            int64_t intVal;
            if (Z_TYPE_P(value) != IS_LONG) {
                return false;
            }
            intVal = Z_LVAL_P(value);
            if (intVal < std::numeric_limits<int32_t>::min() ||
                    intVal > std::numeric_limits<int32_t>::max()) {
                return false;
            }
            returnCode = gsSetRowFieldByInteger(row, column, intVal);
            break;
        }
        case GS_TYPE_FLOAT: {
            float floatVal;
            isSuccess = convertZvalValueToFloat(value, &floatVal);
            if (!isSuccess) {
                return false;
            }
            returnCode = gsSetRowFieldByFloat(row, column, floatVal);
            break;
        }
        case GS_TYPE_DOUBLE: {
            double doubleVal;
            isSuccess = convertZvalValueToDouble(value, &doubleVal);
            if (!isSuccess) {
                return false;
            }
            returnCode = gsSetRowFieldByDouble(row, column, doubleVal);
            break;
        }
        case GS_TYPE_TIMESTAMP: {
            GSTimestamp timestampValue;
            isSuccess = convertDateTimeObjectToGSTimestamp(value,
                                                           &timestampValue);
            if (!isSuccess) {
                return false;
            }
            returnCode = gsSetRowFieldByTimestamp(row, column, timestampValue);
            break;
        }
        case GS_TYPE_BLOB: {
            // Support string type for Blob data
            GSBlob blobVal;
            size_t size;
            if (Z_TYPE_P(value) != IS_STRING) {
                return false;
            }
            blobVal.data = Z_STRVAL_P(value);
            size = Z_STRLEN_P(value);
            blobVal.size = size;
            returnCode = gsSetRowFieldByBlob(row, column,
                                             (const GSBlob *)&blobVal);
            break;
        }
        default:
        return false;
        break;
    }
    return (GS_SUCCEEDED(returnCode));
}
}

/**
 * Support convert type from Zval value to Double.
 * Input in target language can be : float or integer
 */
%fragment("convertZvalValueToDouble", "header") {
static bool convertZvalValueToDouble(zval* value, double* doubleValPtr) {
    if (Z_TYPE_P(value) == IS_LONG) {
        // Input can be integer
        int64_t intVal;
        intVal = Z_LVAL_P(value);
        *doubleValPtr = intVal;
        // When input value is integer, it should be between
        // -9007199254740992(-2^53)/9007199254740992(2^53).
        return (-9007199254740992 <= intVal && 9007199254740992 >= intVal);
    } else if (Z_TYPE_P(value) == IS_DOUBLE) {
        *doubleValPtr = Z_DVAL_P(value);
        return (*doubleValPtr < std::numeric_limits<double>::max() &&
                *doubleValPtr > -1 *std::numeric_limits<double>::max());
    } else {
        return false;
    }
}
}

/**
 * Support convert type from Zval value to Float.
 * Input in target language can be : float or integer
 */
%fragment("convertZvalValueToFloat", "header") {
static bool convertZvalValueToFloat(zval* value, float* floatValPtr) {
    if (Z_TYPE_P(value) == IS_LONG) {
        // Input can be integer
        int64_t intVal;
        intVal = Z_LVAL_P(value);
        *floatValPtr = intVal;
        // When input value is integer, it should be between
        // -16777216(-2^24)/16777216(2^24).
        return (-16777216 <= intVal && 16777216 >= intVal);
    } else if (Z_TYPE_P(value) == IS_DOUBLE) {
        *floatValPtr = Z_DVAL_P(value);
        return (*floatValPtr < std::numeric_limits<float>::max() &&
                *floatValPtr > -1 *std::numeric_limits<float>::max());
    } else {
        return false;
    }
}
}

/**
 * Support convert type from object to GSTimestamp :
 * input in target language can be : datetime object
 */
%fragment("convertDateTimeObjectToGSTimestamp", "header") {
static bool convertDateTimeObjectToGSTimestamp(zval* datetime,
                                               GSTimestamp* timestamp) {
    // Check DateTime class exist or not
    zend_class_entry *ce = NULL;
    const char* dateTimeClassName = "DateTime";

    zend_string *zstrClassName = zend_string_init(dateTimeClassName,
                                                  strlen(dateTimeClassName), 0);
    ce = zend_lookup_class(zstrClassName);
    zend_string_release(zstrClassName);
    if (!ce) {
        return false;
    }

    // Check input in target language is DateTime object
    zval isAFunctionZval;
    zval dateTimeClassNameZval;
    zval isDateTimeZval;

    ZVAL_STRING(&isAFunctionZval, "is_a");
    ZVAL_STRING(&dateTimeClassNameZval, dateTimeClassName);
    zval paramsForIsA[2] = {
        *datetime,
        dateTimeClassNameZval
    };
    call_user_function(EG(function_table), NULL,
            &isAFunctionZval, &isDateTimeZval,
            ARRAY_SIZE(paramsForIsA), paramsForIsA TSRMLS_CC);
    bool isDateTime = zval_is_true(&isDateTimeZval);
    if (!isDateTime) {
        return false;
    }

    // Convert from datetime to timestamp
    // (1)Get timestamp with seconds
    zval dateTimestampGetFunctionZval;
    zval retSecondTimestamp;

    ZVAL_STRING(&dateTimestampGetFunctionZval, "date_timestamp_get");
    zval paramsForDateTimestampGet[1] = {*datetime};
    call_user_function(EG(function_table), NULL,
            &dateTimestampGetFunctionZval,
            &retSecondTimestamp, ARRAY_SIZE(paramsForDateTimestampGet),
            paramsForDateTimestampGet TSRMLS_CC);
    int64_t timestampSecond = Z_LVAL(retSecondTimestamp);

    // (2)Get timestamp with microsecond
    zval dateFormatFunctionZval;
    zval microSecondFormatZval;
    zval retMicrosecondTimestamp;

    ZVAL_STRING(&dateFormatFunctionZval, "date_format");
    ZVAL_STRING(&microSecondFormatZval, "u");
    zval paramsForDateFormat[2] = {
        *datetime,
        microSecondFormatZval
    };
    call_user_function(EG(function_table), NULL,
            &dateFormatFunctionZval,
            &retMicrosecondTimestamp, ARRAY_SIZE(paramsForDateFormat),
            paramsForDateFormat TSRMLS_CC);
    int64_t timestampMicroSecond = atoi(Z_STRVAL(retMicrosecondTimestamp));

    // Convert timestamp to milisecond
    *timestamp = (timestampSecond * 1000) + (timestampMicroSecond/1000);
    return true;
}
}

/**
* Typemaps for RowSet::update() and Container::put() function
* The argument "GSRow *row" is not used in the function body,
* It only for the purpose of typemap matching pattern
* The actual input data is store in class member and can be get
* by function getGSRowPtr()
*/
%typemap(in, fragment = "convertToFieldWithType") (GSRow *row)
        (HashTable *arr, HashPosition pos, zval* data) {
    const int SIZE = 60;
    if (Z_TYPE_P(&$input) != IS_ARRAY) {
        SWIG_exception(E_ERROR, "Expected an array as input");
    }
    arr = Z_ARRVAL_P(&$input);
    int length = zend_hash_num_elements(arr);
    GSRow *tmpRow = arg1->getGSRowPtr();
    int colNum = arg1->getColumnCount();
    GSType* typeList = arg1->getGSTypeList();

    if (length != colNum) {
        SWIG_exception(E_ERROR, "Num row is different with container info");
    }

    for (zend_hash_internal_pointer_reset_ex(arr, &pos);
            (data = zend_hash_get_current_data_ex(arr, &pos)) != NULL;
            zend_hash_move_forward_ex(arr, &pos)) {
        GSType type = typeList[pos];
        if (!(convertToFieldWithType(tmpRow, pos, data, type))) {
            char gsType[SIZE];
            snprintf(gsType, SIZE, "Invalid value for column %d,"
                " type should be : %d", pos, type);
            SWIG_exception(E_ERROR, gsType);
        }
    }
}

/**
 * Support convert row key Field from zval* in target language to
 * C Object with specific type
 */
%fragment("convertToRowKeyFieldWithType", "header") {
static bool convertToRowKeyFieldWithType(griddb::Field &field,
                                         zval* value, GSType type) {
    bool isSuccess;
    field.type = type;

    if (Z_TYPE_P(value) == IS_NULL) {
        // Not support NULL
        return false;
    }

    switch (type) {
        case (GS_TYPE_STRING):
            if (Z_TYPE_P(value) != IS_STRING) {
                return false;
            }
            griddb::Util::strdup(&field.value.asString, Z_STRVAL_P(value));
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
            if (Z_TYPE_P(value) != IS_LONG) {
                return false;
            }
            field.value.asLong = Z_LVAL_P(value);
            break;
        case (GS_TYPE_TIMESTAMP):
            isSuccess = convertDateTimeObjectToGSTimestamp(
                    value, &field.value.asTimestamp);
            if (!isSuccess) {
                return false;
            }
            break;
        default:
            // Not support for now
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
            SWIG_exception(E_ERROR, "Can not convert to row field");
        }
    }
}

%typemap(in, numinputs = 0) (GSRow *rowdata) {
    $1 = NULL;
}

/**
 * Support convert data from GSRow* row to zval array
 */
%fragment("getRowFields", "header",
          fragment = "convertTimestampToDateTimeObject") {
static bool getRowFields(GSRow* row, int columnCount,
        GSType* typeList, int* columnError,
        GSType* fieldTypeError, zval* outList) {
    GSResult returnCode;
    bool returnValue = true;
    for (int i = 0; i < columnCount; i++) {
        // Check NULL value
        GSBool nullValue;
        returnCode = gsGetRowFieldNull(row, (int32_t) i, &nullValue);
        if (!GS_SUCCEEDED(returnCode)) {
            *columnError = i;
            returnValue = false;
            *fieldTypeError = GS_TYPE_NULL;
            return returnValue;
        }
        if (nullValue) {
            add_index_null(outList, i);
            continue;
        }
        switch (typeList[i]) {
        case GS_TYPE_LONG: {
            int64_t longValue;
            returnCode = gsGetRowFieldAsLong(row, (int32_t) i, &longValue);
            if (!GS_SUCCEEDED(returnCode)) {
                break;
            }
            add_index_long(outList, i, longValue);
            break;
        }
        case GS_TYPE_STRING: {
            GSChar* stringValue;
            returnCode = gsGetRowFieldAsString(row, (int32_t) i,
                                               (const GSChar **)&stringValue);
            if (!GS_SUCCEEDED(returnCode)) {
                break;
            }
            add_index_string(outList, i, stringValue);
            break;
        }
        case GS_TYPE_BLOB: {
            GSBlob blobValue = {0};
            returnCode = gsGetRowFieldAsBlob(row, (int32_t) i, &blobValue);
            if (!GS_SUCCEEDED(returnCode)) {
                break;
            }
            add_index_string(outList, i,
                             reinterpret_cast<const char*>(blobValue.data));
            break;
        }
        case GS_TYPE_BOOL: {
            GSBool boolValue;
            bool boolVal;
            returnCode = gsGetRowFieldAsBool(row, (int32_t) i, &boolValue);
            if (!GS_SUCCEEDED(returnCode)) {
                break;
            }
            if (boolValue == GS_TRUE) {
                boolVal = true;
            } else {
                boolVal = false;
            }
            add_index_bool(outList, i, boolVal);
            break;
        }
        case GS_TYPE_INTEGER: {
            int32_t intValue;
            returnCode = gsGetRowFieldAsInteger(row, (int32_t) i, &intValue);
            if (!GS_SUCCEEDED(returnCode)) {
                break;
            }
            add_index_long(outList, i, intValue);
            break;
        }
        case GS_TYPE_FLOAT: {
            float floatValue;
            returnCode = gsGetRowFieldAsFloat(row, (int32_t) i, &floatValue);
            if (!GS_SUCCEEDED(returnCode)) {
                break;
            }
            add_index_double(outList, i, floatValue);
            break;
        }
        case GS_TYPE_DOUBLE: {
            double doubleValue;
            returnCode = gsGetRowFieldAsDouble(row, (int32_t) i, &doubleValue);
            if (!GS_SUCCEEDED(returnCode)) {
                break;
            }
            add_index_double(outList, i, doubleValue);
            break;
        }
        case GS_TYPE_TIMESTAMP: {
            GSTimestamp timestampValue;
            zval dateTime;
            returnCode = gsGetRowFieldAsTimestamp(row,
                                                  (int32_t) i, &timestampValue);
            if (!GS_SUCCEEDED(returnCode)) {
                break;
            }
            convertTimestampToDateTimeObject(&timestampValue, &dateTime);
            add_index_zval(outList, i, &dateTime);
            break;
        }
        case GS_TYPE_BYTE: {
            int8_t byteValue;
            returnCode = gsGetRowFieldAsByte(row, (int32_t) i, &byteValue);
            if (!GS_SUCCEEDED(returnCode)) {
                break;
            }
            add_index_long(outList, i, byteValue);
            break;
        }
        case GS_TYPE_SHORT: {
            int16_t shortValue;
            returnCode = gsGetRowFieldAsShort(row, (int32_t) i, &shortValue);
            if (!GS_SUCCEEDED(returnCode)) {
                break;
            }
            add_index_long(outList, i, shortValue);
            break;
        }
        default: {
            // NOT OK
            returnCode = -1;
            break;
        }
        }
        if (!GS_SUCCEEDED(returnCode)) {
            *columnError = i;
            *fieldTypeError = typeList[i];
            returnValue = false;
            return returnValue;
        }
    }
    return returnValue;
}
}

/**
 * Support convert data from timestamp to DateTime object in target language
 */
%fragment("convertTimestampToDateTimeObject", "header") {
static void convertTimestampToDateTimeObject(GSTimestamp* timestamp,
                                             zval* dateTime) {
    const int SIZE = 60;
    char timeStr[SIZE];
    zval functionNameZval;
    zval formatStringZval;
    zval formattedTimePhp;
    const char* functionName = "date_create_from_format";
    const char* formatString = "U.u";

    // Get time with seconds
    int64_t second = *timestamp/1000;

    // Get time with microSeconds
    int64_t microSecond = (*timestamp % 1000) * 1000;
    snprintf(timeStr, SIZE, "%ld.%06d", second, microSecond);

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
* The argument "GSRow *rowdata" is not used in the function Container::get(),
* it only for the purpose of typemap matching pattern
* The actual output data is store in class member and can be get by function getGSRowPtr()
*/
%typemap(argout, fragment = "getRowFields") (GSRow *rowdata) {
    if (result == GS_FALSE) {
        RETVAL_NULL();
    } else {
        bool returnValue;
        int errorColumn;
        GSType errorType;
        const int SIZE = 60;

        // Get row pointer
        GSRow* row = arg1->getGSRowPtr();

        // Get row fields
        array_init_size(return_value, arg1->getColumnCount());
        returnValue = getRowFields(row, arg1->getColumnCount(),
                arg1->getGSTypeList(),
                &errorColumn, &errorType,
                return_value);

        if (returnValue == false) {
            char errorMsg[SIZE];
            snprintf(errorMsg, SIZE, "Can't get data for field %d with type %d",
                     errorColumn, errorType);
            SWIG_exception(E_ERROR, errorMsg);
        }
    }
}

/**
 * Type map for Rowset::next()
 */
%typemap(in, numinputs = 0) (GSRowSetType* type, bool* hasNextRow,
    griddb::QueryAnalysisEntry** queryAnalysis,
    griddb::AggregationResult** aggResult)
    (GSRowSetType typeTmp, bool hasNextRowTmp,
            griddb::QueryAnalysisEntry* queryAnalysisTmp = NULL,
            griddb::AggregationResult* aggResultTmp = NULL) {
    $1 = &typeTmp;
    hasNextRowTmp = true;
    $2 = &hasNextRowTmp;
    $3 = &queryAnalysisTmp;
    $4 = &aggResultTmp;
}

%typemap(argout, fragment = "getRowFields") (GSRowSetType* type,
    bool* hasNextRow, griddb::QueryAnalysisEntry** queryAnalysis,
    griddb::AggregationResult** aggResult) {
    const int SIZE = 60;
    if (*$2 == false) {
        RETURN_NULL();
    }
    switch (*$1) {
        case (GS_ROW_SET_CONTAINER_ROWS): {
            bool returnValue;
            int errorColumn;

            GSRow* row = arg1->getGSRowPtr();
            array_init_size(return_value, arg1->getColumnCount());
            GSType errorType;
            returnValue = getRowFields(row, arg1->getColumnCount(),
                    arg1->getGSTypeList(),
                    &errorColumn, &errorType, return_value);

            if (returnValue == false) {
                char errorMsg[SIZE];
                snprintf(errorMsg, SIZE, "Can't get data for field"
                    " %d with type %d", errorColumn, errorType);
                SWIG_exception(E_ERROR, errorMsg);
            }
            break;
        }
        case (GS_ROW_SET_AGGREGATION_RESULT): {
            SWIG_SetPointerZval(return_value, reinterpret_cast<void *> (*$4),
                    $descriptor(griddb::AggregationResult *),
                    SWIG_CAST_NEW_MEMORY);

            break;
        }
        case (GS_ROW_SET_QUERY_ANALYSIS): {
            // Not support now
            SWIG_exception(E_ERROR, "Function is not supportted now");
            break;
        }
        default: {
            SWIG_exception(E_ERROR, "Invalid Rowset type");
            break;
        }
    }
}

/*
* Typemap for get function in AggregationResult class
*/
%typemap(in, numinputs = 0) (griddb::Field *agValue)
        (griddb::Field tmpAgValue) {
    $1 = &tmpAgValue;
}

%typemap(argout) (griddb::Field *agValue) {
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
            convertTimestampToDateTimeObject(&($1->value.asTimestamp),
                                             return_value);
        default:
            RETURN_NULL();
    }
}

/*
* Typemap for TimestampUtils::get_time_millis: convert DateTime object from
* target language to timestamp with millisecond in C++ layer
*/
%typemap(in, fragement = "convertDateTimeObjectToGSTimestamp")
        (int64_t timestamp) {
    bool isSuccess;
    GSTimestamp timestampValue;
    isSuccess = convertDateTimeObjectToGSTimestamp(&$input, &timestampValue);
    if (!isSuccess) {
        SWIG_exception(E_ERROR, "Expected a DateTime object as input");
    }
    $1 = timestampValue;
}

/*
* Typemap for set attribute ContainerInfo::column_info_list
*/
%typemap(in, numinputs = 1, fragment = "freeArgColumnInfoList") (ColumnInfoList*)
        (HashTable *arrColumnInfoArray, HashPosition posColumnInfoArray,
            zval *dataColumnInfoArray, HashTable *arrColumnInfo,
            HashPosition posColumnInfo, zval *dataColumnInfo,
            zval *columnName, zval *columnType) {
    ColumnInfoList infolist;
    int i = 0;
    GSColumnInfo* containerInfo;
    $1 = &infolist;

    if (Z_TYPE_P(&$input) != IS_ARRAY) {
        SWIG_exception(E_ERROR, "Expected an array as input");
    }

    arrColumnInfoArray = Z_ARRVAL_P(&$input);
    int length = zend_hash_num_elements(arrColumnInfoArray);

    if (length == 0) {
        SWIG_exception(E_ERROR, "Expected not empty array as input");
    }

    containerInfo = new GSColumnInfo[length];
    if (containerInfo == NULL) {
        SWIG_exception(E_ERROR, "Memmory allocation error");
    }
    memset(containerInfo, 0x0, length*sizeof(GSColumnInfo));

    // Set value for property of columnInfoList
    $1->columnInfo = containerInfo;
    $1->size = length;

    for (zend_hash_internal_pointer_reset_ex(arrColumnInfoArray, &posColumnInfoArray);
            (dataColumnInfoArray = zend_hash_get_current_data_ex(arrColumnInfoArray, &posColumnInfoArray)) != NULL;
            zend_hash_move_forward_ex(arrColumnInfoArray, &posColumnInfoArray)) {
        // Input valid is array only
        if (Z_TYPE_P(dataColumnInfoArray) != IS_ARRAY) {
            freeArgColumnInfoList($1);
            SWIG_exception(E_ERROR, "Expected array property"
                " as ColumnInfo element");
        }

        arrColumnInfo = Z_ARRVAL_P(dataColumnInfoArray);
        int sizeColumn = zend_hash_num_elements(arrColumnInfo);
        if (sizeColumn != 2) {
            freeArgColumnInfoList($1);
            SWIG_exception(E_ERROR, "Expected two elements"
                " for ColumnInfo property");
        }
        // Get column name
        zend_hash_internal_pointer_reset_ex(arrColumnInfo, &posColumnInfo);
        if (Z_TYPE_P(columnName = zend_hash_get_current_data_ex(
            arrColumnInfo, &posColumnInfo)) != IS_STRING) {
            freeArgColumnInfoList($1);
            SWIG_exception(E_ERROR, "Expected string as column name");
        }
        containerInfo[i].name = Z_STRVAL_P(columnName);

        // Get column type
        zend_hash_move_forward_ex(arrColumnInfo, &posColumnInfo);
        if (Z_TYPE_P(columnType = zend_hash_get_current_data_ex(
            arrColumnInfo, &posColumnInfo)) != IS_LONG) {
            freeArgColumnInfoList($1);
            SWIG_exception(E_ERROR, "Expected an integer as column type");
        }
        containerInfo[i].type = Z_LVAL_P(columnType);
        i++;
    }
}

/**
 * Cleanup argument data for set attribute ContainerInfo::column_info_list
 */
%typemap(freearg, fragment = "freeArgColumnInfoList") (ColumnInfoList*) {
    freeArgColumnInfoList($1);
}

%fragment("freeArgColumnInfoList", "header") {
    //SWIG_exception does not include freearg, so we need this function
    static void freeArgColumnInfoList(ColumnInfoList* infoList) {
        if (infoList->columnInfo) {
            delete[](infoList->columnInfo);
        }
    }
}

/*
* Typemap for get attribute ContainerInfo::column_info_list
*/
%typemap(out) (ColumnInfoList*) {
    // Define size of column
    const int SIZE_COLUMN = 2;
    // Define an array contains name and type of each column
    zval columnInfoArray;

    // Get ColumnInfoList object
    ColumnInfoList data = *$1;
    // Get size of columnInfo property
    size_t size = data.size;

    array_init_size(return_value, size);
    for (int i = 0; i < size; i++) {
        array_init_size(&columnInfoArray, SIZE_COLUMN);
        add_next_index_string(&columnInfoArray, (data.columnInfo)[i].name);
        add_next_index_long(&columnInfoArray, (data.columnInfo)[i].type);
        add_next_index_zval(return_value, &columnInfoArray);
    }
}

/**
* Typemaps output for PartitionController::get_container_names
*/
%typemap(in, numinputs = 0) (const GSChar *const ** stringList, size_t *size)
    (GSChar **nameList, size_t size) {
    $1 = &nameList;
    $2 = &size;
}

%typemap(argout, numinputs = 0) (const GSChar * const ** stringList,
    size_t *size) {
    GSChar** nameList = *$1;
    size_t size = *$2;

    array_init_size(return_value, size);
    for (int i = 0; i < size; i++) {
        add_next_index_string(return_value, nameList[i]);
    }
}
