<?php

declare( strict_types = 1 );
namespace NeoWikiBaseBridge;

use ProfessionalWiki\NeoWiki\Domain\Page\PagePropertyProvider;
use ProfessionalWiki\NeoWiki\Domain\Page\PagePropertyProviderContext;

final class WikidataPagePropertyProvider implements PagePropertyProvider {
	public function getProperties( PagePropertyProviderContext $context ): array {
		$value = $context->parserProperties['unlinkedwikibase_id'] ?? null;
		return is_string( $value ) && preg_match( '/\AQ[1-9][0-9]*\z/', $value ) === 1
			? [ 'aewiWikidataId' => $value ] : [];
	}
}
