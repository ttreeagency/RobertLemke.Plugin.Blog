<?php
namespace RobertLemke\Plugin\Blog\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Blog".                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use RobertLemke\Rss\Channel;
use RobertLemke\Rss\Feed;
use RobertLemke\Rss\Item;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Mvc\Routing\UriBuilder;
use TYPO3\Media\Domain\Model\ImageVariant;
use TYPO3\TYPO3CR\Domain\Model\Node;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeTemplate;

/**
 * The posts controller for the Blog package
 *
 * @Flow\Scope("singleton")
 */
class PostController extends ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Service\NodeTypeManager
	 */
	protected $nodeTypeManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\I18n\Service
	 */
	protected $i18nService;

	/**
	 * @Flow\Inject
	 * @var \RobertLemke\Plugin\Blog\Service\ContentService
	 */
	protected $contentService;

	/**
	 * Displays a list of most recent blog posts
	 *
	 * @return string
	 */
	public function indexAction() {
		$blogDocumentNode = $this->request->getInternalArgument('__documentNode');
		if ($blogDocumentNode !== NULL) {
			/** @var NodeInterface $blogDocumentNode */
			$this->view->assign('postsNode', $blogDocumentNode);
			$this->view->assign('hasPostNodes', $blogDocumentNode->hasChildNodes('RobertLemke.Plugin.Blog:Post'));
		} else {
			return 'Error: The Blog Post Plugin cannot determine the current document node. Please make sure to include this plugin only by inserting it into a page / document.';
		}
	}

}
?>