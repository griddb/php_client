GridDB PHP Client

## Overview

GridDB PHP Client is developed using GridDB C Client and [SWIG](http://www.swig.org/) (Simplified Wrapper and Interface Generator).  

## Operating environment

Building of the library and execution of the sample programs have been checked in the following environment.

    OS:              CentOS 7.4(x64)
    SWIG:            The development (master) version (commit ID 3bea8f6b7e0449946c12a0ce2c5aa893d6026883)
    GCC:             4.8.5
    PHP:             7
    GridDB Server:   3.0 (CE)
    GridDB C Client: 3.0 (CE)

## QuickStart
### Preparations

Install SWIG as below.

    $ wget https://sourceforge.net/projects/pcre/files/pcre/8.39/pcre-8.39.tar.gz
    $ tar xvfz pcre-8.39.tar.gz
    $ cd pcre-8.39
    $ ./configure
    $ make
    $ make install

    $ git clone https://github.com/swig/swig
    $ cd swig
    $ ./autogen.sh
    $ ./configure
    $ make
    $ make install

Set LIBRARY_PATH. 

    export LIBRARY_PATH=$LIBRARY_PATH:<C client library file directory path>

### Build and Run 

    1. Execute the command on project directory.

    $ make

	2. Write the following desctiption in /etc/php.ini.

	extension=<PHP client library file directory path>

    3. Include 'griddb_php_client.php' in PHP.

### How to run sample

GridDB Server need to be started in advance.

    1. Set LD_LIBRARY_PATH.

        export LD_LIBRARY_PATH=${LD_LIBRARY_PATH}:<C client library file directory path>

    2. The command to run sample

        $ php sample/sample1.php <GridDB notification address> <GridDB notification port>
            <GridDB cluster name> <GridDB user> <GridDB password>
          -->Person: name=name02 status=false count=2 lob=ABCDEFGHIJ

## Function

(available)
- STRING, BOOL, BYTE, SHORT, INTEGER, LONG, FLOAT, DOUBLE, TIMESTAMP, BLOB type for GridDB
- put single row, get row with key
- normal query, aggregation with TQL

(not available)
- Multi-Put/Get/Query (batch processing)
- GEOMETRY, Array type for GridDB
- timeseries compression
- timeseries-specific function like gsAggregateTimeSeries, gsQueryByTimeSeriesSampling in C client
- trigger, affinity

Please refer to the following files for more detailed information.  
- [PHP Client API Reference](https://griddb.github.io/go_client/PHPAPIReference.htm)

About API:
- When an error occurs, an exception GSException is thrown.
- Based on C Client API. Please refer to C Client API Reference for the detailed information.
  * [API Reference](https://griddb.github.io/griddb_nosql/manual/GridDB_API_Reference.html)
  * [API Reference(Japanese)](https://griddb.github.io/griddb_nosql/manual/GridDB_API_Reference_ja.html)

Note:
1. The current API might be changed in the next version. e.g. ContainerInfo()
2. References to objects obtained using the get method described below must be referenced prior to executing the methods. When referencing after the execution of the get methods, please copy the basic data type such as string from the object and reference it to the copied data.
    - get_row_xxx
    - get_partition_xxx
    - get_container_info

   Please refer to the following note from C Client API Reference document for detailed information of the reason behind the implementation:

    "In order to store the variable-length data such as string or array, it uses a temporary memory area.
    This area is valid until this function or similar functions which use a temporary memory area.
    The behavior is undefined when the area which has been invalidated is accessed."

## Community

  * Issues  
    Use the GitHub issue function if you have any requests, questions, or bug reports. 
  * PullRequest  
    Use the GitHub pull request function if you want to contribute code.
    You'll need to agree GridDB Contributor License Agreement(CLA_rev1.1.pdf).
    By using the GitHub pull request function, you shall be deemed to have agreed to GridDB Contributor License Agreement.

## License
  
  GridDB PHP Client source license is Apache License, version 2.0.
