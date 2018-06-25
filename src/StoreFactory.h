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

#ifndef _STORE_FACTORY_H_
#define _STORE_FACTORY_H_
#define CLIENT_VERSION "GridDB PHP Client 0.5.0"

#include "Resource.h"
#include "gridstore.h"
#include "Store.h"

#include <map>
#include <string>

using namespace std;

namespace griddb {

	/**
	 * Class GridStoreFactory to contain GSGridStoreFactory object.
	 * This class is implemented as singleton.
	 */
	class StoreFactory : public Resource {

		GSBool mIsAllRelated;

		GSGridStoreFactory* mFactory;

	public:
		~StoreFactory();

		static StoreFactory* get_default();
		Store* get_store(const GSPropertyEntry* props, int propsCount);
		void set_properties(const GSPropertyEntry* props, int propsCount);
		string get_version();
		/**
		* Release all GridStore created by this factory and related resources
		*/
		void close();

	private:
		StoreFactory();
		void set_factory(GSGridStoreFactory* factory);

	};
}

#endif
