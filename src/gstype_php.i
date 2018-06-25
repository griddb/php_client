/*
   Copyright (c) 2017 TOSHIBA Digital Solutions Corporation

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

/**
 * Support throw exception in PHP language
 */
%fragment("throwGSException", "header") {
    static void throwGSException(griddb::GSException* exception){
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
    griddb::GSException* tmpException = new griddb::GSException($1.get_code(), $1.what());
    throwGSException(tmpException);
    return;
%}

/**
 * Typemaps for put_container() function
 */
%typemap(in) (const GSColumnInfo* props, int propsCount)
(zval *inputVar, zval *data1, zval *data2, HashTable *arr, HashPosition pos1, HashTable *assocArr, HashPosition pos2, int i, zend_string* key, ulong key_len, ulong index) {
//Convert PHP arrays into GSColumnInfo properties
    //Convert input array to HashTable 
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        php_printf("Expected array as input");
        SWIG_FAIL();
    }
    arr = Z_ARRVAL_P(&$input);
    $2 = (int) zend_hash_num_elements(arr);
    $1 = NULL;
    if ($2 > 0) {
        $1 = (GSColumnInfo *) malloc($2*sizeof(GSColumnInfo));
        if($1 == NULL) {
            php_printf("Memory allocation error");
            SWIG_FAIL();
        }
        memset($1, 0x0, $2*sizeof(GSColumnInfo));

        i = 0;
        for(zend_hash_internal_pointer_reset_ex(arr, &pos1);
                (data1 = zend_hash_get_current_data_ex(arr, &pos1)) != NULL;
                zend_hash_move_forward_ex(arr, &pos1)) {
            if(Z_TYPE_P(data1) != IS_ARRAY) {
                php_printf("Expected associative array as elements");
                SWIG_FAIL();
            }
            assocArr = Z_ARRVAL_P(data1);
            for(zend_hash_internal_pointer_reset_ex(assocArr, &pos2); (data2 = zend_hash_get_current_data_ex(assocArr, &pos2)) != NULL; zend_hash_move_forward_ex(assocArr, &pos2)) {
                if (zend_hash_get_current_key_ex(assocArr, &key, &index, &pos2) == HASH_KEY_IS_STRING) {
                    $1[i].name = ZSTR_VAL(key);
                    $1[i].type = Z_LVAL_P(data2);
                }
            }
            i++;
        }
    }
}

%typemap(typecheck) (const GSColumnInfo* props, int propsCount) {
    $1 = (Z_TYPE_P(&$input) == IS_ARRAY) ? 1 : 0;
}

%typemap(freearg) (const GSColumnInfo* props, int propsCount) (int i) {
    if ($1) {
        free((void *) $1);
    }
}

/**
 * Typemaps for get_store() function
 */
%typemap(in) (const GSPropertyEntry* props, int propsCount)
(HashTable *arr, HashPosition pos, zval *data) {
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        php_printf("Expected associative array as input");
        SWIG_FAIL();
    }

    arr = Z_ARRVAL_P(&$input);
    $2 = (int) zend_hash_num_elements(arr);
    $1 = NULL;
    if ($2 > 0) {
        $1 = (GSPropertyEntry *) malloc($2*sizeof(GSPropertyEntry));
        if($1 == NULL) {
            php_printf("Memory allocation error");
            SWIG_FAIL();
        }
        zend_string *key;
        int key_len;
        long index;
        int i = 0;
        for(zend_hash_internal_pointer_reset_ex(arr, &pos);
                (data = zend_hash_get_current_data_ex(arr, &pos)) != NULL;
                zend_hash_move_forward_ex(arr, &pos)) {
            if(zend_hash_get_current_key_ex(arr, &key, (zend_ulong*)&index, &pos) == HASH_KEY_IS_STRING) {
                $1[i].name = ZSTR_VAL(key);
                $1[i].value = Z_STRVAL_P(data);
                i++;
            }
        }
    }
}

%typemap(freearg) (const GSPropertyEntry* props, int propsCount) (int i = 0) {
    if ($1) {
        free((void *) $1);
    }
}

/**
 * Typemaps for fetch_all() function
 */
%typemap(in) (GSQuery* const* queryList, size_t queryCount)
(HashTable *arr, HashPosition pos, zval *data, griddb::Query *query, int res = 0, int i = 0) {
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        php_printf("Expected associative array as input");
        SWIG_FAIL();
    }
    arr = Z_ARRVAL_P(&$input);
    $2 = (int) zend_hash_num_elements(arr);
    $1 = NULL;
    if($2 > 0) {
        $1 = (GSQuery**) malloc($2*sizeof(GSQuery*));
        if($1 == NULL) {
            php_printf("Memory allocation error");
            SWIG_FAIL();
        }
        zend_string *key;
        int key_len;
        long index;
        for(zend_hash_internal_pointer_reset_ex(arr, &pos);
                (data = zend_hash_get_current_data_ex(arr, &pos)) != NULL;
                zend_hash_move_forward_ex(arr, &pos)) {
            if(zend_hash_get_current_key_ex(arr, &key, (zend_ulong*)&index, &pos) == HASH_KEY_IS_LONG) {
                res = SWIG_ConvertPtr(data, (void**)&query, $descriptor(griddb::Query*), 0);
                if (!SWIG_IsOK(res)) {
                    php_printf("Convert pointer failed");
                    SWIG_FAIL();
                }
                $1[i] = query->gs_ptr();
                i++;
            }
        }
    }
}

%typemap(freearg) (GSQuery* const* queryList, size_t queryCount) {
    if ($1) {
        free((void *) $1);
    }
}

/**
 * Typemaps for put_multi_row_container() function
 */
%typemap(in) (const GSContainerRowEntry* entryList, size_t entryCount)
(int i, int res = 0, void** pRowList = 0, int listSize, HashTable *arr, HashTable *rowArr, HashPosition pos, HashPosition posRow, zval *data, zval *row, griddb::Row *vrow, int res = 0) {
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        php_printf("Expected indexed of associative array as input");
        SWIG_FAIL();
    }
    arr = Z_ARRVAL_P(&$input);
    $2 = (int) zend_hash_num_elements(arr);
    $1 = NULL;
    if ($2 > 0) {
        $1 = (GSContainerRowEntry *) malloc($2*sizeof(GSContainerRowEntry));
        memset($1, 0x0, $2*sizeof(GSContainerRowEntry));
        if($1 == NULL) {
            php_printf("Memory allocation error");
            SWIG_FAIL();
        }
        i = 0;
        zend_string *key;
        int key_len;
        long index;
        for(zend_hash_internal_pointer_reset_ex(arr, &pos);
                (data = zend_hash_get_current_data_ex(arr, &pos)) != NULL;
                zend_hash_move_forward_ex(arr, &pos)) {
            if(zend_hash_get_current_key_ex(arr, &key, (zend_ulong*)&index, &pos) == HASH_KEY_IS_STRING) {
                $1[i].containerName = strdup(ZSTR_VAL(key));
            }
            else {
                php_printf("Expected string as containerName");
                SWIG_FAIL();
            }

            // Check if rowList is an indexed array
            if(Z_TYPE_P(data) != IS_ARRAY) {
                php_printf("Expected indexed array as rowList");
                SWIG_FAIL();
            }
            // Get Row element from list
            rowArr = Z_ARRVAL_P(data);
            listSize = (int) zend_hash_num_elements(rowArr);
            if (listSize > 0) {
                pRowList = (void**)malloc(listSize*sizeof(void *));
                $1[i].rowList = pRowList;
            }
            $1[i].rowCount = listSize;
            zend_string *keyRow;
            int keyLenRow;
            long indexRow;
            int j = 0;
            for(zend_hash_internal_pointer_reset_ex(rowArr, &posRow);
                    (row = zend_hash_get_current_data_ex(rowArr, &posRow)) != NULL;
                    zend_hash_move_forward_ex(rowArr, &posRow)) {
                if(zend_hash_get_current_key_ex(rowArr, &keyRow, (zend_ulong*)&indexRow, &posRow) == HASH_KEY_IS_LONG) {
                    res = SWIG_ConvertPtr(row, (void**)&vrow, $descriptor(griddb::Row*), 0);
                    if (!SWIG_IsOK(res)) {
                        php_printf("Convert pointer failed");
                        SWIG_FAIL();
                    }
                    pRowList[j] = vrow->gs_ptr();
                    j++;
                }
            }
            i++;
        }
    }
}

%typemap(freearg) (const GSContainerRowEntry* entryList, size_t entryCount) (int i) {
    if ($1) {
        for (i = 0; i < $2; i++) {
            if ($1[i].containerName) {
                free((void *) $1[i].containerName);
            }
            if ($1[i].rowList) {
                free((void *) $1[i].rowList);
            }
        }
        free((void *) $1);
    }
}

/**
 * Typemaps for put_multi_row() function
 */
%typemap(in) (const void* const * rowObjs, size_t rowCount)
(HashTable *rowArr, HashPosition pos, zval *data, griddb::Row *row, int res = 0, int i = 0) {
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        php_printf("Expected associative array as input");
        SWIG_FAIL();
    }

    rowArr = Z_ARRVAL_P(&$input);
    $2 = (int) zend_hash_num_elements(rowArr);
    $1 = NULL;
    if($2 > 0) {
        $1 = (void**) malloc($2*sizeof(void*));
        if($1 == NULL) {
            php_printf("Memory allocation error");
            SWIG_FAIL();
        }
        zend_string *key;
        int key_len;
        long index;
        int i = 0;
        for(zend_hash_internal_pointer_reset_ex(rowArr, &pos);
                (data = zend_hash_get_current_data_ex(rowArr, &pos)) != NULL;
                zend_hash_move_forward_ex(rowArr, &pos)) {

            if(zend_hash_get_current_key_ex(rowArr, &key, (zend_ulong*)&index, &pos) == HASH_KEY_IS_LONG) {
                res = SWIG_ConvertPtr(data, (void**)&row, $descriptor(griddb::Row*), 0);
                if (!SWIG_IsOK(res)) {
                    php_printf("Convert pointer failed");
                    SWIG_FAIL();
                }
                $1[i] = row->gs_ptr();
                i++;
            }
        }
    }
}

%typemap(freearg) (const void* const * rowObjs, size_t rowCount) {
    if ($1) {
        free((void *) $1);
    }
}

// Empty typemap to override default (argout) typemaps for (void **) input
// This typemap is required to avoid error because of the use of undeclared variables generated by SWIG  
%typemap(argout) (const void* const * rowObjs, size_t rowCount) {}

/**
 * Typemaps for set_field_by_byte_array() function
 */
%typemap(in) (const int8_t *fieldValue, size_t size)
(HashTable *arr, HashPosition pos, zval *data) {
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        php_printf("Expected associative array as input");
        SWIG_FAIL();
    }
    arr = Z_ARRVAL_P(&$input);
    $2 = (int) zend_hash_num_elements(arr);
    $1 = NULL;
    if ($2 > 0) {
        $1 = (int8_t *) malloc($2*sizeof(int8_t));
        if($1 == NULL) {
            php_printf("Memory allocation error");
            SWIG_FAIL();
        }
        zend_string *key;
        int key_len;
        long index;
        int i = 0;
        for(zend_hash_internal_pointer_reset_ex(arr, &pos);
                (data = zend_hash_get_current_data_ex(arr, &pos)) != NULL;
                zend_hash_move_forward_ex(arr, &pos)) {
            if(zend_hash_get_current_key_ex(arr, &key, (zend_ulong*)&index, &pos) == HASH_KEY_IS_LONG) {
                $1[i] = Z_LVAL_P(data);
                i++;
            }
        }
    }
}

%typemap(freearg) (const int8_t *fieldValue, size_t size) {
    if ($1) {
        free((void *) $1);
    }
}

/**
 * Typemaps input for get_multi_container_row() function
 */
%typemap(in) (const GSRowKeyPredicateEntry *const * predicateList, size_t predicateCount)
(zend_string *key, HashTable *arr, HashPosition pos, zval *data, GSRowKeyPredicateEntry* pList, griddb::RowKeyPredicate *vpredicate, int i, int res = 0) {
    if(Z_TYPE_P(&$input) != IS_ARRAY) {
        php_printf("Expected associative array as input");
        SWIG_FAIL();
    }
    arr = Z_ARRVAL_P(&$input);
    $2 = (int) zend_hash_num_elements(arr);
    $1 = NULL;
    i = 0;
    if($2 > 0) {
        pList = (GSRowKeyPredicateEntry*) malloc($2*sizeof(GSRowKeyPredicateEntry));
        if(pList == NULL) {
            php_printf("Memory allocation error");
            SWIG_FAIL();
        }
        $1 = &pList;
        int key_len;
        long index;
        for(zend_hash_internal_pointer_reset_ex(arr, &pos);
                (data = zend_hash_get_current_data_ex(arr, &pos)) != NULL;
                zend_hash_move_forward_ex(arr, &pos)) {
            if(zend_hash_get_current_key_ex(arr, &key, (zend_ulong*)&index, &pos) == HASH_KEY_IS_STRING) {
                GSRowKeyPredicateEntry *predicateEntry = &pList[i];
                predicateEntry->containerName = strdup(ZSTR_VAL(key));
                res = SWIG_ConvertPtr(data, (void**)&vpredicate, $descriptor(griddb::RowKeyPredicate*), 0);
                if (!SWIG_IsOK(res)) {
                    php_printf("Convert pointer failed");
                    SWIG_FAIL();
                }
                predicateEntry->predicate = vpredicate->gs_ptr();
                i++;
            }
        }
    }
}

%typemap(freearg) (const GSRowKeyPredicateEntry *const * predicateList, size_t predicateCount) (int i, GSRowKeyPredicateEntry* pList) {
    if ($1 && *$1) {
        pList = *$1;

        for (i = 0; i < $2; i++) {
            if(pList[i].containerName) {
                free((void *) pList[i].containerName);
            }
        }
        free((void *) pList);
    }
}

/**
 * Typemaps output for get_multi_container_row() function
 */
%typemap(in, numinputs = 0) (const GSContainerRowEntry **entryList, size_t *entryCount) (GSContainerRowEntry *pEntryList, size_t temp) {
    $1 = &pEntryList;
    $2 = &temp;
}

%typemap(argout, fragment="t_output_helper") (const GSContainerRowEntry **entryList, size_t *entryCount)
(size_t i = 0, size_t j = 0, GSContainerRowEntry *entry, GSRow* pRow, griddb::Row* row, zval temp_result, zval list, zval value) {
    array_init(&temp_result);
    for(i = 0; i < *$2; i++) {
        // Get container
        entry = &(*$1)[i];
        array_init(&list);
        for(j = 0; j < entry->rowCount; j++) {
            pRow = (GSRow *) entry->rowList[j];
            if (pRow) {
                row = new griddb::Row(pRow);
                SWIG_SetPointerZval(&value, (void *)(row), $descriptor(griddb::Row *), 2);
                add_index_zval(&list, j, &value);
            }
        }
        add_assoc_zval(&temp_result, entry->containerName, &list);
    }
    t_output_helper($result, &temp_result);
}

/**
 * Typemaps output for partition controller function
 */
%typemap(in, numinputs=0) (const GSChar *const ** stringList, size_t *size) (GSChar **nameList1, size_t size1) {
    $1 = &nameList1;
    $2 = &size1;
}

%typemap(argout,numinputs=0) (const GSChar *const ** stringList, size_t *size) (int i) {
    array_init_size($result, size1$argnum);
    for (i = 0; i < size1$argnum; i++) {
        add_index_string($result, (ulong) i, nameList1$argnum[i]);
    }
}

%typemap(in, numinputs=0) (const int **intList, size_t *size) (int *intList1, size_t size1) {
    $1 = &intList1;
    $2 = &size1;
}

%typemap(argout,numinputs=0) (const int **intList, size_t *size) (int i) {
    array_init_size($result, size1$argnum);
    for (i = 0; i < size1$argnum; i++) {
        add_index_long($result, (ulong) i, intList1$argnum[i]);
    }
}

%typemap(in, numinputs=0) (const long **longList, size_t *size) (long *longList1, size_t size1) {
    $1 = &longList1;
    $2 = &size1;
}

%typemap(argout,numinputs=0) (const long **longList, size_t *size) (int i) {
    array_init_size($result, size1$argnum);
    for (i = 0; i < size1$argnum; i++) {
        add_index_double($result, (ulong) i, longList1$argnum[i]);
    }
}

// set_field_as_blob
%typemap(in) (const GSBlob *fieldValue) {
    if(Z_TYPE_P(&$input) != IS_STRING) {
        php_printf("Expected string as input");
        RETURN_NULL();
    }

    $1 = (GSBlob*) malloc(sizeof(GSBlob));

    convert_to_string(&$input);
    $1->size = Z_STRLEN_P(&$input);
    $1->data = (char*) Z_STRVAL($input);
}

%typemap(freearg) (const GSBlob *fieldValue) {
    if ($1) {
        free((void *) $1);
    }
}

%typemap(in, numinputs = 0) (GSBlob *value) (GSBlob pValue) {
    $1 = &pValue;
}

// Get_field_as_blob
%typemap(argout, fragment="t_output_helper") (GSBlob *value) {
    zval o;
    ZVAL_STRINGL(&o, (const char*)pValue$argnum.data, pValue$argnum.size);
    t_output_helper($result, &o);
}
