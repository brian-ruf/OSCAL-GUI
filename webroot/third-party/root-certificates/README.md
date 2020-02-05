If you wish to have cURL perform certificate validation, you must have a cacert.pem file here, such as the one available at https://curl.haxx.se/docs/caextract.html (this is not an endorsement of this site - it is an example).

If a file named cacert.pem is present here, PHP will automatically use it to perform certificate validation.
If no cacert.pem file is preent, PHP will not perform certificate validation.