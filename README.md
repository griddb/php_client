GridDB PHP Client

## Overview

GridDB PHP Client is developed using GridDB C Client and [SWIG](http://www.swig.org/) (Simplified Wrapper and Interface Generator).  

## Operating environment

Building of the library and execution of the sample programs have been checked in the following environment.

    OS:              CentOS 7.4(x64)
    SWIG:            4.0.0
    GCC:             4.8.5
    PHP:             7
    GridDB Server:   4.2 (CE)
    GridDB C Client: 4.2 (CE)

## QuickStart
### Preparations

Install SWIG as below.

    $ wget https://prdownloads.sourceforge.net/swig/swig-4.0.0.tar.gz
    $ tar xvfz swig-4.0.0.tar.gz
    $ cd swig-4.0.0
    $ ./configure
    $ make
    $ sudo make install
   
    Note: If CentOS, you might need to install pcre in advance.
    $ sudo yum install pcre2-devel.x86_64

Install PHP7 and GridDB C Client.

Set LIBRARY_PATH. 

    export LIBRARY_PATH=$LIBRARY_PATH:<C client library file directory path>

### Build and Run 

    1. Execute the command on project directory.

    $ make

    2. Include 'griddb_php_client.php' in PHP.

### How to run sample (with Command Line)

GridDB Server need to be started in advance.

	1. Write the following desctiption in /etc/php.ini.

	    extension=<PHP client library file directory path>
	
    2. Set LD_LIBRARY_PATH.

        export LD_LIBRARY_PATH=${LD_LIBRARY_PATH}:<C client library file directory path>

    3. The command to run sample

        $ php sample/sample1.php <GridDB notification address> <GridDB notification port>
            <GridDB cluster name> <GridDB user> <GridDB password>
          -->Person: name=name02 status=false count=2 lob=ABCDEFGHIJ

### How to run sample (with Web Browser)

GridDB Server need to be started in advance.

In the case of Web Server: Apache/2.2.15, please use the following steps.
 
    1. Store griddb_php_client.php and sample/sample1_web.php in /var/www/html.

    2. Store griddb_php_client.so in /usr/lib64/php/modules.

    3. Add extension for griddb_php_client.so in /etc/php.ini.rpmsave or /etc/php/7.2/apache2/php.ini.
        extension=griddb_php_client.so

    4. Set LD_LIBRARY_PATH.

        export LD_LIBRARY_PATH=${LD_LIBRARY_PATH}:<C client library file directory path>

    5. Restart httpd/apache.

    6. In web browser, run : http://localhost:8000/sample1_web.php.

    7. Click submit button after entering address, port, cluster, user and password.

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
- [PHP Client API Reference](https://griddb.github.io/php_client/PHPAPIReference.htm)

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
