# Wizards Module
This module is intended to work with existing content types and fields within the USA.gov repo. It provides a front-end editor for Wizards and Wizard Steps using React, that communicates with the Drupal backend through a self-contained API.

---
#### To add a new field:
 1. Open `usagov_wizards/src/Services/WizardsService.php` in a code editor.
 2. Find the constant called `FIELD_DATA` at the top of the file.
 3. Add a field to the relevant section of this array. Adding to the `#shared` array shares that field across all content types (Wizard and Wizard Step). Adding to one of the other arrays only adds that field to the corresponding content type. If this is a standard Drupal field, typically the machine name is the key, and the value is a pair of fields.
	 1. name - This is the key being sent to React.
	 2. type - This is the type of field. Currently, only 	`"value"` is a valid field type. If other types need to be added, such as `"reference"`, `"language"`, `"revision"`, etc. need to be added, see the section below titled "To add a new field type:"
 4. In order for the module to be able to create the field and add it to relevant content types, some config files are needed. Specifically, one field config and one field storage config. These files should be YAML files that are placed in `usagov_wizards/config_files/field_config` and `usagov_wizards/config_files/field_storage_config` respectively. They should be named appropriately (match existing naming schemes in the folders.
 5. Optionally, to make the field appear in the Entity Form Display of it's content type, the `usagov_wizards/config_files/entity_form_display/mynodetype.yml` file must be edited to include a section for that field.
 
 ##### A Note on fields - All fields from the doc are listed in the `WizardsService.php` file. They are broken into groups. These groups are:
 1. Implemented in the module already.
 2. Not implemented in the module because they're marked in the doc as not to be used/shown in React.
 3. Not implemented in the module due to questions, but may need to be
 4. Not implemented in the module because they're not a Drupal field. This includes things like URL alias, menu settings, etc. Unsure which of these are actually needed in the module.
---
#### To add a new field type:
 1. Open `usagov_wizards/src/Services/WizardsService.php`
 2. Find the `getFieldValue` function.
 3. Add a case to the switch statement to handle the new field type.

---
#### To add a new API function:
 1. Open `usagov_wizards/usagov_wizards.routing.yml`
 2. Add an entry for the new API path, pointing to the controller: `\Drupal\usagov_wizards\Controller\WizardTreeApi::myApiFunction`
 3. Either use an existing or generate a new access function for permissions for this call. See existing routes for examples.
 4. Open `usagov_wizards/src/Controller/WizardTreeApi.php`
 5. Add your function `myApiFunction` to this file.
 6. Optionally, utilize or add functions within the `usagov_wizards/src/Services/WizardsService.php` service.

---
#### To adjust existing API permissions:

 1. Open `usagov_wizards/src/Controller/WizardTreeApi.php`
 2. Find the permissions function you wish to modify. E.g. `updateWizardTreeAccess` or `getWizardTreeAccess`
 3. Modify the function to use new permissions. Currently they point to `WizardsService.php` to request permissions from the service. If keeping that format, functions within the service should be modified (or created) as needed.
