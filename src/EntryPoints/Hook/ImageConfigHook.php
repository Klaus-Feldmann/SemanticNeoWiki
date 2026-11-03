<?php

declare( strict_types = 1 );
namespace ProfessionalWiki\NeoWiki\EntryPoints\Hook;

final class ImageConfigHook {
	public static function onMakeGlobalVariablesScript( array &$vars, $out ): void {
		$vars['wgNeoWikiProjectName'] = (string)$out->getConfig()->get( 'Sitename' );
	}
}
