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

#ifndef _ROW_H_
#define _ROW_H_

#include <vector>
#include <string>
#include <memory>

#include "Resource.h"
#include "gridstore.h"
#include "ContainerInfo.h"

using namespace std;

namespace griddb {

class Row : public Resource {
	GSRow *mRow;
public:
	Row(GSRow *row);
	~Row();

	void set_field_by_string(int32_t column, string value);
	char* get_field_as_string(int32_t column);
	void set_field_by_bool(int32_t column, bool value);
	bool get_field_as_bool(int32_t column);
	void set_field_by_long(int32_t column, int64_t value);
	long get_field_as_long(int32_t column);
	void set_field_by_byte(int32_t column, int8_t value);
	int8_t get_field_as_byte(int32_t column);
	void set_field_by_short(int32_t column, int16_t value);
	int16_t get_field_as_short(int32_t column);
	void set_field_by_integer(int32_t column, int32_t value);
	int32_t get_field_as_integer(int32_t column);
	void set_field_by_float(int32_t column, float value);
	float get_field_as_float(int32_t column);
	void set_field_by_double(int32_t column, double value);
	double get_field_as_double(int32_t column);
	void set_field_by_timestamp(int32_t column, GSTimestamp value);
	GSTimestamp get_field_as_timestamp(int32_t column);

	char* set_field_by_geometry(int32_t column, string value);
	const char* get_field_as_geometry(int32_t column);
	void set_field_by_blob(int32_t column, const GSBlob *fieldValue);
	void get_field_as_blob(int32_t column, GSBlob *value);
	bool set_field_by_string_array(int32_t column, const GSChar *const *fieldValue, size_t size);
	void get_field_as_string_array(int32_t column,
			const char * const **fieldValue, size_t *size);
	bool set_field_by_byte_array(int32_t column, const int8_t *fieldValue, size_t size);
	//TODO: Handle 2 return value : double pointer and size of array.
	void get_field_as_byte_array(int32_t column, const int8_t **fieldValue, size_t *size);
	bool set_field_by_short_array(int32_t column, const int16_t *fieldValue, size_t size);
	void get_field_as_short_array(int32_t column, const int16_t **fieldValue,
			size_t *size);
	bool set_field_by_integer_array(int32_t column, const int32_t *fieldValue, size_t size);
	void get_field_as_integer_array(int32_t column, const int32_t **fieldValue,
			size_t *size);
	bool set_field_by_long_array(int32_t column, const int64_t *fieldValue, size_t size);
	void get_field_as_long_array(int32_t column, const int64_t **fieldValue,
			size_t *size);
	bool set_field_by_float_array(int32_t column, const float *fieldValue, size_t size);
	void get_field_as_float_array(int32_t column, const float **fieldValue,
			size_t *size);
	void set_field_by_double_array(int32_t column, const double *fieldValue, size_t size);
	void get_field_as_double_array(int32_t column, const double **fieldValue,
			size_t *size);
	bool set_field_by_timestamp_array(int32_t column, const GSTimestamp *fieldValue, size_t size);
	void get_field_as_timestamp_array(int32_t column,
			const int64_t **fieldValue, size_t *size);

	ContainerInfo* get_schema();

	GSRow* gs_ptr();

private:
	void close();
};
}

#endif /* _ROW_H_ */
