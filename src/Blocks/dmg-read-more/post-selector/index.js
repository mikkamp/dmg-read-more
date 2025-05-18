/**
 * WordPress dependencies
 */
import { RadioControl, Spinner, TextControl } from '@wordpress/components';
import { useDebouncedInput } from '@wordpress/compose';
import { useEntityRecords } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';
import PaginationControl from './pagination-control';

const POSTS_PER_PAGE = 10;

/**
 * Check if a string is an integer
 *
 * @param {string} text
 * @return {boolean} Is an integer
 */
const isInteger = ( text ) => text == Number.parseInt( text );

const PostSelector = ( { attributes, setAttributes } ) => {
	const { readMoreId } = attributes;
	const [ page, setPage ] = useState( 1 );
	const [ search, setSearch, debouncedSearch ] = useDebouncedInput();

	// Fetch the current post ID from the editor
	const currentPostId = useSelect( ( select ) =>
		select( 'core/editor' ).getCurrentPostId()
	);

	/**
	 * Handle changing the search query
	 *
	 * @param {string} newSearch
	 */
	const onChangeSearch = ( newSearch ) => {
		setSearch( newSearch );
		setPage( 1 ); // Reset page
	};

	/**
	 * Handle selecting a post
	 *
	 * @param {string} newId
	 */
	const onChangeReadMore = ( newId ) => {
		setAttributes( { readMoreId: Number( newId ) } );
	};

	// Query arguments for fetching posts
	const queryArgs = {
		per_page: POSTS_PER_PAGE,
		page,
		order: 'desc',
		orderby: 'date',
		exclude: currentPostId ? [ currentPostId ] : [],
	};

	// Search for specific post or use a generic search
	if ( isInteger( debouncedSearch ) ) {
		queryArgs.include = [ Number( debouncedSearch ) ];
	} else if ( debouncedSearch ) {
		queryArgs.search = debouncedSearch;
		queryArgs.search_columns = [ 'post_title' ];
	}

	// Fetch posts from the data store
	const {
		hasResolved,
		records: posts = [],
		totalPages = 1,
	} = useEntityRecords( 'postType', 'post', queryArgs );

	return (
		<>
			<TextControl
				label={ __( 'Search for post', 'dmg-read-more' ) }
				help={ __( 'Enter search string or post ID', 'dmg-read-more' ) }
				value={ search }
				onChange={ onChangeSearch }
			/>
			{ ! hasResolved && <Spinner className="dmg-read-more__spinner" /> }
			{ hasResolved && ! posts?.length && (
				<p className="dmg-read-more__no-results">
					{ __( 'No results found', 'dmg-read-more' ) }
				</p>
			) }
			{ hasResolved && posts?.length > 0 && (
				<>
					<RadioControl
						label={
							search
								? __( 'Search results', 'dmg-read-more' )
								: __( 'Recent posts', 'dmg-read-more' )
						}
						selected={ readMoreId }
						options={ posts?.map( ( post ) => ( {
							label: post.title.rendered || post.id,
							value: post.id,
						} ) ) }
						onChange={ onChangeReadMore }
					/>
					<PaginationControl
						page={ page }
						totalPages={ totalPages }
						onPageChange={ setPage }
					/>
				</>
			) }
		</>
	);
};

export default PostSelector;
