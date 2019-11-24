# OSCAL-GUI
An open-source graphical user interface (GUI) for interacting with Open Security Controls Assessment Language (OSCAL) files, developed under a collaborative effort between the [Federal Risk and Authorization Management Program (FedRAMP) Program Management Office (PMO)](https://fedramp.gov) and the [National Institute of Science and Technology (NIST) OSCAL Team](https://nist.gov/OSCAL).

This tool is designed to work with Extensible Markup Language (XML)-based OSCAL files, and enables conversion of OSCAL files between XML, JavaScript Object Notation (JSON), and Yet another Markup Language (YAML) in any direction.

## OSCAL-GUI Short Term Roadmap and Progress:

### Complete:
- Core GUI framework (Presentation, Menus, etc.)
- Core OSCAL functions (in oscal-functions.php)
- Basic Project Management Functions
- OSCAL File Import (XML and JSON)
- XML Schema Description (XSD) Update Download and Pre-compile
- Validation of OSCAL files using XSD with friendly error reporting
- OSCAL Profile Resolution: Collapses OSCAL Profiles that contain Pointers to Catalogs, down to a single Catalog.
- OSCAL Format Conversion (XML to/from JSON)

### In Progress:
- Metadata Management (all OSCAL layers)
- Profile Creation/Manipulation
- Catalog Creation/Manipulation
- Back-Matter Management (all OSCAL layers)

### Future:
- OSCAL Format Conversion (XML/JSON to/from YAML)
- SSP Creation/Manipulation
- SSP FedRAMP Validation
- Identity management
- Access control (on a project-by-project basis)
Functionality related to the assessment layers will be planned and developed as the syntax for those OSCAL layers are defined.

## Technical Requirements
- [PHP 7+](https://www.php.net/downloads.php) (Will likely work with PHP 5+; however, this has not been verified)
- Java Runtime: Only required by Saxon-HE (Home Edition) to convert between OSCAL formats (XML to/from JSON and YAML)
- Browser Support: Most major browsers are supported, **EXCEPT Microsoft Internet Explorer and Edge**, which do not support server-side execution (SSE) via javascript/eventSource. The latest version of Google Chrome or Mozilla Firefox is recommended.

This can run on a stand-alone workstation using PHP's integrated web server, or placed on a web server such as Apache. If placed on a web server, must run in the webroot directory.  

### IMPORTANT
While this code will function correctly on a multi-user web server, there is currently no identity management or access control mechanisms built in. All OSCAL files managed by this tool will be exposed to everyone with access to the web server. These capabilities will be added after other OSCAL functionality is complete and stabilized. On a web server, each project is contained within a directory. A web server administrator could apply permissions to the project's directory as the operating system (OS) level.

## Additional Technologies Used
This tool includes and uses the following open-source modules:
- [Jodit 3.2.44](https://xdsoft.net/jodit/) to enable rich editing of mixed and prose OSCAL fields
- [Saxon-HE (Home Edition)](https://saxonica.com/download/java.xml) Version 9.9: Used only to process the XSLT 3.0 files used to convert between OSCAL formats (XML to/from JSON and YAML) 

## OSCAL Layers: Brief Overview

| Layer      | Description     | Syntax Status     |
| :------------- | :---------- | :-----------: |
|  **Catalog** | Syntax for describing control definitions, such as those that appear in NIST 800-53, Revision 4 and ISO-27001. | Fully Drafted |
| **Profile**  | Syntax for describing control baselines, such as the FedRAMP High, Moderate, and Low baselines. | Fully Drafted |
|  **Implementation** | Syntax for describing System Security Plan (SSP) content, as well as vendor-provided component content. | Under Development |
|  **Assessment** | Syntax for describing content related to Security Assessment Plan (SAP), assessment activities, and evidence gathering. | CY 2019 Q4 |
|  **Assessment Results** | Syntax for describing Security Assessment Report (SAR) and Plan of Action and Milestones (POA&M) content. | CY 2019 Q4 |

For more information about OSCAL's architecture, please visit:
[https://pages.nist.gov/OSCAL/docs/](https://pages.nist.gov/OSCAL/docs/)
