<?php

class RandomPagesHooks {
	/**
	 * Setups the mediawiki hook
	 */
	static function aoRandomPages() {
	  global $wgParser;
	  $wgParser->setHook('randompages', [ __class__ , 'aoRandomPagesHook']);
	}

	/**
	 * Callback that replaces &lt;randompages /&gt; wiki tag with a list of links to random wiki pages
	 *
	 * Available options:
	 *
	 * * limit int, to control how many links should be fetched randomly from the database, defaults to 150
	 * * namespace bool, true to restrict only to the global namspace, defaults to false
	 * * levels int, levels of CSS applyed to each entry, defaluts to 5
	 *
	 * Sample Usage:
	 *
	 * <code>
	 *  &lt;randompages limit="10" namespace="true" levels="10" /&gt;
	 * </code>
	 *
	 * Gets 10 random pages from the global namespace with 10 levels of style
	 *
	 * @param $text string, it should be null
	 * @param $params array, additional parameters passed as attributes to randompages tag
	 * @param $parser Parser, the Wiki Parser Object
	 */
	static function aoRandomPagesHook($text, $params, $parser) {
	  global $wgDBprefix;
	  // prevent caching for this wiki page
	  $parser->disableCache();
	  // get parameters
	  $limit = isset($params['limit']) ? (int)$params['limit'] : 150;
	  $namespaced = isset($params['namespace']) ? $params['namespace'] == 'true' : false;
	  $levels = isset($params['levels']) ? (int)$params['levels'] : 5;
	  // build sql query
	  $sql = sprintf('select * from %spage where', $wgDBprefix);
	  $sql .= $namespaced ? ' page_namespace = 0 ' : ' 1=1 ';
	  $sql .= sprintf('order by rand() limit %d', $limit);
	  // execute that.
	  $dbr = wfGetDB( DB_SLAVE );
	  $rs  = $dbr->query( $sql );
	  $buff = '<div class="randomPages">';
	  while( $row = $rs->fetchObject( $rs ) ) {
		$buff .= '<span class="randomPages_level'.rand(1,$levels).'">';
		#KKM commnet out as it is now working.   https://www.mediawiki.org/wiki/Extension_talk:SearchBox
		$title = Title::makeTitleSafe($row->page_namespace, $row->page_title);
		// manually call __toString for php < 5.2
		$buff .= sprintf('<a href="%s" title="%s">%s</a>', $title->getLocalURL(), $title->__toString(), $title->__toString());
		#$buff .= sprintf('<a href="%s" title="%s">%s</a>', $title->escapeLocalURL(), $title->__toString(), $title->__toString());
		$buff .= '</span>';
	  }
	  return $buff . '</div>';
	}
}
