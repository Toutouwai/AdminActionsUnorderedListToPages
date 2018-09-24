<?php

class UnorderedListToPages extends ProcessAdminActions {

	protected function defineOptions() {

		return array(
			array(
				'name' => 'source',
				'label' => 'Source',
				'description' => 'Enter/paste unordered list here. Where a page should use a different template than the default template selected below you can specify the template like so: Page title [[template_name]]',
				'type' => 'InputfieldCKEditor',
				'toolbar' => 'BulletedList, Outdent, Indent, Replace',
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
				'description' => '',
				'type' => 'select',
				'options' => $this->wire()->templates->getAll()->explode('name', array('key' => 'name')),
				'required' => true,
			),
		);

	}

	protected $template;

	protected $page_count = 0;

	protected function executeAction($options) {

		require_once __DIR__ . '/simple_html_dom.php';

		$parent_page = $this->wire()->pages->get((int) $options['parent_page']);
		$this->template = $this->wire()->templates->get((string) $options['template']);

		$html = str_get_html($options['source']);
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
		foreach($items as $item) {
			$page_title = $this->wire()->sanitizer->text($item->find('text', 0)->innertext, array('convertEntities' => true));
			if(!$page_title) continue;
			$p = new Page();
			$template = $this->template;
			// Use override template if given
			if(strpos($page_title, '[[') !== false) {
				$regex = '/\s*\[\[([a-zA-Z0-9_\-]+)\]\]/';
				preg_match_all($regex, $page_title, $matches, PREG_SET_ORDER);
				if(count($matches)) {
					$template_override = $this->wire()->templates->get($matches[0][1]);
					if($template_override) {
						$template = $template_override;
						$page_title = str_replace($matches[0][0], '', $page_title);
					}
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

}
