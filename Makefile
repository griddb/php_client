SWIG = swig -DSWIGWORDSIZE64
CXX = g++

ARCH = $(shell arch)
LDFLAGS = -lpthread -lrt -lgridstore
LDFLAGS_PHP =

CPPFLAGS = -fPIC -std=c++0x -g -O2
INCLUDES = -Iinclude -Isrc
INCLUDES_PHP = $(INCLUDES)	\
			  -I.						\
			  -I/usr/include/php 		\
			  -I/usr/include/php/main 	\
			  -I/usr/include/php/TSRM 	\
			  -I/usr/include/php/Zend 	\
			  -I/usr/include/php/ext 	\
			  -I/usr/include/php/ext/date/lib

CPPFLAGS_PHP  = $(CPPFLAGS) $(INCLUDES_PHP)

PROGRAM = griddb_php_client.so

SOURCES = 	  src/TimeSeriesProperties.cpp \
		  src/ContainerInfo.cpp			\
  		  src/AggregationResult.cpp	\
		  src/Container.cpp			\
		  src/Store.cpp			\
		  src/StoreFactory.cpp	\
		  src/PartitionController.cpp	\
		  src/Query.cpp				\
		  src/QueryAnalysisEntry.cpp			\
		  src/RowKeyPredicate.cpp	\
		  src/RowSet.cpp			\
		  src/TimestampUtils.cpp			\
		  src/Field.cpp \
		  src/Util.cpp

all: $(PROGRAM)

SWIG_DEF = src/griddb.i

SWIG_PHP_SOURCES    = src/griddb_php_client.cxx

OBJS = $(SOURCES:.cpp=.o)
SWIG_PHP_OBJS = $(SWIG_PHP_SOURCES:.cxx=.o)

$(SWIG_PHP_SOURCES) : $(SWIG_DEF)
	$(SWIG) -outdir . -o $@ -c++ -php7 $<


.cpp.o:
	$(CXX) $(CPPFLAGS) -c -o $@ $(INCLUDES) $<

$(SWIG_PHP_OBJS): $(SWIG_PHP_SOURCES)
	$(CXX) $(CPPFLAGS_PHP) -c -o $@ $<

griddb_php_client.so: $(OBJS) $(SWIG_PHP_OBJS)
	$(CXX) -shared  -o $@ $(OBJS) $(SWIG_PHP_OBJS) $(LDFLAGS) $(LDFLAGS_PHP)

clean:
	rm -rf $(OBJS) $(SWIG_PHP_OBJS)
	rm -rf $(SWIG_PHP_SOURCES)
	rm -rf $(PROGRAM)
