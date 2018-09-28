# Unordered List to Pages

An action for the [Admin Actions](https://modules.processwire.com/modules/process-admin-actions/) module for ProcessWire CMS/CMF. Creates a structure of new pages from an unordered list entered into a CKEditor field. The nesting of further unordered lists within the list will determine the nesting of the created pages. Created pages get a default template that you select, and you can override this default template per list item by specifying a template name between `[[` `]]` delimiters.

This action can be useful to quickly create a page structure; especially so if you are rebuilding an existing non-ProcessWire site that has a Sitemap page that you can copy and paste from.

## Usage

[Install](http://modules.processwire.com/install-uninstall/) the Unordered List to Pages module.

Visit the Admin Actions config screen and enable the "Unordered List to Pages" action for the roles who are allowed to use it.

Navigate to Admin Actions > Unordered List to Pages and fill out the config fields:

### Source

Enter/paste an unordered list in the Source field. There is a "cheatsheet" field above that explains the syntax you can use to set some template options for each list item.

If you want to override the default template for an item you can specify a template name after the page title between double square brackets. If the template doesn't already exist it will be created.
Example: `Page title [[staff_members]]`

You can also specify one or more allowed child templates for a template like so: `[[staff_members > manager tech_support]]`
This would create the page using the `staff_members` template, and set the allowed child templates of `staff_members` to `manager` and `tech_support`.

Alternatively you can specify one or more allowed parent templates for a template like so: `[[manager < staff_members]]`
		This would create the page using the `manager` template, and set the allowed parent templates of `manager` to `staff_members`.

### Parent page

Select a parent page that the new pages will be created under.

### Default template

Select the default template to use for the new pages.

## Screenshots

#### Action config:
![2018-09-28_204032](https://user-images.githubusercontent.com/1538852/46197800-e6200380-c35e-11e8-92a2-d8ffd3c59c77.png)

#### Result:
![2018-09-28_204647](https://user-images.githubusercontent.com/1538852/46198069-a6a5e700-c35f-11e8-8160-63d566e49f26.png)

