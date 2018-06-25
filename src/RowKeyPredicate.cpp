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

#include "RowKeyPredicate.h"
#include "GSException.h"

namespace griddb {

RowKeyPredicate::RowKeyPredicate(GSRowKeyPredicate *predicate): Resource(predicate), mPredicate(predicate) {
}

/**
 * Destructor. Convert from C-API: gsCloseRowKeyPredicate
 */
RowKeyPredicate::~RowKeyPredicate() {
	if (mPredicate != NULL) {
		gsCloseRowKeyPredicate(&mPredicate);
		mPredicate = NULL;
	}
}

/**
 * Get finish key by string. Convert from C-API: gsSetPredicateFinishKeyByString
 */
const GSChar* RowKeyPredicate::get_finish_key_as_string() {
	GSChar *finishKey;
	GSResult ret = gsGetPredicateFinishKeyAsString(mPredicate,
			(const GSChar **) &finishKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
	return finishKey;
}

/**
 * Get finish key by int. Convert from C-API: gsGetPredicateFinishKeyAsInteger
 */
int32_t RowKeyPredicate::get_finish_key_as_integer() {
	int32_t* finishKeyPtr;
	GSResult ret = gsGetPredicateFinishKeyAsInteger(mPredicate,
			(const int32_t **) &finishKeyPtr);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
	return *finishKeyPtr;
}

/**
 * Get finish key by long. Convert from C-API: gsGetPredicateFinishKeyAsLong
 */
int64_t RowKeyPredicate::get_finish_key_as_long() {
	int64_t* finishKeyPtr;
	GSResult ret = gsGetPredicateFinishKeyAsLong(mPredicate,
			(const int64_t **) &finishKeyPtr);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
	return *finishKeyPtr;
}

/**
 * Get finish key by timestamp. Convert from C-API: gsGetPredicateFinishKeyAsTimestamp
 */
GSTimestamp RowKeyPredicate::get_finish_key_as_timestamp() {
	GSTimestamp* finishKeyPtr;
	GSResult ret = gsGetPredicateFinishKeyAsTimestamp(mPredicate,
			(const GSTimestamp **) &finishKeyPtr);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
	return *finishKeyPtr;
}

/**
 * Set finish key by string. Convert from C-API: gsSetPredicateFinishKeyByString
 */
void RowKeyPredicate::set_finish_key_by_string(const GSChar* finishKey) {
	GSResult ret = gsSetPredicateFinishKeyByString(mPredicate, finishKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Set finish key by integer. Convert from C-API: gsSetPredicateFinishKeyByInteger
 */
void RowKeyPredicate::set_finish_key_by_integer(const int32_t finishKey) {
	GSResult ret = gsSetPredicateFinishKeyByInteger(mPredicate, &finishKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Set finish key by long. Convert from C-API: gsSetPredicateFinishKeyByLong
 */
void RowKeyPredicate::set_finish_key_by_long(const int64_t finishKey) {
	GSResult ret = gsSetPredicateFinishKeyByLong(mPredicate, &finishKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Set finish key by timestamp. Convert from C-API: gsSetPredicateFinishKeyByTimestamp
 */
void RowKeyPredicate::set_finish_key_by_timestamp(const GSTimestamp finishKey) {
	GSResult ret = gsSetPredicateFinishKeyByTimestamp(mPredicate, &finishKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Add key by string. Convert from C-API: gsAddPredicateKeyByString
 */
void RowKeyPredicate::add_key_by_string(const GSChar* key) {
	GSResult ret = gsAddPredicateKeyByString(mPredicate, key);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Add key by integer. Convert from C-API: gsAddPredicateKeyByTimestamp
 */
void RowKeyPredicate::add_key_by_integer(int32_t key) {
	GSResult ret = gsAddPredicateKeyByInteger(mPredicate, key);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Add key by long. Convert from C-API: gsAddPredicateKeyByLong
 */
void RowKeyPredicate::add_key_by_long(int64_t key) {
	GSResult ret = gsAddPredicateKeyByLong(mPredicate, key);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Get key type. Convert from C-API: gsGetPredicateKeyType
 */
GSType RowKeyPredicate::get_key_type() {
	GSType key;
	GSResult ret = gsGetPredicateKeyType(mPredicate, &key);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
	return key;
}

/**
 * Get start key by string. Convert from C-API: gsSetPredicateStartKeyByString
 */
const GSChar* RowKeyPredicate::get_start_key_as_string() {
	GSChar *startKey;
	GSResult ret = gsGetPredicateStartKeyAsString(mPredicate,
			(const GSChar **) &startKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}

	return startKey;
}

/**
 * Get start key by int. Convert from C-API: gsGetPredicateStartKeyAsInteger
 */
int32_t RowKeyPredicate::get_start_key_as_integer() {
	int32_t* startKey;
	GSResult ret = gsGetPredicateStartKeyAsInteger(mPredicate,
			(const int32_t **) &startKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
	return *startKey;
}

/**
 * Get start key by long. Convert from C-API: gsGetPredicateStartKeyAsLong
 */
int64_t RowKeyPredicate::get_start_key_as_long() {
	int64_t* startKey;
	GSResult ret = gsGetPredicateStartKeyAsLong(mPredicate,
			(const int64_t **) &startKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
	return *startKey;
}

/**
 * Set start key by timestamp. Convert from C-API: gsSetPredicateStartKeyByTimestamp
 */
GSTimestamp RowKeyPredicate::get_start_key_as_timestamp() {
	GSTimestamp* startKey;
	GSResult ret = gsGetPredicateStartKeyAsTimestamp(mPredicate,
			(const GSTimestamp **) &startKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
	return *startKey;
}

/**
 * Set start key by string. Convert from C-API: gsSetPredicateStartKeyByString
 */
void RowKeyPredicate::set_start_key_by_string(const GSChar* startKey) {
	GSResult ret = gsSetPredicateStartKeyByString(mPredicate, startKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Set start key by integer. Convert from C-API: gsSetPredicateStartKeyByInteger
 */
void RowKeyPredicate::set_start_key_by_integer(const int32_t startKey) {
	GSResult ret = gsSetPredicateStartKeyByInteger(mPredicate, &startKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Set start key by long. Convert from C-API: gsSetPredicateStartKeyByLong
 */
void RowKeyPredicate::set_start_key_by_long(const int64_t startKey) {
	GSResult ret = gsSetPredicateStartKeyByLong(mPredicate, &startKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Set start key by timestamp. Convert from C-API: gsSetPredicateStartKeyByTimestamp
 */
void RowKeyPredicate::set_start_key_by_timestamp(const GSTimestamp startKey) {
	GSResult ret = gsSetPredicateStartKeyByTimestamp(mPredicate, &startKey);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Get predicate key as integer. Convert from C-API: gsGetPredicateDistinctKeysAsInteger
 */
void RowKeyPredicate::get_predicate_distinct_keys_as_integer(
		const int **intList, size_t *size) {
	GSResult ret = gsGetPredicateDistinctKeysAsInteger(mPredicate, intList,
			size);
	if (ret != GS_RESULT_OK) {
		throw GSException(ret);
	}
}

/**
 * Get predicate key as long. Convert from C-API: gsGetPredicateDistinctKeysAsLong
 */
void RowKeyPredicate::get_predicate_distinct_keys_as_long(const long **longList,
		size_t *size) {
	GSResult ret = gsGetPredicateDistinctKeysAsLong(mPredicate, longList, size);
	if (ret != GS_RESULT_OK) {
		throw GSException(ret);
	}
}

/**
 * Get predicate key as timestamp. Convert from C-API: gsGetPredicateDistinctKeysAsTimestamp
 */
void RowKeyPredicate::get_predicate_distinct_keys_as_timestamp(
		const long **longList, size_t *size) {
	GSResult ret = gsGetPredicateDistinctKeysAsTimestamp(mPredicate, longList,
			size);
	if (ret != GS_RESULT_OK) {
		throw GSException(ret);
	}
}

/**
 * Add key by timestamp. Convert from C-API: gsAddPredicateKeyByTimestamp
 */
void RowKeyPredicate::add_key_by_timestamp(GSTimestamp key) {
	GSResult ret = gsAddPredicateKeyByTimestamp(mPredicate, key);
	if (ret != GS_RESULT_OK) {
		throw new GSException(ret);
	}
}

/**
 * Get predicate key as string. Convert from C-API: gsGetPredicateDistinctKeysAsString
 */
void RowKeyPredicate::get_predicate_distinct_keys_as_string(
		const GSChar * const ** stringList, size_t *size) {
	GSResult ret = gsGetPredicateDistinctKeysAsString(mPredicate, stringList,
			size);
	if (ret != GS_RESULT_OK) {
		throw GSException(ret);
	}
}

GSRowKeyPredicate* RowKeyPredicate::gs_ptr() {
	return mPredicate;
}

} /* namespace griddb */
