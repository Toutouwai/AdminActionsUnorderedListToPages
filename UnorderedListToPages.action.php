<?php namespace ProcessWire;

class UnorderedListToPages extends ProcessAdminActions {

	protected $executeButtonLabel = 'Create Pages';

	protected function defineOptions() {

		$cheatsheet = "
		<p>Each list item in the Source field will create a page using the list item text as the page title.<p>
		<p>If you want to override the default template for an item you can specify a template name after the page title between double square brackets. If the template doesn't already exist it will be created.<br>
		Example: <code>Page title [[staff_members]]</code></p>
		<p>You can also specify one or more allowed child templates for a template like so: <code>[[staff_members > manager tech_support]]</code><br>
		This would create the page using the <code>staff_members</code> template, and set the allowed child templates of <code>staff_members</code> to <code>manager</code> and <code>tech_support</code>.</p>
		<p>Alternatively you can specify one or more allowed parent templates for a template like so: <code>[[manager < staff_members]]</code><br>
		This would create the page using the <code>manager</code> template, and set the allowed parent templates of <code>manager</code> to <code>staff_members</code>.</p>
		";

		return array(
			array(
				'name' => 'cheatsheet',
				'label' => 'Cheatsheet for Source field',
				'type' => 'markup',
				'value' => $cheatsheet,
				'collapsed' => Inputfield::collapsedYes,
			),
			array(
				'name' => 'source',
				'label' => 'Source',
				'description' => 'Enter/paste unordered list here.',
				'type' => 'InputfieldCKEditor',
				'toolbar' => 'BulletedList, Outdent, Indent, Replace, Source',
				'rows' => 10,
				'required' => true,
			),
			array(
				'name' => 'parent_page',
				'label' => 'Parent page',
				'description' => 'Page that new page structure will be created under.',
				'type' => 'pageListSelect',
				'required' => true,
			),
			array(
				'name' => 'template',
				'label' => 'Default template to use for new pages',
				'type' => 'select',
				'options' => $this->wire()->templates->find('flags=0')->explode('name', array('key' => 'name')),
				'required' => true,
			),
		);

	}

	protected $template;

	protected $page_count = 0;

	protected function executeAction($options) {

		require_once __DIR__ . '/simple_html_dom_rps.php';

		$parent_page = $this->wire()->pages->get((int) $options['parent_page']);
		$this->template = $this->wire()->templates->get((string) $options['template']);

		$source = str_replace(array('<p>', '</p>'), '', $options['source']);
		$html = str_get_html($source);
		$top_ul = $html->find('ul', 0);
		if(!$top_ul) {
			$this->failureMessage = htmlentities('No <ul> element found.');
			return false;
		}

		$this->listItemsToPages($top_ul->children(), $parent_page);

		$this->successMessage = "Created {$this->page_count} new pages.";
		return true;

	}

	/**
	 * Create pages from list items
	 *
	 * @param array $items
	 * @param Page $parent
	 */
	protected function listItemsToPages($items, Page $parent) {
		$sanitizer = $this->wire()->sanitizer;
		foreach($items as $item) {

			$page_title = $sanitizer->text($item->find('text', 0)->innertext, array('convertEntities' => true));
			if(!$page_title) continue;
			$p = new Page();
			$template = $this->template;

			// Use override template if given
			if(strpos($page_title, '[[') !== false) {
				$regex = '/\s*\[\[([a-zA-Z0-9_\-\>\< ]+)\]\]/';
				preg_match_all($regex, $page_title, $matches, PREG_SET_ORDER);
				if(count($matches)) {
					$template_str = trim($matches[0][1]);
					if(strpos($template_str, '>') !== false) {

						// Template string includes allowed templates for children
						$pieces = explode('>', $template_str);
						$template_name = $sanitizer->name(trim($pieces[0]));
						if($template_name) {
							$template = $this->getTemplate($template_name);
							$child_template_names = explode(' ', trim($pieces[1]));
							$child_template_ids = array();
							foreach($child_template_names as $child_template_name) {
								$child_template_name = $sanitizer->name($child_template_name);
								if(!$child_template_name) continue;
								$child_template = $this->getTemplate($child_template_name);
								$child_template_ids[] = $child_template->id;
							}
							if(count($child_template_ids)) {
								$template->childTemplates = $child_template_ids;
								$template->save();
							}
						}

					} elseif(strpos($template_str, '<') !== false) {

						// Template string includes allowed templates for parents
						$pieces = explode('<', $template_str);
						$template_name = $sanitizer->name(trim($pieces[0]));
						if($template_name) {
							$template = $this->getTemplate($template_name);
							$parent_template_names = explode(' ', trim($pieces[1]));
							$parent_template_ids = array();
							foreach($parent_template_names as $parent_template_name) {
								$parent_template_name = $sanitizer->name($parent_template_name);
								if(!$parent_template_name) continue;
								$parent_template = $this->getTemplate($parent_template_name);
								$parent_template_ids[] = $parent_template->id;
							}
							if(count($parent_template_ids)) {
								$template->parentTemplates = $parent_template_ids;
								$template->save();
							}
						}

					} else {

						// Standard template string
						$template_name = $sanitizer->name($template_str);
						if($template_name) $template = $this->getTemplate($template_name);

					}
					$page_title = str_replace($matches[0][0], '', $page_title);
				}
			}
			$p->template = $template;
			$p->parent = $parent;
			$p->title = $page_title;
			$this->wire()->pages->save($p, array('adjustName' => true));
			$this->page_count++;

			// Go recursive when needed
			$sub_ul = $item->find('ul', 0);
			if($sub_ul) $this->listItemsToPages($sub_ul->children(), $p);

		}
	}

	protected function getTemplate($template_name) {
		// Return early if template already exists
		$existing_template = $this->wire()->templates->get($template_name);
		if($existing_template) return $existing_template;

		$fg = new Fieldgroup();
		$fg->name = $template_name;
		$fg->add($this->wire()->fields->get('title'));
		$fg->save();
		$t = new Template();
		$t->name = $template_name;
		$t->fieldgroup = $fg;
		$t->save();
		return $t;
	}

}
