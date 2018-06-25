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

%ignore griddb::AggregationResult::AggregationResult;

%ignore griddb::GSException::GSException;

%ignore griddb::TimestampUtils::TimestampUtils;

//Mark these methods below return new object, need to be free by target language

%feature("new") griddb::Container::query;

%feature("new") griddb::Query::fetch;

%feature("new") griddb::Query::get_row_set;

%feature("new") griddb::RowSet::get_next_query_analysis;

%feature("new") griddb::RowSet::get_next_aggregation;

%feature("new") griddb::Store::get_container;

%feature("new") griddb::Store::get_container_info;

%feature("new") griddb::Store::create_row_key_predicate;

%feature("new") griddb::Store::partition_info;

%feature("new") griddb::StoreFactory::get_store;

%feature("new") griddb::StoreFactory::get_default;

%include "gstype.i"

%include <std_except.i>

#if defined(SWIGPYTHON)
%include "gstype_python.i"
%module griddb_python_client
#elif defined(SWIGRUBY)
%include "gstype_ruby.i"
%module griddb_ruby_client
#elif defined(SWIGJAVASCRIPT)
%include "gstype_js_v8.i"
%module griddb_js_client
#elif defined(SWIGPHP)
%include "gstype_php.i"
%module griddb_php_client
#endif

%{
#include "gridstore.h"
#include "GSException.h"
#include "Resource.h"
#include "TimeSeriesProperties.h"
#include "ContainerInfo.h"
#include "Row.h"
#include "RowSet.h"
#include "Query.h"
#include "Container.h"
#include "PartitionController.h"
#include "RowKeyPredicate.h"
#include "Store.h"
#include "StoreFactory.h"
#include "TimestampUtils.h"
%}

%include <typemaps.i>
%catches(griddb::GSException);

%include "GSException.h"
%include "Resource.h"
%include "AggregationResult.h"
%include "TimeSeriesProperties.h"
%include "ContainerInfo.h"
%include "Row.h"
%include "RowSet.h"
%include "Query.h"
%include "Container.h"
%include "PartitionController.h"
%include "RowKeyPredicate.h"
%include "Store.h"
%include "StoreFactory.h"
%include "TimestampUtils.h"
