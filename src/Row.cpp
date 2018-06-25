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

#include "Row.h"
#include "GSException.h"

namespace griddb {
	Row::Row(GSRow *row) : Resource(row), mRow(row) {
	}

	Row::~Row() {
		close();
	}

	/**
	 * Set field by String
	 */
	void Row::set_field_by_string(int32_t column, string value) {
		GSResult ret = gsSetRowFieldByString(mRow, column, value.c_str());
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get row field with string type.
	 */
	char* Row::get_field_as_string(int32_t column) {
		const GSChar *temp;
		GSResult ret = gsGetRowFieldAsString(mRow, column, &temp);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return (char*) temp;
	}

	/**
	 * Set field by bool
	 */
	void Row::set_field_by_bool(int32_t column, bool value) {
		GSResult ret = gsSetRowFieldByBool(mRow, column, value);
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get row field with boolean type.
	 */
	bool Row::get_field_as_bool(int32_t column) {
		GSBool value;
		GSResult ret = gsGetRowFieldAsBool(mRow, column, &value);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return (bool) value;
	}

	/**
	 * Set field by long
	 */
	void Row::set_field_by_long(int32_t column, int64_t value) {
		GSResult ret = gsSetRowFieldByLong(mRow, column, value);
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get row field with long type.
	 */
	long Row::get_field_as_long(int32_t column) {
		long value;
		GSResult ret = gsGetRowFieldAsLong(mRow, column, &value);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return value;
	}

	/**
	 * Set field by byte. Convert from C-Api: gsSetRowFieldByByte
	 */
	void Row::set_field_by_byte(int32_t column, int8_t value) {
		GSResult ret = gsSetRowFieldByByte(mRow, column, value);
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get row field as byte. Convert from C-Api: gsGetRowFieldAsByte
	 */
	int8_t Row::get_field_as_byte(int32_t column) {
		int8_t value;
		GSResult ret = gsGetRowFieldAsByte(mRow, column, &value);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return value;
	}

	/**
	 * Set field by short. Convert from C-Api: gsSetRowFieldByShort
	 */
	void Row::set_field_by_short(int32_t column, int16_t value) {
		GSResult ret = gsSetRowFieldByShort(mRow, column, value);
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get row field as short. Convert from C-API: gsGetRowFieldAsShort
	 */
	int16_t Row::get_field_as_short(int32_t column) {
		int16_t temp;
		GSResult ret = gsGetRowFieldAsShort(mRow, column, &temp);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return temp;
	}

	/**
	 * Set field by integer. Convert from C-Api: gsSetRowFieldByInteger
	 */
	void Row::set_field_by_integer(int32_t column, int32_t value) {
		GSResult ret = gsSetRowFieldByInteger(mRow, column, value);
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get row field as integer. Convert from C-Api: gsGetRowFieldAsInteger
	 */
	int32_t Row::get_field_as_integer(int32_t column) {
		int32_t temp;
		GSResult ret = gsGetRowFieldAsInteger(mRow, column, &temp);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return temp;
	}

	/**
	 * Set field by timestamp. Convert from C-Api: gsSetRowFieldByTimestamp
	 */
	void Row::set_field_by_timestamp(int32_t column, GSTimestamp value) {
		GSResult ret = gsSetRowFieldByTimestamp(mRow, column, value);
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get row field as timestamp. Convert from C-Api: gsGetRowFieldAsTimestamp
	 */
	GSTimestamp Row::get_field_as_timestamp(int32_t column) {
		GSTimestamp timestamp;
		GSResult ret = gsGetRowFieldAsTimestamp(mRow, column, &timestamp);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return timestamp;
	}

	/**
	 * Set field by float. Convert from C-Api: gsSetRowFieldByFloat
	 */
	void Row::set_field_by_float(int32_t column, float value) {
		GSResult ret = gsSetRowFieldByFloat(mRow, column, value);
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get row field as float. Convert from C-Api: gsGetRowFieldAsFloat
	 */
	float Row::get_field_as_float(int32_t column) {
		float temp;
		GSResult ret = gsGetRowFieldAsFloat(mRow, column, &temp);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return temp;
	}

	/**
	 * Set field by double. Convert from C-Api: gsSetRowFieldByDouble
	 */
	void Row::set_field_by_double(int32_t column, double value) {
		GSResult ret = gsSetRowFieldByDouble(mRow, column, value);
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get row field as double. Convert from C-Api: gsGetRowFieldAsDouble
	 */
	double Row::get_field_as_double(int32_t column) {
		double temp;
		GSResult ret = gsGetRowFieldAsDouble(mRow, column, &temp);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return temp;
	}

	/**
	 * Set row field as GSBlob. Convert from C-Api: gsSetRowFieldByBlob
	 */
	void Row::set_field_by_blob(int32_t column, const GSBlob* fieldValue) {
		GSResult ret = gsSetRowFieldByBlob(mRow, column, fieldValue);

		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Get row field as Blob. Convert from C-Api: gsGetRowFieldAsBlob
	 */
	void Row::get_field_as_blob(int32_t column, GSBlob *value) {
		GSResult ret = gsGetRowFieldAsBlob(mRow, column, value);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/**
	 * Returns the schema corresponding to the specified Row.
	 */
	ContainerInfo* Row::get_schema() {
		GSContainerInfo schemaInfo;
		GSResult ret = gsGetRowSchema(mRow, &schemaInfo);
		if(ret != GS_RESULT_OK) {
			throw GSException(ret);
		}

		return new ContainerInfo(&schemaInfo);
	}

	/**
	 * Close row.
	 */
	void Row::close() {
		if (mRow) {
			gsCloseRow(&mRow);
			mRow = NULL;
		}
	}

	/**
	 * Return raw pointer for other functions.
	 */
	GSRow* Row::gs_ptr() {
		return mRow;
	}

	char* Row::set_field_by_geometry(int32_t column, string value) {
		//TODO: update with C-API implementation
		return NULL;
	}

	const char* Row::get_field_as_geometry(int32_t column) {
		//TODO: update with C-API implementation
		return NULL;
	}

	bool Row::set_field_by_string_array(int32_t column, const GSChar *const *fieldValue, size_t size) {
		//TODO: update with C-API implementation
		return NULL;
	}

	void Row::get_field_as_string_array(int32_t column,
			const char* const ** fieldValue, size_t* size) {
		//TODO: update with C-API implementation
	}

	bool Row::set_field_by_byte_array(int32_t column, const int8_t *fieldValue, size_t size) {
		//TODO: update with C-API implementation
		return NULL;
	}

	void Row::get_field_as_byte_array(int32_t column, const int8_t **fieldValue, size_t *size) {
		//TODO: update with C-API implementation
	}

	bool Row::set_field_by_short_array(int32_t column, const int16_t *fieldValue, size_t size) {
		//TODO: update with C-API implementation
		return NULL;
	}

	void Row::get_field_as_short_array(int32_t column, const int16_t** fieldValue,
			size_t* size) {
		//TODO: update with C-API implementation
	}

	bool Row::set_field_by_integer_array(int32_t column,
			const int32_t *fieldValue, size_t size) {
		//TODO: update with C-API implementation
		return NULL;
	}

	void Row::get_field_as_integer_array(int32_t column, const int32_t** fieldValue,
			size_t* size) {
		//TODO: update with C-API implementation
	}

	bool Row::set_field_by_long_array(int32_t column, const int64_t *fieldValue, size_t size) {
		//TODO: update with C-API implementation
		return NULL;
	}

	void Row::get_field_as_long_array(int32_t column, const int64_t** fieldValue,
			size_t* size) {
		//TODO: update with C-API implementation
	}

	bool Row::set_field_by_float_array(int32_t column, const float *fieldValue, size_t size) {
		//TODO: update with C-API implementation
		return NULL;
	}

	void Row::get_field_as_float_array(int32_t column, const float** fieldValue,
			size_t* size) {
		//TODO: update with C-API implementation
	}

	void Row::set_field_by_double_array(int32_t column, const double *fieldValue, size_t size) {
		//TODO: update with C-API implementation
	}

	void Row::get_field_as_double_array(int32_t column, const double** fieldValue,
			size_t* size) {
		//TODO: update with C-API implementation
	}

	bool Row::set_field_by_timestamp_array(int32_t column, const GSTimestamp *fieldValue, size_t size) {
		//TODO: update with C-API implementation
		return NULL;
	}

	void Row::get_field_as_timestamp_array(int32_t column,
			const int64_t** fieldValue, size_t* size) {
		//TODO: update with C-API implementation
	}
}
