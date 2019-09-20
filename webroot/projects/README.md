This folder must exist under the webroot. It is where the OSCAL data files are stored and managed. 

Currently, the application will NOT automatically create a project folder if one doesn't exist.

If you wish to change the name or location of the project folder, please modify the **PROJECT_LOCATION** variable in the **oscal-config.php** file.

Future versions of the software will provide access control on a project-by-project basis, and enable more robust project directory placement. Projects may eventually be handled with a back-end XML server, such as [BaseX](http://basex.org/).