<?php
namespace RobertLemke\Plugin\Blog\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "Blog".                      *
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

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * A service which can render specific views of blog related content
 *
 * @Flow\Scope("singleton")
 */
class ContentService {

	/**
	 * Renders the given Node as a teaser text with up to 600 characters, with all <p> and <a> tags removed.
	 *
	 * @param NodeInterface $node
	 * @param integer $maxCharacters Place where to truncate the string
	 * @return mixed
	 */
	public function renderTeaser(NodeInterface $node, $maxCharacters = 600) {
		$stringToTruncate = '';

		foreach ($node->getNode('main')->getChildNodes('TYPO3.Neos.NodeTypes:Text') as $contentNode) {
			foreach ($contentNode->getProperties() as $propertyValue) {
				if (!is_object($propertyValue) || method_exists($propertyValue, '__toString')) {
					$stringToTruncate .= $propertyValue;
				}
			}
		}

		$jumpPosition = mb_strpos($stringToTruncate, '<!-- read more -->');

		if ($jumpPosition !== FALSE) {
			return $this->stripUnwantedTags(substr($stringToTruncate, 0, ($jumpPosition - 1)));
		}

		$jumpPosition = mb_strpos($stringToTruncate, '</p>');
		if ($jumpPosition !== FALSE && $jumpPosition < $maxCharacters) {
			return $this->stripUnwantedTags(mb_substr($stringToTruncate, 0, $jumpPosition + 4));
		}

		if (mb_strlen($stringToTruncate) > $maxCharacters) {
			return mb_substr($this->stripUnwantedTags($stringToTruncate), 0, $maxCharacters + 1) . ' ...';
		} else {
			return $this->stripUnwantedTags($stringToTruncate);
		}

	}

	/**
	 * Extract first image from the give Node
	 *
	 * @param NodeInterface $node
	 * @return array
	 */
	public function extractFirstImage(NodeInterface $node) {
		$images = $this->extractImages($node);

		return $images === array() ? NULL : array_shift($images);
	}

	/**
	 * Extract images from the give Node
	 *
	 * @param NodeInterface $node
	 * @return array
	 */
	public function extractImages(NodeInterface $node) {
		$path = $node->getPath() . '/main';

		return $node->getNode($path)->getChildNodes('TYPO3.Neos.NodeTypes:Image');
	}

	/**
	 * If the content starts with <p> and ends with </p> these tags are stripped.
	 *
	 * @param string $content The original content
	 * @return string The stripped content
	 */
	protected function stripUnwantedTags($content) {
		$content = preg_replace('/(?:<|&lt;)\/?([a-z]+) *[^\/(?:<|&lt;)]*?(?:>|&gt;)/', '', $content);
		$content = str_replace('&nbsp;', ' ', $content);

		return trim($content);
	}
}

?>