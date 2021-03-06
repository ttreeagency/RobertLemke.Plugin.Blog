<?php
namespace RobertLemke\Plugin\Blog\Controller;

/*                                                                         *
 * This script belongs to the TYPO3 Flow package "RobertLemke.Plugin.Blog" *
 *                                                                         *
 * It is free software; you can redistribute it and/or modify it under     *
 * the terms of the MIT License.                                           *
 *                                                                         *
 * The TYPO3 project - inspiring people to share!                          *
 *                                                                         */

use RobertLemke\Rss\Channel;
use RobertLemke\Rss\Feed;
use RobertLemke\Rss\Item;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Mvc\Routing\UriBuilder;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

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

	/**
	 * Renders an RSS feed
	 *
	 * @return string
	 */
	public function rssAction() {
		/** @var NodeInterface $blogDocumentNode */
		$rssDocumentNode = $this->request->getInternalArgument('__documentNode');
		if ($rssDocumentNode === NULL) {
			return 'Error: The Blog Post Plugin cannot determine the current document node. Please make sure to include this plugin only by inserting it into a page / document.';
		}

		$blogDocumentNode = $rssDocumentNode->getParent();

		$uriBuilder = new UriBuilder();
		$uriBuilder->setRequest($this->request->getMainRequest());
		$uriBuilder->setCreateAbsoluteUri(TRUE);

		if ($this->settings['feed']['uri'] !== '') {
			$feedUri = $this->settings['feed']['uri'];
		} else {
			$uriBuilder->setFormat('xml');
			$feedUri = $uriBuilder->uriFor('show', array('node' => $rssDocumentNode), 'Frontend\Node', 'TYPO3.Neos');
		}

		$channel = new Channel();
		$channel->setTitle($this->settings['feed']['title']);
		$channel->setDescription($this->settings['feed']['description']);
		$channel->setFeedUri($feedUri);
		$channel->setWebsiteUri($this->request->getHttpRequest()->getBaseUri());
		$channel->setLanguage((string)$this->i18nService->getConfiguration()->getCurrentLocale());

		foreach ($blogDocumentNode->getChildNodes('RobertLemke.Plugin.Blog:Post') as $postNode) {
			/* @var $postNode NodeInterface */

			$uriBuilder->setFormat('html');
			$postUri = $uriBuilder->uriFor('show', array('node' => $postNode), 'Frontend\Node', 'TYPO3.Neos');

			$item = new Item();
			$item->setTitle($postNode->getProperty('title'));
			$item->setGuid($postNode->getIdentifier());
			$item->setPublicationDate($postNode->getProperty('datePublished'));
			$item->setItemLink((string)$postUri);
			$item->setCommentsLink((string)$postUri . '#comments');
			$item->setCreator($postNode->getProperty('author'));
#			$item->setCategories(array('test'));

			$description = $this->contentService->renderTeaser($postNode) . ' <a href="' . $postUri . '">Read more</a>';
			$item->setDescription($description);

			if ($this->settings['feed']['includeContent'] === TRUE) {
				$item->setContent($this->contentService->renderContent($postNode));
			}

			$channel->addItem($item);
		}

			// This won't work yet (plugin sub responses can't set headers yet) but keep that as a reminder:
		$headers = $this->response->getHeaders();
		$headers->setCacheControlDirective('s-max-age', 3600);
		$headers->set('Content-Type', 'application/rss+xml');

		$feed = new Feed();
		$feed->addChannel($channel);
		return $feed->render();
	}

}
