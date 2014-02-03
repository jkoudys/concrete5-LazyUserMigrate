concrete5 Lazy User Migrate
=========================

Quick and simple way to export your concrete5 users from one site and import them into another. Intended primarily for in-place migrations. Supports password migrations for same-salt instances, and user attributes.

Description
-----------
This is a lazy way to migrate users, not a way to migrate lazy users. There are a few bigger solutions out there intended for non-developers who wish to import large CSV files of users. These solutions seem best-suited for bulk imports from a separate database, e.g. converting your payroll spreadsheet into a csv you can load into concrete5. The intent of this simple tool is to meet a requirement for migration or syncing from one user DB to another. So long as both instances have the same SALT, your newly migrated users will still have their same password, too.

All user attributes which are defined with a value() function that does a basic toString() (and can be similarly created) will also be exported/imported. There's a section in the code reserved for you to add your own special-casing for any attribute key that uses a custom object.

Installation & Use
------------------
Copy the userexport.xml.php to the tools directory of your "from" site (e.g. oldsite/tools), and the userimport.php to the tools directory of the "to" site (e.g. newsite.tools). The userexport.xml can be retrieved directly, if you wish to save it somewhere else. The userimport takes the URL passed in the 'xml' parameter, and loads the xml into the user DB. For example:

`http://mysite.com/tools/userimport&xml=http://mysite.com/oldinstance/tools/userexport.xml`

userimport parameters:
* xml : The URL to the XML file containing your users to import. Usually, but not required to be, exported by userexport.xml.
* json : The URL to the JSON file containing your users to import. (unimplemented)
* validate : 1 to mark all created users as valid, 0 to require they be validated. (default 1)
* checkAttributes : Set to validate that user attributes in the "from" site exist in the "to", and do not attempt to update them if they don't. Causes a performance hit if enabled, so only use this if you know the two sites won't match up. (default false)

Notes
-----
The export/import is using XML out of a combined sense of curiosity to learn XML in PHP and masochism. If you'd like this tool but would much prefer it use JSON, I'd be happy to include that option, and happier if you'd write it in and contribute it.

WARNING
-------
Make sure you set proper access control around these tools (even webserver-level filters against the URLs are a good idea), because it's essentially creating two new attack vectors: retrieval of the one-way hashes for your users (which would require the SALT to try to brute-force), and bulk user load (which could be used for a pretty heavy DoS attack.)

