<?php
/**
 * RandomCategoryItems – zeigt eine zufällige Auswahl von Seiten einer Kategorie an.
 *
 * Verwendung im Wiki:
 *   <randomcategoryitems category="Freestylekite" count="10" more="Kategorie:Freestylekite" />
 *
 * Parameter:
 *   category  – Name der Kategorie (ohne "Kategorie:"-Präfix)
 *   count     – Anzahl der anzuzeigenden Einträge (Standard: 10)
 *   more      – Ziel des "Mehr"-Links (Standard: Kategorie:<category>)
 *   morelabel – Beschriftung des "Mehr"-Links (Standard: "Alle Einträge →")
 */

use MediaWiki\Html\Html;
use MediaWiki\Parser\Parser;
use MediaWiki\Title\Title;

class RandomCategoryItems {

	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setHook( 'randomcategoryitems', [ self::class, 'render' ] );
	}

	public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
		$category  = isset( $args['category'] ) ? trim( $args['category'] ) : '';
		$count     = isset( $args['count'] )    ? max( 1, intval( $args['count'] ) ) : 10;
		$moreLabel = isset( $args['morelabel'] ) ? trim( $args['morelabel'] ) : 'Alle Einträge →';

		if ( $category === '' ) {
			return Html::element( 'p', [ 'class' => 'rci-error' ],
				'RandomCategoryItems: Kein Kategoriename angegeben.' );
		}

		if ( isset( $args['more'] ) && $args['more'] !== '' ) {
			$moreTarget = trim( $args['more'] );
		} else {
			$moreTarget = 'Kategorie:' . $category;
		}

		$parser->getOutput()->updateCacheExpiry( 86400 );

		$pages = self::getCategoryMembers( $category );

		if ( empty( $pages ) ) {
			return Html::element( 'p', [ 'class' => 'rci-empty' ],
				'Keine Einträge in dieser Kategorie gefunden.' );
		}

		shuffle( $pages );
		$selected = array_slice( $pages, 0, $count );

		$parser->getOutput()->addModuleStyles( [ 'ext.randomCategoryItems' ] );

		$html = Html::openElement( 'div', [ 'class' => 'rci-container' ] );
		$html .= Html::openElement( 'ul', [ 'class' => 'rci-list' ] );

		foreach ( $selected as $page ) {
			$title = Title::newFromText( $page );
			if ( $title === null ) {
				continue;
			}
			$link = Html::element( 'a',
				[ 'href' => $title->getLocalURL() ],
				$title->getText()
			);
			$html .= Html::rawElement( 'li', [ 'class' => 'rci-item' ], $link );
		}

		$html .= Html::closeElement( 'ul' );

		$moreTitle = Title::newFromText( $moreTarget );
		if ( $moreTitle !== null ) {
			$html .= Html::rawElement( 'div', [ 'class' => 'rci-more' ],
				Html::element( 'a',
					[ 'href' => $moreTitle->getLocalURL(), 'class' => 'rci-more-link' ],
					$moreLabel
				)
			);
		}

		$html .= Html::closeElement( 'div' );

		return $html;
	}

	private static function getCategoryMembers( string $categoryName ): array {
		$dbr = \MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );

		$categoryNameUnderscored = str_replace( ' ', '_', $categoryName );

		$res = $dbr->select(
			[ 'categorylinks', 'page', 'linktarget' ],
			[ 'page_title', 'page_namespace' ],
			[
				'lt_namespace'   => NS_CATEGORY,
				'lt_title'       => $categoryNameUnderscored,
				'page_namespace' => NS_MAIN,
			],
			__METHOD__,
			[ 'ORDER BY' => 'page_title' ],
			[
				'linktarget' => [ 'INNER JOIN', 'lt_id = cl_target_id' ],
				'page'       => [ 'INNER JOIN', 'page_id = cl_from' ],
			]
		);

		$pages = [];
		foreach ( $res as $row ) {
			$pages[] = str_replace( '_', ' ', $row->page_title );
		}

		return $pages;
	}
}
