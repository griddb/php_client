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

#include "StoreFactory.h"
#include "GSException.h"

namespace griddb {

	StoreFactory::StoreFactory() :
			Resource(NULL), mFactory(NULL), mIsAllRelated(GS_FALSE) {
	}

	StoreFactory::~StoreFactory() {
		close();
	}

	/**
	 * Close factory.
	 */
	void StoreFactory::close() {
		if (mFactory != NULL) {
			gsCloseFactory(&mFactory, mIsAllRelated);
			mFactory = NULL;
		}
	}

	StoreFactory* StoreFactory::get_default() {
		GSGridStoreFactory* pFactory = gsGetDefaultFactory();
		StoreFactory* factory(new StoreFactory());
		factory->set_factory(pFactory);

		return factory;
	}

	/**
	 * Get gridstore object. Convert from C-API: gsGetGridStore
	 */
	Store* StoreFactory::get_store(
			const GSPropertyEntry* props, int propsCount) {
		GSGridStore *store;
		GSResult ret = gsGetGridStore(mFactory, props, propsCount, &store);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
		return new Store(store);
	}

	/**
	 * Changes the settings for this Factory.
	 * The changed settings will be reflected in GridStore object which is already created by the specified Factory and GridStore object which will be created later by the Factory.
	 */
	void StoreFactory::set_properties(const GSPropertyEntry* props,
			int propsCount) {
		GSResult ret = gsSetFactoryProperties(mFactory, props, propsCount);

		// Check ret, if error, throw exception
		if (ret != GS_RESULT_OK) {
			throw GSException(ret);
		}
	}

	/*
	 * Return current client version
	 */
	string StoreFactory::get_version() {
		return CLIENT_VERSION;
	}

	void StoreFactory::set_factory(GSGridStoreFactory* factory) {
		mFactory = factory;
	}

}
