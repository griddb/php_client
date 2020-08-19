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

//Read only attribute Container::type
%include <attribute.i>
//%attribute(griddb::Store, int, type_data, get_data);



