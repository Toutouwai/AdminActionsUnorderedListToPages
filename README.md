# Unordered List to Pages

An action for the [Admin Actions](https://modules.processwire.com/modules/process-admin-actions/) module for ProcessWire CMS/CMF. Creates a structure of new pages from an unordered list entered into a CKEditor field. The nesting of further unordered lists within the list will determine the nesting of the created pages. Created pages get a default template that you select, and you can override this default template per list item by specifying a template name between `[[` `]]` delimiters.

This action can be useful to quickly create a page structure; especially so if you are rebuilding an existing non-ProcessWire site that has a Sitemap page that you can copy and paste from.

## Usage

[Install](http://modules.processwire.com/install-uninstall/) the Unordered List to Pages module.

Visit the Admin Actions config screen and enable the "Unordered List to Pages" action for the roles who are allowed to use it.

Navigate to Admin Actions > Unordered List to Pages and fill out the config fields:

* Enter/paste an unordered list in the Source field.
    * Where a page should use a different template than the default template you can specify the template like so: `Page title [[template_name]]`
* Select a parent page that the new pages will be created under.
* Select the default template to use for the new pages.

Execute the action.

## Screenshots

#### Action config:
![2018-09-24_121519](https://user-images.githubusercontent.com/1538852/45934536-8e4a6b00-bff3-11e8-8440-99d6f4b1e436.png)

#### Result:
![2018-09-21_191234](https://user-images.githubusercontent.com/1538852/45934539-93a7b580-bff3-11e8-875f-bc83b73e0e88.png)
