<?php
/**
 * RandomCategoryItems – zeigt eine zufällige Auswahl von Seiten einer Kategorie an.
 *
 * Verwendung im Wiki:
 *   <randomcategoryitems category="Freestylekite" count="10" />
 *
 * Parameter:
 *   category    – Name der Kategorie (ohne "Kategorie:"-Präfix)
 *   count       – Anzahl der anzuzeigenden Einträge (Standard: 10)
 *   more        – Ziel des "Mehr"-Links (Standard: Kategorie:<category>)
 *   morelabel   – Beschriftung des "Mehr"-Links (Standard: "Alle Einträge →")
 *   layout      – "horizontal" (Standard) oder "vertical"
 *   border      – "yes" (Standard) oder "no"
 *   bordercolor – Farbe des Rahmens, z.B. "#cccccc" oder "red" (Standard: keiner)
 *   bgcolor     – Hintergrundfarbe, z.B. "#f0f0f0" (Standard: CSS-Variable)
 *   textcolor   – Textfarbe, z.B. "#333333" (Standard: CSS-Variable)
 *   radius      – Eckenradius: "round" (Standard, 3px), "square" (0px) oder Pixelwert z.B. "8px"
 *   fontsize    – Schriftgröße, z.B. "0.9em" oder "14px" (Standard: 0.95em)
 *   bullets     – Bullet-Punkte bei vertical: "yes" oder "no" (Standard: "no")
 */

use MediaWiki\Html\Html;
use MediaWiki\Parser\Parser;
use MediaWiki\Title\Title;

class RandomCategoryItems {

	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setHook( 'randomcategoryitems', [ self::class, 'render' ] );
	}

	public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
		$category  = isset( $args['category'] )  ? trim( $args['category'] )  : '';
		$count     = isset( $args['count'] )     ? max( 1, intval( $args['count'] ) ) : 10;
		$moreLabel = isset( $args['morelabel'] ) ? trim( $args['morelabel'] ) : 'Alle Einträge →';
		$border    = isset( $args['border'] )    ? trim( $args['border'] )    : 'yes';
		$layout    = isset( $args['layout'] )    ? trim( $args['layout'] )    : 'horizontal';

		// Styling-Parameter
		$borderColor = isset( $args['bordercolor'] ) ? trim( $args['bordercolor'] ) : '';
		$bgColor     = isset( $args['bgcolor'] )     ? trim( $args['bgcolor'] )     : '';
		$textColor   = isset( $args['textcolor'] )   ? trim( $args['textcolor'] )   : '';
		$fontsize    = isset( $args['fontsize'] )    ? trim( $args['fontsize'] )    : '';
		$radiusArg   = isset( $args['radius'] )      ? trim( $args['radius'] )      : 'round';
		$bullets     = isset( $args['bullets'] )     ? trim( $args['bullets'] )     : 'no';

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

		// CSS-Klassen für ul
		$listClasses = [ 'rci-list' ];
		if ( $layout === 'vertical' ) {
			$listClasses[] = 'rci-vertical';
		}
		if ( $border === 'no' ) {
			$listClasses[] = 'rci-no-border';
		}
		if ( $bullets === 'yes' ) {
			$listClasses[] = 'rci-bullets';
		}

		// Inline-Style für Links berechnen
		$linkStyle = self::buildLinkStyle( $border, $borderColor, $bgColor, $textColor, $fontsize, $radiusArg );

		$html = Html::openElement( 'div', [ 'class' => 'rci-container' ] );
		$html .= Html::openElement( 'ul', [ 'class' => implode( ' ', $listClasses ) ] );

		foreach ( $selected as $page ) {
			$title = Title::newFromText( $page );
			if ( $title === null ) {
				continue;
			}
			$linkAttrs = [ 'href' => $title->getLocalURL() ];
			if ( $linkStyle !== '' ) {
				$linkAttrs['style'] = $linkStyle;
			}
			$link = Html::element( 'a', $linkAttrs, $title->getText() );
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

	/**
	 * Baut den inline style-String für die Links zusammen.
	 */
	private static function buildLinkStyle(
		string $border, string $borderColor, string $bgColor,
		string $textColor, string $fontsize, string $radiusArg
	): string {
		// Nur wenn border="no" und keine individuellen Styles gesetzt: nichts tun
		if ( $border === 'no' && $borderColor === '' && $bgColor === '' && $textColor === '' && $fontsize === '' ) {
			return '';
		}

		$styles = [];

		// Eckenradius
		if ( $border !== 'no' ) {
			if ( $radiusArg === 'square' ) {
				$styles[] = 'border-radius: 0';
			} elseif ( $radiusArg === 'round' ) {
				$styles[] = 'border-radius: 3px';
			} else {
				// Direkte Angabe wie "8px" oder "50%", nur alphanumerisch+% erlauben
				$safe = preg_replace( '/[^a-zA-Z0-9.%]/', '', $radiusArg );
				if ( $safe !== '' ) {
					$styles[] = 'border-radius: ' . $safe;
				}
			}
		}

		// Rahmenfarbe → border setzen
		if ( $borderColor !== '' && $border !== 'no' ) {
			$safe = self::sanitizeColor( $borderColor );
			if ( $safe !== '' ) {
				$styles[] = 'border: 1px solid ' . $safe;
			}
		}

		// Hintergrundfarbe
		if ( $bgColor !== '' ) {
			$safe = self::sanitizeColor( $bgColor );
			if ( $safe !== '' ) {
				$styles[] = 'background-color: ' . $safe;
			}
		} elseif ( $border === 'no' ) {
			$styles[] = 'background-color: transparent';
		}

		// Textfarbe
		if ( $textColor !== '' ) {
			$safe = self::sanitizeColor( $textColor );
			if ( $safe !== '' ) {
				$styles[] = 'color: ' . $safe;
			}
		}

		// Schriftgröße
		if ( $fontsize !== '' ) {
			$safe = preg_replace( '/[^a-zA-Z0-9.%]/', '', $fontsize );
			if ( $safe !== '' ) {
				$styles[] = 'font-size: ' . $safe;
			}
		}

		return implode( '; ', $styles );
	}

	/**
	 * Einfache Sanitierung von Farbwerten (hex, rgb, benannte Farben).
	 */
	private static function sanitizeColor( string $color ): string {
		$color = trim( $color );
		// Erlaubt: #fff, #ffffff, rgb(...), rgba(...), benannte Farben (nur Buchstaben)
		if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $color ) ) {
			return $color;
		}
		if ( preg_match( '/^rgba?\(\s*[\d.,\s%]+\)$/', $color ) ) {
			return $color;
		}
		if ( preg_match( '/^[a-zA-Z]{2,30}$/', $color ) ) {
			return $color;
		}
		return '';
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
