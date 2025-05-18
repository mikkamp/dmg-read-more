/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const PaginationControl = ( { page, totalPages, onPageChange } ) => {
	if ( totalPages <= 1 ) {
		return null;
	}

	return (
		<div className="dmg-read-more__pagination-controls">
			<Button
				variant="secondary"
				icon="arrow-left"
				label={ __( 'Previous', 'dmg-read-more' ) }
				onClick={ () => onPageChange( page - 1 ) }
				disabled={ page <= 1 }
			/>
			<span className="dmg-read-more__pagination-label">
				{ __( 'Page', 'dmg-read-more' ) } { page } / { totalPages }
			</span>
			<Button
				variant="secondary"
				icon="arrow-right"
				label={ __( 'Next', 'dmg-read-more' ) }
				onClick={ () => onPageChange( page + 1 ) }
				disabled={ page >= totalPages }
			/>
		</div>
	);
};

export default PaginationControl;
